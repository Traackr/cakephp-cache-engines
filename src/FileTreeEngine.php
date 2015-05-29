<?php

namespace Cake\Cache;

class FileTreeEngine extends Engine\FileEngine {


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