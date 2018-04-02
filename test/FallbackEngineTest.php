<?php
require_once __DIR__ . '/../src/Engines.php';

class FallbackEngineTest extends \PHPUnit\Framework\TestCase
{
    private $cacheOne = 'fallbackOne';
    private $cacheTwo = 'fallbackTwo';

    public function setUp()
    {
        $factory = new \M6Web\Component\RedisMock\RedisMockFactory();
        $redisMock = $factory->getAdapter('Predis\Client', true);

        Cache::config($this->cacheOne, array(
            'engine' => 'FallbackMock',
            'name' => $this->cacheOne,
            'primary' => array('engine' => 'RedisTreeMock', 'duration' => 60),
            'secondary' => array('engine' => 'FileTree', 'duration' => 60)
        ));
        CacheMock::setEngine($this->cacheOne . '-primary', $redisMock);

        Cache::config($this->cacheTwo, array(
            'engine' => 'FallbackMock',
            'name' => $this->cacheTwo,
            'primary' => array('engine' => 'FileTree', 'duration' => 60),
            'secondary' => array('engine' => 'FileTree', 'duration' => 60)
        ));
    }

    public function testFallback()
    {
        $key = 'FallbackEngineTestKey';
        $value = date('Y-m-d h:m');

        $fileCacheSettings = CacheMock::settings($this->cacheOne . '-secondary');

        CacheMock::write($key, $value, $this->cacheOne);
        $this->assertEquals($value, CacheMock::read($key, $this->cacheOne));
        $this->assertFalse(file_exists(CACHE . DS . $fileCacheSettings['prefix'] . 'fallbackenginetestkey'));
        CacheMock::delete($key, $this->cacheOne);

        CacheMock::fallback($this->cacheOne);

        CacheMock::write($key, $value, $this->cacheOne);
        $this->assertEquals($value, CacheMock::read($key, $this->cacheOne));
        $this->assertTrue(file_exists(CACHE . DS . $fileCacheSettings['prefix'] . 'fallbackenginetestkey'));

        CacheMock::delete($key, $this->cacheOne);
    }

    /**
     * @expectedException CacheException
     */
    public function testInitMissingName()
    {
        Cache::config('random', array(
            'engine' => 'FallbackMock',
            'primary' => array('engine' => 'RedisTreeMock', 'duration' => 60),
            'secondary' => array('engine' => 'FileTree', 'duration' => 60)
        ));
    }

    public function testInit()
    {
        $key = 'FallbackEngineTestKey';
        $value = date('Y-m-d h:m');

        $fileCacheSettings = CacheMock::settings($this->cacheTwo . '-secondary');

        CacheMock::write($key, $value, $this->cacheTwo);
        $this->assertEquals($value, CacheMock::read($key, $this->cacheTwo));
        $this->assertTrue(
            file_exists(CACHE . DS . $fileCacheSettings['prefix'] . 'fallbackenginetestkey'),
            'Cache file not found'
        );
        CacheMock::delete($key, $this->cacheTwo);
    }
}
