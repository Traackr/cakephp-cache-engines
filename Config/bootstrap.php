<?php

// Bootstrap for CakePHP plugin loading
// Loads required classes and all engines in the plugin
App::uses('FileEngine', 'Cache/Engine');
require_once(dirname(__FILE__) . '/../src/CacheEnginesHelper.php');
require_once(dirname(__FILE__) . '/../src/Engines.php');
