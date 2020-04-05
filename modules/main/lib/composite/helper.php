<?
namespace Bitrix\Main\Composite;

/**
 *
 * This class shares static methods between Responder and other composite classes.
 * Methods of this class can't call Bitrix API at all.
 *
 * @alias \CHTMLPagesCache
 * @package Bitrix\Main\Composite
 */
class Helper
{
	private static $options = array();
	private static $isAjaxRequest = null;
	private static $ajaxRandom = null;

	/**
	 * Returns Request URI
	 * @return string
	 */
	public static function getRequestUri()
	{
		if (self::isSpaMode())
		{
			return isset($options["SPA_REQUEST_URI"]) ? $options["SPA_REQUEST_URI"] : "/";
		}
		else
		{
			return $_SERVER["REQUEST_URI"];
		}
	}

	/**
	 * Returns HTTP hostname
	 *
	 * @param string $host
	 *
	 * @return string
	 */
	public static function getHttpHost($host = null)
	{
		return preg_replace("/:(80|443)$/", "", $host === null ? $_SERVER["HTTP_HOST"] : $host);
	}

	/**
	 * Returns valid domains from the composite options
	 * @return array
	 */
	public static function getDomains()
	{
		$options = self::getOptions();
		$domains = array();
		if (isset($options["DOMAINS"]) && is_array($options["DOMAINS"]))
		{
			$domains = array_values($options["DOMAINS"]);
		}

		return array_map(array(__CLASS__, "getHttpHost"), $domains);
	}

	public static function getSpaPostfixByUri($requestUri)
	{
		$options = self::getOptions();
		$requestUri = ($p = strpos($requestUri, "?")) === false ? $requestUri : substr($requestUri, 0, $p);

		if (isset($options["SPA_MAP"]) && is_array($options["SPA_MAP"]))
		{
			foreach ($options["SPA_MAP"] as $mask => $postfix)
			{
				if (preg_match($mask, $requestUri))
				{
					return $postfix;
				}
			}
		}

		return null;
	}

	public static function getSpaPostfix()
	{
		$options = self::getOptions();
		if (isset($options["SPA_MAP"]) && is_array($options["SPA_MAP"]))
		{
			return array_values($options["SPA_MAP"]);
		}

		return array();
	}

	public static function getRealPrivateKey($privateKey = null, $postfix = null)
	{
		if (self::isSpaMode())
		{
			$postfix = $postfix === null ? self::getSpaPostfixByUri($_SERVER["REQUEST_URI"]) : $postfix;
			if ($postfix !== null)
			{
				$privateKey .= $postfix;
			}
		}

		return $privateKey;
	}

	public static function getUserPrivateKey()
	{
		$options = self::getOptions();
		if (isset($options["COOKIE_PK"]) && array_key_exists($options["COOKIE_PK"], $_COOKIE))
		{
			return $_COOKIE[$options["COOKIE_PK"]];
		}

		return null;
	}

	public static function setUserPrivateKey($prefix, $expire = 0)
	{
		$options = self::getOptions();
		if (isset($options["COOKIE_PK"]) && strlen($options["COOKIE_PK"]) > 0)
		{
			setcookie($options["COOKIE_PK"], $prefix, $expire, "/", false, false, true);
		}
	}

	public static function deleteUserPrivateKey()
	{
		$options = self::getOptions();
		if (isset($options["COOKIE_PK"]) && strlen($options["COOKIE_PK"]) > 0)
		{
			setcookie($options["COOKIE_PK"], "", 0, "/");
		}
	}

	/**
	 * Returns true if the current request was initiated by Ajax.
	 *
	 * @return bool
	 */
	public static function isAjaxRequest()
	{
		if (self::$isAjaxRequest === null)
		{
			self::$isAjaxRequest =
				(isset($_SERVER["HTTP_BX_ACTION_TYPE"]) && $_SERVER["HTTP_BX_ACTION_TYPE"] === "get_dynamic") ||
				(defined("actionType") && constant("actionType") === "get_dynamic");
		}

		return self::$isAjaxRequest;
	}

	public static function isAppCacheRequest()
	{
		return
			(isset($_SERVER["HTTP_BX_CACHE_MODE"]) && $_SERVER["HTTP_BX_CACHE_MODE"] === "APPCACHE") ||
			(defined("CACHE_MODE") && constant("CACHE_MODE") === "APPCACHE");
	}

	public static function isCompositeRequest()
	{
		return
			(isset($_SERVER["HTTP_BX_CACHE_MODE"]) && $_SERVER["HTTP_BX_CACHE_MODE"] === "HTMLCACHE") ||
			(defined("CACHE_MODE") && constant("CACHE_MODE") === "HTMLCACHE");
	}

	/**
	 * Returns true if the current request URI has bitrix folder
	 *
	 * @return bool
	 */
	public static function isBitrixFolder()
	{
		$folders = array(BX_ROOT, BX_PERSONAL_ROOT);
		$requestUri = "/".ltrim($_SERVER["REQUEST_URI"], "/");
		foreach ($folders as $folder)
		{
			$folder = rtrim($folder, "/")."/";
			if (strncmp($requestUri, $folder, strlen($folder)) == 0)
			{
				return true;
			}
		}

		return false;
	}

	public static function isSpaMode()
	{
		$options = self::getOptions();

		return isset($options["SPA_MODE"]) && $options["SPA_MODE"] === "Y";
	}

	/**
	 *
	 * Decodes a gzip compressed string
	 *
	 * @param $data
	 *
	 * @return string
	 */
	public static function gzdecode($data)
	{
		if (function_exists("gzdecode"))
		{
			return gzdecode($data);
		}

		$data = self::getBinarySubstring($data, 10, -8);
		if ($data !== "")
		{
			$data = gzinflate($data);
		}

		return $data;
	}

	/**
	 *
	 * Binary version of substr
	 *
	 * @param $str
	 * @param $start
	 *
	 * @return string
	 */
	private static function getBinarySubstring($str, $start)
	{
		if (function_exists("mb_substr"))
		{
			$length = (func_num_args() > 2 ? func_get_arg(2) : self::getBinaryLength($str));

			return mb_substr($str, $start, $length, "latin1");
		}

		if (func_num_args() > 2)
		{
			return substr($str, $start, func_get_arg(2));
		}

		return substr($str, $start);
	}

	/**
	 * Binary version of strlen
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function getBinaryLength($str)
	{
		return function_exists("mb_strlen") ? mb_strlen($str, "latin1") : strlen($str);
	}

	/**
	 * Returns bxrand value
	 *
	 * @return string|false
	 */
	public static function getAjaxRandom()
	{
		if (self::$ajaxRandom === null)
		{
			self::$ajaxRandom = self::removeRandParam();
		}

		return self::$ajaxRandom;
	}

	/**
	 * Removes bxrand parameter from the current request and returns its value
	 *
	 * @return string|false
	 */
	public static function removeRandParam()
	{
		if (!array_key_exists("bxrand", $_GET) || !preg_match("/^[0-9]+$/", $_GET["bxrand"]))
		{
			return false;
		}

		self::$ajaxRandom = $_GET["bxrand"];

		unset($_GET["bxrand"]);
		unset($_REQUEST["bxrand"]);

		if (isset($_SERVER["REQUEST_URI"]))
		{
			$_SERVER["REQUEST_URI"] = preg_replace(
				"/((?<=\\?)bxrand=\\d+&?|&bxrand=\\d+\$)/",
				"",
				$_SERVER["REQUEST_URI"]
			);
			$_SERVER["REQUEST_URI"] = rtrim($_SERVER["REQUEST_URI"], "?&");
		}

		if (isset($_SERVER["QUERY_STRING"]))
		{
			$_SERVER["QUERY_STRING"] = preg_replace("/[?&]?bxrand=[0-9]+/", "", $_SERVER["QUERY_STRING"]);
			$_SERVER["QUERY_STRING"] = trim($_SERVER["QUERY_STRING"], "&");
			if (isset($GLOBALS["QUERY_STRING"]))
			{
				$GLOBALS["QUERY_STRING"] = $_SERVER["QUERY_STRING"];
			}
		}

		return self::$ajaxRandom;
	}

	/**
	 * Converts URI to a cache key (file path)
	 * / => /index.html
	 * /index.php => /index.html
	 * /aa/bb/ => /aa/bb/index.html
	 * /aa/bb/index.php => /aa/bb/index.html
	 * /?a=b&b=c => /index@a=b&b=c.html
	 *
	 * @param string $uri
	 * @param string $host
	 * @param string $privateKey
	 *
	 * @return string
	 */
	public static function convertUriToPath($uri, $host = null, $privateKey = null)
	{
		$uri = "/".trim($uri, "/");
		$parts = explode("?", $uri, 2);

		$uriPath = $parts[0];
		$uriPath = preg_replace("~/index\\.(php|html)$~i", "", $uriPath);
		$uriPath = rtrim(str_replace("..", "__", $uriPath), "/");
		$uriPath .= "/index";

		$queryString = isset($parts[1]) ? self::removeIgnoredParams($parts[1]) : "";
		$queryString = str_replace(".", "_", $queryString);

		$host = self::getHttpHost($host);
		if (strlen($host) > 0)
		{
			$host = "/".$host;
			$host = preg_replace("/:(\\d+)\$/", "-\\1", $host);
		}

		$privateKey = preg_replace("~[^a-z0-9/_]~i", "", $privateKey);
		if (strlen($privateKey) > 0)
		{
			$privateKey = "/".trim($privateKey, "/");
		}

		$cacheKey = $host.$uriPath."@".$queryString.$privateKey.".html";

		return str_replace(array("?", "*"), "_", $cacheKey);
	}

	public static function removeIgnoredParams($queryString)
	{
		if (!is_string($queryString) || $queryString === "")
		{
			return "";
		}

		$params = array();
		parse_str($queryString, $params);

		$options = self::getOptions();
		$ignoredParams = isset($options["~IGNORED_PARAMETERS"]) && is_array($options["~IGNORED_PARAMETERS"])
			? $options["~IGNORED_PARAMETERS"] : array();

		if (empty($ignoredParams) || empty($params))
		{
			return $queryString;
		}

		foreach ($params as $key => $value)
		{
			foreach ($ignoredParams as $ignoredParam)
			{
				if (strcasecmp($ignoredParam, $key) == 0)
				{
					unset($params[$key]);
					break;
				}
			}
		}

		return http_build_query($params, "", "&");
	}

	/**
	 * Return true if html cache is on
	 * @return bool
	 */
	public static function isOn()
	{
		return file_exists(self::getEnabledFilePath());
	}

	/**
	 * Return true if composite mode is enabled
	 * @return bool
	 */
	public static function isCompositeEnabled()
	{
		return self::isOn();
	}

	public static function getEnabledFilePath()
	{
		return $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/html_pages/.enabled";
	}

	public static function getConfigFilePath()
	{
		return $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/html_pages/.config.php";
	}

	public static function getSizeFilePath()
	{
		return $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/html_pages/.size";
	}

	/**
	 * Saves cache options
	 *
	 * @param array $arOptions
	 *
	 * @return void
	 */
	public static function setOptions($arOptions = array())
	{
		$arOptions = array_merge(self::getOptions(), $arOptions);
		self::compileOptions($arOptions);

		$fileName = self::getConfigFilePath();
		$tempFileName = $fileName.md5(mt_rand()).".tmp";
		self::makeDirPath($fileName);

		$fh = fopen($tempFileName, "wb");
		if ($fh !== false)
		{
			$content = "<?\n\$arHTMLPagesOptions = array(\n";
			foreach ($arOptions as $key => $value)
			{
				if (is_integer($key))
				{
					$phpKey = $key;
				}
				else
				{
					$phpKey = "\"".self::escapePHPString($key)."\"";
				}

				if (is_array($value))
				{
					$content .= "\t".$phpKey." => array(\n";
					foreach ($value as $key2 => $val)
					{
						if (is_integer($key2))
						{
							$phpKey2 = $key2;
						}
						else
						{
							$phpKey2 = "\"".self::escapePHPString($key2)."\"";
						}

						$content .= "\t\t".$phpKey2." => \"".self::escapePHPString($val)."\",\n";
					}
					$content .= "\t),\n";
				}
				else
				{
					$content .= "\t".$phpKey." => \"".self::escapePHPString($value)."\",\n";
				}
			}

			$content .= ");\n?>";
			$written = fwrite($fh, $content);
			$len = function_exists('mb_strlen') ? mb_strlen($content, 'latin1') : strlen($content);
			if ($written === $len)
			{
				fclose($fh);
				if (file_exists($fileName))
				{
					unlink($fileName);
				}
				rename($tempFileName, $fileName);
				@chmod($fileName, defined("BX_FILE_PERMISSIONS") ? BX_FILE_PERMISSIONS : 0664);
			}
			else
			{
				fclose($fh);
				if (file_exists($tempFileName))
				{
					unlink($tempFileName);
				}
			}

			self::$options = array();
		}
	}

	public static function makeDirPath($path)
	{
		$path = str_replace(array("\\", "//"), "/", $path);

		//remove file name
		if (substr($path, -1) != "/")
		{
			$p = strrpos($path, "/");
			$path = substr($path, 0, $p);
		}

		$path = rtrim($path, "/");

		if ($path == "")
		{
			//current folder always exists
			return true;
		}

		if (!file_exists($path))
		{
			return mkdir($path, defined("BX_DIR_PERMISSIONS") ? BX_DIR_PERMISSIONS : 0755, true);
		}

		return is_dir($path);
	}

	public static function escapePHPString($str)
	{
		$from = array("\\", "\$", "\"");
		$to = array("\\\\", "\\\$", "\\\"");
		return str_replace($from, $to, $str);
	}

	/**
	 * Returns an array with cache options.
	 * @return array
	 */
	public static function getOptions()
	{
		if (!empty(self::$options))
		{
			return self::$options;
		}

		$arHTMLPagesOptions = array();
		$fileName = self::getConfigFilePath();
		if (file_exists($fileName))
		{
			include($fileName);
		}

		$compile = count(array_diff(self::getCompiledOptions(), array_keys($arHTMLPagesOptions))) > 0;
		$arHTMLPagesOptions = $arHTMLPagesOptions + self::getDefaultOptions();
		if ($compile)
		{
			self::compileOptions($arHTMLPagesOptions);
		}

		if (isset($arHTMLPagesOptions["AUTO_COMPOSITE"]) && $arHTMLPagesOptions["AUTO_COMPOSITE"] === "Y")
		{
			$arHTMLPagesOptions["FRAME_MODE"] = "Y";
			$arHTMLPagesOptions["FRAME_TYPE"] = "DYNAMIC_WITH_STUB";
			$arHTMLPagesOptions["AUTO_UPDATE"] = "Y";
		}

		self::$options = $arHTMLPagesOptions;

		return self::$options;
	}

	public static function resetOptions()
	{
		self::setOptions(self::getDefaultOptions());
	}

	private static function getDefaultOptions()
	{
		return array(
			"INCLUDE_MASK" => "/*",
			"EXCLUDE_MASK" => "/bitrix/*; /404.php; ",
			"FILE_QUOTA" => 100,
			"BANNER_BGCOLOR" => "#E94524",
			"BANNER_STYLE" => "white",
			"STORAGE" => "files",
			"ONLY_PARAMETERS" => "id; ELEMENT_ID; SECTION_ID; PAGEN_1; ",
			"IGNORED_PARAMETERS" =>
				"utm_source; utm_medium; utm_campaign; utm_content; fb_action_ids; ".
				"utm_term; yclid; gclid; _openstat; from; ".
				"referrer1; r1; referrer2; r2; referrer3; r3; ",
			"WRITE_STATISTIC" => "Y",
			"EXCLUDE_PARAMS" => "ncc; ",
			"COMPOSITE" => "Y"
		);
	}

	private static function getCompiledOptions()
	{
		return array(
			"INCLUDE_MASK",
			"~INCLUDE_MASK",
			"EXCLUDE_MASK",
			"~EXCLUDE_MASK",
			"FILE_QUOTA",
			"~FILE_QUOTA",
			"~GET",
			"ONLY_PARAMETERS",
			"IGNORED_PARAMETERS",
			"~IGNORED_PARAMETERS",
			"INDEX_ONLY",
			"EXCLUDE_PARAMS",
			"~EXCLUDE_PARAMS",
		);
	}

	public static function compileOptions(&$arOptions)
	{
		$arOptions["~INCLUDE_MASK"] = array();
		$inc = str_replace(
			array("\\", ".", "?", "*", "'"),
			array("/", "\\.", ".", ".*?", "\\'"),
			$arOptions["INCLUDE_MASK"]
		);
		$arIncTmp = explode(";", $inc);
		foreach ($arIncTmp as $mask)
		{
			$mask = trim($mask);
			if (strlen($mask) > 0)
			{
				$arOptions["~INCLUDE_MASK"][] = "'^".$mask."$'";
			}
		}

		$arOptions["~EXCLUDE_MASK"] = array();
		$exc = str_replace(
			array("\\", ".", "?", "*", "'"),
			array("/", "\\.", ".", ".*?", "\\'"),
			$arOptions["EXCLUDE_MASK"]
		);
		$arExcTmp = explode(";", $exc);
		foreach ($arExcTmp as $mask)
		{
			$mask = trim($mask);
			if (strlen($mask) > 0)
			{
				$arOptions["~EXCLUDE_MASK"][] = "'^".$mask."$'";
			}
		}

		if (intval($arOptions["FILE_QUOTA"]) > 0)
		{
			$arOptions["~FILE_QUOTA"] = doubleval($arOptions["FILE_QUOTA"]) * 1024.0 * 1024.0;
		}
		else
		{
			$arOptions["~FILE_QUOTA"] = 0.0;
		}

		$arOptions["INDEX_ONLY"] = isset($arOptions["NO_PARAMETERS"]) && ($arOptions["NO_PARAMETERS"] === "Y");
		$arOptions["~GET"] = array();
		$onlyParams = explode(";", $arOptions["ONLY_PARAMETERS"]);
		foreach ($onlyParams as $str)
		{
			$str = trim($str);
			if (strlen($str) > 0)
			{
				$arOptions["~GET"][] = $str;
			}
		}

		$arOptions["~IGNORED_PARAMETERS"] = array();
		$ignoredParams = explode(";", $arOptions["IGNORED_PARAMETERS"]);
		foreach ($ignoredParams as $str)
		{
			$str = trim($str);
			if (strlen($str) > 0)
			{
				$arOptions["~IGNORED_PARAMETERS"][] = $str;
			}
		}

		$arOptions["~EXCLUDE_PARAMS"] = array();
		$excludeParams = explode(";", $arOptions["EXCLUDE_PARAMS"]);
		foreach ($excludeParams as $str)
		{
			$str = trim($str);
			if (strlen($str) > 0)
			{
				$arOptions["~EXCLUDE_PARAMS"][] = $str;
			}
		}

		if (function_exists("IsModuleInstalled"))
		{
			$arOptions["COMPRESS"] = IsModuleInstalled('compression');
			$arOptions["STORE_PASSWORD"] = \COption::GetOptionString("main", "store_password", "Y");
			$cookie_prefix = \COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
			$arOptions["COOKIE_LOGIN"] = $cookie_prefix.'_LOGIN';
			$arOptions["COOKIE_PASS"] = $cookie_prefix.'_UIDH';
			$arOptions["COOKIE_NCC"] = $cookie_prefix.'_NCC';
			$arOptions["COOKIE_CC"] = $cookie_prefix.'_CC';
			$arOptions["COOKIE_PK"] = $cookie_prefix.'_PK';
		}
	}

	/**
	 * Returns the number of bytes of file cache. If file .size doesn't exist returns false
	 * @return bool|float
	 */
	public static function getCacheFileSize()
	{
		$result = false;
		$fileName = self::getSizeFilePath();
		if (file_exists($fileName) && ($contents = file_get_contents($fileName)) !== false)
		{
			$result = doubleval($contents);
		}

		return $result;
	}

	public static function updateCacheFileSize($bytes = 0.0)
	{
		$options = self::getOptions();
		if ($options["WRITE_STATISTIC"] === "N")
		{
			return;
		}

		$fileName = self::getSizeFilePath();
		if (($handle = @fopen($fileName, "c+")) === false)
		{
			return;
		}

		if (@flock($handle, LOCK_EX))
		{
			$cacheSize = $bytes === false ? 0 : doubleval(fgets($handle)) + doubleval($bytes);
			$cacheSize = $cacheSize > 0 ? $cacheSize : 0;

			fseek($handle, 0);
			ftruncate($handle, 0);
			fwrite($handle, $cacheSize);
			flock($handle, LOCK_UN);
		}

		fclose($handle);
	}

	/**
	 * Returns array with cache statistics data.
	 * Returns an empty array in case of disabled html cache.
	 *
	 * @return array
	 */
	public static function readStatistic()
	{
		$result = false;
		$fileName = self::getEnabledFilePath();
		if (file_exists($fileName) && ($contents = file_get_contents($fileName)) !== false)
		{
			$fileValues = explode(",", $contents);
			$result = array(
				"HITS" => intval($fileValues[0]),
				"MISSES" => intval($fileValues[1]),
				"QUOTA" => intval($fileValues[2]),
				"POSTS" => intval($fileValues[3]),
				"FILE_SIZE" => doubleval($fileValues[4]),
			);
		}

		return $result;
	}

	/**
	 * Updates cache usage statistics.
	 * Each of parameters is added to appropriate existing stats.
	 *
	 * @param integer|false $hits Number of cache hits.
	 * @param integer|false $writings Number of cache writing.
	 * @param integer|false $quota Quota change in bytes.
	 * @param integer|false $posts Number of POST requests.
	 * @param float|false $files File size in bytes.
	 *
	 * @return void
	 */
	public static function writeStatistic($hits = 0, $writings = 0, $quota = 0, $posts = 0, $files = 0.0)
	{
		$options = self::getOptions();
		if ($options["WRITE_STATISTIC"] === "N")
		{
			return;
		}

		$fileName = self::getEnabledFilePath();
		if (!file_exists($fileName) || ($fp = @fopen($fileName, "r+")) === false)
		{
			return;
		}

		if (@flock($fp, LOCK_EX))
		{
			$fileValues = explode(",", fgets($fp));
			$cacheSize = (isset($fileValues[4]) ? doubleval($fileValues[4]) + doubleval($files) : doubleval($files));
			$newFileValues = array(
				$hits === false ? 0 : (isset($fileValues[0]) ? intval($fileValues[0]) + $hits : $hits),
				$writings === false ? 0 : (isset($fileValues[1]) ? intval($fileValues[1]) + $writings : $writings),
				$quota === false ? 0 : (isset($fileValues[2]) ? intval($fileValues[2]) + $quota : $quota),
				$posts === false ? 0 : (isset($fileValues[3]) ? intval($fileValues[3]) + $posts : $posts),
				$files === false ? 0 : $cacheSize > 0 ? $cacheSize : 0,
			);

			fseek($fp, 0);
			ftruncate($fp, 0);
			fwrite($fp, implode(",", $newFileValues));
			flock($fp, LOCK_UN);
		}

		fclose($fp);
	}

	/**
	 * Checks disk quota.
	 * Returns true if quota is not exceeded.
	 *
	 * @param int $requiredFreeSpace
	 *
	 * @return bool
	 */
	public static function checkQuota($requiredFreeSpace = 0)
	{
		$compositeOptions = self::getOptions();
		$cacheQuota = doubleval($compositeOptions["~FILE_QUOTA"]);

		$cacheSize = self::getCacheFileSize();
		$cacheSize = $cacheSize !== false ? $cacheSize : 0.0;

		return ($cacheSize + doubleval($requiredFreeSpace)) < $cacheQuota;
	}

	/**
	 * Updates disk quota and cache statistic
	 *
	 * @param float $bytes positive or negative value
	 */
	public static function updateQuota($bytes)
	{
		if ($bytes == 0.0)
		{
			return;
		}

		self::updateCacheFileSize($bytes);
	}

	//This method  exists because of SiteUpdate features.
	//When you reinstall updates (main 17.1.0 with previous ones).
	public static function __callStatic($name, $arguments)
	{
		if (strtoupper($name) === strtoupper("OnUserLogin"))
		{
			\Bitrix\Main\Composite\Engine::onUserLogin();
		}
		elseif (strtoupper($name) === strtoupper("OnUserLogout"))
		{
			\Bitrix\Main\Composite\Engine::onUserLogout();
		}
	}

	//region Deprecated Methods

	/**
	 * @deprecated
	 * use Engine::install and Engine::uninstall
	 * @param $status
	 * @param bool $setDefaults
	 */
	public static function setEnabled($status, $setDefaults = true)
	{
		if ($status)
		{
			Engine::install($setDefaults);
		}
		else
		{
			Engine::uninstall();
		}
	}

	/**
	 * @deprecated
	 * use
	 * $page = \Bitrix\Main\Composite\Page::getInstance();
	 * $page->deleteAll();
	 */
	public static function cleanAll()
	{
		$bytes = Data\FileStorage::deleteRecursive("/");

		if (class_exists("cdiskquota"))
		{
			\CDiskQuota::updateDiskQuota("file", $bytes, "delete");
		}

		self::updateQuota(-$bytes);
	}

	//endregion
}

if (!class_exists("CHTMLPagesCache", false))
{
	class_alias("Bitrix\\Main\\Composite\\Helper", "CHTMLPagesCache");
}