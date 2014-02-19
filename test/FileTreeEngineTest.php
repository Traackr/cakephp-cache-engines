<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');

class FileTreeEngineTest extends PHPUnit_Framework_TestCase {


   private $cache = 'FileTree';

   public function setUp() {

      Cache::config($this->cache, array(
         'engine' => 'FileTree',
         'duration' => 4
      ));

   } // End function setUp()


   public function tearDown() {


   } // End functiuon tearDown()


   function testRead() {

      $key = 'FileTreeEngine:TestKey:R';
      $value = date('Y-m-d h:m');
      Cache::write($key, $value, $this->cache);
      $this->assertEquals($value, Cache::read($key, $this->cache));

      Cache::delete($key, $this->cache);

   } // End function testRead()

   function testWrite() {

      $key = 'FileTreeEngine:TestKey:W';
      $value = date('Y-m-d h:m');
      Cache::write($key, $value, $this->cache);
      sleep(2);
      $this->assertEquals($value, Cache::read($key, $this->cache));
      sleep(3);
      $this->assertFalse(Cache::read($key, $this->cache));

      Cache::delete($key, $this->cache);

   } // End function testWrite()

   function testDelete() {

      $key = 'FileTreeEngine:TestKey:D:';
      $otherKey = 'SometOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      CacheMock::write($key, $value, $this->cache);
      $deletedKeysCount = CacheMock::delete($key, $this->cache);
      $this->assertEquals(1, $deletedKeysCount, 'Incorrect number of keys deleted');
      $this->assertFalse(CacheMock::read($key, $this->cache), 'Key not deleted');

      $deletedKeysCount = CacheMock::delete('RandomKeyDoesNotExists', $this->cache);
      $this->assertEquals(0, $deletedKeysCount, 'Incorrect number of keys deleted');

      Cache::write($keyOne, $value, $this->cache);
      Cache::write($keyTwo, $value, $this->cache);
      Cache::write($otherKey, $value, $this->cache);
      Cache::delete($key.'*', $this->cache);
      $this->assertFalse(Cache::read($keyOne, $this->cache), $keyOne . ' key not deleted');
      $this->assertFalse(Cache::read($keyTwo, $this->cache), $keyTwo . ' key not deleted');
      $this->assertEquals($value, Cache::read($otherKey, $this->cache), $otherKey . ' key was deleted when it should not have been');

      Cache::delete($otherKey);

   } // End function testDelete()

function testClear() {

      $key = 'FileTreeEngine:TestKey:C:';
      $otherKey = 'SomeOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      Cache::write($key, $value, $this->cache);
      Cache::write($keyOne, $value, $this->cache);
      Cache::write($keyTwo, $value, $this->cache);
      Cache::write($otherKey, $value, $this->cache);
      Cache::clear(false, $this->cache);
      $this->assertFalse(Cache::read($key, $this->cache), 'Key not deleted');
      $this->assertFalse(Cache::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertFalse(Cache::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertFalse(Cache::read($otherKey, $this->cache), 'Key not deleted');

   } // End testClear()
} // End class FileTreeEngineTest