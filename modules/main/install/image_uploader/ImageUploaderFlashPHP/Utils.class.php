<?php

define('UPLOADER_COOKIE_PARAM_NAME', '__COOKIE04EFC7C1758744E1847E17ED90E00C80');

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
  	if (empty($_POST[UPLOADER_COOKIE_PARAM_NAME])) {
  		return FALSE;	
  	}
  	
  	$new_cookie = explode(';', $_POST[UPLOADER_COOKIE_PARAM_NAME]);
  	$session_cookie_name = session_name();
    foreach ($new_cookie as $cookie) {
      $cookie = explode('=', $cookie);
      if (count($cookie) != 2) {
        // Incorrect value, just ignore it.
        continue;
      }

      $cookie_name = rawurldecode($cookie[0]);
      
      if ($cookie_name == $session_cookie_name) {
      	$cookie_value = rawurldecode($cookie[1]);
      	$_COOKIE[$cookie_name] = $cookie_value;
      	return TRUE;
      }
    }
    
    return FALSE;
  }

  public static function fixCookie(&$cookie_array, $new_cookie, $replace = FALSE) {
  	if (!is_array($cookie_array)) {
  		return ;
  	}
  	
  	if (empty($new_cookie)) {
  		return;
  	}
  	
  	$new_cookie = explode(';', $new_cookie);
  	foreach ($new_cookie as $cookie) {
  		$cookie = explode('=', $cookie);
  		if (count($cookie) != 2) {
  			// Incorrect value, just ignore it.
  			continue;
  		}

  		$cookie_name = rawurldecode($cookie[0]);
  		$cookie_value = rawurldecode($cookie[1]);
  		
  		if (!empty($cookie_name) && !empty($cookie_value)) {
  			if ($replace || !array_key_exists($cookie_name, $cookie_array)) {
  				$cookie_array[$cookie_name] = $cookie_value;
  			}
  		}
  	}
  }
}