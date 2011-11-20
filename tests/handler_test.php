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

// Signals
$signals = new Seraph_Signal_Collection();
$signal  = new Seraph_Signal();

$signals['seraph.handler.raw_request'] = $signal;

// Dispatcher
class HandlerTestFrontController implements Seraph_FrontController_Interface
{
    public function onRequest(Seraph_Request $request, Seraph_Response $response) {
        $response->setBody('Hello, World!');
    }
}

$frontController = new HandlerTestFrontController();
$dispatcher      = new Seraph_Request_Dispatcher($frontController);

$signal->connect(array($dispatcher, 'onRawRequest'));

// Handler
$handler = new Seraph_Handler('seraph_handler_test', $signals);
$handler->registerServer('m2', 'tcp://127.0.0.1:9997', 'tcp://127.0.0.1:9996')
    ->run();
