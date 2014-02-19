CakePHP Cache Engines
=====================
[![Build Status](https://api.travis-ci.org/Traackr/cakephp-cache-engines.png?branch=master)](https://travis-ci.org/Traackr/cakephp-cache-engines)

This CakePHP plugin provide some addition cache engines that can be used by CakePHP.  
We currently provide 3 cache engines

1. RedisTreeCacheEngine: Cache backed up by Redis. Support managing keys using wildcards
2. FileTreeCacheEngine: Cache backed up using local files. Support managing keys using wildcards
3. FallBackCacheEngine: Cache allows you to define 2 cache engines. One is used as the primary cache engine, the second is used if the primary fails
