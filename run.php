<?php

require __DIR__.'/vendor/autoload.php';


if (!defined('REAL_DIR')) {
	define('REAL_DIR', __DIR__);
}
if (!is_file(REAL_DIR.'/Config.php')) {
	copy(__DIR__.'/Config.dist.php', REAL_DIR.'/Config.php');
	print("New Config.php file was generated for you\n");
	print("Fill in all data required and restart application\n");
	die();
}
require REAL_DIR.'/Config.php';


$photoDownloader = new PhotoDownloaderEzShare(Config::$ezHost
                                            , Config::$ezDir
                                            , Config::$photoPath
);
$filenameManager = new FilenameManager();
$poster = new PosterTumblr(Config::$blogName
                         , Config::$consumerKey
                         , Config::$consumerSecret
                         , Config::$token
                         , Config::$tokenSecret
);


$photoDownloader->run('photoProcessor');

function photoProcessor($file) {
	global $filenameManager, $poster;
	$file = $filenameManager->fixFileName($file);
	printf("Posting %s to tumblr ... ", basename($file));
	try {
		$poster->postPhoto($file);
		print("[ OK ]\n");
	} catch (Tumblr\API\RequestException $e) {
		printf("[FAIL] %s\n", $e->getMessage());
	}
}
