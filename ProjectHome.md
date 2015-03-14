##  THIS PROJECT IS DEPRECATED IN FAVOUR OF <a href='http://core.googlecode.com'>CORE</a>  ##

phpd is a pre-forked persistent web/application server written in PHP 5 built using the [APLC library](http://aplc.googlecode.com) and sporting it's own PHP extension (written in C) for handling HTTPS traffic that is around 8% faster than PHP streams.

Note, that I am following RFCs with little to no regard to broken web browsers.  However, I do make sure everything is working with the latest releases of Firefox and IE.

phpd takes a modular approach to all aspects from request and response to it's transports which means it can easily be extended.  The modules can be likened to Java Servlets however all modules are called on every request and are never targeted individually, it is the job of the Application module to call the correct "Servlet" dependent on the URI. Current modules:

  * Reaper
  * Request
  * Database
  * Session
  * Authentication
  * Application
  * Compression
  * Gc (Garbage Collection to counter the "unknowns" as mentioned below)
  * Profiler

The current transport modules are:

  * Sockets (http)
  * phpd (https)
  * Streams (http/https)

Supported equivalent Apache/PHP features:
  * Get, Post, Cookie and Server data.
  * Sessions
  * HTTP and HTTPS

Because phpd is persistent there are [unknowns](http://paul-m-jones.com/?p=262) with how it will handle memory.  To help keep it in check the Reaper module is set to re-spawn the phpd child processes after a user set request limit which by default is set at 10000, however I have yet to see any problems and this is more of a safety net than anything.

Performance is very good, in fact much better than Apache when dealing with dynamic content.  However phpd is NOT intended to serve static content of any kind apart from cached data.  If you want to serve images for example with your application then install [thttpd](http://www.acme.com/software/thttpd/) on another port and set image links accordingly.

For now phpd is an experiment and I would not suggest using it for anything other than to satisfy your curiosity.

To install phpd you will first need the [APLC library](http://aplc.googlecode.com) installed.  I do something like:

  * svn export http://aplc.googlecode.com/svn/trunk/aplc-libs/ /usr/lib/aplc
  * svn checkout http://phpd.googlecode.com/svn/trunk/ phpd
  * Enable the PHP modules: posix, sockets...
  * If using the Suhosin-Patch add /usr/lib/aplc /var/log/ /var/run to open\_basedir.

To run phpd simply `cd phpd` and `./phpd` from a shell and it will background automatically.  The log by default is: /var/log/phpd.log

To use the phpd transport you need to compile and install the PHP phpd\_transport extension in pecl/phpd\_transport.  Something like this will do:

  * cd phpd/pecl/phpd\_transport
  * phpize
  * ./configure
  * make install
  * Add extension=phpd\_transport.so to your php.ini
  * Set the transport to Phpd in your phpd.ini

A quick output of phpd vs apache performance using httperf.  Running on a dual Athlon MP2000 with both preforked at 50 processes:
```
httperf --client=0/1 --server=localhost --port=80 --uri=/index2.php --send-buffer=4096 --recv-buffer=16384 --num-conns=10000 --num-calls=1
```

phpd:
```
Total: connections 10000 requests 10000 replies 10000 test-duration 46.960 s
Connection rate: 212.9 conn/s (4.7 ms/conn, <=1 concurrent connections)
CPU time [s]: user 2.02 system 15.92 (user 4.3% system 33.9% total 38.2%)
Net I/O: 1559.0 KB/s (12.8*10^6 bps)
```

Apache:
```
Total: connections 10000 requests 10000 replies 10000 test-duration 123.577 s
Connection rate: 80.9 conn/s (12.4 ms/conn, <=1 concurrent connections)
CPU time [s]: user 11.59 system 89.21 (user 9.4% system 72.2% total 81.6%)
Net I/O: 350.2 KB/s (2.9*10^6 bps)
```