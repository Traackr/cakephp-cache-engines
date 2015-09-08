CakePHP Cache Engines
=====================
[![Build Status](https://api.travis-ci.org/Traackr/cakephp-cache-engines.png?branch=master)](https://travis-ci.org/Traackr/cakephp-cache-engines)

This CakePHP plugin provides some additional cache engines that can be used by CakePHP.

We currently provide three cache engines:

1. RedisTreeCacheEngine: Redis based cache that supports managing keys using wildcards
2. FileTreeCacheEngine: Local filesystem based cache that supports managing keys using wildcards
3. FallBackCacheEngine: Allows you to define two cache engines; the first engine is used as the primary cache engine.
   The second cache engine is used only if the primary fails.

##Installation

   ```bash
   $ cd /path/to/cake/application/app
   $ composer require traackr/cache-engines
   $ composer update
   ```

##Documentation

All documentation can be found in the [doc](https://github.com/Traackr/cakephp-cache-engines/blob/master/doc) folder.

##Contributing

* [Getting Started](https://github.com/Traackr/cakephp-cache-engines/blob/master/doc/CONTRIBUTING.md)
* [Bug Reports](https://github.com/Traackr/cakephp-cache-engines/blob/master/doc/CONTRIBUTING.md#bug-reports)
* [Feature Requests](https://github.com/Traackr/cakephp-cache-engines/blob/master/doc/CONTRIBUTING.md#feature-requests)
* [Pull Requests](https://github.com/Traackr/cakephp-cache-engines/blob/master/doc/CONTRIBUTING.md#pull-requests)