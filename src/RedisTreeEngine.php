<?php
use Predis\Collection\Iterator;

/**
 * Redis storage engine for cache.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.cache
 */
class RedisTreeEngine extends CacheEngine
{
    /**
     * Redis wrapper.
     */
    protected $redis = null;

    /**
     * Needs to be protected, not private since it's reset in RedisTreeMockEngine
     */
    protected $supportsScan = false;

    /**
     * Settings
     */
    public $settings = array();

    /**
     * Initialize the Cache Engine
     *
     * Called automatically by the cache frontend
     * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
     *
     * @param array $settings
     * @return bool True if the engine has been successfully initialized, false if not
     * @internal param array $setting array of setting for the engine
     */
    public function init($settings = array())
    {
        $settings = array_merge(array(
            'engine' => 'RedisTree',
            'server' => '127.0.0.1',
            'port' => 6379,
            'prefix' => '',
            'duration' => 60,
            'groups' => array(),
            'probability' => 100
        ), $settings);
        parent::init($settings);

        if (!class_exists('Predis\Client')) {
            return false;
        }

        if (!isset($this->redis)) {
            try {
                $this->redis = new Predis\Client(array(
                    'scheme' => 'tcp',
                    'host' => $this->settings['server'],
                    'port' => $this->settings['port'],
                ));
            } catch (Exception $e) {
                // If creation fails, return false
                return false;
            }
        }

        // If redis is still null, maybe server is down.
        // Return false, let caller deal with it
        if (!isset($this->redis)) {
            return false;
        }

        $profile = $this->redis->getProfile();
        // profile is empty for redis-mock
        $this->supportsScan = !empty($profile) && $profile->supportsCommand('scan');

        return true;
    }

    public function keys()
    {
        return $this->redis->keys('*');
    }

    /*
     * Transform characters that are not valid for a key.
     * In Redis all characters can be used in a key
     */
    public function key($key)
    {
        return $key;
    }

    /**
     * Write data for key into cache.
     *
     * @param string $key Identifier for the data
     * @param mixed $value Data to be cached
     * @param integer $duration How long to cache the data, in seconds
     * @return bool True if the data was successfully cached, false on failure
     * @throws Exception
     */
    public function write($key, $value, $duration)
    {
        // Cake's Redis cache engine sets a default prefix of null. We'll need to handle both
        // a prefix configured by the user or left as null.
        if (strpos($key, '[') !== false && substr($key, -1) == ']') {
            $keys = $this->parseMultiKey($key);

            if (count($keys) != count($value)) {
                throw new Exception('Num keys != num values.');
            }
            $key_vals = array_combine($keys, $value);

            return $this->_mwrite($key_vals, $duration);
        }

        return $this->_write($key, $value, $duration);
    }

    /**
     * Internal multi-val write.
     * @param $key_value_array
     * @param $duration
     * @return
     */
    private function _mwrite($key_value_array, $duration)
    {
        foreach ($key_value_array as $key => &$value) {
            if (!is_int($value)) {
                $value = serialize($value);
            }
        }
        unset($value);

        if ($duration === 0) {
            return $this->redis->mset($key_value_array);
        }

        //note that there is no "msetex" in redis! must do this in a more convoluted way:
        $this->redis->multi();
        foreach ($key_value_array as $key => $value) {
            $this->redis->setex($key, $duration, $value);
        }

        return $this->redis->exec();
    }

    /**
     * Internal single-val write.
     * @param $key
     * @param $value
     * @param $duration
     * @return
     */
    private function _write($key, $value, $duration)
    {
        if (!is_int($value)) {
            $value = serialize($value);
        }
        if ($duration === 0) {
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $duration, $value);
    }

    /**
     * Read a key from the cache
     *
     * @param string $key Identifier for the data
     * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
     */
    public function read($key)
    {
        // Cake's Redis cache engine sets a default prefix of null. We'll need to handle both
        // a prefix configured by the user or left as null.
        if (strpos($key, '[') !== false && substr($key, -1) == ']') {
            $keys = $this->parseMultiKey($key);

            return $this->_mread($keys);
        }

        return $this->_read($key);
    }

    /**
     * Internal multi-val read.
     * @param $keys
     * @return array
     * @throws Exception
     */
    private function _mread($keys)
    {
        $items = $this->redis->mget($keys);

        if (!is_array($items)) {
            throw new Exception('mget() should have returned array: ' . print_r($items, true));
        }

        $returnVal = array();

        foreach ($items as $value) {
            if (ctype_digit($value)) {
                $value = (int)$value;
            }
            if ($value !== false && is_string($value)) {
                $value = unserialize($value);
            }

            $returnVal[] = $value;
        }

        return $returnVal;
    }

    /**
     * Internal single-val read.
     * @param $key
     * @return int|mixed
     */
    private function _read($key)
    {
        $value = $this->redis->get($key);
        if (ctype_digit($value)) {
            $value = (int)$value;
        }
        if ($value !== false && is_string($value)) {
            $value = unserialize($value);
        }

        return $value;
    }

    /**
     * Increments the value of an integer cached key
     *
     * @param string $key Identifier for the data
     * @param integer $offset How much to increment
     * @return int|bool New incremented value, false otherwise
     * @internal param int $duration How long to cache the data, in seconds
     */
    public function increment($key, $offset = 1)
    {
        return $this->redis->incrBy($key, $offset);
    }

    /**
     * Decrements the value of an integer cached key
     *
     * @param string $key Identifier for the data
     * @param integer $offset How much to subtract
     * @return int|bool New decremented value, false otherwise
     * @internal param int $duration How long to cache the data, in seconds
     */
    public function decrement($key, $offset = 1)
    {
        return $this->redis->decrBy($key, $offset);
    }

    /**
     * Delete a key from the cache
     *
     * @param string $key Identifier for the data
     * @return boolean True if the value was successfully deleted, false if it didn't exist or couldn't be removed
     */
    public function delete($key)
    {
        // Cake's Redis cache engine sets a default prefix of null. We'll need to handle both
        // a prefix configured by the user or left as null.
        if (strpos($key, '[') !== false && substr($key, -1) == ']') {
            $keys = $this->parseMultiKey($key);

            return $this->_mdelete($keys);
        }

        return $this->_delete($key);
    }

    /**
     * Internal multi-val read.
     * @param $keys
     * @return array
     * @throws Exception
     */
    private function _mdelete($keys)
    {
        $finalKeys = array();

        foreach ($keys as $key) {
            // keys() is an expensive call; only call it if we need to (i.e. if there actually is a wildcard);
            // the chars "?*[" seem to be the right ones to listen for according to: http://redis.io/commands/KEYS
            if (preg_match('/[\?\*\[]/', $key)) {
                if ($this->supportsScan) {
                    $currKeys = array();
                    foreach (new Iterator\Keyspace($this->redis, $key) as $currKey) {
                        $currKeys[] = $currKey;
                    }
                    $finalKeys = array_merge($finalKeys, $currKeys);
                } else {
                    $finalKeys = array_merge($finalKeys, $this->redis->keys($key));
                }
            } else {
                $finalKeys[] = $key;
            }
        }

        // Check if there are any key to delete
        if (!empty($finalKeys)) {
            return $this->redis->del($finalKeys);
        }

        return 0;
    }

    /**
     * Internal single-val delete.
     * @param $key
     * @return int|mixed
     */
    private function _delete($key)
    {
        // keys() is an expensive call; only call it if we need to (i.e. if there actually is a wildcard);
        // the chars "?*[" seem to be the right ones to listen for according to: http://redis.io/commands/KEYS
        if (preg_match('/[\?\*\[]/', $key)) {
            if ($this->supportsScan) {
                $keys = array();
                foreach (new Iterator\Keyspace($this->redis, $key) as $currKey) {
                    $keys[] = $currKey;
                }
            } else {
                $keys = $this->redis->keys($key);
            }
        } else {
            $keys = array($key);
        }

        // Check if there are any key to delete
        if (!empty($keys)) {
            return $this->redis->del($keys);
        }

        return 0;
    }

    /**
     * Delete all keys from the cache
     *
     * @param boolean $check Optional - only delete expired cache items
     * @return boolean True if the cache was successfully cleared, false otherwise
     */
    public function clear($check = false)
    {
        if ($check) {
            return true;
        }

        if ($this->supportsScan) {
            $keys = array();
            foreach (new Iterator\Keyspace($this->redis, $this->settings['prefix'] . '*') as $currKey) {
                $keys[] = $currKey;
            }
        } else {
            $keys = $this->redis->keys($this->settings['prefix'] . '*');
        }
        $this->redis->del($keys);

        return true;
    }

    /**
     * Returns the `group value` for each of the configured groups
     * If the group initial value was not found, then it initializes
     * the group accordingly.
     *
     * @return array
     */
    public function groups()
    {
        $result = array();
        foreach ($this->settings['groups'] as $group) {
            $value = $this->redis->get($this->settings['prefix'] . $group);
            if (!$value) {
                $value = 1;
                $this->redis->set($this->settings['prefix'] . $group, $value);
            }
            $result[] = $group . $value;
        }

        return $result;
    }

    /**
     * Increments the group value to simulate deletion of all keys under a group
     * old values will remain in storage until they expire.
     *
     * @param $group
     * @return bool success
     */
    public function clearGroup($group)
    {
        return (bool)$this->redis->incr($this->settings['prefix'] . $group);
    }

    protected function parseMultiKey($key)
    {
        $matches = array();

        // Multi-keys are of the form <prefix>[key1, key2]
        // e.g., foo:[hello, world], foobar-[first, post], [no, prefix, needed]
        preg_match("/([^\[]*)\[([^\]]+)\]/", $key, $matches);

        $prefix = $matches[1];

        $keys = array();
        foreach (explode(",", $matches[2]) as $match) {
            $keys[] = $prefix . trim($match);
        }

        return $keys;
    }
}
