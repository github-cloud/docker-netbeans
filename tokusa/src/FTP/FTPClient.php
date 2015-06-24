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

namespace Tokusa\FTP;

use Tokusa\FTP\FTPHost;
use Tokusa\FTP\FTPAccount;
use Tokusa\FTP\FTPConnectionException;

/**
 * Description of FTPClient
 *
 * @author developer
 */
class FTPClient {

    private $server;
    private $account;
    private $connId;
    private $autoPASV;
    private $mode;

    public function __construct(FTPHost $server, FTPAccount $account, $autPASV = true, $mode = FTP_ASCII) {
        $this->server = $server;
        $this->account = $account;
        $this->autoPASV = $autPASV;
        $this->mode = $mode;
    }

    public function connect($timeout = 60) {
        $this->connId = $this->server->connect($timeout);
        $loginResult = ftp_login($this->connId, $this->account->username, $this->account->password);
        if (!$this->connId || !$loginResult) {
            throw new FTPConnectionException(
            sprintf("FTP connection has failed. Attempt connect to %s for user %s", $this->server->host, $this->account->username));
        }

        if ($this->autoPASV) {
            $this->pasv();
        }

        return $this;
    }

    public function pasv() {
        $result = ftp_pasv($this->connId, true);
        if (!$result) {
            throw new FTPConnectionException(sprintf("PASV Connection failed."));
        }

        return $this;
    }

    public function mode($mode) {
        if (!($mode === FTP_ASCII) && !($mode === FTP_BINARY)) {
            throw new \Psr\Log\InvalidArgumentException(sprintf("Invalid FTP Transfer mode. [%s]", $mode));
        }

        $this->mode = $mode;

        return $this;
    }

    public function put($remote, $local) {
        $result = ftp_put($this->connId, $remote, $local, $this->mode);
        if (!$result) {
            throw new FTPConnectionException(
            sprintf("There was a problem while uploading. [%s]", $local));
        }

        return $this;
    }

    public function get($local, $remote) {
        $result = ftp_get($this->connId, $local, $remote, $this->mode);
        if (!$result) {
            throw new FTPConnectionException(
            sprintf("There was a problem while downloading. [%s]", $remote));
        }

        return $this;
    }

    public function chdir($directory) {
        $result = ftp_chdir($this->connId, $directory);
        if (!$result) {
            throw new FTPConnectionException(
            sprintf("Couldn't change directory. [%s]", $directory));
        }

        return $this;
    }

    public function cd($directory) {
        $this->chdir($directory);

        return $this;
    }

    public function mkdir($directory) {
        $result = ftp_mkdir($this->connId, $directory);
        if (!$result) {
            throw new FTPConnectionException(
            sprintf("There was a problem while creating directory. [%s]", $directory));
        }

        return $this;
    }

    public function delete($path) {
        $result = ftp_delete($this->connId, $path);
        if (!$result) {
            throw new FTPConnectionException(sprintf("Couldn't delete file or directory. [%s]", $path));
        }

        return $this;
    }

    public function del($path) {
        $this->delete($path);

        return $this;
    }

    public function chmod($mode, $filename) {
        $result = ftp_chmod($this->connId, $mode, $filename);
        if (!$result) {
            throw new FTPConnectionException(sprintf("Couldn't change mode. [%s]", $filename));
        }

        return $this;
    }

    public function rename($oldname, $newname) {
        $result = ftp_rename($this->connId, $oldname, $newname);
        if (!$result) {
            throw new FTPConnectionException(sprintf("There was a problem while renaming %s to %s", $oldname, $newname));
        }

        return $this;
    }

    public function rmdir($directory) {
        $result = ftp_rmdir($this->connId, $directory);
        if (!$result) {
            throw new FTPConnectionException(sprintf("There was a problem while deleting %s.", $directory));
        }

        return $this;
    }

    public function rawlist($directory = ".", $recursive = false) {
        $results = ftp_rawlist($this->connId, $directory, $recursive);
        print_r($results);

        return $this;
    }

    public function pwd() {
        $string = ftp_pwd($this->connId);

        echo $string;

        return $this;
    }

    public function close() {
        ftp_close($this->connId);
    }

}
