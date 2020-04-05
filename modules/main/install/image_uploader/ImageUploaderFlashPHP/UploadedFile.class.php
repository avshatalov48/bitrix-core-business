<?php

require_once 'ConvertedFile.class.php';

class UploadedFile {

  private $_package;
  private $_index;
  private $_packageFiles;
  private $_convertedFiles = NULL;

  /**
   * UploadedFile constructor
   * @param Package $package
   * @param int $index
   */
  function __construct($package, $index, $packageFiles) {
    if ($package == NULL) {
      throw new Exception('$package parameter can not be null');
    }
    if (@!is_numeric($index)) {
      throw new Exception('$index parameter should be a number');
    }
    if (@!is_array($packageFiles)) {
      throw new Exception('$packageFiles parameter should be an array');
    }
    $this->_package = $package;
    $this->_index = $index;
    $this->_packageFiles = $packageFiles;
  }

  private function initFileArray() {
    $rg = '/^File(\d+)_' . $this->_index . '$/i';
    $this->_convertedFiles = array();
    foreach ($this->_packageFiles as $key => $file) {
      $mathes = null;
      if (preg_match($rg, $key, $mathes)) {
        $converterIndex = $mathes[1];
        $this->_convertedFiles[$converterIndex] = new ConvertedFile($this, $converterIndex, $file);
      }
    }
  }
  
  public function getAngle() {
    return $this->_package->getPackageField(sprintf(PostFields::angle, $this->_index));
  }

  public function getConvertedFiles() {
    if (!is_array($this->_convertedFiles)) {
      $this->initFileArray();
    }
    return $this->_convertedFiles;
  }
  
  public function getCropBounds() {
    return $this->_package->getPackageField(sprintf(PostFields::cropBounds, $this->_index));
  }

  public function getDescription() {
    return $this->_package->getPackageField(sprintf(PostFields::description, $this->_index));
  }

  public function getIndex() {
    return $this->_index;
  }

  /**
   * Get package
   * @return Package
   */
  public function getPackage() {
    return  $this->_package;
  }

  public function getRelativePath() {
    $name = $this->_package->getPackageField(sprintf(PostFields::sourceName, $this->_index));
    if (empty($name)) {
      return NULL;
    } else {
    	$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
      $dir = dirname($name);
      if (empty($dir) || $dir == '.') {
        return NULL;
      } else {
        return $dir;
      }
    }
  }

  public function getSourceCreatedDateTime() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceCreatedDateTime, $this->_index));
  }

  public function getSourceCreatedDateTimeLocal() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceCreatedDateTimeLocal, $this->_index));
  }

  public function getSourceHeight() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceHeight, $this->_index));
  }

  public function getSourceLastModifiedDateTime() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceLastModifiedDateTime, $this->_index));
  }

  public function getSourceLastModifiedDateTimeLocal() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceLastModifiedDateTimeLocal, $this->_index));
  }

  public function getSourceName() {
    $name = $this->_package->getPackageField(sprintf(PostFields::sourceName, $this->_index));
    if (empty($name)) {
      return $name;
    } else {
    	$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
      return basename($name);
    }
  }

  public function getSourceSize() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceSize, $this->_index));
  }

  public function getSourceWidth() {
    return $this->_package->getPackageField(sprintf(PostFields::sourceWidth, $this->_index));
  }

  public function getHorizontalResolution() {
    return $this->_package->getPackageField(sprintf(PostFields::horizontalResolution, $this->_index));
  }

  public function getVerticalResolution() {
    return $this->_package->getPackageField(sprintf(PostFields::verticalResolution, $this->_index));
  }

  public function getTag() {
    return $this->_package->getPackageField(sprintf(PostFields::tag, $this->_index));
  }
}