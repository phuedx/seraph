# Hello, World!

This is a *really* brief introduction to **Seraph** and **Mongrel2** in the form of a "Hello, World!" application. To get it running:

1. In one prompt type:

        $ mkdir run logs tmp
        $ m2sh load --config handler.conf --db handler.sqlite
        $ m2sh start --host handler_host --db handler.sqlite

2. In another prompt type:

        $ php handler.php

Now open up your favourite web browser and browse to [http://localhost:8080/](http://localhost:8080).
