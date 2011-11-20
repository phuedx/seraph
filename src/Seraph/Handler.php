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

    public function __construct($id, Seraph_Signal_Collection $signals, ZMQContext $context = null, ZMQPoll $poll = null) {
        if ( ! trim($id)) {
            throw new InvalidArgumentException("\"{$id}\" isn't a good ID for a Seraph environment.");
        }

        if ( ! $context) {
            $context = new ZMQContext();
        }

        if ( ! $poll) {
            $poll = new ZMQPoll();
        }

        $this->id              = $id;
        $this->signals         = $signals;
        $this->context         = $context;
        $this->poll            = $poll;
        $this->outboundSockets = array();
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

    // TODO public function removeServer($name) { }

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

            assert(isset($this->outboundSockets[$name]));

            $this->signals->emit('seraph.handler.raw_request', $socket, $this->outboundSockets[$name], $name);
        }

        assert(count($writeable) == 0);
    }
}
