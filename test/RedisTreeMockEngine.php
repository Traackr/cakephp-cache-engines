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
        $profile = $this->redis->getProfile();
        // profile is empty for redis-mock
        $this->supportsScan = !empty($profile) && $profile->supportsCommand('scan');
    }

}
