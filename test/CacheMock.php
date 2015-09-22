<?php

/**
 * Mock for the Cache base class
 * Exposes a setEngine() method to update an engine already configured.
 * Exposes a fallback() method that can be used to force a Fallback engine
 * (if used) to fallback
 */
class CacheMock extends Cache
{

    public static function setEngine($name, $engine)
    {
        self::$_engines[$name]->setEngine($engine);
    }

    public static function getEngine($name)
    {
        return self::$_engines[$name]->getEngine();
    }

    public static function fallback($name)
    {
        self::$_engines[$name]->fallback();
    }

    public static function keys($pattern, $name)
    {
        return self::$_engines[$name]->keys($pattern);
    }

    public static function getNodesKey($name)
    {
        return self::$_engines[$name]->getNodeskey();
    }

    public static function sismember($key, $member, $name)
    {
        return self::$_engines[$name]->sismember($key, $member);
    }
}
