<?php
/*
 * Mock classes for unit tests.
 * They are mainly wrapper arounf existing classes for the purpose
 * of accessing/exposing private or protected variables or functions
 */


/*
 * Mock for the RedisTreeEngine
 * Adds a setRedis() function to change the internal Redis client
 */
class RedisTreeMockEngine extends RedisTreeEngine {

   public function setRedis($redis) {
      $this->redis = $redis;
   } // End class setRedis()

   public function keys() {
      return $this->redis->keys('*');
   } // End class setRedis()

} // End  class RedisTreeMockEngine


/*
 * Mock for the FallbackEngine
 * Adds a public function to force the engine to fallback to the secondary
 */
class FallbackMockEngine extends FallbackEngine {

   public function fallback() {
      parent::fallback();
   } // End class fallback()

} // End class FallbackMockEngine


/*
 * Mock for the Cache base class
 * Exposes a setEngine() method to update an engine already configured.
 * Exposes a fallback() method that can be used to force a Fallback engine
 * (if used) to fallback
 */
class CacheMock extends Cache {

   public static function setEngine($name, $engine) {
      self::$_engines[$name]->setRedis($engine);
   } // End function setEngine()

   public static function fallback($name) {
      self::$_engines[$name]->fallback();
   } // End function fallback()

   public static function keys($name) {
      return self::$_engines[$name]->keys();
   } // End function fallback()

} // End class CacheMock
