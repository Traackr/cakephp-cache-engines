<?php

class FileTreeEngine extends FileEngine {


   public function delete($key) {

      // $key = $this->key($key);
      // echo 'Cache: ', CACHE ."\n";
      // echo 'Path: ' . $this->settings['path'] ."\n";
      // echo 'Prefix: ' . $this->settings['prefix'] ."\n";
      // echo 'Key: ' . $key ."\n";
      // var_dump(glob($this->settings['path'] . $key));
      $keys = glob($this->settings['path'] . $this->key($key));
      foreach ( $keys as $k ) {
         $f = new SplFileInfo($k);
         parent::delete($f->getFilename());
         // echo $f->getFilename() . "\n";
      }

   }


   protected function _setKey($key, $createKey = false) {
      return parent::_setKey($this->key($key), $createKey);
   }

   public function key($key) {

      return str_replace(array(DS, '/', '.', '<', '>', '?', ':', '|', ' ', '"'), '_', $key);

   } // End function translateKey()


   /**
 * Used to clear a directory of matching files.
 *
 * @param string $path The path to search.
 * @param integer $now The current timestamp
 * @param integer $threshold Any file not modified after this value will be deleted.
 * @return void
 */
   protected function _clearDirectory($path, $now, $threshold) {
      $prefixLength = strlen($this->settings['prefix']);

      if (!is_dir($path)) {
         return;
      }

      $dir = dir($path);
      while (($entry = $dir->read()) !== false) {
         // if (substr($entry, 0, $prefixLength) !== $this->settings['prefix']) {
         //    continue;
         // }
         $filePath = $path . $entry;
         if (!file_exists($filePath) || is_dir($filePath)) {
            continue;
         }
         $file = new SplFileObject($path . $entry, 'r');

         if ($threshold) {
            $mtime = $file->getMTime();

            if ($mtime > $threshold) {
               continue;
            }
            $expires = (int)$file->current();

            if ($expires > $now) {
               continue;
            }
         }
         if ($file->isFile()) {
            $filePath = $file->getRealPath();
            $file = null;

            //@codingStandardsIgnoreStart
            @unlink($filePath);
            //@codingStandardsIgnoreEnd
         }
      }
   }

} // End class FileTreeEngine