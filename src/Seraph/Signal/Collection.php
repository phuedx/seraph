<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Signal_Collection implements ArrayAccess
{
    protected $signals = array();

    /**
     * Checks whether or not the signal is part of the collection and, if so,
     * emits it.
     *
     * @convenience
     */
    public function emit($signal) {
        if ( ! isset($this->signals[$signal])) {
            return;
        }

        $callable = array($this->signals[$signal], 'emit');
        $args     = array_slice(func_get_args(), 1);

        call_user_func_array($callable, $args);
    }

    // ArrayAccess implementation
    public function offsetExists($offset) {
        return isset($this->signals[$offset]);
    }

    public function offsetGet($offset) {
        return $this->signals[$offset];
    }

    public function offsetSet($offset, $value) {
        if ( ! $value instanceof Seraph_Signal) {
            throw new InvalidArgumentException(
                'The "Seraph_Signal_Collection" class can only have instances of the "Seraph_Signal" class added to it.'
            );
        }

        $this->signals[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->signals[$offset]);
    }
}
