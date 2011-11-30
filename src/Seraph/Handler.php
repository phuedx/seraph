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
    const POLL_TIMEOUT = 250; // 250 (ms)

    protected $id;
    protected $signals;
    protected $context;
    protected $poll;
    protected $outboundSockets;

    public function __construct($id, Seraph_Signal_Collection $signals) {
        if ( ! trim($id)) {
            throw new InvalidArgumentException("\"{$id}\" isn't a good ID for a Seraph environment.");
        }

        $this->id              = $id;
        $this->signals         = $signals;
        $this->dispatcher      = new Seraph_Request_Dispatcher();
        $this->context         = new ZMQContext();
        $this->poll            = new ZMQPoll();
        $this->outboundSockets = array();
    }

    public function setDispatcher(Seraph_Request_Dispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    public function setContext(ZMQContext $context) {
        $this->context = $context;

        return $this;
    }

    public function setPoll(ZMQPoll $poll) {
        $this->poll = $poll;

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
        $this->dispatcher->registerApplication($application);

        return $this;
    }

    // public function removeApplication(Seraph_Application_Interface $application) { }

    public function run() {
        while (true) {
            $this->runOnce();
        }
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
            $name = $socket->getSockOpt(ZMQ::SOCKOPT_IDENTITY);

            assert(strlen($name));
            assert(isset($this->outboundSockets[$name]));

            $outboundSocket = $this->outboundSockets[$name];

            $this->dispatcher->onRawRequest($socket, $outboundSocket, $name);

            $this->signals->emit('seraph.handler.raw_request', $socket, $outboundSocket, $name);
        }

        assert(count($writeable) == 0);
    }
}
