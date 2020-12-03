<?php

require_once dirname(__FILE__) . '/../src/Engines.php';


class CacheEnginesHelperTest extends \PHPUnit\Framework\TestCase
{


    private $cache = 'RedisTree';

    public function setUp()
    {

        // Comment this to use real redis server
        /**/

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

    public function testWriteWithParent()
    {
        // use RedisTreeEngine, which supports writeWithParent
        $factory = new \M6Web\Component\RedisMock\RedisMockFactory();
        $redisMock = $factory->getAdapter('Predis\Client', true);
        CacheMock::config(
            'RedisTree',
            [
                'engine' => 'RedisTreeMock',
                'duration' => 4
            ]
        );
        CacheMock::setEngine('RedisTree', $redisMock);

        $key = 'CacheEnginesHelper:testWriteWithParent:1';
        $parentKeys = [
            'CacheEnginesHelper:TestParent:10',
            'CacheEnginesHelper:TestParent:20'
        ];
        $value = date('Y-m-d h:m');
        CacheEnginesHelper::writeWithParent($key, $value, $this->cache, $parentKeys);
        $this->assertEquals($value, CacheMock::read($key, $this->cache));
    }

    public function testWriteWithParentTriggerError()
    {
        // use FileEngine, which does not support writeWithParent
        CacheMock::config(
            'File',
            [
                'engine' => 'File',
                'duration' => 4
            ]
        );

        $key = 'CacheEnginesHelper:testWriteWithParent:1';
        $parentKeys = [
            'CacheEnginesHelper:TestParent:10',
            'CacheEnginesHelper:TestParent:20'
        ];
        $value = date('Y-m-d h:m');
        try {
            CacheEnginesHelper::writeWithParent(
                $key,
                $value,
                'File',
                $parentKeys
            );
        } catch (PHPUnit_Framework_Error $e) {
            $this->assertTrue(true);
        }
    }
}
