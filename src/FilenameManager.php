<?php


class FilenameManager {

	public function fixFileName($file)
	{
		$newName = $this->getNewFileName($file);
		if ( $newName ) {
			if (!file_exists($newName) || md5_file($file) == md5_file($newName)) {
				if ($file != $newName) {
					printf("renaming => %s => %s\n", basename($file), basename($newName) );
					rename($file, $newName);
				}
				return $newName;
			} else {
				printf("target file exists => %s => %s\n", basename($file), basename($newName) );
			}
		} else {
			printf("failed to rename  %s \n", basename($file) );
		}
		return false;
	}
	
	private function getNewFileName($file)
	{
		$cmds[1] = sprintf("/usr/bin/identify -verbose '%s' | /bin/grep 'Date Time Original'", $file);
		$cmds[2] = sprintf("/bin/ls -og --time-style=+%%Y:%%m:%%d\\ %%H:%%M:%%S '%s' | /usr/bin/cut -d' ' -f4,5", $file);

		foreach ($cmds as $cmd) {
			$tmp = shell_exec($cmd);
			if (preg_match("/20([0-9][0-9]):([01][0-9]):([0-3][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])/Uis",$tmp,$res)) {
				$target = str_replace(" ", "_", basename($file));
				$target = str_replace(".JPG", ".jpg", $target);
				$filename_part_date = sprintf("%02d%02d%02d_%02d%02d%02d", $res[1], $res[2], $res[3], $res[4], $res[5], $res[6]);
				if (strncmp("P".$filename_part_date, $target, 14) == 0) {
					return $file;
				} elseif (strncmp("IMG_20".$filename_part_date, $target, 17) == 0) {
					return sprintf("%s/P%s.jpg", dirname($file), $filename_part_date);
				} else {
					return sprintf("%s/P%s_%s", dirname($file), $filename_part_date, $target);
				}
			}
		}
		return false;
	}

}
