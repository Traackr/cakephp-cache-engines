<?php

require_once(dirname(__FILE__) . '/../src/Engines.php');


class RedisTreeEngineTest extends \PHPUnit\Framework\TestCase
{


    private $cache = 'RedisTree';
    private $redisMock;

    public function setUp()
    {

        // Comment this to use real redis server
        /**/
        $factory = new \M6Web\Component\RedisMock\RedisMockFactory();
        $this->redisMock = $factory->getAdapter('Predis\Client', true);
        CacheMock::config($this->cache, array(
            'engine' => 'RedisTreeMock',
            'duration' => 4
        ));
        CacheMock::setEngine($this->cache, $this->redisMock);
        /**/

        // Uncomment this to use real redis server
        /*
         CacheMock::config($this->cache, array(
            'engine' => 'RedisTree',
            'duration' => 4
         ));
        /**/


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


    public function testWriteWithParentReadDeleteWithSharedParent()
    {

        $key1 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithSharedParent:1';
        $key2 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithSharedParent:2';
        $multiKey = '[' . $key1 . ',' . $key2 . ']';

        $parentKey = 'RedisTreeEngine:TestParent:-1';

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        CacheEnginesHelper::writeWithParent($multiKey, $values, $this->cache, $parentKey);

        $multiVal = CacheMock::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        $childKeys = $this->redisMock->smembers($parentKey . ':child_keys');
        $this->assertEquals(
            [
                $key1,
                $key2
            ],
            $childKeys
        );

        CacheMock::delete($parentKey, $this->cache);
        $this->assertNull(CacheMock::read($key1, $this->cache), 'Key 1 is deleted');
        $this->assertNull(CacheMock::read($key2, $this->cache), 'Key 2 is deleted');
    }


    public function testWriteWithParentReadDeleteWithSharedParents()
    {

        $key1 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithSharedParents:1';
        $key2 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithSharedParents:2';
        $multiKey = '[' . $key1 . ',' . $key2 . ']';

        $parentKeys = [
            'RedisTreeEngine:TestParent:1',
            'RedisTreeEngine:TestParent:2'
        ];

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        CacheEnginesHelper::writeWithParent($multiKey, $values, $this->cache, $parentKeys);

        $multiVal = CacheMock::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        foreach ($parentKeys as $pk) {
            $childKeys = $this->redisMock->smembers($pk . ':child_keys');
            $this->assertEquals(
                [
                    $key1,
                    $key2
                ],
                $childKeys
            );
        }

        CacheMock::delete($parentKeys[0], $this->cache);
        $this->assertNull(CacheMock::read($key1, $this->cache), 'Key 1 is deleted');
        $this->assertNull(CacheMock::read($key2, $this->cache), 'Key 2 is deleted');
    }


    public function testWriteWithParentReadDeleteWithUnqiueParents()
    {

        $key1 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithUnqiueParents:1';
        $key2 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithUnqiueParents:2';
        $multiKey = '[' . $key1 . ',' . $key2 . ']';

        $parentKeys = [
            $key1 => [
                'RedisTreeEngine:TestParent:10',
                'RedisTreeEngine:TestParent:11'
            ],
            $key2 => [
                'RedisTreeEngine:TestParent:21',
                'RedisTreeEngine:TestParent:22',
            ]
        ];

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        CacheEnginesHelper::writeWithParent($multiKey, $values, $this->cache, $parentKeys);

        $multiVal = CacheMock::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        foreach ($parentKeys as $key => $pks) {
            foreach ($pks as $pk) {
                $childKeys = $this->redisMock->smembers($pk . ':child_keys');
                $this->assertEquals([$key], $childKeys);
            }
        }

        CacheMock::delete($parentKeys[$key2][1], $this->cache);
        $this->assertNotNull(CacheMock::read($key1, $this->cache), 'Key 1 is not deleted');
        $this->assertNull(CacheMock::read($key2, $this->cache), 'Key 2 is deleted');
    }


    public function testWriteWithParentReadDeleteWithUnqiueParent()
    {
        $key1 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithUnqiueParent:1';
        $key2 = 'RedisTreeEngine:testWriteWithParentReadDeleteWithUnqiueParent:2';
        $multiKey = '[' . $key1 . ',' . $key2 . ']';

        $parentKeys = [
            $key1 => 'RedisTreeEngine:TestParent:100',
            $key2 => 'RedisTreeEngine:TestParent:210'
        ];

        $value1 = date('Y-m-d h:m') . ':1';
        $value2 = date('Y-m-d h:m') . ':2';
        $values = array(
            $value1,
            $value2
        );

        CacheEnginesHelper::writeWithParent(
            $multiKey,
            $values,
            $this->cache,
            $parentKeys
        );

        $multiVal = CacheMock::read($multiKey, $this->cache);

        $this->assertInternalType('array', $multiVal);
        $this->assertEquals(2, count($multiVal));
        $first = $multiVal[0];
        $this->assertEquals($first, $value1);
        $second = $multiVal[1];
        $this->assertEquals($second, $value2);

        $childKeys = $this->redisMock->smembers($parentKeys[$key1] . ':child_keys');
        $this->assertEquals([$key1], $childKeys);
        $childKeys = $this->redisMock->smembers($parentKeys[$key2] . ':child_keys');
        $this->assertEquals([$key2], $childKeys);

        CacheMock::delete($parentKeys[$key2], $this->cache);
        $this->assertNotNull(CacheMock::read($key1, $this->cache), 'Key 1 is not deleted');
        $this->assertNull(CacheMock::read($key2, $this->cache), 'Key 2 is deleted');
    }


    public function testWriteWithParentDeleteWithParent()
    {

        $key1 = 'RedisTreeEngine:testWriteWithParentDeleteWithParent:1';
        $parentKey = 'RedisTreeEngine:TestParent:30';
        $value = date('Y-m-d h:m');
        CacheEnginesHelper::writeWithParent($key1, $value, $this->cache, $parentKey);
        $this->assertEquals($value, CacheMock::read($key1, $this->cache));

        $key2 = 'RedisTreeEngine:testWriteWithParentDeleteWithParent:2';
        $value2 = date('Y-m-d h:m');
        CacheMock::write($key2, $value2, $this->cache);

        CacheMock::delete($parentKey, $this->cache);
        $this->assertNull(CacheMock::read($key1, $this->cache), 'Key 1 is deleted');
        $this->assertNotNull(CacheMock::read($key2, $this->cache), 'Key 2 is not deleted');
    }


    public function testWriteWithParentDeleteWithParents()
    {
        $key = 'RedisTreeEngine:testWriteWithParentDeleteWithParents:1';
        $parentKeys = [
            'RedisTreeEngine:TestParent:40',
            'RedisTreeEngine:TestParent:50'
        ];
        $value = date('Y-m-d h:m');
        CacheEnginesHelper::writeWithParent($key, $value, $this->cache, $parentKeys);
        $this->assertEquals($value, CacheMock::read($key, $this->cache));

        $key2 = 'RedisTreeEngine:testWriteWithParentDeleteWithParents:2';
        $value2 = date('Y-m-d h:m');
        CacheMock::write($key2, $value2, $this->cache);

        CacheMock::delete($parentKeys[1], $this->cache);
        $this->assertNull(CacheMock::read($key, $this->cache), 'Key is deleted');
        $this->assertNotNull(CacheMock::read($key2, $this->cache), 'Key 2 is not deleted');
    }
}
