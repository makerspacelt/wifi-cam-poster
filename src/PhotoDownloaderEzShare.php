<?php


class PhotoDownloaderEzShare {
	
	private $ezHost;
	private $ezDir;
	private $photoPath;
	private $cardStatus;
	
	public function __construct($ezHost, $ezDir, $photoPath)
	{
		$this->ezHost = $ezHost;
		$this->ezDir = $ezDir;
		$this->photoPath = $photoPath;
		ini_set('default_socket_timeout', 3); // timeout for controll requests
	}
	
	public function run($callback)
	{
		$this->callback = $callback;
		
		print("Starting photo downloader\n");
		while (true) {
			if ($this->isThereSomethingNew()) {
				$this->download();
			} else {
				sleep(3);
			}
		}
	}
	
	private function isThereSomethingNew()
	{
		global $file_list;
		$file_list = $this->getFileList();
		$last_match = end(array_values($file_list));
		return ($last_match != $this->getLastFilename());
	}
	
	private function download()
	{
		global $file_list;
		$last_filename = $this->getLastFilename();
		if (in_array($last_filename, $file_list)) {
			foreach ($file_list as $k => $filename) {
				unset($file_list[$k]);
				if ($filename == $last_filename) {
					break;
				}
			}
		} else {
			if (!empty($file_list)) {
				printf("WARN: last file(%s) not found, downloading all photos\n", $last_filename);
			}
		}
		return $this->downloadAllFiles($file_list);
	}
	
	private function downloadAllFiles($file_list)
	{
		foreach ($file_list as $filename) {
			if ( !$this->downloadFile($filename) ) {
				return false;
			}
		}
		return true;
	}
	
	private function downloadFile($filename)
	{
		printf("Downloading %s ... ", $filename);
		$url = sprintf('http://%s/download?num=1&fdir=%s&folderFlag=0&fn1=%s'
			, $this->ezHost
			, $this->ezDir
			, $filename
		);
		$cmd = sprintf('wget --timeout 30 -qO - "%s" | tar x -C %s/ 2>&1'
			, $url
			, $this->photoPath
		);
		shell_exec($cmd);

		$localFile = $this->photoPath.'/'.$filename;
		if (!is_file($localFile)) {
			print("[FAIL] no file\n");
			return false;
		}
		if (filesize($localFile) < 1 * 1024 * 1024) { // 1MB
			print("[FAIL] to small\n");
			return false;
		}
		chmod($localFile, 0644);
		$this->setLastFilename($filename);
		print(" [ OK ]\n");
		
		call_user_func($this->callback, $localFile);
		return true;
	}
	
	private function getLastFilename()
	{
		if (is_file($this->photoPath.'/last_downloaded_photo')) {
			return trim(file_get_contents(
				$this->photoPath.'/last_downloaded_photo'
			));
		}
	}
	
	private function setLastFilename($filename)
	{
		file_put_contents($this->photoPath.'/last_downloaded_photo', $filename);
	}
	
	private function getFileList()
	{
		$url = sprintf('http://%s/photo?fdir=%s&vtype=0&ftype=0'
			, $this->ezHost
			, $this->ezDir
		);
		$list_html = @file_get_contents($url);

		// empty html, card offline?
		if (empty($list_html)) {
			$this->cardStatus(false);
			return array();
		}
		// html not empty, so card is online.
		$this->cardStatus(true);
		// no links found, card empty?
		preg_match_all('#thumbnail\?fname=([^&]*)&fdir=[^&]*&ftype=0&time=[0-9]*#ms', $list_html, $matches);
		if (empty($matches[1])) {
			return array();
		}

		return array_reverse($matches[1]);
	}
	
	private function cardStatus($status = null)
	{
		if ($status === null) {
			return $this->cardStatus;
		}
		if ($this->cardStatus !== $status) {
			if ($status) {
				print("Camera got online\n");
			} else {
				print("Camera got offline\n");
			}
			$this->cardStatus = $status;
		}
	}

}
