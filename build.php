#!/usr/bin/php -d phar.readonly=0
<?php

$phar_name = 'wifi-cam-poster.phar';

if (!is_file('./composer.phar')) {
	shell_exec("php -r \"readfile('https://getcomposer.org/installer');\" | php");
}

shell_exec('./composer.phar update');

if (is_file($phar_name)) {
    unlink($phar_name);
}

$p = new Phar($phar_name, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $phar_name);
$p->startBuffering();

$p->setStub('#!/usr/bin/env php
<?php
Phar::mapPhar();
echo "Build: '.$phar_name.' @'.date("Y-m-d").'\n";
define("REAL_DIR", __DIR__);
include "phar://'.$phar_name.'/run.php";
__HALT_COMPILER();?>');


$p->buildFromDirectory('./', '#/Config.dist.php#');
$p->buildFromDirectory('./', '#/run.php#');
$p->buildFromDirectory('./', '#/LICENSE#');
$p->buildFromDirectory('./', '#/README.md#');
$p->buildFromDirectory('./', '#/vendor/(.*)#');
$p->buildFromDirectory('./', '#/src/(.*)#');


$p->stopBuffering();


chmod($phar_name, 0755);

