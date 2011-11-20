<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

class Seraph_Signal
{
    protected $slots = array();

    public function connect($slot) {
        if ( ! is_callable($slot)) {
            throw new InvalidArgumentException("The slot isn't callable.");
        }

        if ( ! in_array($slot, $this->slots)) {
            $this->slots[] = $slot;
        }
    }

    public function disconnect($slot) {
        foreach ($this->slots as $i => $value) {
            if ($value === $slot) {
                unset($this->slots[$i]);

                return;
            }
        }
    }

    public function emit() {
        $args = func_get_args();

        foreach ($this->slots as $slot) {
            call_user_func_array($slot, $args);
        }
    }
}
