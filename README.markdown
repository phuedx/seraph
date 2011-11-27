# Seraph

**Seraph** is a framework for writing handlers for **Mongrel2** in PHP.

## Motivation

You've heard of Mongrel2, right? One of the *many* awesome features of Mongrel2 is that it supports *handlers*. I think of handlers as long-running applications that publish responses to requests that are pulled from a Mongrel2 server using a couple of **ØMQ** sockets.

I wrote **Seraph** because I wanted to highlight that, with the help of Mikko Koppanen's "php-zmq" PHP extension, PHP is no longer bound by the lifecycle of an Apache web server.

## Hello, World

Here's the smallest "Hello, World!" application that I can write *cleanly*:

```php
<?php

require_once '/path/to/seraph/autoload.php';

// TODO Get rid of all of this ICKY bootstrap code!

// Signals
$signals = new Seraph_Signal_Collection();
$signal  = new Seraph_Signal();

$signals['seraph.handler.raw_request'] = $signal;

// App
class HelloWorldApp implements Seraph_Application_Interface
{
    public function onRequest(Seraph_Request $request, Seraph_Response $response) {
        $response->setBody('Hello, World!');
    }
}

$app        = new HelloWorldApp();
$dispatcher = new Seraph_Request_Dispatcher();
$dispatcher->registerApplication($app);

$signal->connect(array($dispatcher, 'onRawRequest'));

// Handler
$handler = new Seraph_Handler('seraph_handler_test', $signals);
$handler->registerServer('m2', 'tcp://127.0.0.1:9997', 'tcp://127.0.0.1:9996')
    ->run();

```

## See Also

1. [The Mongrel2 Web Server Project](http://mongrel2.org/)
2. [ØMQ Community - zeromq](http://www.zeromq.org/community)
3. [mkoppanen/php-zmq - GitHub](https://github.com/mkoppanen/php-zmq)

## License

**Seraph** is licensed under the MIT license and is copyright (c) 2011 Sam
Smith. See the *LICENSE* file for full copyright and license information.
