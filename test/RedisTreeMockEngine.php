<?php

/**
 * Mock for the RedisTreeEngine
 * Adds a setRedis() function to change the internal Redis client
 */
class RedisTreeMockEngine extends RedisTreeEngine
{

    public function setEngine($redis)
    {
        $this->redis = $redis;
        $profile = $this->redis->getProfile();
        // profile is empty for redis-mock
        $this->supportsScan = !empty($profile) && $profile->supportsCommand('scan');
    }

    public function getEngine()
    {
        return $this->redis;
    }

    public function keys($pattern)
    {
        return $this->redis->keys($pattern);
    }

    public function sismember($key, $member)
    {
        return $this->redis->sismember($key, $member);
    }
}
