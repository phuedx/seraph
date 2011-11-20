<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Request_Dispatcher
{
    const SERVER_HEADER_NAME = 'X-M2-Server';

    protected $frontController;
    protected $request;
    protected $response;

    public function __construct(Seraph_FrontController_Interface $frontController, Seraph_Request $request = null, Seraph_Response $response = null) {
        if ( ! $request) {
            $request = new Seraph_Request();
        }

        if ( ! $response) {
            $response = new Seraph_Response();
        }

        $this->frontController = $frontController;
        $this->request         = $request;
        $this->response        = $response;
    }

    public function onRawRequest(ZMQSocket $inboundSocket, ZMQSocket $outboundSocket, $server) {
        $request    = $this->request;
        $rawRequest = $inboundSocket->recv();
        $response   = $this->response;

        $request->fromRawRequest($rawRequest)
            ->setHeader(self::SERVER_HEADER_NAME, $server);

        $this->response->fromRequest($request);

        $this->frontController->onRequest($request, $response); // It's dispatchin' time!

        $outboundSocket->send($response);
    }
}
