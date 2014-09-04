<?php

class FileTreeEngine extends FileEngine {


   public function read($key) {
   
      //combo keys will be of the form: prefix_[blah,blah]
      if (strpos($key, ',') !== false && strpos($key, '[') !== false && strpos($key, ']') !== false) {

         $parts = str_replace(array(',', '[', ']'), ',', $key);
         $parts = explode(',', $parts);
         
         //get rid of trailing empty
         $parts = array_diff($parts, array(''));
         
         $prefix = $parts[0];
         
         $returnVal = array();
         for($i = 1; $i < count($parts); $i++) {
            $key = $prefix . $parts[$i];
         
            $returnVal[] = parent::read($key);
         }
         return $returnVal;
      }
      
      return parent::read($key);

   } // End function read()


   public function delete($key) {

      $deletedKeysCount = 0;
      $keys = glob($this->settings['path'] . $this->key($key));
      foreach ( $keys as $k ) {
         $f = new SplFileInfo($k);
         parent::delete($f->getFilename());
         $deletedKeysCount++;
      }
      return $deletedKeysCount;

   }


   protected function _setKey($key, $createKey = false) {
      // Ned to transform key before file is created in _setKey()
      return parent::_setKey($this->key($key), $createKey);
   }

   /*
    * Change invalid file name charatcer with a _
    * Allows * for wildcharacter
    */
   public function key($key) {

      return str_replace(array(DS, '/', '.', '<', '>', '?', ':', '|', ' ', '"'), '_', $key);

   } // End function translateKey()


} // End class FileTreeEngine