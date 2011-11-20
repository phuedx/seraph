<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_HandlerTest extends PHPUnit_Framework_TestCase
{
    protected $handler;

    public function setUp() {
        $this->id = 'ID';

        $this->mockSignals        = $this->getMock('Seraph_Signal_Collection');
        $this->mockContext        = $this->getMock('ZMQContext');
        $this->mockPoll           = $this->getMock('ZMQPoll');
        $this->mockInboundSocket  = $this->getMockBuilder('ZMQSocket')
            ->setConstructorArgs(array($this->mockContext, ZMQ::SOCKET_PULL))
            ->getMock();

        $this->mockOutboundSocket = $this->getMockBuilder('ZMQSocket')
            ->setConstructorArgs(array($this->mockContext, ZMQ::SOCKET_PUB))
            ->getMock();

        $this->handler = new Seraph_Handler($this->id, $this->mockSignals, $this->mockContext, $this->mockPoll);
    }

    public static function stringIsEmptyProvider() {
        return array(
            array(''),
            array('    '),
        );
    }

    /**
     * @dataProvider stringIsEmptyProvider
     * @expectedException InvalidArgumentException
     */
    public function test__constructThrowsWhenIDIsEmpty($id) {
        $handler = new Seraph_Handler($id, $this->mockSignals, $this->mockContext, $this->mockPoll);
    }

    /**
     * @dataProvider stringIsEmptyProvider
     * @expectedException InvalidArgumentException
     */
    public function testRegisterServerThrowsWhenNameIsEmpty($name) {
        $senderDSN   = 'SENDER_DSN';
        $receiverDSN = 'RECEIVER_DSN';

        $this->handler->registerServer($name, $senderDSN, $receiverDSN);
    }

    public function testRegister() {
        $name        = 'NAME';
        $senderDSN   = 'SENDER_DSN';
        $receiverDSN = 'RECEIVER_DSN';

        $this->mockContext->expects($this->at(0))
            ->method('getSocket')
            ->with(ZMQ::SOCKET_PULL)
            ->will($this->returnValue($this->mockInboundSocket));

        $this->mockInboundSocket->expects($this->once())
            ->method('setSockOpt')
            ->with(ZMQ::SOCKOPT_IDENTITY, $name);

        $this->mockInboundSocket->expects($this->once())
            ->method('connect')
            ->with($senderDSN);

        $this->mockContext->expects($this->at(1))
            ->method('getSocket')
            ->with(ZMQ::SOCKET_PUB)
            ->will($this->returnValue($this->mockOutboundSocket));

        $this->mockOutboundSocket->expects($this->once())
            ->method('setSockOpt')
            ->with(ZMQ::SOCKOPT_IDENTITY, $this->id);

        $this->mockOutboundSocket->expects($this->once())
            ->method('connect')
            ->with($receiverDSN);

        $this->mockPoll->expects($this->once())
            ->method('add')
            ->with($this->mockInboundSocket, ZMQ::POLL_IN);

        $this->handler->registerServer($name, $senderDSN, $receiverDSN);
    }
}
