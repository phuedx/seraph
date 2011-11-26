<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Request_DispatcherTest extends PHPUnit_Framework_TestCase
{
    protected $mockApplication;
    protected $mockRequest;
    protected $mockResponse;
    protected $dispatcher;

    public function setUp() {
        // Mocks
        $this->mockApplication = $this->getMock('Seraph_Application_Interface');
        $this->mockRequest     = $this->getMock('Seraph_Request');
        $this->mockResponse    = $this->getMock('Seraph_Response');

        $this->dispatcher = new Seraph_Request_Dispatcher($this->mockApplication, $this->mockRequest, $this->mockResponse);
    }

    public function testOnRawRequest() {
        $context    = new ZMQContext();
        $rawRequest = 'RAW REQUEST';
        $server     = 'SERVER';
        
        $mockInboundSocket = $this->getMockBuilder('ZMQSocket')
            ->setConstructorArgs(array($context, ZMQ::SOCKET_PULL))
            ->getMock();

        $mockInboundSocket->expects($this->once())
            ->method('recv')
            ->will($this->returnValue($rawRequest));

        $this->mockRequest->expects($this->once())
            ->method('fromRawRequest')
            ->with($rawRequest)
            ->will($this->returnSelf());

        $this->mockRequest->expects($this->once())
            ->method('setHeader')
            ->with(Seraph_Request_Dispatcher::SERVER_HEADER_NAME, $server);

        $this->mockApplication->expects($this->once())
            ->method('onRequest')
            ->with($this->mockRequest, $this->mockResponse);

        $this->mockResponse->expects($this->once())
            ->method('fromRequest')
            ->with($this->mockRequest);

        $mockOutboundSocket = $this->getMockBuilder('ZMQSocket')
            ->setConstructorArgs(array($context, ZMQ::SOCKET_PUB))
            ->getMock();

        $mockOutboundSocket->expects($this->once())
            ->method('send')
            ->with($this->mockResponse);

        $this->dispatcher->onRawRequest($mockInboundSocket, $mockOutboundSocket, $server);
    }
}
