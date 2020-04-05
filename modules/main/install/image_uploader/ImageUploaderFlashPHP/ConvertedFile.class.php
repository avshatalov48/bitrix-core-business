<?php

require_once 'UploadCache.class.php';

class ConvertedFile {

  private $_uploadedFile;
  private $_convertedFileIndex;
  private $_uploadedFileIndex;
  private $_file;
  private $_size;

  /**
   * ConvertedFile constructor
   * @param UploadedFile $uploadedFile
   * @param int $index
   * @param array $file
   */
  function __construct($uploadedFile, $index, $file) {
    if (empty($uploadedFile)) {
      throw new Exception('$uploadedFile parameter can not be empty');
    }

    if (empty($file)) {
      throw new Exception('$file parameter can not be empty');
    }
    $this->_convertedFileIndex = $index;

    $this->_uploadedFile = $uploadedFile;
    $this->_uploadedFileIndex = $uploadedFile->getIndex();
    $this->_file = $file;

    $expectedSize = $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::fileSize,
    $this->_convertedFileIndex, $this->_uploadedFileIndex));

    if ($expectedSize < 2*1024*1024*1024) {
      $actualSize = $this->_file['size'];
      if ($expectedSize != $actualSize) {
        throw new Exception('File is corrupted');
      }
    }

    $this->_size = intval($expectedSize, 10);
  }

  /**
   * Get height of converted file
   */
  public function getHeight() {
    return $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::fileHeight,
    $this->_convertedFileIndex, $this->_uploadedFileIndex));
  }

  /**
   * Get converter mode applied to the uploaded file
   */
  public function getMode() {
    return $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::fileMode,
    $this->_convertedFileIndex, $this->_uploadedFileIndex));
  }

  /**
   * Get converted file name
   */
  public function getName() {
    return $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::fileName,
    $this->_convertedFileIndex, $this->_uploadedFileIndex));
  }

  /**
   * Get converted file size
   * @return int
   */
  public function getSize() {
    return $this->_size;
  }

  /**
   * Get converted file width
   */
  public function getWidth() {
    return $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::fileWidth,
    $this->_convertedFileIndex, $this->_uploadedFileIndex));
  }

  /**
   * Get content of converted file into string
   * @return string
   */
  public function getFileContent() {
    if (isset($this->_file['in_request']) && $this->_file['in_request'] == true) {
      return $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::file, $this->_convertedFileIndex, $this->_uploadedFileIndex));
    } else {
      return file_get_contents($this->_file['tmp_name']);
    }
  }

  /**
   * Move converted file to a new location
   * @param $destination The destination of the moved file
   */
  public function moveTo($destination) {

    if (isset($this->_file['in_request']) && $this->_file['in_request'] == true) {
      $data = $this->_uploadedFile->getPackage()->getPackageField(sprintf(PostFields::file,
      $this->_convertedFileIndex, $this->_uploadedFileIndex));
      $handle = fopen($destination, 'w');
      $result = fwrite($handle, $data);
      if ($result !== FALSE) {
        // no error
        $result = TRUE;
      }
      $result &= fclose($handle);
    } else {
      $path = $this->_file['tmp_name'];
      if (is_uploaded_file($path)) {
        $result = move_uploaded_file($path, $destination);
      } else if ($this->_file['cached']) {
        $result = UploadCache::moveFile($path, $destination);
      } else {
        throw new Exception('File is not "is_uploaded_file" and is not cached file');
      }
    }
    if (!$result) {
      throw new Exception("Unable to move file \"$path\" to \"$destination\"");
    }
  }
}