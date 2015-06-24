<?php

/* 
 * Copyright (c) 2015, developer
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

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
