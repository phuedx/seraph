# Seraph

**Seraph** is a framework for writing handlers for **Mongrel2** in PHP.

## Overview

You've heard of **Mongrel2**, right? Among other things, **Mongrel2** supports
*handlers*, or long-running applications that can speak a super-simple protocol
over a couple of **ZeroMQ** sockets.

After a *handler* has received a request on the inbound **ZeroMQ** socket, it's
passed to a *dispatcher*. The *dispatcher* does a little work wiring up
instances of the `Seraph_Request` and `Seraph_Response` classes, which are then
passed to your implementation of the `Seraph_FrontController_Interface`
interface.

## See Also

1. [The Mongrel2 Web Server Project](http://mongrel2.org/)
2. [ØMQ Community - zeromq](http://www.zeromq.org/community)

## License

**Seraph** is licensed under the MIT license and is copyright (c) 2011 Sam
Smith. See the *LICENSE* file for full copyright and license information.
