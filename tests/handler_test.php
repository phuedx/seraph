<?php

/**
 * This file is part of the Seraph project and is copyright
 *
 * (c) 2011 Sam Smith <me@phuedx.com>.
 *
 * Please refer the to LICENSE file that was distributed with this source code
 * for the full copyright and license information.
 */

require_once dirname(__FILE__) . '/../autoload.php';

// App
class HelloWorldApp implements Seraph_Application_Interface
{
    public function onRequest(Seraph_Request $request, Seraph_Response $response) {
        $response->setBody('Hello, World!');
    }
}

$app = new HelloWorldApp();

// Handler
$signals = new Seraph_Signal_Collection();
$handler = new Seraph_Handler('seraph_handler_test', $signals);
$handler->registerServer('Mongrel2', 'tcp://127.0.0.1:9997', 'tcp://127.0.0.1:9996')
    ->registerApplication($app)
    ->run();
