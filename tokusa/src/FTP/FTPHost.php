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

use Tokusa\Lib\DataInterface;
use Tokusa\Lib\DataTrait;

/**
 * Description of FTPServer
 *
 * @author Ryoh Kawai
 */
class FTPHost implements DataInterface
{
    use DataTrait;

    private $host;
    private $port;
    private $withSSL;
    private $defaults = [
        "host" => null,
        "port" => 21,
        "withSSL" => true,
    ];

    public function connect($timeout = 60) {
        return $this->withSSL ? $this->connectWithSSL($timeout) : $this->connectNoSSL($timeout);
    }

    private function connectNoSSL($timeout) {
        return ftp_connect($this->host, $this->port, $timeout);
    }

    private function connectWithSSL($timeout) {
        return ftp_ssl_connect($this->host, $this->port, $timeout);
    }

}
