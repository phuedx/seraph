<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

require_once dirname(__FILE__) . '/vendor/tnetstring/tnetstring.php';

function __seraph_autoload($class) {
    $path = dirname(__FILE__) . '/src/' . str_replace('_', '/', $class) . '.php';

    return is_file($path) ? require_once $path : false;
}

spl_autoload_register('__seraph_autoload');
