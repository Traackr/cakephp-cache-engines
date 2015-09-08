<?php

/**
 * File system storage engine for cache.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.cache
 */
class FileTreeEngine extends FileEngine
{

    /**
     * Enhanced read to handle combo keys surrounded by brackets
     * @param string $key
     * @return array|mixed
     */
    public function read($key)
    {

        //combo keys will be of the form: prefix_[blah,blah]; prefix is prepended by internal Cake code
        if (strpos($key, '[') !== false && substr($key, -1) == ']') {
            $parts = str_replace(array('[', ']'), ',', $key);
            $parts = explode(',', $parts);

            //get rid of trailing empty (or beginning empty, if no prefix)
            $parts = array_diff($parts, array(''));

            //note that if there is no prefix, the array_diff above will leave us with an array whose first index is "1"
            if (isset($parts[0])) {
                $prefix = $parts[0];
            } else {
                $prefix = '';
            }

            $returnVal = array();
            for ($i = 1; $i < count($parts); $i++) {
                $key = $prefix . $parts[$i];

                $returnVal[] = parent::read($key);
            }
            return $returnVal;
        }

        return parent::read($key);

    }

    /**
     * Enhanced read to handle combo keys surrounded by brackets
     * @param string $key
     * @param mixed $data
     * @param int $duration
     * @return bool
     * @throws Exception
     */
    public function write($key, $data, $duration)
    {

        //combo keys will be of the form: prefix_[blah,blah]; prefix is prepended by internal Cake code
        if (strpos($key, '[') !== false && substr($key, -1) == ']') {
            $parts = str_replace(array('[', ']'), ',', $key);
            $parts = explode(',', $parts);

            //get rid of trailing empty (or beginning empty, if no prefix)
            $parts = array_diff($parts, array(''));

            //note that if there is no prefix, the array_diff above will leave us with an array whose first index is "1"
            if (isset($parts[0])) {
                $prefix = $parts[0];
            } else {
                $prefix = '';
            }

            $success = true;
            for ($i = 1; $i < count($parts); $i++) {
                $key = $prefix . $parts[$i];

                if (!isset($data[$i - 1])) {
                    throw new Exception('Num keys != num values.');
                }
                //this is the best we can do to return an "overall success"
                $success = $success && parent::write($key, $data[$i - 1], $duration);
            }
            return $success;
        }

        return parent::write($key, $data, $duration);

    }


    public function delete($key)
    {

        $deletedKeysCount = 0;
        $keys = glob($this->settings['path'] . $this->key($key));
        foreach ($keys as $k) {
            $f = new SplFileInfo($k);
            parent::delete($f->getFilename());
            $deletedKeysCount++;
        }
        return $deletedKeysCount;

    }


    protected function _setKey($key, $createKey = false)
    {
        // Need to transform key before file is created in _setKey()
        return parent::_setKey($this->key($key), $createKey);
    }

    /*
     * Change invalid file name character with a _
     * Allows * for wildcard
     */
    public function key($key)
    {

        return str_replace(array(DS, '/', '.', '<', '>', '?', ':', '|', ' ', '"'), '_', $key);

    }
}
