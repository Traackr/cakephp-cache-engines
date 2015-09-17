CakePHP Cache Engines
=====================
[![Build Status](https://api.travis-ci.org/Traackr/cakephp-cache-engines.png?branch=master)](https://travis-ci.org/Traackr/cakephp-cache-engines)

This CakePHP plugin provide some addition cache engines that can be used by CakePHP.  
We currently provide 3 cache engines

1. RedisTreeCacheEngine: Cache backed up by Redis. Support managing keys using wildcards
2. FileTreeCacheEngine: Cache backed up using local files. Support managing keys using wildcards
3. FallBackCacheEngine: Cache allows you to define 2 cache engines. One is used as the primary cache engine, the second is used if the primary fails



Installing
----------

These cache engines are provided as CakePHP plugins. To install them and have them available in your configuration, simply add this plugin to you `composer.json` dependencies. In your require section:

    "require": {
        "traackr/cache-engines": "dev-master"
    }

Then you can simply do:

    > php composer.phar update


Usage
-----

To configure and use these cache engine, simply specify the cache engine name when you configure your cache. The `RedisTreeeEngine` and `FileTreeEngine` take the same argument as the `RedisEngine` and `FileEngine` that ship with CakePHP.
For instance:

    Cache::config("post_data", array(
       'engine' => 'RedisTree',
       'server' => 'redis-server',
       'port' => 6379,
       'duration' => 300,
       'prefix' => 'posts:'
    ));
    
The `FallbackTreeEngine` expects a configuration for the primary and secondary engines:

    Cache::config("post_data", array(
       'engine' => 'Fallback',
       'name' => "post_data",
       'primary' => array(
          'engine' => 'RedisTree',
          'server' => 'redis-server',
          'port' => 6379,
          'duration' => 300,
          'prefix' => 'posts:'
       ),
       'secondary' => array(
          // alternate cache if Redis fails
          'engine' => 'FileTree',
          'path' => CACHE.'/data/',
          'duration' => 300
       )
    ));
    