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

   public function setEngine($redis) {
      $this->redis = $redis;
   } // End function setEngine()

   public function getEngine() {
     return $this->redis;
   } // End function getEngine()

   public function keys($pattern) {
      return $this->redis->keys($pattern);
   } // End function keys()

  public function sismember($key, $member) {
      return $this->redis->sismember($key, $member);
  }
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
      self::$_engines[$name]->setEngine($engine);
   } // End function setEngine()

  public static function getEngine($name) {
     return self::$_engines[$name]->getEngine();
  } // End function getEngine()

   public static function fallback($name) {
      self::$_engines[$name]->fallback();
   } // End function fallback()

   public static function keys($pattern, $name) {
      return self::$_engines[$name]->keys($pattern);
   } // End function keys()

  public static function getNodesKey($name) {
    return self::$_engines[$name]->getNodeskey();
  }

  public static function sismember($key, $member, $name) {
     return self::$_engines[$name]->sismember($key, $member);
  }

} // End class CacheMock
