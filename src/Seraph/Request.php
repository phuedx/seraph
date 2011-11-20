<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Request
{
    protected $uuid;
    protected $listenerID;
    protected $path;
    protected $headers;
    protected $body;
    protected $method;

    public function fromRawRequest($string) {
        // TODO Extract method or, one day, `goto`
        if ( ! trim($string)) {
            throw new InvalidArgumentException("\"$string\" certainly isn't a Mongrel2 request.");
        }

        list ($uuid, $listenerID, $path, $remaining) = explode(' ', $string, 4);
        list ($headers, $body)                       = tnetstring_decode($remaining);

        if ($error = tnetstring_last_error()) {
            tnetstring_clear_last_error();

            throw new InvalidArgumentException("\"$string\" certainly isn't a Mongrel2 request.");
        }

        $headers = json_decode($headers, true);

        if ($headers === null) {
            throw new InvalidArgumentException("\"$string\" certainly isn't a Mongrel2 request.");
        }

        $this->uuid       = $uuid;
        $this->listenerID = intval($listenerID);
        $this->path       = $path;
        $this->headers    = $headers;
        $this->body       = $body;

        return $this;
    }

    public function getUUID() {
        return $this->uuid;
    }

    public function getListenerID() {
        return $this->listenerID;
    }

    public function getPath() {
        return $this->path;
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

    public function getBody() {
        return $this->body;
    }
}
