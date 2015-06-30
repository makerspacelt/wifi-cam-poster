#!/usr/bin/php
<?php

chdir(__DIR__);

$sd_host = '192.168.0.1:8080';
ini_set('default_socket_timeout', 3); // timeout for controll requests
$status = false;


print("Starting photo downloader\n");
while (true) {
	if (is_there_something_new()) {
		download();
	} else {
		sleep(5);
	}
}
print("DONE\n");


function is_there_something_new()
{
	global $file_list;
	$file_list=get_file_list();
	$last_match=end(array_values($file_list));
	return ($last_match != get_last_filename());
}
function download()
{
	global $file_list;
	$last_filename = get_last_filename();
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
	return download_all($file_list);
}
function download_all($file_list)
{
	foreach ($file_list as $filename) {
		if ( !download_file($filename) ) {
			return false;
		}
	}
	return true;
}
function download_file($filename)
{
	global $sd_host;
	printf("Downloading %s ... ", $filename);
	$url = sprintf('http://%s/download?num=1&fdir=100CANON&folderFlag=0&fn1=%s',$sd_host, $filename);
	$cmd = sprintf('wget --timeout 30 -qO - "%s" | tar x 2>&1', $url);
	shell_exec($cmd);

	if (!is_file($filename)) {
		print("[FAIL] no file\n");
		return false;
	}
	if (filesize($filename) < 1 * 1024 * 1024) { // 1MB
		print("[FAIL] to small\n");
		return false;
	}
	chmod($filename, 0644);
	set_last_filename($filename);
	print(" [ OK ]\n");
	return true;
}
function get_last_filename()
{
	if (is_file('last_downloaded_photo')) {
		return trim(file_get_contents('last_downloaded_photo'));
	}
}
function set_last_filename($filename)
{
	file_put_contents('last_downloaded_photo', $filename);
}
function get_file_list()
{
	global $sd_host, $status;
	$list_html = @file_get_contents(sprintf('http://%s/photo?fdir=100CANON&vtype=0&ftype=0',$sd_host));
	if (empty($list_html)) {
		if ($status) {
			print("Camera got offline\n");
			$status = false;
		}
		//print("DEBUG: Failed to get files list from sdcard. is camera offline?\n");
		return array();
	}

	preg_match_all('#thumbnail\?fname=([^&]*)&fdir=[^&]*&ftype=0&time=[0-9]*#ms', $list_html, $matches);
	if (empty($matches[1])) {
		print("ERROR: no files fond.\n");
		return array();
	}

	if (!$status) {
		print("Camera got online\n");
		$status = true;
	}
	return array_reverse($matches[1]);
}

