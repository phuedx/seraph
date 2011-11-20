<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

require_once dirname(__FILE__) . '/fixtures/simple_interface.php';

class Seraph_SignalTest extends PHPUnit_Framework_TestCase
{
    protected $signal;

    public function setUp() {
        $this->signal = new Seraph_Signal();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConnectThrowsWhenSlotIsntCallable() {
        $this->signal->connect("THIS ISN'T CALLABLE");
    }

    public function testEmit() {
        $string = 'THIS IS CALLABLE';

        $mockSlot = $this->getMock('Simple');
        $mockSlot->expects($this->once())
            ->method('call')
            ->with($string);

        $this->signal->connect(array($mockSlot, 'call'));
        $this->signal->emit($string);
    }

    public function testDisconnect() {
        $string = 'THIS IS FALSE';

        $mockSlot = $this->getMock('Simple');
        $mockSlot->expects($this->never())
            ->method('call');

        $callable = array($mockSlot, 'call');

        $this->signal->connect($callable);
        $this->signal->disconnect($callable);
        $this->signal->emit($string);
    }
}
