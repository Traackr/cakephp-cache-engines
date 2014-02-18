<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');


class FallbackEngineTest extends PHPUnit_Framework_TestCase {

   private $cache = 'Fallback';

   public function setUp() {

      $factory = new \M6Web\Component\RedisMock\RedisMockFactory();
      $redisMock = $factory->getAdapter('Predis\Client', true);

      Cache::config($this->cache, array(
         'engine' => 'FallbackMock',
         'primary' => array('engine' => 'RedisTreeMock', 'duration' => 60),
         'secondary' => array('engine' => 'FileTree', 'duration' => 60)
      ));

       CacheMock::setEngine('primary', $redisMock);

   } // End function setUp()


   public function tearDown() {


   } // End functiuon tearDown()


   function testFallback() {

      $key = 'FallbackEngineTestKey';
      $value = date('Y-m-d h:m');

      $fileCacheSettings = CacheMock::settings('secondary');

      CacheMock::write($key, $value, $this->cache);
      $this->assertEquals($value, CacheMock::read($key, $this->cache));
      $this->assertFalse(file_exists(CACHE . DS . $fileCacheSettings['prefix'] . 'fallbackenginetestkey'));
      CacheMock::delete($key, $this->cache);

      CacheMock::fallback($this->cache);

      CacheMock::write($key, $value, $this->cache);
      $this->assertEquals($value, CacheMock::read($key, $this->cache));
      $this->assertTrue(file_exists(CACHE . DS . $fileCacheSettings['prefix'] . 'fallbackenginetestkey'));

      CacheMock::delete($key, $this->cache);

   } // End function testRead()


} // End class FallbackEngineTest
