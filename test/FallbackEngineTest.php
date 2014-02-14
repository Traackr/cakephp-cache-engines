<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');

class FallbackEngineTest extends PHPUnit_Framework_TestCase {

   private $engine = 'Fallback';

   public function setUp() {

      // $factory          = new \M6Web\Component\RedisMock\RedisMockFactory();
      // $myRedisMockClass = $factory->getAdapterClass('Predis\Client', true);

      Cache::config($this->engine, array(
         'engine' => 'Fallback',
         'primary' => array('engine' => 'RedisTree', 'duration' => 60),
         'secondary' => array('engine' => 'FileTree', 'duration' => 60)
      ));

   } // End function setUp()


   public function tearDown() {


   } // End functiuon tearDown()


   function testRead() {

      $key = 'FallbackEngine:TestKey:R';
      $value = date('Y-m-d h:m');
      Cache::write($key, $value, $this->engine);
      $this->assertEquals($value, Cache::read($key, $this->engine));

      Cache::delete($key, $this->engine);

   } // End function testRead()


} // End class FallbackEngineTest
