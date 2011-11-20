<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Response
{
    protected $uuid;
    protected $listenerIDs;
    protected $statusCode;
    protected $reasonPhrase;
    protected $headers;
    protected $body;

    public function fromRequest(Seraph_Request $request) {
        $listenerID = $request->getListenerID();

        $this->uuid         = $request->getUUID();
        $this->listenerIDs  = array($listenerID);
        $this->statusCode   = 200;
        $this->reasonPhrase = 'OK';
        $this->headers      = array();
        $this->body         = '';
    }

    public function getUUID() {
        return $this->uuid;
    }
    
    public function getListenerIDs() {
        return $this->listenerIDs;
    }

    public function setHeader($name, $value) {
        $this->headers[$name] = $value;
    }

    public function hasHeader($name) {
        return isset($this->headers[$name]);
    }

    public function getHeader($name, $defaultValue = null) {
        return isset($this->headers[$name]) ? $this->headers[$name] : $defaultValue;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    // TODO Move to Seraph_Response_Writer_HTTP
    public function __toString() {
        $listenerIDs = implode(' ', $this->listenerIDs);
        $listenerIDs = tnetstring_encode($listenerIDs);

        $this->headers['Content-Length'] = strlen($this->body);

        $headers = '';

        foreach ($this->headers as $name => $value) {
            $headers .= "$name: $value\r\n";
        }

        $http = "HTTP/1.1 {$this->statusCode} {$this->reasonPhrase}\r\n{$headers}\r\n\r\n{$this->body}\r\n";

        return "{$this->uuid} {$listenerIDs} $http";
    }
}
