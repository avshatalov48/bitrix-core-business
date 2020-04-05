<?php

require_once 'Utils.class.php';
require_once 'UploadSession.class.php';

// sys_get_temp_dir function exists in PHP 5 >= 5.2.1
if ( !function_exists('sys_get_temp_dir')) {
  function sys_get_temp_dir() {
    if ($temp = ini_get('upload_tmp_dir')) {
      return $temp;
    }
    if ($temp = getenv('TMP')) {
      return $temp;
    }
    if ($temp = getenv('TEMP')) {
      return $temp;
    }
    if ($temp = getenv('TMPDIR')){
      return $temp;
    }
    $temp = tempnam(dirname(__FILE__), '');
    if (file_exists($temp)) {
      unlink($temp);
      return dirname($temp);
    }
    return null;
  }
}

/**
 * Handle upload requests from uploader.
 */
class UploadHandler {

  private static $_processed = false;

  private $_fileUploadedCallback = NULL;
  private $_allFilesUploadedCallback = NULL;

  private $_destination;
  private $_cacheAliveTimeout = 1800; // 30 minutes
  private $_cacheRoot;

  function __construct() {
    // set default cache directory
    $tempDir = rtrim(sys_get_temp_dir(), '/\\');
    $this->_cacheRoot = $tempDir . DIRECTORY_SEPARATOR . 'uploader_c2215afa418f4cc2bc2c0f92746882f0';
  }

  /**
   * Add file uploaded callback function. Function will be called for every uploaded file.
   * @param callback $callback
   */
  public function setFileUploadedCallback($callback) {
    $this->_fileUploadedCallback = $callback;
  }

  public function getFileUploadedCallback() {
    return $this->_fileUploadedCallback;
  }

  public function setAllFilesUploadedCallback($callback) {
    $this->_allFilesUploadedCallback = $callback;
  }

  public function getAllFilesUploadedCallback() {
    return $this->_allFilesUploadedCallback;
  }

  /**
   * Get upload cache expire timeout.
   */
  public function getCacheAliveTimeout() {
    return $this->_cacheAliveTimeout;
  }

  /**
   * Set upload cache expire timeout.
   * After timeout expires all files for this upload session will be removed.
   * @param int $value Timeout in seconds
   */
  public function setCacheAliveTimeout($value) {
    $this->_cacheAliveTimeout = $value;
  }

  /**
   * Get temp directory for uploaded files
   */
  public function getUploadCacheDirectory() {
    return $this->_cacheRoot;
  }

  /**
   * Set temp directory for uploaded files
   * @param string $value
   */
  public function setUploadCacheDirectory($value) {
    $this->_cacheRoot = $value;
  }

  public function processRequest() {

    // Ignore other requests except POST.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST[PostFields::packageGuid])) {
      return;
    }
    if (UploadHandler::$_processed) {
      return;
    }

    UploadHandler::$_processed = true;

    if (array_key_exists('HTTP_X_PREPROCESS_REQUIRED', $_SERVER) && $_SERVER['HTTP_X_PREPROCESS_REQUIRED'] == 'true') {
      $files = $this->getRequestFiles($_POST);
    } else {
      $files = &$_FILES;
    }

    // Restore cookies
    if (!empty($_POST[UPLOADER_COOKIE_PARAM_NAME])) {
    	$cookies_string = $_POST[UPLOADER_COOKIE_PARAM_NAME];
      Utils::fixCookie($_COOKIE, $cookies_string);
    }

    $uploadCache = new UploadCache($this->_cacheRoot);
    $uploadSession = new UploadSession($uploadCache, $_POST, $files, $_SERVER);

    if (!empty($this->_allFilesUploadedCallback)) {
      $uploadSession->setAllFilesUploadedCallback($this->_allFilesUploadedCallback);
    }

    if (!empty($this->_fileUploadedCallback)) {
      $uploadSession->setFileUploadedCallback($this->_fileUploadedCallback);
    }
    $uploadSession->processRequest();

    $this->removeExpiredSessions($uploadCache);
    
    // Flash requires non-empty response
    if (!headers_sent() && array_key_exists('HTTP_USER_AGENT', $_SERVER) && $_SERVER['HTTP_USER_AGENT'] === 'Shockwave Flash') {
      echo '0';
    }
  }

  /**
   * Image Uploader Flash upload files as ordinary field with binary data.
   * So it is placed in the $_POST and not in $_FILES
   */
  private function getRequestFiles($post) {
    $files = array();
    for ($i = 0; $i < 3; $i++) {
      $fileField = sprintf(PostFields::file, $i, 0);
      $fileNameField = sprintf(PostFields::fileName, $i, 0);
      $fileSizeField = sprintf(PostFields::fileSize, $i, 0);

      if (array_key_exists($fileField, $post) && isset($post[$fileField])) {
        $files[$fileField] = array(
          'name' => $post[$fileNameField],
          'in_request' => true,
          'size' => intval($post[$fileSizeField], 10)
        );
      }
    }
    
    return $files;
  }

  /**
   * Remove expired upload sessions
   */
  private function removeExpiredSessions($uploadCache) {
    $cacheRoot = $uploadCache->getCacheRoot();
    if (empty($cacheRoot) || !is_dir($cacheRoot)) {
      return;
    }

    $cacheRoot = rtrim($cacheRoot, '/\\');
     
    $lastFullScan = $uploadCache->getLastFullScanTimestamp();
    $currentTimestamp = time();
     
    // Do not scan all cache too often
    if ($lastFullScan + $this->_cacheAliveTimeout / 2 > $currentTimestamp) {
      return;
    }
     
    $dirs = scandir($cacheRoot);
    foreach ($dirs as $dir) {
      if ($dir != '.' && $dir != '..' && is_dir($cacheRoot . DIRECTORY_SEPARATOR . $dir)) {
        $uploadCache = new UploadCache($cacheRoot);
        $uploadSessionId = $dir;
        if ($uploadCache->getWriteTimestamp($uploadSessionId) + $this->_cacheAliveTimeout < $currentTimestamp) {
          $uploadCache->cleanUploadSessionCache($uploadSessionId);
        }
      }
    }

    $uploadCache->setLastFullScanTimestamp($currentTimestamp);
  }

  public function saveFiles($destination) {
    if (empty($destination)) {
      return;
    }
    rtrim($destination, '/\\');
    $this->_destination = $destination;

    $this->setFileUploadedCallback(array($this, 'saveUploadedFileCallback'));
    $this->processRequest();
  }

  /**
   * Save uploaded file callback
   * @param UploadedFile $uploadedFile
   */
  public function saveUploadedFileCallback($uploadedFile) {
    if (empty($this->_destination)) {
      return;
    }
    /* @var $uploadedFile UploadedFile */
    $relativePath = $uploadedFile->getRelativePath();
    if (empty($relativePath)) {
      $basePath = $this->_destination;
    } else {
      $relativePath = trim($relativePath, '/\\');
      $basePath = $this->_destination . DIRECTORY_SEPARATOR . $relativePath;
    }
    if (!is_dir($basePath)) {
      mkdir($basePath, 0777, true);
    }
    $basePath .= DIRECTORY_SEPARATOR;
    foreach ($uploadedFile->getConvertedFiles() as $convertedFile) {
      /* @var $convertedFile ConvertedFile */
      $name = $convertedFile->getName();
      $path_info = pathinfo($name);
      $i = 2;
      $fullFilePath = $basePath . $name;
      while (file_exists($fullFilePath)) {
        $fullFilePath = $basePath . $path_info['filename'] . '_' . $i . '.' . $path_info['extension'];
        $i++;
      }
      $convertedFile->moveTo($fullFilePath);
    }
  }
}