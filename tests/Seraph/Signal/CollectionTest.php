<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Signal_CollectionTest extends PHPUnit_Framework_TestCase
{
    protected $signals;

    public function setUp() {
        $this->signals = new Seraph_Signal_Collection();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOffsetSetThrowsAnExceptionWhenValueIsntASignal() {
        $this->signals[0] = "THIS ISN'T A SIGNAL";
    }

    public function testEmit() {
        $string = 'THIS IS CALLABLE';

        $mockSignal = $this->getMock('Seraph_Signal');
        $mockSignal->expects($this->once())
            ->method('emit')
            ->with($string);

        $this->signals['mock'] = $mockSignal;
        $this->signals->emit('mock', $string);
    }
}
