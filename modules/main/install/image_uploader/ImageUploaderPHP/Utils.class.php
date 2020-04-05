<?php

define('SESSION_PARAM_NAME', 'PHP_SESSIONID');

final class Utils
{
  public static function getPhpLibraryDirectory() {
    $basePath = dirname($_SERVER['SCRIPT_FILENAME']);
    $basePath = str_replace('\\', '/', $basePath);

    $path = str_replace('\\', '/', dirname( __FILE__ ));

    $basePath = explode('/', $basePath);
    $path = explode('/', $path);
    while (count($basePath) > 0 && count($path) > 0 && $basePath[0] == $path[0]) {
      array_shift($basePath);
      array_shift($path);
    }

    while (count($basePath) > 0) {
      array_shift($basePath);
      array_unshift($path, '..');
    }

    $path = implode('/', $path);
    return $path;
  }

  public static function getDefaultScriptDirectory() {
    return self::getPhpLibraryDirectory() . '/Scripts';
  }

  public static function getRelativePath($path){

    if (array_key_exists('DOCUMENT_ROOT', $_SERVER)) {
      $docRoot = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
    } elseif (array_key_exists('SCRIPT_NAME', $_SERVER) && array_key_exists('SCRIPT_FILENAME', $_SERVER)) {
      $scriptName = $_SERVER['SCRIPT_NAME'];
      $scriptFileName = str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME']);
      $pos = strrpos($scriptFileName, $scriptName);
      if ($pos == strlen($scriptFileName) - strlen($scriptName)) {
        $docRoot = substr($scriptFileName, 0, $pos);
      }
    }

    if ($docRoot) {
      return str_replace($docRoot, '', str_replace("\\", "/", realpath($path)));
    }
    else {
      return $path;
    }

  }

  public static function throwException($message) {
    header('HTTP/1.0 500 Internal Server Error');
    throw new Exception($message);
  }

  /**
   * Recursive directory removal
   * @param $dir directoru path
   * @return bool True if directory was removed, otherwise false
   */
  public static function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir) || is_link($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') continue;
      if (!self::deleteDirectory($dir . "/" . $item)) {
        chmod($dir . "/" . $item, 0777);
        if (!self::deleteDirectory($dir . "/" . $item)) return false;
      };
    }
    return rmdir($dir);
  }

  /**
   * Fix for the Flash Player Cookie bug in Non-IE browsers. Call this before session_start() function.
   * @return bool TRUE if session was restored.
   */
  public static function restoreSession() {
    /* Since Flash Player always sends the IE cookies even in FireFox
     * we have to bypass the cookies by sending the values as part of the POST
     * and overwrite the cookies with the passed in values.
     */
    $sessionId = $_POST[SESSION_PARAM_NAME];
    if ($sessionId) {
      // place right cookie with session id
      $_COOKIE[session_name()] = $sessionId;
      return TRUE;
    } else {
      return FALSE;
    }
  }

}