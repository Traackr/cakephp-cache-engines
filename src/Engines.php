<?php

// Load all engines available
require(dirname(__FILE__) . '/RedisTreeEngine.php');
require(dirname(__FILE__) . '/FileTreeEngine.php');
require(dirname(__FILE__) . '/FallbackEngine.php');
