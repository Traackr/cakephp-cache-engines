<?php

// CakePHP required class for unit tests
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/basics.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Core/App.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Core/CakePlugin.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Model/Datasource/CakeSession.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Network/CakeRequest.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/I18n/I18n.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/I18n/L10n.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Error/exceptions.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Utility/Hash.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Core/Configure.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Cache/Cache.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Cache/CacheEngine.php';
require_once __DIR__ . '/../vendor/cakephp/cakephp/lib/Cake/Cache/Engine/FileEngine.php';

// Load all engines
require_once __DIR__ . '/../src/Engines.php';
// Load mock classes for unit tests
require_once __DIR__ . '/RedisTreeMockEngine.php';
require_once __DIR__ . '/FallbackMockEngine.php';
require_once __DIR__ . '/CacheMock.php';

// Some constants required by CakePHP
define('CACHE', __DIR__ . '/tmp');
define('DS', DIRECTORY_SEPARATOR);

// Create tmp file for File cache
if (!file_exists(__DIR__ . '/tmp') && !mkdir(__DIR__ . '/tmp') && !is_dir(__DIR__ . '/tmp')) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', __DIR__ . '/tmp'));
}
