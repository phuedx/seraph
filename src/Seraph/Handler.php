<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Handler
{
    const POLL_TIMEOUT       = 250; // 250 (ms)
    const SERVER_HEADER_NAME = 'X-Mongrel2-Server';

    protected $id;
    protected $signals;
    protected $applications;
    protected $context;
    protected $poll;
    protected $outboundSockets;

    public function __construct($id, Seraph_Signal_Collection $signals) {
        if ( ! trim($id)) {
            throw new InvalidArgumentException("\"{$id}\" isn't a good ID for a Seraph environment.");
        }

        $this->id              = $id;
        $this->signals         = $signals;
        $this->context         = new ZMQContext();
        $this->poll            = new ZMQPoll();
        $this->request         = new Seraph_Request();
        $this->response        = new Seraph_Response();
        $this->outboundSockets = array();
        $this->applications    = array();
    }

    public function setContext(ZMQContext $context) {
        $this->context = $context;

        return $this;
    }

    public function setPoll(ZMQPoll $poll) {
        $this->poll = $poll;

        return $this;
    }

    public function setRequest(Seraph_Request $request) {
        $this->request = $request;

        return $this;
    }

    public function setResponse(Seraph_Response $response) {
        $this->response = $response;

        return $this;
    }

    public function registerServer($name, $senderDSN, $receiverDSN) {
        if ( ! trim($name)) {
            throw new InvalidArgumentException("\"{$name}\" isn't a good name for a Mongrel2 server.");
        }

        $inboundSocket = $this->context->getSocket(ZMQ::SOCKET_PULL, null);
        $inboundSocket->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $name);
        $inboundSocket->connect($senderDSN);

        $outboundSocket = $this->context->getSocket(ZMQ::SOCKET_PUB, null);
        $outboundSocket->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $this->id);
        $outboundSocket->connect($receiverDSN);

        $this->poll->add($inboundSocket, ZMQ::POLL_IN);

        $this->outboundSockets[$name] = $outboundSocket;

        return $this;
    }

    // public function removeServer($name) { }

    public function registerApplication(Seraph_Application_Interface $application) {
        if ( ! in_array($application, $this->applications)) {
            $this->applications[] = $application;
        }

        return $this;
    }

    // public function removeApplication(Seraph_Application_Interface $application) { }

    public function dispatch(Seraph_Request $request, Seraph_Response $response) {
        foreach ($this->applications as $application) {
            $application->onRequest($request, $response);
        }

        return $this;
    }

    public function run() {
        while (true) {
            $this->runOnce();
        }
    }

    protected function onError($error) {
        $this->signals->emit('seraph.handler.error', $error);
    }

    protected function onReadable(ZMQSocket $readable) {
        $name = $readable->getSockOpt(ZMQ::SOCKOPT_IDENTITY);

        assert(strlen($name));
        assert(isset($this->outboundSockets[$name]));

        $rawRequest = $readable->recv();

        $this->request->fromRawRequest($rawRequest)
            ->setHeader(self::SERVER_HEADER_NAME, $name);

        $this->response->fromRequest($this->request);

        // It's dispatchin' time!
        $this->dispatch($this->request, $this->response);

        $outboundSocket = $this->outboundSockets[$name];
        $outboundSocket->send($this->response);
    }

    /**
     * @emit seraph.handler.error
     * @emit seraph.handler.raw_request
     */
    protected function runOnce() {
        $readable = array();
        $writeable = array();
        $count    = $this->poll->poll($readable, $writeable, self::POLL_TIMEOUT);

        if ($lastErrors = $this->poll->getLastErrors()) {
            foreach ($lastErrors as $error) {
                $this->signals->emit('seraph.handler.error', $error);
            }
        }

        if ( ! $count) {
            return;
        }

        foreach ($readable as $socket) {
            $this->onReadable($socket);
        }

        assert(count($writeable) == 0);
    }
}
