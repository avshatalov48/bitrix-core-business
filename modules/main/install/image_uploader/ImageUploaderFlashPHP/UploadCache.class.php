<?php

class UploadCache {

  private $_cacheRoot;

  /**
   * UploadCache constructor
   * @param UploadSession $uploadSession
   */
  function __construct($cacheRoot) {
    if (!empty($cacheRoot)) {
      $cacheRoot = rtrim($cacheRoot, '/\\');
    }

    if (empty($cacheRoot)) {
      throw new Exception('Cache root directory can not be null.');
    }

    $this->_cacheRoot = $cacheRoot;
  }

  /**
   * Get default root upload cache directory.
   */
  public function getCacheRoot() {
    return $this->_cacheRoot;
  }

  public static function moveFile($source, $destination) {
    $result = @rename($source, $destination);
    if (!$result) {
      // copy-remove otherwise
      $result = copy($source, $destination);
      unlink($source);
    }
    return $result;
  }

  public function getLastFullScanTimestamp() {
    $file = $this->getCacheRoot().DIRECTORY_SEPARATOR.'timestamp';
    $timestamp = 0;
    if (is_file($file)) {
      $timestamp = file_get_contents($file);
      $timestamp = @intval($timestamp, 10);
    }
    return $timestamp;
  }

  public function setLastFullScanTimestamp($value = NULL) {
    $file = $this->getCacheRoot().DIRECTORY_SEPARATOR.'timestamp';
    if ($value === NULL) {
      $value = time();
    }
    file_put_contents($file, $value);
  }

  /**
   * Check if package exists in the cache
   * @param $package
   */
  public function isPackageCached($uploadSessionId, $packageIndex) {
    return file_exists($this->getPackageCacheDirectory($uploadSessionId, $packageIndex));
  }

  public function loadSavedFields($uploadSessionId, $packageIndex) {
    $filePath = $this->getPackageCacheDirectory($uploadSessionId, $packageIndex) . DIRECTORY_SEPARATOR . 'post';
    return unserialize(file_get_contents($filePath));
  }

  public function loadSavedFiles($uploadSessionId, $packageIndex) {
    $path = $this->getPackageCacheDirectory($uploadSessionId, $packageIndex) . DIRECTORY_SEPARATOR;
    $items = scandir($path);
    $rg = '#^File\\d+_\\d+$#';
    $files = array();
    foreach ($items as $file) {
      if (preg_match($rg, $file)) {
        $files[$file] = array(
          'cached' => true,
          'tmp_name' => $path . $file,
          'type' => 'application/octet-stream',
          'error' => UPLOAD_ERR_OK,
          'size' => filesize($path . $file)
        );
      }
    }
    return $files;
  }

  /**
   * Save package fields and files into upload temp cache
   * @param Package $package
   */
  public function saveRequestData($uploadSessionId, $packageIndex, $fields, $files) {
    $path = $this->getPackageCacheDirectory($uploadSessionId, $packageIndex);

    if (!file_exists($path)) {
      mkdir($path, 0777, true);
    }

    $this->saveFields($path, $fields);
    $this->saveFiles($path, $files, $fields);

    $this->setWriteTimestamp($uploadSessionId);
  }

  private function saveFields($path, $fields) {
    $filePath = $path . DIRECTORY_SEPARATOR . 'post';
    if (file_exists($filePath)) {
      $data = file_get_contents($filePath);
      $data = unserialize($data);
    }
    if (isset($data)) {
      $fields = array_merge($data, $fields);
    }
    file_put_contents($path . DIRECTORY_SEPARATOR . 'post', serialize($fields));
  }

  private function saveFiles($path, $files, $fields) {
    foreach ($files as $key => $file) {
      $filePath = $path . DIRECTORY_SEPARATOR . $key;

      if (isset($file['in_request']) && $file['in_request'] === true) {
        $data = $fields[$key];
        $fdst = fopen($filePath, 'a');
        fwrite($fdst, $data);
        fclose($fdst);
      } else {
        if (is_uploaded_file($file['tmp_name']) && !file_exists($filePath)) {
          move_uploaded_file($file['tmp_name'], $filePath);
        } else {
          $this->appendToFile($file['tmp_name'], $filePath);
        }
      }
    }
  }

  private function appendToFile($source, $destination) {
    $buff = 4096;
    $fsrc = fopen($source, 'r');
    $fdst = fopen($destination, 'a');
    while (($data = fread($fsrc, $buff)) !== '') {
      fwrite($fdst, $data);
    }
    fclose($fsrc);
    fclose($fdst);
  }

  public function getSessionCacheDirectory($uploadSessionId) {
    return $this->getCacheRoot() . DIRECTORY_SEPARATOR . $uploadSessionId;
  }

  public function getPackageCacheDirectory($uploadSessionId, $packageIndex) {
    return $this->getSessionCacheDirectory($uploadSessionId) . DIRECTORY_SEPARATOR . $packageIndex;
  }

  private function setWriteTimestamp($uploadSessionId, $time = NULL) {
    if ($time === NULL) {
      $time = time();
    }
    file_put_contents($this->getSessionCacheDirectory($uploadSessionId).DIRECTORY_SEPARATOR.'timestamp', $time);
  }

  public function getWriteTimestamp($uploadSessionId) {
    $timestampFile = $this->getSessionCacheDirectory($uploadSessionId).DIRECTORY_SEPARATOR.'timestamp';
    $timestamp = -1;
    if (is_file($timestampFile)) {
      $timestamp = file_get_contents($timestampFile);
      $timestamp = @intval($timestamp, 10);
    }

    if ($timestamp <= 0) {
      // If no timestamp file then set current time
      $timestamp = time();
      $this->setWriteTimestamp($uploadSessionId, $timestamp);
    }

    return $timestamp;
  }

  public function cleanUploadSessionCache($uploadSessionId) {
    $dir = $this->getSessionCacheDirectory($uploadSessionId);
    if (!empty($dir) && file_exists($dir)) {
      UploadCache::rmdir_recursive($dir);
    }
  }

  private static function rmdir_recursive($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != '.' && $object != '..') {
          if (is_dir($dir.DIRECTORY_SEPARATOR.$object)) {
            UploadCache::rmdir_recursive($dir.DIRECTORY_SEPARATOR.$object);
          } else {
            unlink($dir.DIRECTORY_SEPARATOR.$object);
          }
        }
      }
      reset($objects);
      rmdir($dir);
    }
  }
}