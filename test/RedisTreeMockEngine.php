<?php
/**
 * Mock for the RedisTreeEngine
 * Adds a setRedis() function to change the internal Redis client
 */
class RedisTreeMockEngine extends RedisTreeEngine
{

    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    public function keys()
    {
        return $this->redis->keys('*');
    }
}
