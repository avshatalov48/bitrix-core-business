<?php
namespace Bitrix\Main\Composite;

use Bitrix\Main\Composite\Debug;
use Bitrix\Main\Composite\Debug\Logger;
use Bitrix\Main\Composite\Internals\Model\PageTable;
use Bitrix\Main\Composite\Internals\Locker;
use Bitrix\Main\Composite\Internals\PageManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetMode;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Text\BinaryString;
use Bitrix\Main\Web\Uri;

Loc::loadMessages(__FILE__);

final class Engine
{
	const PAGE_DELETION_LIMIT = 100;
	const PAGE_DELETION_ATTEMPTS = 2;

	private static $instance;
	private static $isEnabled = false;
	private static $useHTMLCache = false;
	private static $onBeforeHandleKey = false;
	private static $onRestartBufferHandleKey = false;
	private static $onBeforeLocalRedirect = false;
	private static $autoUpdate = true;
	private static $autoUpdateTTL = 0;
	private static $isCompositeInjected = false;
	private static $isRedirect = false;
	private static $isBufferRestarted = false;

	/**
	 * use self::getInstance()
	 */
	private function __construct()
	{

	}

	/**
	 * you can't clone it
	 */
	private function __clone()
	{

	}

	/**
	 * Singleton instance.
	 * @deprecated just use static methods
	 * @return Engine
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new Engine();
		}

		return self::$instance;
	}

	/**
	 * Sets isEnable property value and attaches needed handlers.
	 *
	 * @param bool $isEnabled Mode control flag.
	 *
	 * @return void
	 */
	public static function setEnable($isEnabled = true)
	{
		if ($isEnabled && !self::$isEnabled)
		{
			self::$onBeforeHandleKey = AddEventHandler(
				"main",
				"OnBeforeEndBufferContent",
				array(__CLASS__, "onBeforeEndBufferContent")
			);
			self::$onRestartBufferHandleKey = AddEventHandler(
				"main",
				"OnBeforeRestartBuffer",
				array(__CLASS__, "onBeforeRestartBuffer")
			);
			self::$onBeforeLocalRedirect = AddEventHandler(
				"main",
				"OnBeforeLocalRedirect",
				array(__CLASS__, "onBeforeLocalRedirect"),
				2
			);
			self::$isEnabled = true;
			\CJSCore::init(array("fc"));
		}
		elseif (!$isEnabled && self::$isEnabled)
		{
			if (self::$onBeforeHandleKey >= 0)
			{
				RemoveEventHandler("main", "OnBeforeEndBufferContent", self::$onBeforeHandleKey);
			}

			if (self::$onRestartBufferHandleKey >= 0)
			{
				RemoveEventHandler("main", "OnBeforeRestartBuffer", self::$onRestartBufferHandleKey);
			}

			if (self::$onBeforeLocalRedirect >= 0)
			{
				RemoveEventHandler("main", "OnBeforeLocalRedirect", self::$onBeforeLocalRedirect);
			}

			self::$isEnabled = false;
		}
	}

	/**
	 * Gets isEnabled property.
	 *
	 * @return boolean
	 */
	public static function isEnabled()
	{
		return self::$isEnabled;
	}

	/**
	 * Sets useAppCache property.
	 *
	 * @param boolean $useAppCache AppCache mode control flag.
	 *
	 * @return void
	 */
	public static function setUseAppCache($useAppCache = true)
	{
		if (self::getUseAppCache())
		{
			self::setUseHTMLCache(false);
		}
		$appCache = AppCache::getInstance();
		$appCache->setEnabled($useAppCache);
	}

	/**
	 * Gets useAppCache property.
	 *
	 * @return boolean
	 */
	public static function getUseAppCache()
	{
		$appCache = AppCache::getInstance();

		return $appCache->isEnabled();
	}

	/**
	 * Sets useHTMLCache property.
	 *
	 * @param boolean $useHTMLCache Composite mode control flag.
	 *
	 * @return void
	 */
	public static function setUseHTMLCache($useHTMLCache = true)
	{
		self::$useHTMLCache = $useHTMLCache;
		self::setEnable();
	}

	/**
	 * Gets useHTMLCache property.
	 *
	 * @return boolean
	 */
	public static function getUseHTMLCache()
	{
		return self::$useHTMLCache;
	}

	/**
	 * Returns true if current request was initiated by Ajax.
	 *
	 * @return boolean
	 */
	public static function isAjaxRequest()
	{
		return Helper::isAjaxRequest();
	}

	public static function isInvalidationRequest()
	{
		return self::isAjaxRequest() && Context::getCurrent()->getServer()->get("HTTP_BX_INVALIDATE_CACHE") === "Y";
	}

	/**
	 * Returns true if we should inject banner into a page.
	 * @return bool
	 */
	public static function isBannerEnabled()
	{
		return Option::get("main", "~show_composite_banner", "Y") == "Y";
	}

	/**
	 * Sets autoUpdate property
	 *
	 * @param bool $flag
	 *
	 * @return void
	 */
	public static function setAutoUpdate($flag)
	{
		self::$autoUpdate = $flag === false ? false : true;
	}

	/**
	 * Gets autoUpdate property
	 * @return bool
	 */
	public static function getAutoUpdate()
	{
		return self::$autoUpdate;
	}

	/**
	 * Sets auto update ttl
	 *
	 * @param int $ttl - number of seconds
	 *
	 * @return void
	 */
	public static function setAutoUpdateTTL($ttl)
	{
		self::$autoUpdateTTL = intval($ttl);
	}

	/**
	 * Gets auto update ttl
	 * @return int
	 */
	public static function getAutoUpdateTTL()
	{
		return self::$autoUpdateTTL;
	}

	/**
	 * OnBeforeEndBufferContent handler.
	 * Prepares the stage for composite mode handler.
	 *
	 * @return void
	 */
	public static function onBeforeEndBufferContent()
	{
		$params = array();
		if (self::getUseAppCache())
		{
			$manifest = AppCache::getInstance();
			$params = $manifest->OnBeforeEndBufferContent();
			$params["CACHE_MODE"] = "APPCACHE";
			$params["PAGE_URL"] = Context::getCurrent()->getServer()->getRequestUri();
		}
		elseif (self::getUseHTMLCache())
		{
			$page = Page::getInstance();
			$page->onBeforeEndBufferContent();

			if ($page->isCacheable())
			{
				$params["CACHE_MODE"] = "HTMLCACHE";

				if (self::isBannerEnabled())
				{
					$options = Helper::getOptions();
					$params["banner"] = array(
						"url" => GetMessage("COMPOSITE_BANNER_URL"),
						"text" => GetMessage("COMPOSITE_BANNER_TEXT"),
						"bgcolor" => isset($options["BANNER_BGCOLOR"]) ? $options["BANNER_BGCOLOR"] : "",
						"style" => isset($options["BANNER_STYLE"]) ? $options["BANNER_STYLE"] : ""
					);
				}
			}
			else
			{
				return;
			}
		}

		$params["storageBlocks"] = array();
		$params["dynamicBlocks"] = array();
		$dynamicAreas = StaticArea::getDynamicAreas();
		foreach ($dynamicAreas as $id => $dynamicArea)
		{
			$stub = $dynamicArea->getStub();
			self::replaceSessid($stub);

			$params["dynamicBlocks"][$dynamicArea->getId()] = substr(md5($stub), 0, 12);
			if ($dynamicArea->getBrowserStorage())
			{
				$realId = $dynamicArea->getContainerId() !== null ? $dynamicArea->getContainerId() : "bxdynamic_".$id;
				$params["storageBlocks"][] = $realId;
			}
		}

		$params["AUTO_UPDATE"] = self::getAutoUpdate();
		$params["AUTO_UPDATE_TTL"] = self::getAutoUpdateTTL();

		Asset::getInstance()->addString(
			self::getInjectedJs($params),
			false,
			AssetLocation::BEFORE_CSS,
			self::getUseHTMLCache() ? AssetMode::COMPOSITE : AssetMode::ALL
		);

		self::$isCompositeInjected = true;
	}

	/**
	 * @param $content
	 *
	 * @return null|string
	 * @internal
	 */
	public static function startBuffering($content)
	{
		if (!self::isEnabled() || !is_object($GLOBALS["APPLICATION"]) || !self::$isCompositeInjected)
		{
			return null;
		}

		if (defined("BX_BUFFER_SHUTDOWN"))
		{
			Logger::log(
				array(
					"TYPE" => Logger::TYPE_PHP_SHUTDOWN,
				)
			);

			return null;
		}

		$newBuffer = $GLOBALS["APPLICATION"]->buffer_content;
		$cnt = count($GLOBALS["APPLICATION"]->buffer_content_type);

		Asset::getInstance()->setMode(AssetMode::COMPOSITE);

		self::$isCompositeInjected = false; //double-check
		for ($i = 0; $i < $cnt; $i++)
		{
			$method = $GLOBALS["APPLICATION"]->buffer_content_type[$i]["F"];
			if (!is_array($method) || count($method) !== 2 || $method[0] !== $GLOBALS["APPLICATION"])
			{
				continue;
			}

			if (in_array($method[1], array("GetCSS", "GetHeadScripts", "GetHeadStrings")))
			{
				$newBuffer[$i * 2 + 1] = call_user_func_array(
					$method,
					$GLOBALS["APPLICATION"]->buffer_content_type[$i]["P"]
				);
				if (self::$isCompositeInjected !== true && $method[1] === "GetHeadStrings")
				{
					self::$isCompositeInjected =
						\CUtil::BinStrpos($newBuffer[$i * 2 + 1], "w.frameRequestStart") !== false;
				}
			}
		}

		Asset::getInstance()->setMode(AssetMode::STANDARD);

		if (!self::$isCompositeInjected)
		{
			Logger::log(
				array(
					"TYPE" => Logger::TYPE_COMPOSITE_NOT_INJECTED,
				)
			);
		}

		return self::$isCompositeInjected ? implode("", $newBuffer).$content : null;
	}

	/**
	 * Returns true if $originalContent was modified
	 *
	 * @param $originalContent
	 * @param $compositeContent
	 *
	 * @return bool
	 * @internal
	 */
	public static function endBuffering(&$originalContent, $compositeContent)
	{
		if (!self::isEnabled() || $compositeContent === null || defined("BX_BUFFER_SHUTDOWN"))
		{
			//this happens when die() invokes in self::onBeforeLocalRedirect
			if (self::isAjaxRequest() && self::$isRedirect === false)
			{
				$originalContent = self::getAjaxError();
				Page::getInstance()->delete();

				return true;
			}

			return false;
		}

		if (function_exists("getmoduleevents"))
		{
			foreach (GetModuleEvents("main", "OnEndBufferContent", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array(&$compositeContent));
			}
		}

		$compositeContent = self::processPageContent($compositeContent);
		if (self::isAjaxRequest() || self::getUseAppCache())
		{
			$originalContent = $compositeContent;

			return true;
		}

		return false;
	}

	/**
	 * * There are two variants of content's modification in this method.
	 * The first one:
	 * If it's ajax-hit the content will be replaced by json data with dynamic blocks,
	 * javascript files and etc. - dynamic part
	 *
	 * The second one:
	 * If it's simple hit the content will be modified also,
	 * all dynamic blocks will be cut out of the content - static part.
	 *
	 * @param string $content Html page content.
	 *
	 * @return string
	 */
	private static function processPageContent($content)
	{
		global $APPLICATION, $USER;

		$dividedData = self::getDividedPageData($content);
		$htmlCacheChanged = false;

		if (self::getUseHTMLCache())
		{
			$page = Page::getInstance();
			if ($page->isCacheable())
			{
				$cacheExists = $page->exists();
				$rewriteCache = $page->getMd5() !== $dividedData["md5"];
				if (self::getAutoUpdate() && self::getAutoUpdateTTL() > 0 && $cacheExists)
				{
					$mtime = $page->getLastModified();
					if ($mtime !== false && ($mtime + self::getAutoUpdateTTL()) > time())
					{
						$rewriteCache = false;
					}
				}

				$invalidateCache = self::getAutoUpdate() === false && self::isInvalidationRequest();

				$oldContent = null;
				if (!$cacheExists || $rewriteCache || $invalidateCache)
				{
					if ($invalidateCache || Locker::lock($page->getCacheKey()))
					{
						if ($cacheExists && Logger::isOn())
						{
							$oldContent = $page->read();
						}

						if ($page->getStorage() instanceof Data\FileStorage)
						{
							$freeSpace = BinaryString::getLength($dividedData["static"]) + strlen($dividedData["md5"]);
							self::ensureFileQuota($freeSpace);
						}

						$success = $page->write($dividedData["static"], $dividedData["md5"]);

						if ($success)
						{
							$htmlCacheChanged = true;
							$page->setUserPrivateKey();
						}

						Locker::unlock($page->getCacheKey());
					}
				}

				$pageId = PageManager::register(
					$page->getCacheKey(),
					array(
						"CHANGED" => $htmlCacheChanged,
						"SIZE" => $page->getSize()
					)
				);

				if ($oldContent !== null)
				{
					Logger::log(
						array(
							"TYPE" => Logger::TYPE_CACHE_REWRITING,
							"MESSAGE" => $oldContent,
							"PAGE_ID" => $pageId
						)
					);
				}
			}
			else
			{
				$page->delete();

				return self::getAjaxError();
			}
		}

		if (self::getUseAppCache() == true) //Do we use html5 application cache?
		{
			AppCache::getInstance()->generate($dividedData["static"]);
		}
		else
		{
			AppCache::checkObsoleteManifest();
		}

		if (self::isAjaxRequest())
		{
			self::sendRandHeader();

			header("Content-Type: application/x-javascript; charset=".SITE_CHARSET);
			header("X-Bitrix-Composite: Ajax ".($htmlCacheChanged ? "(changed)" : "(stable)"));

			$content = array(
				"js" => $APPLICATION->arHeadScripts,
				"additional_js" => $APPLICATION->arAdditionalJS,
				"lang" => \CJSCore::GetCoreMessages(),
				"css" => $APPLICATION->GetCSSArray(),
				"htmlCacheChanged" => $htmlCacheChanged,
				"isManifestUpdated" => AppCache::getInstance()->getIsModified(),
				"dynamicBlocks" => $dividedData["dynamic"],
				"spread" => array_map(array("CUtil", "JSEscape"), $APPLICATION->GetSpreadCookieUrls()),
			);

			if ($USER->isAuthorized() && self::getUseAppCache())
			{
				if (Loader::includeModule("pull") && \CPullOptions::CheckNeedRun())
				{
					$content["pull"] = \CPullChannel::GetConfig($USER->GetID());
				}
			}

			$content = \CUtil::PhpToJSObject($content);
		}
		else
		{
			$content = $dividedData["static"];
		}

		return $content;
	}

	/**
	 * This method returns the divided content.
	 * The content is divided by two parts - static and dynamic.
	 * Example of returned value:
	 * <code>
	 * array(
	 *    "static"=>"Hello World!"
	 *    "dynamic"=>array(
	 *        array("ID"=>"someID","CONTENT"=>"someDynamicContent", "HASH"=>"md5ofDynamicContent")),
	 *        array("ID"=>"someID2","CONTENT"=>"someDynamicContent2", "HASH"=>"md5ofDynamicContent2"))
	 * );
	 * </code>
	 *
	 * @param string $content Html page content.
	 *
	 * @return array
	 */
	private static function getDividedPageData($content)
	{
		$data = array(
			"dynamic" => array(),
			"static" => "",
			"md5" => "",
		);

		$dynamicAreas = StaticArea::getDynamicAreas();
		if (count($dynamicAreas) > 0 && ($areas = self::getFrameIndexes($content)) !== false)
		{
			$offset = 0;
			$pageBlocks = self::getPageBlocks();
			foreach ($areas as $area)
			{
				$dynamicArea = StaticArea::getDynamicArea($area->id);
				if ($dynamicArea === null)
				{
					continue;
				}

				$realId = $dynamicArea->getContainerId() !== null ? $dynamicArea->getContainerId() : "bxdynamic_".$area->id;
				$assets =  Asset::getInstance()->getAssetInfo($dynamicArea->getAssetId(), $dynamicArea->getAssetMode());
				$areaContent = \CUtil::BinSubstr($content, $area->openTagEnd, $area->closingTagStart - $area->openTagEnd);
				$areaContentMd5 = substr(md5($areaContent), 0, 12);

				$blockId = $dynamicArea->getId();
				$hasSameContent = isset($pageBlocks[$blockId]) && $pageBlocks[$blockId] === $areaContentMd5;

				if (!$hasSameContent)
				{
					$data["dynamic"][] = array(
						"ID" => $realId,
						"CONTENT" => $areaContent,
						"HASH" => $areaContentMd5,
						"PROPS" => array(
							"CONTAINER_ID" => $dynamicArea->getContainerId(),
							"USE_BROWSER_STORAGE" => $dynamicArea->getBrowserStorage(),
							"AUTO_UPDATE" => $dynamicArea->getAutoUpdate(),
							"USE_ANIMATION" => $dynamicArea->getAnimation(),
							"CSS" => $assets["CSS"],
							"JS" => $assets["JS"],
							"STRINGS" => $assets["STRINGS"],
						),
					);
				}

				$data["static"] .= \CUtil::BinSubstr($content, $offset, $area->openTagStart - $offset);

				if ($dynamicArea->getContainerId() === null)
				{
					$data["static"] .=
						'<div id="bxdynamic_'.$area->id.'_start" style="display:none"></div>'.
						$dynamicArea->getStub().
						'<div id="bxdynamic_'.$area->id.'_end" style="display:none"></div>';
				}
				else
				{
					$data["static"] .= $dynamicArea->getStub();
				}

				$offset = $area->closingTagEnd;
			}

			$data["static"] .= \CUtil::BinSubstr($content, $offset);
		}
		else
		{
			$data["static"] = $content;
		}

		self::replaceSessid($data["static"]);
		Asset::getInstance()->moveJsToBody($data["static"]);

		$data["md5"] = md5($data["static"]);

		return $data;
	}

	/**
	 * @param string $content
	 *
	 * @return array|bool
	 */
	private static function getFrameIndexes($content)
	{
		$openTag = "<!--'start_frame_cache_";
		$closingTag = "<!--'end_frame_cache_";
		$ending = "'-->";

		$areas = array();
		$offset = 0;
		while (($openTagStart = \CUtil::BinStrpos($content, $openTag, $offset)) !== false)
		{
			$endingPos = \CUtil::BinStrpos($content, $ending, $openTagStart);
			if ($endingPos === false)
			{
				break;
			}

			$idStart = $openTagStart + strlen($openTag);
			$idLength = $endingPos - $idStart;
			$areaId = \CUtil::BinSubstr($content, $idStart, $idLength);
			$openTagEnd = $endingPos + strlen($ending);

			$realClosingTag = $closingTag.$areaId.$ending;
			$closingTagStart = \CUtil::BinStrpos($content, $realClosingTag, $openTagEnd);
			if ($closingTagStart === false)
			{
				$offset = $openTagEnd;
				continue;
			}

			$closingTagEnd = $closingTagStart + strlen($realClosingTag);

			$area = new \stdClass();
			$area->id = $areaId;
			$area->openTagStart = $openTagStart;
			$area->openTagEnd = $openTagEnd;
			$area->closingTagStart = $closingTagStart;
			$area->closingTagEnd = $closingTagEnd;
			$areas[] = $area;

			$offset = $closingTagEnd;
		}

		return count($areas) > 0 ? $areas : false;
	}

	private static function getPageBlocks()
	{
		$blocks = array();
		$json = Context::getCurrent()->getServer()->get("HTTP_BX_CACHE_BLOCKS");
		if ($json !== null && strlen($json) > 0)
		{
			$blocks = json_decode($json, true);
			if ($blocks === null)
			{
				$blocks = array();
			}
		}

		return $blocks;
	}

	/**
	 * Replaces bitrix sessid in the $content
	 *
	 * @param string $content
	 */
	private static function replaceSessid(&$content)
	{
		$methodInvocations = bitrix_sessid_post("sessid", true);
		if ($methodInvocations > 0)
		{
			$content = str_replace("value=\"".bitrix_sessid()."\"", "value=\"\"", $content);
		}
	}

	/**
	 * OnBeforeRestartBuffer event handler.
	 * Disables composite mode when called.
	 *
	 * @return void
	 */
	public static function onBeforeRestartBuffer()
	{
		self::$isBufferRestarted = true;
		self::setEnable(false);

		Logger::log(
			array(
				"TYPE" => Logger::TYPE_BUFFER_RESTART,
				"MESSAGE" =>
					"Script: ".
					(isset($_SERVER["REAL_FILE_PATH"]) ? $_SERVER["REAL_FILE_PATH"] : $_SERVER["SCRIPT_NAME"])
			)
		);
	}

	public static function onBeforeLocalRedirect(&$url, $skip_security_check, $isExternal)
	{
		global $APPLICATION;
		if (!self::isAjaxRequest() || ($isExternal && $skip_security_check !== true))
		{
			return;
		}

		$response = array(
			"error" => true,
			"reason" => "redirect",
			"redirect_url" => $url,
		);

		self::setEnable(false);

		Logger::log(
			array(
				"TYPE" => Logger::TYPE_LOCAL_REDIRECT,
				"MESSAGE" =>
					"Script: ".
					(isset($_SERVER["REAL_FILE_PATH"]) ? $_SERVER["REAL_FILE_PATH"] : $_SERVER["SCRIPT_NAME"])."\n".
					"Redirect Url: ".$url
			)
		);

		if ($APPLICATION->buffered)
		{
			$APPLICATION->RestartBuffer();
		}

		self::$isRedirect = true;
		Page::getInstance()->delete();

		header("X-Bitrix-Composite: Ajax (error:redirect)");
		self::sendRandHeader();
		echo \CUtil::PhpToJSObject($response);

		die(); //it provokes register_shutdown_function callback which invokes startBuffering/endBuffering
	}

	private static function ensureFileQuota($requiredFreeSpace = 0)
	{
		static $tries = 2;
		if (Helper::checkQuota($requiredFreeSpace) || $tries <= 0)
		{
			return;
		}

		$records = PageTable::getList(
			array(
				"select" => array("ID", "CACHE_KEY"),
				"order" => array("LAST_VIEWED" => "ASC", "ID" => "ASC"),
				"limit" => self::getDeletionLimit()
			)
		);

		$ids = array();
		$compositeOptions = Helper::getOptions();
		$deletedSize = 0.0;
		while ($record = $records->fetch())
		{
			$ids[] = $record["ID"];
			$fileStorage = new Data\FileStorage($record["CACHE_KEY"], array(), $compositeOptions);
			$deletedSize += doubleval($fileStorage->delete());
		}

		PageTable::deleteBatch(array("ID" => $ids));

		Helper::updateCacheFileSize(-$deletedSize);

		Logger::log(array(
			"TYPE" => Logger::TYPE_CACHE_RESET,
			"MESSAGE" =>
				"Pages: ".count($ids)."\n".
				"Size: ".\CFile::formatSize($deletedSize)
		));

		if (!Helper::checkQuota($requiredFreeSpace))
		{
			$tries--;
			self::ensureFileQuota($requiredFreeSpace);
		}
	}

	/**
	 * @return int
	 */
	private static function getDeletionLimit()
	{
		$options = Helper::getOptions();
		if (isset($options["PAGE_DELETION_LIMIT"]) && intval($options["PAGE_DELETION_LIMIT"]) > 0)
		{
			return intval($options["PAGE_DELETION_LIMIT"]);
		}
		else
		{
			return self::PAGE_DELETION_LIMIT;
		}
	}

	private static function getAjaxError($errorMsg = null)
	{
		$error = "unknown";
		if ($errorMsg !== null)
		{
			$error = $errorMsg;
		}
		elseif (self::$isBufferRestarted)
		{
			$error = "buffer_restarted";
		}
		elseif (!self::isEnabled())
		{
			$error = "not_enabled";
		}
		elseif (defined("BX_BUFFER_SHUTDOWN"))
		{
			$error = "php_shutdown";
		}
		elseif (!Page::getInstance()->isCacheable())
		{
			$error = "not_cacheable";
		}
		elseif (!self::$isCompositeInjected)
		{
			$error = "not_injected";
		}

		header("X-Bitrix-Composite: Ajax (error:".$error.")");
		self::sendRandHeader();

		$response = array(
			"error" => true,
			"reason" => $error,
		);

		return \CUtil::PhpToJSObject($response);
	}

	/**
	 * Sends BX-RAND Header
	 */
	private static function sendRandHeader()
	{
		$bxRandom = Helper::getAjaxRandom();
		if ($bxRandom !== false)
		{
			header("BX-RAND: ".$bxRandom);
		}
	}

	/**
	 * Returns JS minified code that will do dynamic hit to the server.
	 * The code is returned in the 'start' key of the array.
	 *
	 * @param array $params
	 *
	 * @return array[string]string
	 */
	private static function getInjectedJs($params = array())
	{
		$vars = \CUtil::PhpToJSObject($params);

		$inlineJS = <<<JS
			(function(w, d) {

			var v = w.frameCacheVars = $vars;
			var inv = false;
			if (v.AUTO_UPDATE === false)
			{
				if (v.AUTO_UPDATE_TTL && v.AUTO_UPDATE_TTL > 0)
				{
					var lm = Date.parse(d.lastModified);
					if (!isNaN(lm))
					{
						var td = new Date().getTime();
						if ((lm + v.AUTO_UPDATE_TTL * 1000) >= td)
						{
							w.frameRequestStart = false;
							w.preventAutoUpdate = true;
							return;
						}
						inv = true;
					}
				}
				else
				{
					w.frameRequestStart = false;
					w.preventAutoUpdate = true;
					return;
				}
			}

			var r = w.XMLHttpRequest ? new XMLHttpRequest() : (w.ActiveXObject ? new w.ActiveXObject("Microsoft.XMLHTTP") : null);
			if (!r) { return; }

			w.frameRequestStart = true;

			var m = v.CACHE_MODE; var l = w.location; var x = new Date().getTime();
			var q = "?bxrand=" + x + (l.search.length > 0 ? "&" + l.search.substring(1) : "");
			var u = l.protocol + "//" + l.host + l.pathname + q;

			r.open("GET", u, true);
			r.setRequestHeader("BX-ACTION-TYPE", "get_dynamic");
			r.setRequestHeader("BX-CACHE-MODE", m);
			r.setRequestHeader("BX-CACHE-BLOCKS", v.dynamicBlocks ? JSON.stringify(v.dynamicBlocks) : "");
			if (inv)
			{
				r.setRequestHeader("BX-INVALIDATE-CACHE", "Y");
			}
			
			try { r.setRequestHeader("BX-REF", d.referrer || "");} catch(e) {}

			if (m === "APPCACHE")
			{
				r.setRequestHeader("BX-APPCACHE-PARAMS", JSON.stringify(v.PARAMS));
				r.setRequestHeader("BX-APPCACHE-URL", v.PAGE_URL ? v.PAGE_URL : "");
			}

			r.onreadystatechange = function() {
				if (r.readyState != 4) { return; }
				var a = r.getResponseHeader("BX-RAND");
				var b = w.BX && w.BX.frameCache ? w.BX.frameCache : false;
				if (a != x || !((r.status >= 200 && r.status < 300) || r.status === 304 || r.status === 1223 || r.status === 0))
				{
					var f = {error:true, reason:a!=x?"bad_rand":"bad_status", url:u, xhr:r, status:r.status};
					if (w.BX && w.BX.ready)
					{
						BX.ready(function() {
							setTimeout(function(){
								BX.onCustomEvent("onFrameDataRequestFail", [f]);
							}, 0);
						});
					}
					else
					{
						w.frameRequestFail = f;
					}
					return;
				}

				if (b)
				{
					b.onFrameDataReceived(r.responseText);
					if (!w.frameUpdateInvoked)
					{
						b.update(false);
					}
					w.frameUpdateInvoked = true;
				}
				else
				{
					w.frameDataString = r.responseText;
				}
			};

			r.send();

			})(window, document);
JS;

		$html = "";
		if (self::isBannerEnabled())
		{
			$html .= '<style type="text/css">'.str_replace(array("\n", "\t"), "", self::getInjectedCSS())."</style>\n";
		}

		$html .= '<script type="text/javascript" data-skip-moving="true">'.
				 str_replace(array("\n", "\t"), "", $inlineJS).
				 "</script>";

		return $html;
	}

	/**
	 * Returns css string to be injected.
	 *
	 * @internal
	 * @return string
	 */
	public static function getInjectedCSS()
	{
		/** @noinspection CssUnknownTarget */
		/** @noinspection CssUnusedSymbol */
		return <<<CSS

			.bx-composite-btn {
				background: url(/bitrix/images/main/composite/sprite-1x.png) no-repeat right 0 #e94524;
				border-radius: 15px;
				color: #fff !important;
				display: inline-block;
				line-height: 30px;
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
				font-size: 12px !important;
				font-weight: bold !important;
				height: 31px !important;
				padding: 0 42px 0 17px !important;
				vertical-align: middle !important;
				text-decoration: none !important;
			}

			@media screen 
  				and (min-device-width: 1200px) 
  				and (max-device-width: 1600px) 
  				and (-webkit-min-device-pixel-ratio: 2)
  				and (min-resolution: 192dpi) {
					.bx-composite-btn {
						background-image: url(/bitrix/images/main/composite/sprite-2x.png);
						background-size: 42px 124px;
					}
			}

			.bx-composite-btn-fixed {
				position: absolute;
				top: -45px;
				right: 15px;
				z-index: 10;
			}

			.bx-btn-white {
				background-position: right 0;
				color: #fff !important;
			}

			.bx-btn-black {
				background-position: right -31px;
				color: #000 !important;
			}

			.bx-btn-red {
				background-position: right -62px;
				color: #555 !important;
			}

			.bx-btn-grey {
				background-position: right -93px;
				color: #657b89 !important;
			}

			.bx-btn-border {
				border: 1px solid #d4d4d4;
				height: 29px !important;
				line-height: 29px !important;
			}

			.bx-composite-loading {
				display: block;
				width: 40px;
				height: 40px;
				background: url(/bitrix/images/main/composite/loading.gif);
			}
CSS;
	}

	/**
	 * Checks whether HTML Cache should be enabled.
	 *
	 * @internal
	 * @return void
	 */
	public static function shouldBeEnabled()
	{
		if (defined("USE_HTML_STATIC_CACHE") && USE_HTML_STATIC_CACHE === true)
		{
			if (
				!defined("BX_SKIP_SESSION_EXPAND") &&
				(!defined("ADMIN_SECTION") || (defined("ADMIN_SECTION") && ADMIN_SECTION != "Y"))
			)
			{
				if (self::isInvalidationRequest())
				{
					$cacheKey = Helper::convertUriToPath(
						Helper::getRequestUri(),
						Helper::getHttpHost(),
						Helper::getRealPrivateKey(Page::getPrivateKey())
					);

					if (!Locker::lock($cacheKey))
					{
						die(Engine::getAjaxError("invalidation_request_locked"));
					}
				}

				self::setUseHTMLCache();

				$options = Helper::getOptions();
				if (isset($options["AUTO_UPDATE"]) && $options["AUTO_UPDATE"] === "N")
				{
					self::setAutoUpdate(false);
				}

				if (isset($options["AUTO_UPDATE_TTL"]))
				{
					self::setAutoUpdateTTL($options["AUTO_UPDATE_TTL"]);
				}

				define("BX_SKIP_SESSION_EXPAND", true);
			}
		}
		else if (Responder::getLastError() !== null && Logger::isOn())
		{
			$result = Logger::log(array(
				"TYPE" => Responder::getLastError(),
				"MESSAGE" => Responder::getLastErrorMessage()
			));

			//try to update page title on the end of a page execution
			if ($result && $result->getId())
			{
				$recordId = $result->getId();
				$eventManager = EventManager::getInstance();
				$eventManager->addEventHandler("main", "OnEpilog", function() use($recordId) {
					if (is_object($GLOBALS["APPLICATION"]))
					{
						Debug\Model\LogTable::update($recordId, array(
							"TITLE" => $GLOBALS["APPLICATION"]->getTitle()
						));
					}
				});
			}
		}

		if (
			(defined("ENABLE_HTML_STATIC_CACHE_JS") && ENABLE_HTML_STATIC_CACHE_JS === true) &&
			(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
		)
		{
			\CJSCore::init(array("fc")); //to warm up localStorage
		}


	}

	/**
	 * Checks if admin panel will be shown or not.
	 * Disables itself if panel will be show.
	 *
	 * @internal
	 * @return void
	 */
	public static function checkAdminPanel()
	{
		if (
			$GLOBALS["APPLICATION"]->showPanelWasInvoked === true &&
			self::getUseHTMLCache() &&
			!self::isAjaxRequest() &&
			\CTopPanel::shouldShowPanel()
		)
		{
			self::setEnable(false);

			Logger::log(
				array(
					"TYPE" => Logger::TYPE_ADMIN_PANEL,
				)
			);
		}
	}

	public static function install($setDefaults = true)
	{
		$eventManager = EventManager::getInstance();

		$eventManager->registerEventHandler("main", "OnEpilog", "main", "\\".__CLASS__, "onEpilog");
		$eventManager->registerEventHandler("main", "OnLocalRedirect", "main", "\\".__CLASS__, "onEpilog");
		$eventManager->registerEventHandler("main", "OnChangeFile", "main", "\\".__CLASS__, "onChangeFile");

		//For very first run we have to fall into defaults
		if ($setDefaults === true)
		{
			Helper::setOptions();
		}

		$file = new File(Helper::getEnabledFilePath());
		if (!$file->isExists())
		{
			$file->putContents("");
		}
	}

	public static function uninstall()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->unRegisterEventHandler("main", "OnEpilog", "main", "\\".__CLASS__, "onEpilog");
		$eventManager->unRegisterEventHandler("main", "OnLocalRedirect", "main", "\\".__CLASS__, "onEpilog");
		$eventManager->unRegisterEventHandler("main", "OnChangeFile", "main", "\\".__CLASS__, "onChangeFile");

		$file = new File(Helper::getEnabledFilePath());
		$file->delete();
	}

	/**
	 *
	 * Returns true if composite mode is turned on
	 * @return bool
	 */
	public static function isOn()
	{
		return Helper::isOn();
	}

	/**
	 * @internal
	 * OnEpilog Event Handler
	 * @return void
	 */
	public static function onEpilog()
	{
		if (!self::isOn())
		{
			return;
		}

		global $USER, $APPLICATION;

		if (is_object($USER) && $USER->IsAuthorized())
		{
			if (self::isCurrentUserCC())
			{
				if ($APPLICATION->get_cookie("CC") !== "Y" || $APPLICATION->get_cookie("NCC") === "Y")
				{
					self::setCC();
				}
			}
			else
			{
				if ($APPLICATION->get_cookie("NCC") !== "Y" || $APPLICATION->get_cookie("CC") === "Y")
				{
					self::setNCC();
				}
			}
		}
		else
		{
			if ($APPLICATION->get_cookie("NCC") === "Y" || $APPLICATION->get_cookie("CC") === "Y")
			{
				self::deleteCompositeCookies();
			}
		}

		if (\Bitrix\Main\Data\Cache::shouldClearCache())
		{
			$server = Context::getCurrent()->getServer();

			$queryString = DeleteParam(
				array(
					"clear_cache",
					"clear_cache_session",
					"bitrix_include_areas",
					"back_url_admin",
					"show_page_exec_time",
					"show_include_exec_time",
					"show_sql_stat",
					"bitrix_show_mode",
					"show_link_stat",
					"login"
				)
			);

			$uri = new Uri($server->getRequestUri());
			$refinedUri = $queryString != "" ? $uri->getPath()."?".$queryString : $uri->getPath();

			$cacheStorage = new Page($refinedUri, Helper::getHttpHost());
			$cacheStorage->delete();
		}
	}

	/**
	 * OnChangeFile Event Handler
	 *
	 * @internal
	 * @param $path
	 * @param $site
	 */
	public static function onChangeFile($path, $site)
	{
		$domains = Helper::getDomains();
		$bytes = 0.0;
		foreach ($domains as $domain)
		{
			$cacheStorage = new Page($path, $domain);
			$cacheStorage->delete();
		}

		Helper::updateQuota(-$bytes);
	}

	/**
	 * OnUserLogin Event Handler
	 */
	public static function onUserLogin()
	{
		if (!self::isOn())
		{
			return;
		}

		if (self::isCurrentUserCC())
		{
			self::setCC();
		}
		else
		{
			self::setNCC();
		}
	}

	/**
	 * OnUserLogout Event Handler
	 */
	public static function onUserLogout()
	{
		if (self::isOn())
		{
			self::deleteCompositeCookies();
		}
	}

	public static function isCurrentUserCC()
	{
		global $USER;
		$options = Helper::getOptions();

		$groups = isset($options["GROUPS"]) && is_array($options["GROUPS"]) ? $options["GROUPS"] : array();
		$groups[] = "2";

		$diff = array_diff($USER->GetUserGroupArray(), $groups);

		return count($diff) === 0;
	}

	/**
	 * Sets NCC cookie
	 */
	public static function setNCC()
	{
		global $APPLICATION;
		$APPLICATION->set_cookie("NCC", "Y");
		$APPLICATION->set_cookie("CC", "", 0);
		Helper::deleteUserPrivateKey();
	}

	/**
	 * Sets CC cookie
	 */
	public static function setCC()
	{
		global $APPLICATION;
		$APPLICATION->set_cookie("CC", "Y");
		$APPLICATION->set_cookie("NCC", "", 0);

		$page = Page::getInstance();
		$page->setUserPrivateKey();
	}

	/**
	 * Removes all composite cookies
	 */
	public static function deleteCompositeCookies()
	{
		global $APPLICATION;
		$APPLICATION->set_cookie("NCC", "", 0);
		$APPLICATION->set_cookie("CC", "", 0);
		Helper::deleteUserPrivateKey();
	}

	//region Deprecated Methods

	/**
	 * Sets useHTMLCache property.
	 *
	 * @param boolean $preventAutoUpdate property.
	 *
	 * @deprecated use setAutoUpdate
	 * @return void
	 */
	public static function setPreventAutoUpdate($preventAutoUpdate = true)
	{
		self::$autoUpdate = !$preventAutoUpdate;
	}

	/**
	 * Gets preventAutoUpdate property.
	 *
	 * @return boolean
	 * @deprecated use getAutoUpdate
	 */
	public static function getPreventAutoUpdate()
	{
		return !self::$autoUpdate;
	}

	/**
	 * Gets ids of the dynamic blocks.
	 *
	 * @deprecated
	 * @return array
	 */
	public function getDynamicIDs()
	{
		return StaticArea::getDynamicIDs();
	}

	/**
	 * Returns the identifier of current dynamic area.
	 *
	 * @deprecated
	 * @see \Bitrix\Main\Composite\StaticArea::getCurrentDynamicId
	 * @return string|false
	 */
	public function getCurrentDynamicId()
	{
		return StaticArea::getCurrentDynamicId();
	}

	/**
	 * Adds dynamic data to be sent to the client.
	 *
	 * @deprecated
	 *
	 * @param string $id Unique identifier of the block.
	 * @param string $content Dynamic part html.
	 * @param string $stub Html to use as stub.
	 * @param string $containerId Identifier of the html container.
	 * @param boolean $useBrowserStorage Use browser storage for caching or not.
	 * @param boolean $autoUpdate Automatically or manually update block contents.
	 * @param boolean $useAnimation Animation flag.
	 *
	 * @return void
	 */
	public function addDynamicData(
		$id, $content, $stub = "", $containerId = null, $useBrowserStorage = false,
		$autoUpdate = true, $useAnimation = false
	)
	{
		$area = new StaticArea($id);
		$area->setStub($stub);
		$area->setContainerId($containerId);
		$area->setBrowserStorage($useBrowserStorage);
		$area->setAutoUpdate($autoUpdate);
		$area->setAnimation($useAnimation);
		StaticArea::addDynamicArea($area);
	}

	/**
	 * Marks start of a dynamic block.
	 *
	 * @deprecated
	 *
	 * @param integer $id Unique identifier of the block.
	 *
	 * @return boolean
	 */
	public function startDynamicWithID($id)
	{
		$dynamicArea = new StaticArea($id);

		return $dynamicArea->startDynamicArea();
	}

	/**
	 * Marks end of the dynamic block if it's the current dynamic block
	 * and its start was being marked early.
	 *
	 * @deprecated
	 *
	 * @param string $id Unique identifier of the block.
	 * @param string $stub Html to use as stub.
	 * @param string $containerId Identifier of the html container.
	 * @param boolean $useBrowserStorage Use browser storage for caching or not.
	 * @param boolean $autoUpdate Automatically or manually update block contents.
	 * @param boolean $useAnimation Animation flag.
	 *
	 * @return boolean
	 */
	public function finishDynamicWithID(
		$id, $stub = "", $containerId = null, $useBrowserStorage = false,
		$autoUpdate = true, $useAnimation = false)
	{
		$curDynamicArea = StaticArea::getCurrentDynamicArea();
		if ($curDynamicArea === null || $curDynamicArea->getId() !== $id)
		{
			return false;
		}

		$curDynamicArea->setStub($stub);
		$curDynamicArea->setContainerId($containerId);
		$curDynamicArea->setBrowserStorage($useBrowserStorage);
		$curDynamicArea->setAutoUpdate($autoUpdate);
		$curDynamicArea->setAnimation($useAnimation);

		return $curDynamicArea->finishDynamicArea();
	}

	//endregion
}

if (!class_exists("Bitrix\\Main\\Page\\Frame", false))
{
	class_alias("Bitrix\\Main\\Composite\\Engine", "Bitrix\\Main\\Page\\Frame");
}
