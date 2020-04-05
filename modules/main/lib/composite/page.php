<?php
namespace Bitrix\Main\Composite;

use Bitrix\Main;
use Bitrix\Main\Composite\Data\CacheProvider;
use Bitrix\Main\Composite\Data;
use Bitrix\Main\Composite\Debug\Logger;
use Bitrix\Main\Composite\Internals\Model\PageTable;
use Bitrix\Main\Composite\Internals\PageManager;

/**
 * Class Page
 * @package Bitrix\Main\Composite
 */
class Page
{
	/**
	 * @var Page
	 */
	protected static $instance = null;
	/**
	 * @var string
	 */
	private $cacheKey = null;
	/**
	 * @var bool
	 */
	private $canCache = true;

	/**
	 * @var Data\AbstractStorage
	 */
	private $storage = null;

	/**
	 * @var CacheProvider
	 */
	private $cacheProvider = null;

	private $voting = true;
	/**
	 * Creates new cache manager instance.
	 * @param {string } $requestUri
	 * @param {string} $host
	 * @param {string} $privateKey
	 */
	public function __construct($requestUri = null, $host = null, $privateKey = null)
	{
		if (func_num_args())
		{
			$cacheKey = static::convertUriToPath($requestUri, $host, $privateKey);
			$this->init($cacheKey);
		}
	}

	protected function init($cacheKey)
	{
		if (is_string($cacheKey) && strlen($cacheKey))
		{
			$this->cacheKey = $cacheKey;
			$this->storage = $this->getStaticHtmlStorage($this->cacheKey);
		}
	}

	public static function createFromCacheKey($cacheKey)
	{
		$storage = new static();
		$storage->init($cacheKey);

		return $storage;
	}

	/**
	 * Returns current instance of the Storage.
	 *
	 * @return Page
	 */
	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			$cacheProvider = static::getCacheProvider();
			$privateKey = $cacheProvider !== null ? $cacheProvider->getCachePrivateKey() : null;

			static::$instance = new static(
				Helper::getRequestUri(),
				Helper::getHttpHost(),
				Helper::getRealPrivateKey($privateKey)
			);

			if ($cacheProvider !== null)
			{
				static::$instance->setCacheProvider($cacheProvider);
			}
		}

		return static::$instance;
	}

	/**
	 *
	 * Returns File Storage or Memcached Storage
	 * @return Data\AbstractStorage|null
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	public function setCacheProvider(CacheProvider $provider)
	{
		$this->cacheProvider = $provider;
	}

	/**
	 * @return CacheProvider|null
	 */
	private static function getCacheProvider()
	{
		foreach (GetModuleEvents("main", "OnGetStaticCacheProvider", true) as $arEvent)
		{
			$provider = ExecuteModuleEventEx($arEvent);
			if (is_object($provider) && $provider instanceof CacheProvider)
			{
				return $provider;
			}
		}

		return null;
	}

	/*
	 * Returns private cache key
	 */
	public static function getPrivateKey()
	{
		$cacheProvider = static::getCacheProvider();
		return $cacheProvider !== null ? $cacheProvider->getCachePrivateKey() : null;
	}
	/**
	 * Converts request uri into path safe file with .html extension.
	 * Returns empty string if fails.
	 * @param string $uri Uri.
	 * @param string $host Host name.
	 * @param string $privateKey
	 * @return string
	 */
	public static function convertUriToPath($uri, $host = null, $privateKey = null)
	{
		return Helper::convertUriToPath($uri, $host, $privateKey);
	}

	/**
	 * Returns cache key
	 * @return string
	 */
	public function getCacheKey()
	{
		return $this->cacheKey;
	}
	/**
	 * Writes the content to the storage
	 * @param string $content the string that is to be written
	 * @param string $md5 the content hash
	 *
	 * @return bool
	 */
	public function write($content, $md5)
	{
		if ($this->storage === null)
		{
			return false;
		}

		$cacheSize = $this->storage->getSize();
		$written = $this->storage->write($content."<!--".$md5."-->", $md5);
		if ($written !== false && $this->storage->shouldCountQuota())
		{
			$delta = $cacheSize !== false ? $written - $cacheSize : $written;
			if ($delta !== 0)
			{
				Helper::updateCacheFileSize($delta);
			}
		}

		return $written;
	}

	/**
	 * Returns html content from the cache
	 *
	 * @return string
	 */
	public function read()
	{
		if ($this->storage !== null)
		{
			return $this->storage->read();
		}

		return false;
	}

	/**
	 * Deletes the cache
	 *
	 * @return bool|int
	 */
	public function delete()
	{
		if ($this->storage === null)
		{
			return false;
		}

		$deletedSize = $this->storage->delete();
		if ($deletedSize !== false && $this->storage->shouldCountQuota())
		{
			Helper::updateCacheFileSize(-$deletedSize);
		}

		PageManager::deleteByCacheKey($this->cacheKey);

		return $deletedSize;
	}

	/**
	 * Deletes all cache data
	 * @return bool
	 */
	public function deleteAll()
	{
		if ($this->storage === null)
		{
			return false;
		}

		$this->storage->deleteAll();

		if ($this->storage->shouldCountQuota())
		{
			Helper::updateCacheFileSize(false);
		}

		PageTable::deleteAll();

		return true;
	}

	/**
	 * Returns the time the cache was last modified
	 * @return int|false
	 */
	public function getLastModified()
	{
		if ($this->storage !== null)
		{
			return $this->storage->getLastModified();
		}

		return false;
	}

	/**
	 * Returns true if the cache exists
	 *
	 * @return boolean
	 */
	public function exists()
	{
		if ($this->storage !== null)
		{
			return $this->storage->exists();
		}

		return false;
	}

	/**
	 * Returns hash of the cache
	 * @return string|false
	 */
	public function getMd5()
	{
		if ($this->storage !== null)
		{
			return $this->storage->getMd5();
		}

		return false;
	}

	/**
	 * Returns cache size
	 * @return int|false
	 */
	public function getSize()
	{
		if ($this->storage !== null)
		{
			return $this->storage->getSize();
		}

		return false;
	}

	/**
	 * Returns true if we can cache current request
	 *
	 * @return bool
	 */
	public function isCacheable()
	{
		if ($this->storage === null)
		{
			return false;
		}

		if ($this->cacheProvider !== null && $this->cacheProvider->isCacheable() === false)
		{
			return false;
		}

		if (isset($_SESSION["SESS_SHOW_TIME_EXEC"]) && ($_SESSION["SESS_SHOW_TIME_EXEC"] == 'Y'))
		{
			return false;
		}
		elseif (isset($_SESSION["SHOW_SQL_STAT"]) && ($_SESSION["SHOW_SQL_STAT"] == 'Y'))
		{
			return false;
		}
		elseif (isset($_SESSION["SHOW_CACHE_STAT"]) && ($_SESSION["SHOW_CACHE_STAT"] == 'Y'))
		{
			return false;
		}

		$httpStatus = intval(\CHTTP::GetLastStatus());
		if ($httpStatus == 200 || $httpStatus === 0)
		{
			return $this->canCache;
		}

		return false;
	}

	/**
	 * Marks current page as non cacheable.
	 *
	 * @return void
	 */
	public function markNonCacheable()
	{
		$this->canCache = false;

		if (Logger::isOn())
		{
			$debugBacktrace = debug_backtrace();

			Logger::log(array(
				"TYPE" => Debug\Logger::TYPE_PAGE_NOT_CACHEABLE,
				"MESSAGE" => "File: ".$debugBacktrace[0]["file"].":".$debugBacktrace[0]["line"]
			));
		}
	}

	public function setUserPrivateKey()
	{
		if ($this->cacheProvider !== null)
		{
			$this->cacheProvider->setUserPrivateKey();
		}
	}

	public function onBeforeEndBufferContent()
	{
		if ($this->cacheProvider !== null)
		{
			$this->cacheProvider->onBeforeEndBufferContent();
		}
	}

	/**
	 * Returns the instance of the StaticHtmlStorage
	 * @param string $cacheKey unique cache identifier
	 *
	 * @return Data\AbstractStorage|null
	 */
	public static function getStaticHtmlStorage($cacheKey)
	{
		$configuration = array();
		$htmlCacheOptions = Helper::getOptions();
		$storage = isset($htmlCacheOptions["STORAGE"]) ? $htmlCacheOptions["STORAGE"] : false;

		if (in_array($storage, array("memcached", "memcached_cluster")))
		{
			if (extension_loaded("memcache"))
			{
				return new Data\MemcachedStorage($cacheKey, $configuration, $htmlCacheOptions);
			}
			else
			{
				return null;
			}
		}
		else
		{
			return new Data\FileStorage($cacheKey, $configuration, $htmlCacheOptions);
		}
	}

	public function enableVoting()
	{
		$this->voting = true;
	}

	public function disableVoting()
	{
		$this->voting = false;
	}

	public function isVotingEnabled()
	{
		return $this->voting;
	}

	/**
	 * Tries to vote against composite mode
	 * @param string $context
	 * @internal
	 */
	public function giveNegativeComponentVote($context = "")
	{
		if (
			defined("USE_HTML_STATIC_CACHE")
			&& USE_HTML_STATIC_CACHE === true
			&& StaticArea::getCurrentDynamicId() === false //Voting doesn't work inside a dynamic area
		)
		{
			if (!$this->isVotingEnabled())
			{
				return;
			}

			$this->canCache = false;

			Debug\Logger::log(array(
				"TYPE" => Debug\Logger::TYPE_COMPONENT_VOTING,
				"MESSAGE" => $context
			));
		}
	}
}

class_alias("Bitrix\\Main\\Composite\\Page", "Bitrix\\Main\\Data\\StaticHtmlCache");