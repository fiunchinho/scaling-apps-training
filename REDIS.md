# Redis
Redis is an open source, in-memory data structure store, used as database, cache and message broker. It supports data structures such as strings, hashes, lists, sets, sorted sets with range queries, bitmaps, hyperloglogs and geospatial indexes with radius queries.
Redis has built-in replication, Lua scripting, LRU eviction, transactions and different levels of on-disk persistence, and provides high availability via Redis Sentinel and automatic partitioning with Redis Cluster.

The most common use case for Redis is to be used as a key-value store that stores simple strings. The essence of a key-value store is the ability to store some data, called a value, inside a key. The value can be retrieved later only if we know the specific key it was stored in. There is no direct way to search for a key by value. In a sense, it is like a very large hash/dictionary, but it is persistent.

You can use anything as key. It's advisable to use namespaces on keys, so they are easily identified. For example, the email of the user with id equals to 123, would be stored in the key `user:123:email`.

Values stored in Redis can be from different data types. Strings are the most basic data type, and if we only store strings, Redis is really similar to other tools like [Memcached](https://memcached.org/).

## Learning Redis
Follow [this tutorial](http://try.redis.io/) to learn the basic commands and data structures.

## Install Redis
First you need to install Redis

```bash
$ sudo yum install -y epel-release redis
```

If that command doesn't work, [follow these steps](http://sharadchhetri.com/2014/10/04/install-redis-server-centos-7-rhel-7/) to install the latest version of Redis. Typically, the configuration file for Redis is in `/etc/redis`, and it's listening on port 6379.

## Redis clients
Once Redis is installed, there are different ways in which you can interact with redis. The simplest one is to do it directly through the command line

```bash
$ redis-cli set foo bar
OK
$ redis-cli get foo
bar
```

Typically, you will use it through a client library in your programming language (like PHP), where you will find different libraries to connect to a Redis server. In PHP, we'll use the [Predis library](https://github.com/nrk/predis) that you can install via Composer

```bash
$ composer require predis/predis
```

Here is an example of connecting to Redis and executing some commands

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$client = new Predis\Client('tcp://192.168.99.100:6379');

// Saves value in the "hello_world" key
$redis->set("hello_world", "Hi from php!");
$value = $redis->get("hello_world");
var_dump($value);

// Make this key expire specifying an absolute date
$redis->expireat("expire.in.one.week", strtotime("+1 week"));

// Or a relative one, in seconds
$redis->expire("expire.in.one.hour", 3600);

// Now check how long until it expires
var_dump($redis->ttl("expire.in.one.week"));
var_dump($redis->ttl("expire.in.one.hour"));

// Check if the key exists
echo ($redis->exists("Santa.Claus")) ? "true" : "false";

// increment the number of views by 1 for an article with id 234
$redis->incr("article:views:234");

// increment views for article 237 by 5
$redis->incrby("article:views:237", 5);

// decrement views for article 237
$redis->decr("article:views:237");

// decrement views for article 237 by 3
$redis->decrby("article:views:237", 3);
```

[Find here more examples](http://www.sitepoint.com/an-introduction-to-redis-in-php-using-predis/) for different commands.

We can easily use Redis in our Symfony applications [using the SncRedisBundle](https://github.com/snc/SncRedisBundle/blob/master/Resources/doc/index.md).

### Admin Panel
There are different alternatives in case you want a Redis UI admin panel
- [Desktop Manager](http://redisdesktop.com/) (for desktop, multi-platform)
- [Redis Commander](https://joeferner.github.io/redis-commander/) (NodeJS)
- [Redmon](https://steelthread.github.io/redmon/) (Ruby)
- [Redsmin](https://www.redsmin.com/) (SaaS)

## Redis Exercises
Let's create a page where users can award movies depending on how much they like them. We'll use Redis as database, and [Predis as PHP Client](https://github.com/nrk/predis).

### Inserting movies
- Create an HTML form `insert_movie.php` that allow admins to add movies to the database. Just a simple form with the name of the movie.

- Create a page `movie.php` that shows an specific movie. This page only shows the movie title.

```bash
$ curl localhost/movie.php?movie=Matrix
```

### Voting movies
Create an HTML form `vote.php` that contains two select fields. In one field you can select one of the inserted movies. In the other you can select the points for that you want to award to that movie, from 0 to 10. When form is sent, that movie is awarded with those points.

### On every page
- Show the top5 movies.
- Show the latest movies that received points.

### Rankings
Show full list of ranked movies `ranking.php`, paginated. On each page, 5 movies should be shown.

### Filtering
Create an HTML form `filter.php` that let the user see which movies received an specific amount of points. For example, a user may want to see which movies received 10 points.

