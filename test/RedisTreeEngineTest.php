<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');

class RedisTreeEngineTest extends PHPUnit_Framework_TestCase {


   private $cache = 'RedisTree';

   public function setUp() {

      // $factory          = new \M6Web\Component\RedisMock\RedisMockFactory();
      // $myRedisMockClass = $factory->getAdapterClass('Predis\Client', true);

      Cache::config($this->cache, array(
         'engine' => 'RedisTree',
         'duration' => 4
      ));
   } // End function setUp()


   public function tearDown() {


   } // End functiuon tearDown()


   function testRead() {

      $key = 'RedisTreeEngine:TestKey:R';
      $value = date('Y-m-d h:m');
      Cache::write($key, $value, $this->cache);
      $this->assertEquals($value, Cache::read($key, $this->cache));

      Cache::delete($key);

   } // End function testRead()


   function testWrite() {

      $key = 'RedisTreeEngine:TestKey:W';
      $value = date('Y-m-d h:m');
      Cache::write($key, $value, $this->cache);
      sleep(2);
      $this->assertEquals($value, Cache::read($key, $this->cache));
      sleep(3);
      $this->assertNull(Cache::read($key, $this->cache));

   } // End function testWrite()


   function testDelete() {

      $key = 'RedisTreeEngine:TestKey:D:';
      $otherKey = 'SomeOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      Cache::write($key, $value, $this->cache);
      Cache::delete($key, $this->cache);
      $this->assertNull(Cache::read($key, $this->cache), 'Key not deleted');

      Cache::write($keyOne, $value, $this->cache);
      Cache::write($keyTwo, $value, $this->cache);
      Cache::write($otherKey, $value, $this->cache);
      Cache::delete($key.'*', $this->cache);
      $this->assertNull(Cache::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(Cache::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertEquals($value, Cache::read($otherKey, $this->cache), 'Key was deleted when it should not have been');

       Cache::delete($otherKey, $this->cache);

   } // End function testDelete()


   function testClear() {

      $key = 'RedisTreeEngineTestKey';
      $otherKey = 'SomeOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      Cache::write($key, $value, $this->cache);
      Cache::write($keyOne, $value, $this->cache);
      Cache::write($keyTwo, $value, $this->cache);
      Cache::write($otherKey, $value, $this->cache);
      Cache::clear(false, $this->cache);
      $this->assertNull(Cache::read($key, $this->cache), 'Key not deleted');
      $this->assertNull(Cache::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(Cache::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertNull(Cache::read($otherKey, $this->cache), 'Key not deleted');

   } // End testClear()


} // End class RedisTreeCacheTest