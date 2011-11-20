<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_RequestTest extends PHPUnit_Framework_TestCase
{
    protected $request;
    
    public function setUp() {
        $this->request = new Seraph_Request();
    }
    
    public static function stringIsInvalidProvider() {
        return array(
            array(''),
            array('    '),
            array('UUID 1 / BAD_NETSTRING'),
            array('UUID 1 / 18:{"Host":"localhost,0:,'),
        );
    }
    
    /**
     * @dataProvider stringIsInvalidProvider
     * @expectedException InvalidArgumentException
     */
    public function testFromRawRequestThrowsWhenStringIsInvalid($string) {
        $this->request->fromRawRequest($string);
    }
    
    public function testFromRawRequest() {
        $string = 'UUID 1 / 20:{"Host":"localhost"},0:,';
        
        $this->request->fromRawRequest($string);
        
        $this->assertEquals('UUID', $this->request->getUUID());
        $this->assertEquals(1, $this->request->getListenerID());
        $this->assertEquals('/', $this->request->getPath());
        $this->assertEquals('localhost', $this->request->getHeader('Host'));
        $this->assertEquals('', $this->request->getBody());
    }
}
