<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');

class FileTreeEngineTest extends \PHPUnit\Framework\TestCase
{

    private $cache = 'FileTree';

    public function setUp()
    {

        Cache::config($this->cache, array(
            'engine' => 'FileTree',
            'duration' => 4
        ));

    }


    public function tearDown()
    {

    }


    public function testRead()
    {

        $key = 'FileTreeEngine:TestKey:R';
        $value = date('Y-m-d h:m');
        Cache::write($key, $value, $this->cache);
        $this->assertEquals($value, Cache::read($key, $this->cache));

        Cache::delete($key, $this->cache);

    }

    public function testWrite()
    {

        $key = 'FileTreeEngine:TestKey:W';
        $value = date('Y-m-d h:m');
        Cache::write($key, $value, $this->cache);
        sleep(2);
        $this->assertEquals($value, Cache::read($key, $this->cache));
        sleep(3);
        $this->assertFalse(Cache::read($key, $this->cache));

        Cache::delete($key, $this->cache);

    }

    public function testDelete()
    {

        $key = 'FileTreeEngine:TestKey:D:';
        $otherKey = 'SomeOtherKey';
        $keyOne = $key . 'One';
        $keyTwo = $key . 'Two';
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
        Cache::delete($key . '*', $this->cache);
        $this->assertFalse(Cache::read($keyOne, $this->cache), $keyOne . ' key not deleted');
        $this->assertFalse(Cache::read($keyTwo, $this->cache), $keyTwo . ' key not deleted');
        $this->assertEquals(
            $value,
            Cache::read($otherKey, $this->cache),
            $otherKey . ' key was deleted when it should not have been'
        );

        Cache::delete($otherKey);

        $values = array_fill(0, 3, $value);
        // now test multi-syntax with regex (no prefix)
        CacheMock::write('[' . $keyOne . ',' . $keyTwo . ',' . $otherKey . ']', $values, $this->cache);
        CacheMock::delete('[' . $key . '*,' . $otherKey . ']', $this->cache);
        $this->assertFalse(CacheMock::read($keyOne, $this->cache), 'Key (1, no-prefix) not deleted');
        $this->assertFalse(CacheMock::read($keyTwo, $this->cache), 'Key (2, no-prefix) not deleted');
        $this->assertFalse(CacheMock::read($otherKey, $this->cache), 'Key (other, no-prefix) not deleted');

        // now test multi-syntax with regex (with prefix)
        CacheMock::write('alist:[' . $keyOne . ',' . $keyTwo . ',' . $otherKey . ']', $values, $this->cache);
        CacheMock::delete('alist:[' . $key . '*,' . $otherKey . ']', $this->cache);
        $this->assertFalse(CacheMock::read($keyOne, $this->cache), 'Key (1, prefix) not deleted');
        $this->assertFalse(CacheMock::read($keyTwo, $this->cache), 'Key (2, prefix) not deleted');
        $this->assertFalse(CacheMock::read($otherKey, $this->cache), 'Key (other, prefix) not deleted');
    }

    public function testClear()
    {

        $key = 'FileTreeEngine:TestKey:C:';
        $otherKey = 'SomeOtherKey';
        $keyOne = $key . 'One';
        $keyTwo = $key . 'Two';
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

    }

    public function testMultiWriteReadDelete()
    {

        $key1 = 'FileTreeEngine:TestKey:R:1';
        $key2 = 'FileTreeEngine:TestKey:R:2';
        $multiKey = '[' . $key1 . ',' . $key2 . ']';

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        Cache::write($multiKey, $values, $this->cache);

        $multiVal = Cache::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        Cache::delete($multiKey, $this->cache);
        $this->assertFalse(Cache::read($key1, $this->cache), 'Key 1 not deleted');
        $this->assertFalse(Cache::read($key2, $this->cache), 'Key 2 not deleted');

    }
}
