<?php
namespace Bitrix\Main\Composite;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

class AppCache
{

	const MANIFEST_CHECK_FILE = "/bitrix/tools/check_appcache.php";
	const DEBUG_HOLDER = "//__APP_CACHE_DEBUG_HOLDER__";
	private static $debug;
	private static $instance;
	private static $isEnabled = false;
	private static $customCheckFile = null;
	private $files = Array();
	private $pageURI = "";
	private $network = Array();
	private $fallbackPages = Array();
	private $params = Array();
	private $isSided = false;
	private $isModified = false;
	private $receivedManifest = "";
	private $excludeImagePatterns= array();

	private $receivedCacheParams = Array();

	private function __construct()
	{
		//use \Bitrix\Main\Composite\AppCache::getInstance();
	}

	/**
	 * @return boolean
	 */
	public static function getDebug()
	{
		return self::$debug;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled()
	{
		return self::$isEnabled;
	}

	/**
	 * Sets the array of path patterns to exclude unused images from the manifest file
	 * @return array
	 */
	public function getExcludeImagePatterns()
	{
		return $this->excludeImagePatterns;
	}

	/**
	 * Returns the array of path patters
	 * @param array $excludeImagePatterns
	 */
	public function setExcludeImagePatterns($excludeImagePatterns)
	{
		$this->excludeImagePatterns = $excludeImagePatterns;
	}


	private function __clone()
	{
		//you can't clone it

	}

	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new static();
			self::$debug = (defined("BX_APPCACHE_DEBUG") && BX_APPCACHE_DEBUG);
		}

		return self::$instance;
	}

	/**
	 * Creates or updates the manifest file for the page with usage its content.
	 *
	 * @param bool $isEnabled
	 */
	public static function setEnabled($isEnabled = true)
	{
		self::$isEnabled = (bool)$isEnabled;

	}

	public function generate(&$content)
	{
		$manifest = static::getInstance();
		$files = $manifest->getFilesFromContent($content);

		$this->isModified = false;
		$manifestId = $this->getCurrentManifestID();

		if ($this->isSided)
		{
			$curManifestId = $this->getManifestID($this->pageURI, $this->receivedCacheParams);
			if ($curManifestId != $manifestId)
			{
				self::removeManifestById($curManifestId);
			}
		}

		$currentHashSum = md5(serialize($files["FULL_FILE_LIST"]) . serialize($this->fallbackPages) . serialize($this->network) . serialize($this->excludeImagePatterns));
		$manifestCache = $this->readManifestCache($manifestId);
		if (!$manifestCache || $manifestCache["FILE_HASH"] != $currentHashSum || self::$debug)
		{
			$this->isModified = true;
			$this->setFiles($files["FULL_FILE_LIST"]);
			$this->setNetworkFiles(Array("*"));
			$arFields = array(
				"ID" => $manifestId,
				"TEXT" => $this->getManifestContent(),
				"FILE_HASH" => $currentHashSum,
				"EXCLUDE_PATTERNS_HASH"=> md5(serialize($this->excludeImagePatterns)),
				"FILE_DATA" => Array(
					"FILE_TIMESTAMPS" => $files["FILE_TIMESTAMPS"],
					"CSS_FILE_IMAGES" => $files["CSS_FILE_IMAGES"]
				)
			);

			if (!self::$debug)
			{
				$this->writeManifestCache($arFields);
			}
			else
			{
				$jsFields = json_encode($arFields);
				$fileCount = count($this->files);
				$params = json_encode($this->params);
				$fileCountImages = 0;
				foreach ($arFields["FILE_DATA"]["CSS_FILE_IMAGES"] as $file=>$images)
				{
					if (is_array($images))
					{
						$fileCountImages += count($images);
					}
				}


				$debugOutput = <<<JS

					console.log("-------APPLICATION CACHE DEBUG INFO------");
					console.log("File count:", $fileCount);
					console.log("Image file count:", $fileCountImages);
					console.log("Params:", $params);
					console.log("Detail:", $jsFields);
					console.log("--------------------------------------------");
JS;

				$jsContent = str_replace(array("\n", "\t"), "", $debugOutput);
				$content = str_replace(self::DEBUG_HOLDER, $jsContent, $content);
			}
		}

		return $this->getIsModified();
	}

	/**
	 * OnBeforeEndBufferContent handler
	 * @return array|mixed
	 */
	public static function onBeforeEndBufferContent()
	{
		global $APPLICATION;
		$selfObject = self::getInstance();
		$server = \Bitrix\Main\Context::getCurrent()->getServer();
		$params = Array();
		$appCacheUrl = $server->get("HTTP_BX_APPCACHE_URL");
		$appCacheParams = $server->get("HTTP_BX_APPCACHE_PARAMS");
		if ($appCacheUrl <> '')
		{
			//TODO compare $_SERVER["REQUEST_URI"] and $_SERVER["HTTP_BX_APPCACHE_URL"]
			$selfObject->setIsSided(true);
			$selfObject->setPageURI($appCacheUrl);
			if ($appCacheParams)
			{
				$params = json_decode($appCacheParams, true);

				if (!is_array($params))
				{
					$params = array();
				}

				$selfObject->setReceivedCacheParams($params);
			}
		}
		else
		{
			$selfObject->setPageURI($server->get("REQUEST_URI"));

			if(!self::$debug)
			{
				$APPLICATION->SetPageProperty("manifest", " manifest=\"" . self::getManifestCheckFile() . "?manifest_id=" . $selfObject->getCurrentManifestID() . "\"");
			}
			else
			{
				Asset::getInstance()->addString("<script>".self::DEBUG_HOLDER."</script>");
			}

			$params = Array(
				"PAGE_URL" => $selfObject->getPageURI(),
				"PARAMS" => $selfObject->getAdditionalParams(),
				"MODE" => "APPCACHE"
			);
		}

		return (is_array($params) ? $params : array());
	}

	/**
	 * Gets file path for getting of manifest content
	 * @return string
	 */
	public static function getManifestCheckFile()
	{
		$checkFile = self::MANIFEST_CHECK_FILE;
		if(self::$customCheckFile != null && self::$customCheckFile <> '')
			$checkFile = self::$customCheckFile;
		return $checkFile;
	}

	/**
	 * Sets custom file for getting of manifest content
	 * self::MANIFEST_CHECK_FILE uses by default
	 *@param string $customManifestCheckFile
	 */
	public function setManifestCheckFile($customManifestCheckFile)
	{
		self::$customCheckFile = $customManifestCheckFile;
	}

	/*
	 * OnEndBufferContent handler
	 */
	public static function onEndBufferContent(&$content)
	{
		static::getInstance()->generate($content);
	}

	/**
	 * Returns content of the manifest
	 * @return string
	 */
	public function getManifestContent()
	{
		$manifestText = "CACHE MANIFEST\n\n";
		$manifestText .= $this->getManifestDescription();
		$manifestText .= "#files" . "\n\n";
		$manifestText .= implode("\n", $this->files) . "\n\n";
		$manifestText .= "NETWORK:\n";
		$manifestText .= implode("\n", $this->network) . "\n\n";
		$manifestText .= "FALLBACK:\n\n";
		$countFallback = count($this->fallbackPages);
		for ($i = 0; $i < $countFallback; $i++)
		{
			$manifestText .= $this->fallbackPages[$i]["online"] . " " . $this->fallbackPages[$i]["offline"] . "\n";
		}

		return $manifestText;
	}

	/**
	 * Parses the passed content to find css, js and images. Returns the array of files.
	 *
	 * @param $content
	 *
	 * @return array
	 */
	public function getFilesFromContent($content)
	{
		$files = Array();
		$arFilesByType = Array();
		$arExtensions = Array("js", "css");
		$extension_regex = "(?:" . implode("|", $arExtensions) . ")";
		$findImageRegexp = "/
				((?i:
					href=
					|src=
					|BX\\.loadCSS\\(
					|BX\\.loadScript\\(
					|jsUtils\\.loadJSFile\\(
					|background\\s*:\\s*url\\(
				))                                                   #attribute
				(\"|')                                               #open_quote
				([^?'\"]+\\.)                                        #href body
				(" . $extension_regex . ")                           #extentions
				(|\\?\\d+|\\?v=\\d+)                                 #params
				(\\2)                                                #close_quote
			/x";
		$match = Array();
		preg_match_all($findImageRegexp, $content, $match);

		$link = $match[3];
		$extension = $match[4];
		$params = $match[5];
		$linkCount = count($link);
		$fileData = array(
			"FULL_FILE_LIST" => array(),
			"FILE_TIMESTAMPS" => array(),
			"CSS_FILE_IMAGES" => array()
		);
		for ($i = 0; $i < $linkCount; $i++)
		{
			$fileData["FULL_FILE_LIST"][] = $files[] = $link[$i] . $extension[$i] . $params[$i];
			$fileData["FILE_TIMESTAMPS"][$link[$i] . $extension[$i]] = $params[$i];
			$arFilesByType[$extension[$i]][] = $link[$i] . $extension[$i];
		}

		$manifestCache = $this->readManifestCache($this->getCurrentManifestID());
		$excludePatternsHash = md5(serialize($this->excludeImagePatterns));

		if (array_key_exists("css", $arFilesByType))
		{
			$findImageRegexp = '#([;\s:]*(?:url|@import)\s*\(\s*)(\'|"|)(.+?)(\2)\s*\)#si';
			if(!empty($this->excludeImagePatterns))
			{
				$findImageRegexp = '#([;\s:]*(?:url|@import)\s*\(\s*)(\'|"|)((?:(?!'.implode("|",$this->excludeImagePatterns).').)+?)(\2)\s*\)#si';
			}

			$cssCount = count($arFilesByType["css"]);
			for ($j = 0; $j < $cssCount; $j++)
			{
				$cssFilePath = $arFilesByType["css"][$j];
				if ($manifestCache["FILE_DATA"]["FILE_TIMESTAMPS"][$cssFilePath] != $fileData["FILE_TIMESTAMPS"][$cssFilePath]
					||$excludePatternsHash != $manifestCache["EXCLUDE_PATTERNS_HASH"]
				)
				{

					$fileContent = false;
					$fileUrl = parse_url($cssFilePath);
					$file = new  \Bitrix\Main\IO\File(Application::getDocumentRoot() . $fileUrl['path']);

					if($file->getExtension() !== "css")
						continue;

					if ($file->isExists() && $file->isReadable())
					{
						$fileContent = $file->getContents();
					}
					elseif ($fileUrl["scheme"])
					{
						$req = new \CHTTP();
						$req->http_timeout = 20;
						$fileContent = $req->Get($cssFilePath);
					}

					if ($fileContent != false)
					{
						$cssFileRelative = new \Bitrix\Main\IO\File($cssFilePath);
						$cssPath = $cssFileRelative->getDirectoryName();
						preg_match_all($findImageRegexp, $fileContent, $match);
						$matchCount = count($match[3]);
						for ($k = 0; $k < $matchCount; $k++)
						{

							$file = self::replaceUrlCSS($match[3][$k], addslashes($cssPath));

							if (!in_array($file, $files) && !mb_strpos($file, ";base64"))
							{
								$fileData["FULL_FILE_LIST"][] = $files[] = $file;
								$fileData["CSS_FILE_IMAGES"][$cssFilePath][] = $file;
							}
						}
					}
				}
				else
				{
					$fileData["CSS_FILE_IMAGES"][$cssFilePath] = $manifestCache["FILE_DATA"]["CSS_FILE_IMAGES"][$cssFilePath];
					if (is_array($manifestCache["FILE_DATA"]["CSS_FILE_IMAGES"][$cssFilePath]))
					{
						$fileData["FULL_FILE_LIST"] = array_merge($fileData["FULL_FILE_LIST"], $manifestCache["FILE_DATA"]["CSS_FILE_IMAGES"][$cssFilePath]);
					}
				}

			}
		}

		return $fileData;
	}

	/**
	 * Replaces url in css-file with absolute path.
	 *
	 * @param $url
	 * @param $cssPath
	 *
	 * @return string
	 */
	private static function replaceUrlCSS($url, $cssPath)
	{
		if (str_contains($url, "://") || str_contains($url, "data:"))
		{
			return $url;
		}
		$url = trim(stripslashes($url), "'\" \r\n\t");
		if (str_starts_with($url, "/"))
		{
			return $url;
		}

		return $cssPath . '/' . $url;
	}

	/**
	 * Sets received cache params
	 * @param $receivedCacheParams
	 */
	public function setReceivedCacheParams($receivedCacheParams)
	{
		$this->receivedCacheParams = $receivedCacheParams;
	}

	/**
	 * Gets received cache parameters
	 * @return array
	 */
	public function getReceivedCacheParams()
	{
		return $this->receivedCacheParams;
	}

	/**
	 * Sets received path to manifest
	 *
	 * @param $receivedManifest
	 */
	public function setReceivedManifest($receivedManifest)
	{
		$this->receivedManifest = $receivedManifest;
	}

	public function getReceivedManifest()
	{
		return $this->receivedManifest;
	}

	public function setIsSided($isSided)
	{
		$this->isSided = $isSided;
	}

	public function getIsSided()
	{
		return $this->isSided;
	}

	public function setPageURI($pageURI = "")
	{
		$this->pageURI = $pageURI;
	}

	public function getPageURI()
	{
		return $this->pageURI;
	}

	public function setFiles($arFiles)
	{
		if (!empty($this->files))
		{
			$this->files = array_merge($this->files, $arFiles);
		}
		else
		{
			$this->files = $arFiles;
		}
	}

	public function addFile($filePath)
	{
		$this->files[] = $filePath;
	}

	public function addAdditionalParam($name, $value)
	{
		$this->params[$name] = $value;
	}

	public function getAdditionalParams()
	{
		return $this->params;
	}

	public function setNetworkFiles($network)
	{
		$this->network = $network;
	}

	public function getNetworkFiles()
	{
		return $this->network;
	}

	public function addFallbackPage($onlinePage, $offlinePage)
	{
		$this->fallbackPages[] = Array(
			"online" => $onlinePage,
			"offline" => $offlinePage
		);
	}

	public function getFallbackPages()
	{
		return $this->fallbackPages;
	}

	public function getCurrentManifestID()
	{
		return $this->getManifestID($this->pageURI, $this->params);
	}

	public function getIsModified()
	{
		return $this->isModified && !self::$debug;
	}

	private function getManifestDescription()
	{

		$manifestParams = "";
		$arCacheParams = $this->params;
		if (!empty($arCacheParams))
		{
			foreach ($arCacheParams as $key => $value)
			{
				$manifestParams .= "#" . $key . "=" . $value . "\n";
			}
		}

		$desc = "#Date: " . date("r") . "\n";
		$desc .= "#Page: " . $this->pageURI . "\n";
		$desc .= "#Count: " . count($this->files) . "\n";
		$desc .= "#Params: \n" . $manifestParams . "\n\n";
		$desc .= "#Exclude patterns: \n" . "#".implode("\n#",$this->getExcludeImagePatterns()) . "\n\n";

		return $desc;
	}

	private function writeManifestCache($arFields)
	{
		$cache = new \CPHPCache();
		$manifestId = $arFields["ID"];
		$this->removeManifestById($manifestId);
		$cachePath = self::getCachePath($manifestId);
		$cache->StartDataCache(3600 * 24 * 365, $manifestId, $cachePath);
		$cache->EndDataCache($arFields);

		return true;
	}

	public static function readManifestCache($manifestId)
	{
		$cache = new \CPHPCache();

		$cachePath = self::getCachePath($manifestId);
		if ($cache->InitCache(3600 * 24 * 365, $manifestId, $cachePath))
		{
			return $cache->getVars();
		}

		return false;
	}

	private static function removeManifestById($manifestId)
	{
		$cache = new \CPHPCache();
		$cachePath = self::getCachePath($manifestId);

		$cache->CleanDir($cachePath);
	}

	/**
	 * @param $manifestId
	 *
	 * @return string
	 */
	public static function getCachePath($manifestId)
	{
		$cachePath = "/appcache/".mb_substr($manifestId, 0, 2)."/".mb_substr($manifestId, 2, 4) . "/";

		return $cachePath;
	}


	private static function getManifestID($pageURI, $arParams)
	{
		$id = $pageURI;
		if (!empty($arParams))
		{
			$strCacheParams = "";
			foreach ($arParams as $key => $value)
			{
				$strCacheParams .= $key . "=" . $value;
			}

			$id .= $strCacheParams;
		}

		return md5($id);
	}

	public static function checkObsoleteManifest()
	{
		$server = \Bitrix\Main\Context::getCurrent()->getServer();
		$appCacheUrl = $server->get("HTTP_BX_APPCACHE_URL");
		$appCacheParams = $server->get("HTTP_BX_APPCACHE_PARAMS");
		if ($appCacheUrl)
		{
			$params = json_decode($appCacheParams, true);

			if (!is_array($params))
			{
				$params = array();
			}

			static::clear($appCacheUrl, $params);
		}
	}

	private static function clear($url, $params)
	{
		$manifestId = self::getManifestID($url, $params);
		if (self::readManifestCache($manifestId))
		{
			self::removeManifestById($manifestId);
			self::getInstance()->isModified = true;
		}

	}

}

class_alias("Bitrix\\Main\\Composite\\AppCache", "Bitrix\\Main\\Data\\AppCacheManifest");