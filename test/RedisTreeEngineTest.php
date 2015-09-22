<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');


class RedisTreeEngineTest extends PHPUnit_Framework_TestCase
{


    private $cache = 'RedisTree';

    public function setUp()
    {

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


    }


    public function tearDown()
    {


    }


    public function testRead()
    {

        $key = 'RedisTreeEngine:TestKey:R';
        $value = date('Y-m-d h:m');
        CacheMock::write($key, $value, $this->cache);
        $this->assertEquals($value, CacheMock::read($key, $this->cache));

        CacheMock::delete($key);

    }


    public function testMultiWriteReadNoPrefix()
    {

        $key1 = 'RedisTreeEngine:TestKey:R:1';
        $key2 = 'RedisTreeEngine:TestKey:R:2';
        $multiKey = '[' . $key1 . ',' . $key2 . ']';

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        CacheMock::write($multiKey, $values, $this->cache);

        $multiVal = CacheMock::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        CacheMock::delete($key1);
        CacheMock::delete($key2);

    }

    public function testMultiWriteReadWithPrefix()
    {

        $key1 = 'RedisTreeEngine:TestKey:R:1';
        $key2 = 'RedisTreeEngine:TestKey:R:2';
        $multiKey = 'alist:[' . $key1 . ',' . $key2 . ']';

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        CacheMock::write($multiKey, $values, $this->cache);

        $multiVal = CacheMock::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        CacheMock::delete($key1);
        CacheMock::delete($key2);

    }


    public function testWrite()
    {

        $key = 'RedisTreeEngine:TestKey:W';
        $value = date('Y-m-d h:m');
        CacheMock::write($key, $value, $this->cache);
        sleep(2);
        $this->assertEquals($value, CacheMock::read($key, $this->cache));
        sleep(3);
        $this->assertNull(CacheMock::read($key, $this->cache));

      $nodes_key = CacheMock::getNodesKey($this->cache);
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, 'RedisTreeEngine', $this->cache),
        'Node key missing');
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, 'RedisTreeEngine:TestKey', $this->cache),
        'Node key missing');
      $this->assertEquals(0,
          CacheMock::sismember($nodes_key, 'RedisTreeEngine:TestKey:W', $this->cache),
          'Node key missing');

      // Test key value is not transformed
      $specialKey = 'test-/\.<>?:| \'""';
      CacheMock::write($specialKey, $value, $this->cache);
      $this->assertEquals($value, CacheMock::read($specialKey, $this->cache));
      $keys = CacheMock::keys('test-*', $this->cache);
      $this->assertCount(1, $keys, "Wrong number of keys found");
      $this->assertEquals($specialKey, $keys[0], "Incorrect key");

    }


    public function testDelete()
    {

      $node = 'RedisTreeEngine:TestKey:D';
      $key = $node.':';
      $otherKey = 'SomeOtherKey';
      $keyOne = $key.'One';
      $keyTwo = $key.'Two';
      $value = date('Y-m-d h:m');

      //
      // Simple delete
      //
      CacheMock::write($otherKey, $value, $this->cache);
      $deletedKeysCount = CacheMock::delete($otherKey, $this->cache);
      $this->assertEquals(1, $deletedKeysCount, 'Incorrect number of keys deleted');
      $this->assertNull(CacheMock::read($otherKey, $this->cache), 'Key not deleted');

        $deletedKeysCount = CacheMock::delete('RandomKeyDoesNotExists', $this->cache);
        $this->assertEquals(0, $deletedKeysCount, 'Incorrect number of keys deleted');

      //
      // Delete with *
      //
      CacheMock::write($keyOne, $value, $this->cache);
      CacheMock::write($keyTwo, $value, $this->cache);
      CacheMock::write($otherKey, $value, $this->cache);
      $deletedKeysCount = CacheMock::delete($key.'*', $this->cache);
      // Check both 'leaf' keys were deleted
      $this->assertEquals(2, $deletedKeysCount, 'Incorrect number of keys deleted');
      $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key not deleted');
      // other key should not have been deleted
      $this->assertEquals($value, CacheMock::read($otherKey, $this->cache), 'Key was deleted when it should not have been');

      //
      // Delete node when key ends with delimiter
      //
      CacheMock::write($keyOne, $value, $this->cache);
      CacheMock::write($keyTwo, $value, $this->cache);
      CacheMock::write($otherKey, $value, $this->cache);
      $nodes_key = CacheMock::getNodesKey($this->cache);
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, 'RedisTreeEngine', $this->cache),
        'Node key missing');
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, $node, $this->cache),
        'Node key missing');
      $deletedKeysCount = CacheMock::delete($key, $this->cache);
      // Check both 'leaf' keys were deleted
      $this->assertEquals(2, $deletedKeysCount, 'Incorrect number of keys deleted');
      $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, 'RedisTreeEngine', $this->cache),
        'Node key missing');
      $this->assertEquals(0,
        CacheMock::sismember($nodes_key, $node, $this->cache),
        'Node key not deleted');
      // other key should not have been deleted
      $this->assertEquals($value, CacheMock::read($otherKey, $this->cache), 'Key was deleted when it should not have been');

      //
      // Delete node when key does not end with delimiter but is a node
      //
      CacheMock::write($keyOne, $value, $this->cache);
      CacheMock::write($keyTwo, $value, $this->cache);
      CacheMock::write($otherKey, $value, $this->cache);
      $nodes_key = CacheMock::getNodesKey($this->cache);
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, 'RedisTreeEngine', $this->cache),
        'Node key missing');
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, $node, $this->cache),
        'Node key missing');
      $deletedKeysCount = CacheMock::delete($node, $this->cache);
      // Check both 'leaf' keys were deleted
      $this->assertEquals(2, $deletedKeysCount, 'Incorrect number of keys deleted');
      $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key not deleted');
      $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key not deleted');
      $this->assertEquals(1,
        CacheMock::sismember($nodes_key, 'RedisTreeEngine', $this->cache),
        'Node key missing');
      $this->assertEquals(0,
        CacheMock::sismember($nodes_key, $node, $this->cache),
        'Node key not deleted');
      // other key should not have been deleted
      $this->assertEquals($value, CacheMock::read($otherKey, $this->cache), 'Key was deleted when it should not have been');

      // Cleanup
      CacheMock::delete($otherKey, $this->cache);

    }


    public function testClear()
    {

        $key = 'RedisTreeEngineTestKey';
        $otherKey = 'SomeOtherKey';
        $keyOne = $key . 'One';
        $keyTwo = $key . 'Two';
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

    }
}
