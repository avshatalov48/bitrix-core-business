<?php

require_once 'UploadCache.class.php';
require_once 'UploadedFile.class.php';

class Package {

  private $_uploadedFiles = NULL;
  private $_packageIndex = NULL;
  private $_uploadSession = NULL;
  private $_completed = false;
  private $_cached = false;
  private $_packageFields = NULL;
  private $_packageFiles = NULL;

  /**
   * Package constructor
   * @param UploadSession $uploadSession
   * @param int $index
   * @param array $post
   * @param array $files
   */
  function __construct($uploadSession, $index, $saveIntoCache = false) {
    if (empty($uploadSession)) {
      throw new Exception('Upload session can not be null');
    }

    $post = $uploadSession->getRequestFields();
    $files = $uploadSession->getRequestFiles();

    $this->_uploadSession = $uploadSession;
    $this->_packageIndex = $index;
    
    $uploadSessionId = $uploadSession->getUploadSessionId();

    if ($index < $post[PostFields::packageIndex]) {
      // This is previous package we save for AllPackages event
      $this->_completed = true;
      $this->_cached = true;
    } else if ($index == $post[PostFields::packageIndex]) {
      // this package is current package
      $this->_completed = @!empty($post[PostFields::packageComplete]);
      $cache = $this->_uploadSession->getUploadCache();
      $this->_cached = $cache->isPackageCached($uploadSessionId, $this->_packageIndex);

      // If package completed, but already cached then it is last chunk of the package
      // and we also need to save it.
      if (!$this->_completed || $this->_cached || $saveIntoCache) {
        $cache->saveRequestData($uploadSessionId, $this->_packageIndex, $post, $files);
        $this->_cached = true;
      } else {
        $this->_packageFields = $post;
        $this->_packageFiles = $files;
      }
    } else {
      throw new Exception('Incorrect $index value or POST fields.');
    }
  }

  private function loadSavedFields() {
    $uploadSessionId = $this->_uploadSession->getUploadSessionId();
    $cache = $this->_uploadSession->getUploadCache();
    $this->_packageFields = $cache->loadSavedFields($uploadSessionId, $this->_packageIndex);
  }

  private function loadSavedFiles() {
    $uploadSessionId = $this->_uploadSession->getUploadSessionId();
    $cache = $this->_uploadSession->getUploadCache();
    $this->_packageFiles = $cache->loadSavedFiles($uploadSessionId, $this->_packageIndex);
  }

  /**
   * Get package field value by field name
   * @param string $fieldName
   */
  public function getPackageField($fieldName) {
    $fields = $this->getPackageFields();
    $value = @$fields[$fieldName];
    return isset($value) ? $value : NULL;
  }

  /**
   * @return boolean Returns true if package received completely, otherwise returns false.
   */
  public function getCompleted() {
    return $this->_completed;
  }
  
  public function getCached() {
  	return $this->_cached;
  }

  /**
   * Get all package fields
   * @return array
   */
  public function getPackageFields() {
    if (empty($this->_packageFields)) {
      $this->loadSavedFields();
    }
    return $this->_packageFields;
  }

  /**
   * Get total package count for upload session
   */
  public function getPackageCount() {
    return $this->getPackageField(PostFields::packageCount);
  }

  /**
   * Get count of files selected to upload in this package
   */
  public function getPackageFileCount() {
    return $this->getPackageField(PostFields::packageFileCount);
  }

  /**
   * Get unique identificator for upload session
   */
  public function getPackageGuid() {
    return $this->getPackageField(PostFields::packageGuid);
  }

  /**
   * Get packahe index
   */
  public function getPackageIndex() {
    return $this->_packageIndex;
  }

  /**
   * Get uploaded files
   * @return array Array of UploadedFile objects
   */
  public function getUploadedFiles() {
    // Can not get files from incomplete package
    if (!$this->_completed) {
      return NULL;
    }
    if (!is_array($this->_uploadedFiles)) {
      $this->getUploadedFilesInternal();
    }
    return $this->_uploadedFiles;
  }

  private function getUploadedFilesInternal() {
    $this->_uploadedFiles = array();
    $count = $this->getPackageField(PostFields::packageFileCount);
    if ($count > 0) {
      if (!is_array($this->_packageFiles)) {
        if ($this->_cached) {
          $this->loadSavedFiles();
        } else {
          throw new Exception('Package claims to be non-cached, but packageFiles property is NULL');
        }
      }
      for ($i = 0; $i < $count; $i++) {
        $this->_uploadedFiles[] = new UploadedFile($this, $i, $this->_packageFiles);
      }
    }
  }
}