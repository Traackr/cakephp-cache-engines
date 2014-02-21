<?php

/**
 * Redis storage engine for cache.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.cache
 */
class RedisTreeEngine extends CacheEngine {

   /**
    * Redis wrapper.
    */
   protected $redis = null;

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
    * @param array $setting array of setting for the engine
    * @return boolean True if the engine has been successfully initialized, false if not
    */
   public function init($settings = array()) {

      $settings += array_merge(array(
         'engine'=> 'RedisTree',
         'host' => '127.0.0.1',
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
               'host'   => $this->settings['host'],
               'port'   => $this->settings['port'],
            ));
         }
         catch (Exception $e) {
            // If creation fails, return false
            return false;
         }
      }

      // If redis is still null, maybe server is down.
      // Return false, let caller deal with it
      if (!isset($this->redis)) {
         return false;
      }

      return true;

   } // End function init()

   /*
    * Transfrom characters that are not valid for a key.
    * In Redis all characters can be used in a key
    */
   public function key($key) {

      return $key;

   } // End function translateKey()

   /**
    * Write data for key into cache.
    *
    * @param string $key Identifier for the data
    * @param mixed $value Data to be cached
    * @param integer $duration How long to cache the data, in seconds
    * @return boolean True if the data was succesfully cached, false on failure    */
   public function write($key, $value, $duration) {

      if (!is_int($value)) {
        $value = serialize($value);
      }
      if ($duration === 0) {
        return $this->redis->set($key, $value);
      }

      return $this->redis->setex($key, $duration, $value);

   } // End funcntion write()

   /**
    * Read a key from the cache
    *
    * @param string $key Identifier for the data
    * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
    */
   public function read($key) {

      $value = $this->redis->get($key);
      if (ctype_digit($value)) {
        $value = (int)$value;
      }
      if ($value !== false && is_string($value)) {
        $value = unserialize($value);
      }
      return $value;

   } // End function read()

   /**
    * Increments the value of an integer cached key
    *
    * @param string $key Identifier for the data
    * @param integer $offset How much to increment
    * @param integer $duration How long to cache the data, in seconds
    * @return New incremented value, false otherwise
    */
   public function increment($key, $offset = 1) {

      return $this->redis->incrBy($key, $offset);

   } // End functon increment()

   /**
    * Decrements the value of an integer cached key
    *
    * @param string $key Identifier for the data
    * @param integer $offset How much to substract
    * @param integer $duration How long to cache the data, in seconds
    * @return New decremented value, false otherwise
    */
   public function decrement($key, $offset = 1) {

      return $this->redis->decrBy($key, $offset);

   } // End functuon decrement

   /**
    * Delete a key from the cache
    *
    * @param string $key Identifier for the data
    * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
    */
   public function delete($key) {

      $keys = $this->redis->keys($key);
      // Check if there are any key to delete
      if ( !empty($keys) ) {
         return $this->redis->del($keys);
      }
      else {
         return 0;
      }

   } // End function delete()

   /**
    * Delete all keys from the cache
    *
    * @param boolean $check Optional - only delete expired cache items
    * @return boolean True if the cache was succesfully cleared, false otherwise
    */
   public function clear($check = false) {

      if ($check) {
         return true;
      }
      $keys = $this->redis->keys($this->settings['prefix'] . '*');
      $this->redis->del($keys);
      return true;

   } // End function clear()

   /**
    * Returns the `group value` for each of the configured groups
    * If the group initial value was not found, then it initializes
    * the group accordingly.
    *
    * @return array
    */
   public function groups() {
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
    * @return boolean success
    */
   public function clearGroup($group) {
      return (bool)$this->redis->incr($this->settings['prefix'] . $group);
   }

} // End class RedisTreeENgine
