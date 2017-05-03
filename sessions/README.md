# Scaling PHP Sessions
When scaling horizontally from one single server to more than one server, we want that users don't lose their current session just because their requests ended up on a different servers.
User sessions must be shared between all servers.
Session data stored in temporary files would only be accessible to their particular host.
Since the server handling a request may not be the same one that handled the previous request, guaranteed access to state information is impossible without an alternative handling mechanism.
Session data could be stored to a central storage mechanism and be made available to all machines in the cluster.

## Setting up Redis as session handler
In PHP, the session handler is responsible for storing and retrieving data saved into sessions - by default, PHP uses files for that. [In 5.4 and later, we can create a class](https://secure.php.net/manual/en/session.customhandler.php) that implements the `SessionHandlerInterface` interface and implement our own way of handling session data.
Let's use PHP Redis session handler to save our sessions, so our web servers can use the Redis servers to store and retrieve data saved into sessions.
Luckily for us, PHP comes with a native Redis handler that implements that interface.
It only needs us to install a [PHP extension to connect to Redis](https://github.com/phpredis/phpredis#php-session-handler)

```bash
$ yum install php-pecl-redis
```

```bash
$ sudo apt-get install php5-redis
```

Check if the extension has been correctly installed listing currently installed extensions and searching for redis

```bash
$ php -m | grep redis
```

It it's correctly installed, open your php.ini file and change the lines about the session handler to use (by default, you'll see the `files` handler, and the path where these files are saved)

```
session.save_handler = redis
session.save_path = "tcp://localhost:6379"
; multiple servers can be configured comma separated
; session.save_path = "tcp://host1:6379?weight=1&database=2, tcp://host2:6379?weight=2&timeout=2.5, tcp://host3:6379?weight=2"
```

Restart apache so changes take effect.

## Using Redis as session handler
This repository contains a `index.php` file. It's a simple PHP script that increments a counter every time a user visits the page. The counter is stored in the user session, so different users will see different counters.
Deploy our simple `index.php` file to your server and try loading the page with different browsers: you should see different values.

You can see how sessions are being stored in Redis using the `redis-cli`

```bash
$ redis-cli keys "*"
1) "PHPREDIS_SESSION:j9rsgtde6st2rqb6lu5u6f4h83"
2) "PHPREDIS_SESSION:dajlsd8asd7gdasod8897as6ds"
```

## Using Redis as session handler in Symfony
Using [the SncRedisBundle](https://packagist.org/packages/snc/redis-bundle) it's very easy [to save our Symfony sessions in Redis](https://github.com/snc/SncRedisBundle/blob/master/Resources/doc/index.md#sessions).

## Sources
- [How to setup Redis as a session handler for PHP](https://www.digitalocean.com/community/tutorials/how-to-set-up-a-redis-server-as-a-session-handler-for-php-on-ubuntu-14-04)
- [Custom Session Handlers in PHP](https://secure.php.net/manual/en/session.customhandler.php)
