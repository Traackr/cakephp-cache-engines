<?php

/**
 * Helper utility methods for the advanced features offered by
 * some Cache Engines, such as the RedisTreeEngine.
 */
class CacheEnginesHelper
{
    /**
     * Write data for key into a cache engine with one or more 'parent'.
     *
     * The following is a modified version of:
     * https://github.com/cakephp/cakephp/blob/2.10.22/lib/Cake/Cache/Cache.php
     * The modifications are limited to the addition of
     * the `$parentKey` parameter.
     *
     * ### Usage:
     *
     * Write the value for a single key with a single parent:
     *
     * `Cache::write('cache_key', $data, $config,  'parent_cache_key');`
     *
     * Write the value for a single key with multiple parents:
     *
     * `Cache::write('cache_key', $data, $config,  [
     *     'parent_cache_key_1',
     *     'parent_cache_key_2',
     * ]);`
     *
     * Write the values for multiple keys with the same parent:
     *
     * `Cache::write(
     *     '[cache_key_1,cache_key_2]',
     *     $data,
     *     $config,
     *     'parent_cache_key_1'
     * );`
     *
     * Write the values for multiple keys with the same parents:
     *
     * `Cache::write(
     *     '[cache_key_1,cache_key_2]',
     *     $data,
     *     $config,
     *     [
     *         'parent_cache_key_1',
     *         'parent_cache_key_2',
     *     ]
     * );`
     * Write the values for multiple keys with different parents:
     *
     * `Cache::write(
     *     '[cache_key_1,cache_key_2]',
     *     $data,
     *     $config,
     *     [
     *         'cache_key_1' => [
     *             'parent_cache_key_1'
     *         ],
     *         'cache_key_2' => [
     *             'parent_cache_key_2'
     *         ]
     *     ]
     * );`
     *
     * Writing to a specific cache config:
     *
     * `Cache::write('cached_data', $data, 'long_term');`
     *
     * @param  string       $key       Identifier for the data
     * @param  mixed        $value     Data to be cached - anything except a resource
     * @param  string       $config    Optional string configuration name to write to
     *                                 Defaults to 'default'
     * @param  string|array $parentKey Parent key that data is a dependent child of
     * @return bool True if the data was successfully cached, false on failure
     */
    public static function writeWithParent(
        $key,
        $value,
        $config = 'default',
        $parentKey = ''
    ) {

        if (method_exists(Cache::engine($config), 'getActiveCacheSettings')) {
            $settings = Cache::engine($config)->getActiveCacheSettings();
        } else {
            $settings = Cache::settings($config);
        }

        if (empty($settings)) {
            return false;
        }
        if (!Cache::isInitialized($config)) {
            return false;
        }
        $key = Cache::engine($config)->key($key);

        if (!$key || is_resource($value)) {
            return false;
        }

        $success = false;
        if (method_exists(Cache::engine($config), 'writeWithParent')) {
            $success = Cache::engine($config)->writeWithParent(
                $settings['prefix'] . $key,
                $value,
                $settings['duration'],
                $parentKey
            );
        }
        Cache::set(null, $config);
        if ($success === false && $value !== '') {
            trigger_error(
                __d(
                    'cake_dev',
                    "%s cache was unable to write '%s' to %s cache",
                    $config,
                    $key,
                    Cache::$_engines[$config]->settings['engine']
                ),
                E_USER_WARNING
            );
        }
        return $success;
    }
}
