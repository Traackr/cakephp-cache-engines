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
        self::$_engines[$name]->setRedis($engine);
    }

    public static function fallback($name)
    {
        self::$_engines[$name]->fallback();
    }

    public static function keys($name)
    {
        return self::$_engines[$name]->keys();
    }
}
