<?php

/**
 * The Fallback engine manages two cache engines.
 * The primary is used, but if any operation fails, it will automatically
 * fallback to the secondary
 */
class FallbackEngine extends CacheEngine
{

    private $primaryConfig = 'primary';
    private $secondaryConfig = 'secondary';

    /*
     * The active of the 2 caches
     */
    private $activeCache = null;


    public function init($settings = array())
    {

        $settings += array(
            'engine' => 'Fallback',
            'prefix' => '',
            'probability' => 100,
            'duration' => 0,
            'groups' => array()
        );
        parent::init($settings);

        // 'name' is required
        if (empty($this->settings['name'])) {
            return false;
        }
        // Generate unique config name
        $config = $this->settings['name'];
        $this->primaryConfig = $config . "-" . $this->primaryConfig;
        $this->secondaryConfig = $config . "-" . $this->secondaryConfig;

        try {
            if (isset($this->settings['primary']['engine'])) {
                Cache::config($this->primaryConfig, $this->settings['primary']);
            }
            $this->activeCache = $this->primaryConfig;

            if (isset($this->settings['secondary']['engine'])) {
                Cache::config($this->secondaryConfig, $this->settings['secondary']);
            }
        } catch (CacheException $e) {
            try {
                if (isset($this->settings['secondary']['engine'])) {
                    Cache::config($this->secondaryConfig, $this->settings['secondary']);
                }
                $this->activeCache = $this->secondaryConfig;
            } catch (CacheException $ee) {
                return false;
            }
        }
        return true;

    }

    public function write($key, $value, $duration)
    {
        try {
            return Cache::write($key, $value, $this->activeCache);
        } catch (Exception $e) {
            $this->fallback();
            return Cache::write($key, $value, $this->activeCache);
        }
    }

    /**
     * Write data for key into a cache engine with one or more 'parent'.
     *
     * @param string $key Identifier for the data
     * @param mixed $value Data to be cached
     * @param integer $duration How long to cache the data, in seconds
     * @param string|array $parentKey Parent key that data is a dependent child of
     * @return bool True if the data was successfully cached, false on failure
     * @throws Exception
     */
    public function writeWithParent($key, $value, $duration, $parentKey = '')
    {
        return Cache::engine($this->activeCache)->writeWithParent($key, $value, $duration, $parentKey);
    }

    public function read($key)
    {
        try {
            return Cache::read($key, $this->activeCache);
        } catch (Exception $e) {
            $this->fallback();
            return Cache::read($key, $this->activeCache);
        }
    }

    public function delete($key)
    {
        try {
            return Cache::delete($key, $this->activeCache);
        } catch (Exception $e) {
            $this->fallback();
            return Cache::delete($key, $this->activeCache);
        }

    }

    public function increment($key, $offset = 1)
    {
        try {
            return Cache::increment($key, $offset, $this->activeCache);
        } catch (Exception $e) {
            $this->fallback();
            return Cache::increment($key, $offset, $this->activeCache);
        }
    }

    public function decrement($key, $offset = 1)
    {
        try {
            return Cache::decrement($key, $offset, $this->activeCache);
        } catch (Exception $e) {
            $this->fallback();
            return Cache::decrement($key, $offset, $this->activeCache);
        }
    }

    public function clear($check = false)
    {
        try {
            return Cache::clear($check, $this->activeCache);
        } catch (Exception $e) {
            $this->fallback();
            return Cache::clear($check, $this->activeCache);
        }
    }

    protected function fallback($setPrimary = false)
    {
        if ($setPrimary) {
            $this->activeCache = $this->primaryConfig;
        } else {
            $this->activeCache = $this->secondaryConfig;
        }
    }

    public function key($key)
    {
        return Cache::engine($this->activeCache)->key($key);
    }
}
