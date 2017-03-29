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

        CacheMock::delete($key, $this->cache);

    }


    public function testMultiWriteReadDeleteNoPrefix()
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

        CacheMock::delete($multiKey, $this->cache);
        $this->assertNull(CacheMock::read($key1, $this->cache), 'Key 1 not deleted');
        $this->assertNull(CacheMock::read($key2, $this->cache), 'Key 2 not deleted');
    }

    public function testMultiWriteReadDeleteWithPrefix()
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

        CacheMock::delete($multiKey, $this->cache);
        $this->assertNull(CacheMock::read($key1, $this->cache), 'Key 1 not deleted');
        $this->assertNull(CacheMock::read($key2, $this->cache), 'Key 2 not deleted');
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

        // test key value is not transformed
        $specialKey = '/\.<>?:| \'""';
        CacheMock::write($specialKey, $value, $this->cache);
        $this->assertEquals($value, CacheMock::read($specialKey, $this->cache));
        $keys = CacheMock::keys($this->cache);
        $this->assertCount(1, $keys, "Wrong number of keys found");
        $this->assertEquals($specialKey, $keys[0], "Incorrect key");

    }


    public function testDelete()
    {

        $key = 'RedisTreeEngine:TestKey:D:';
        $otherKey = 'SomeOtherKey';
        $keyOne = $key . 'One';
        $keyTwo = $key . 'Two';
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
        CacheMock::delete($key . '*', $this->cache);
        $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key not deleted');
        $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key not deleted');
        $this->assertEquals(
            $value,
            CacheMock::read($otherKey, $this->cache),
            'Key was deleted when it should not have been'
        );

        CacheMock::delete($otherKey, $this->cache);

        $values = array_fill(0, 3, $value);
        // now test multi-syntax with regex (no prefix)
        CacheMock::write('[' . $keyOne . ',' . $keyTwo . ',' . $otherKey . ']', $values, $this->cache);
        CacheMock::delete('[' . $key . '*,' . $otherKey . ']', $this->cache);
        $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key (1, no-prefix) not deleted');
        $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key (2, no-prefix) not deleted');
        $this->assertNull(CacheMock::read($otherKey, $this->cache), 'Key (other, no-prefix) not deleted');

        // now test multi-syntax with regex (with prefix)
        CacheMock::write('alist:[' . $keyOne . ',' . $keyTwo . ',' . $otherKey . ']', $values, $this->cache);
        CacheMock::delete('alist:[' . $key . '*,' . $otherKey . ']', $this->cache);
        $this->assertNull(CacheMock::read($keyOne, $this->cache), 'Key (1, prefix) not deleted');
        $this->assertNull(CacheMock::read($keyTwo, $this->cache), 'Key (2, prefix) not deleted');
        $this->assertNull(CacheMock::read($otherKey, $this->cache), 'Key (other, prefix) not deleted');
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
