<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');


class RedisTreeEngineTest extends PHPUnit_Framework_TestCase {


   private $cache = 'RedisTree';

   public function setUp() {

      // Comment this to use real redis server
      $factory = new \M6Web\Component\RedisMock\RedisMockFactory();
      $redisMock = $factory->getAdapter('Predis\Client', true);
      CacheMock::config($this->cache, array(
         'engine' => 'RedisTreeMock',
         'duration' => 4
      ));
      CacheMock::setEngine($this->cache, $redisMock);

      // Uncomment this to use real redis server
      // CacheMock::config($this->cache, array(
      //    'engine' => 'RedisTree',
      //    'duration' => 4
      // ));


   } // End function setUp()


   public function tearDown() {


   } // End functiuon tearDown()


   function testRead() {

      $key = 'RedisTreeEngine:TestKey:R';
      $value = date('Y-m-d h:m');
      CacheMock::write($key, $value, $this->cache);
      $this->assertEquals($value, CacheMock::read($key, $this->cache));

      CacheMock::delete($key);

   } // End function testRead()


   /*
    * This test will have to remain commented out until RedisMock supports MGET/MSET
   function testMultiRead() {

      $key1 = 'RedisTreeEngine:TestKey:R:1';
      $value1 = date('Y-m-d h:m') . ':1';
      CacheMock::write($key1, $value1, $this->cache);

      $key2 = 'RedisTreeEngine:TestKey:R:2';
      $value2 = date('Y-m-d h:m') . ':2';
      CacheMock::write($key2, $value2, $this->cache);

      $multiKey = '[' . $key1 . ',' . $key2 . ']';

      $multiVal = CacheMock::read($multiKey, $this->cache);
      
      $this->assertInternalType('array', $multiVal);
      $this->assertEquals(2, count($multiVal));
      $first = $multiVal[0];
      $this->assertEquals($first, $value1);
      $second = $multiVal[1];
      $this->assertEquals($second, $value2);

      CacheMock::delete($key1);
      CacheMock::delete($key2);

   } // End function testMultiRead()
   
   TODO: tests for multi-write and full tests for file-tree-engine
   */

   function testWrite() {

      $key = 'RedisTreeEngine:TestKey:W';
      $value = date('Y-m-d h:m');
      CacheMock::write($key, $value, $this->cache);
      sleep(2);
      $this->assertEquals($value, CacheMock::read($key, $this->cache));
      sleep(3);
      $this->assertNull(CacheMock::read($key, $this->cache));

      // test key value is not transformed
      $specialKey = '/\.<>?:| \'""';
      CacheMock::write($specialKey, $value, $this->cache);
      $this->assertEquals($value, CacheMock::read($specialKey, $this->cache));
      $keys = CacheMock::keys($this->cache);
      $this->assertCount(1, $keys, "Wrong number of keys found");
      $this->assertEquals($specialKey, $keys[0], "Incorrect key");

   } // End function testWrite()


   function testDelete() {

      $key = 'RedisTreeEngine:TestKey:D:';
      $otherKey = 'SomeOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      CacheMock::write($key, $value, $this->cache);
      $deletedKeysCount = CacheMock::delete($key, $this->cache);
      $this->assertEquals(1, $deletedKeysCount, 'Incorrect number of keys deleted');
      $this->assertNull(CacheMock::read($key, $this->cache), 'Key not deleted');

      $deletedKeysCount = CacheMock::delete('RandomKeyDoesNotExists', $this->cache);
      $this->assertEquals(0, $deletedKeysCount, 'Incorrect number of keys deleted');

      CacheMock::write($keyOne, $value, $this->cache);
      CacheMock::write($keyTwo, $value, $this->cache);
      CacheMock::write($otherKey, $value, $this->cache);
      CacheMock::delete($key.'*', $this->cache);
      $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertEquals($value, CacheMock::read($otherKey, $this->cache), 'Key was deleted when it should not have been');

       CacheMock::delete($otherKey, $this->cache);

   } // End function testDelete()


   function testClear() {

      $key = 'RedisTreeEngineTestKey';
      $otherKey = 'SomeOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      CacheMock::write($key, $value, $this->cache);
      CacheMock::write($keyOne, $value, $this->cache);
      CacheMock::write($keyTwo, $value, $this->cache);
      CacheMock::write($otherKey, $value, $this->cache);
      CacheMock::clear(false, $this->cache);
      $this->assertNull(CacheMock::read($key, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($otherKey, $this->cache), 'Key not deleted');

   } // End testClear()


} // End class RedisTreeCacheTest