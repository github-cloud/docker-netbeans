<?php

require 'vendor/autoload.php';

use Tokusa\FTP\FTPClient;
use Tokusa\FTP\FTPHost;
use Tokusa\FTP\FTPAccount;

$server = new FTPHost([
    "host" => "clavor.xsrv.jp"
]);

$account = new FTPAccount([
    "username" => "grattie@clavor.xsrv.jp",
    "password" => "0qww294e",
]);

$ftp = new FTPClient($server, $account);

$ftp->connect()
    ->pwd()
    ->cd("clavor.xsrv.jp")
    ->rawlist()
    ->mkdir("ftpsample")
    ->rawlist()
    ->rename("ftpsample", "ftpsample2")
    ->rawlist()
    ->rmdir("ftpsample2")
    ->rawlist()
    ->close();
