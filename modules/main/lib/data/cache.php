<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Data;

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Diag;

class Cache
{
	/** @var ICacheEngine | \ICacheBackend */
	protected $cacheEngine;

	protected $content;
	protected $vars;
	protected int $ttl = 0;
	protected string $uniqueString = '';
	protected string $baseDir = 'cache';
	protected string $initDir = '';
	protected string $filename = '';
	protected bool $isStarted = false;

	protected static $showCacheStat = false;
	protected static $clearCache = null;
	protected static $clearCacheSession = null;

	protected bool $forceRewriting = false;
	protected bool $hasOutput = true;

	public static function createCacheEngine($params = [])
	{
		$hash = sha1(serialize($params));

		static $cacheEngine = [];
		if (!empty($cacheEngine[$hash]))
		{
			return clone $cacheEngine[$hash];
		}

		// Events can't be used here because events use cache
		$cacheType = 'files';
		$v = Config\Configuration::getValue('cache');
		if (!empty($v['type']))
		{
			$cacheType = $v['type'];
		}

		if (is_array($cacheType))
		{
			if (isset($cacheType['class_name']))
			{
				if (!isset($cacheType['extension']) || extension_loaded($cacheType['extension']))
				{
					if (isset($cacheType['required_file']) && ($requiredFile = Main\Loader::getLocal($cacheType['required_file'])) !== false)
					{
						require_once($requiredFile);
					}

					if (isset($cacheType['required_remote_file']))
					{
						require_once($cacheType['required_remote_file']);
					}

					$className = $cacheType['class_name'];
					if (class_exists($className))
					{
						$cacheEngine[$hash] = new $className($params);
					}
				}
			}
		}
		else
		{
			switch ($cacheType)
			{
				case 'redis':
					$cacheEngine[$hash] = new CacheEngineRedis($params);
					break;
				case 'memcached':
					$cacheEngine[$hash] = new CacheEngineMemcached($params);
					break;
				case 'memcache':
					$cacheEngine[$hash] = new CacheEngineMemcache($params);
					break;
				case 'apc':
				case 'apcu':
					$cacheEngine[$hash] = new CacheEngineApc($params);
					break;
				case 'files':
					$cacheEngine[$hash] = new CacheEngineFiles($params);
					break;
				default:
					$cacheEngine[$hash] = new CacheEngineNone();
					break;
			}
		}

		if (empty($cacheEngine[$hash]))
		{
			$cacheEngine[$hash] = new CacheEngineNone();
			trigger_error('Cache engine is not found', E_USER_WARNING);
		}

		if (!$cacheEngine[$hash]->isAvailable())
		{
			$cacheEngine[$hash] = new CacheEngineNone();
			trigger_error('Cache engine is not available', E_USER_WARNING);
		}

		return clone $cacheEngine[$hash];
	}

	public static function getCacheEngineType()
	{
		$obj = static::createCacheEngine();
		$class = get_class($obj);
		if (($pos = strrpos($class, "\\")) !== false)
		{
			$class = substr($class, $pos + 1);
		}

		return strtolower($class);
	}

	/**
	 * @param array $params
	 * @return static Cache
	 */
	public static function createInstance($params = [])
	{
		$cacheEngine = static::createCacheEngine($params);
		return new static($cacheEngine);
	}

	public function __construct($cacheEngine)
	{
		$this->cacheEngine = $cacheEngine;
	}

	public static function setShowCacheStat($showCacheStat)
	{
		static::$showCacheStat = $showCacheStat;
	}

	public static function getShowCacheStat()
	{
		return static::$showCacheStat;
	}

	/**
	 * A privileged user wants to skip cache on this hit.
	 * @param bool $clearCache
	 */
	public static function setClearCache($clearCache)
	{
		static::$clearCache = $clearCache;
	}

	/**
	 * A privileged user wants to skip cache on this session.
	 * @param bool $clearCacheSession
	 */
	public static function setClearCacheSession($clearCacheSession)
	{
		static::$clearCacheSession = $clearCacheSession;
	}

	public static function getSalt()
	{
		$context = Main\Application::getInstance()->getContext();
		$server = $context->getServer();

		$scriptName = $server->get('SCRIPT_NAME');
		if ($scriptName == '/bitrix/urlrewrite.php' && (($v = $server->get('REAL_FILE_PATH')) != null))
		{
			$scriptName = $v;
		}
		elseif ($scriptName == '/404.php' && (($v = $server->get('REAL_FILE_PATH')) != null))
		{
			$scriptName = $v;
		}
		return '/' . mb_substr(md5($scriptName), 0, 3);
	}

	/**
	 * Returns true if a privileged user wants to skip reading from cache (on this hit or session).
	 * @return bool
	 */
	public static function shouldClearCache()
	{
		global $USER;

		$application = Main\Application::getInstance();

		if (!$application->isInitialized())
		{
			return false;
		}

		$kernelSession = $application->getKernelSession();

		if (isset(static::$clearCacheSession) || isset(static::$clearCache))
		{
			if ($USER instanceof \CUser && $USER->CanDoOperation('cache_control'))
			{
				if (isset(static::$clearCacheSession))
				{
					if (static::$clearCacheSession === true)
					{
						$kernelSession['SESS_CLEAR_CACHE'] = 'Y';
					}
					else
					{
						unset($kernelSession['SESS_CLEAR_CACHE']);
					}
				}

				if (isset(static::$clearCache) && (static::$clearCache === true))
				{
					return true;
				}
			}
		}

		if (isset($kernelSession['SESS_CLEAR_CACHE']) && $kernelSession['SESS_CLEAR_CACHE'] === 'Y')
		{
			return true;
		}

		return false;
	}

	public static function getPath($uniqueString)
	{
		$un = md5($uniqueString);
		return mb_substr($un, 0, 2) . '/' . $un . '.php';
	}

	public function clean($uniqueString, $initDir = false, $baseDir = 'cache')
	{
		$personalRoot = Main\Application::getPersonalRoot();
		$baseDir = $personalRoot . '/' . $baseDir . '/';
		$filename = $this->getPath($uniqueString);

		if (static::$showCacheStat)
		{
			Diag\CacheTracker::add(0, '', $baseDir, $initDir, '/' . $filename, 'C');
		}

		return $this->cacheEngine->clean($baseDir, $initDir, '/' . $filename);
	}

	public function cleanDir($initDir = false, $baseDir = 'cache')
	{
		$personalRoot = Main\Application::getPersonalRoot();
		$baseDir = $personalRoot . '/' . $baseDir . '/';

		if (static::$showCacheStat)
		{
			Diag\CacheTracker::add(0, "", $baseDir, $initDir, "", "C");
		}

		$this->cacheEngine->clean($baseDir, $initDir);
	}

	public function initCache($ttl, $uniqueString, $initDir = false, $baseDir = 'cache')
	{
		if ($initDir === false)
		{
			$initDir = 'default';
		}

		$personalRoot = Main\Application::getPersonalRoot();
		$this->baseDir = $personalRoot . '/' . $baseDir . '/';
		$this->initDir = $initDir;
		$this->filename = '/' . $this->getPath($uniqueString);
		$this->ttl = $ttl;
		$this->uniqueString = $uniqueString;
		$this->vars = false;

		if ($ttl <= 0 || $this->forceRewriting || static::shouldClearCache())
		{
			return false;
		}

		$data = ['CONTENT' => '', 'VARS' => ''];
		if (!$this->cacheEngine->read($data, $this->baseDir, $this->initDir, $this->filename, $this->ttl))
		{
			return false;
		}

		if (!is_array($data) || empty($data) || !isset($data['CONTENT']) || !isset($data['VARS']))
		{
			return false;
		}

		if (static::$showCacheStat)
		{
			$read = 0;
			$path = '';
			if ($this->cacheEngine instanceof ICacheEngineStat)
			{
				$read = $this->cacheEngine->getReadBytes();
				$path = $this->cacheEngine->getCachePath();
			}
			elseif ($this->cacheEngine instanceof \ICacheBackend)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				$read = $this->cacheEngine->read;

				/** @noinspection PhpUndefinedFieldInspection */
				$path = $this->cacheEngine->path;
			}

			Diag\CacheTracker::addCacheStatBytes($read);
			Diag\CacheTracker::add($read, $path, $this->baseDir, $this->initDir, $this->filename, 'R');
		}

		$this->content = $data['CONTENT'];
		$this->vars = $data['VARS'];

		return true;
	}

	public function output()
	{
		if ($this->hasOutput)
		{
			echo $this->content;
		}
	}

	public function noOutput()
	{
		$this->hasOutput = false;
	}

	public function getVars()
	{
		return $this->vars;
	}

	public function startDataCache($TTL = false, $uniqueString = false, $initDir = false, $vars = array(), $baseDir = 'cache')
	{
		$narg = func_num_args();
		if ($narg <= 0)
		{
			$TTL = $this->ttl;
		}

		if ($narg <= 1)
		{
			$uniqueString = $this->uniqueString;
		}

		if ($narg <= 2)
		{
			$initDir = $this->initDir;
		}

		if ($narg <= 3)
		{
			$vars = $this->vars;
		}

		if ($this->initCache($TTL, $uniqueString, $initDir, $baseDir))
		{
			$this->output();
			return false;
		}

		if ($TTL <= 0)
		{
			return true;
		}

		if ($this->hasOutput)
		{
			ob_start();
		}

		$this->vars = $vars;
		$this->isStarted = true;

		return true;
	}

	public function abortDataCache()
	{
		if (!$this->isStarted)
		{
			return;
		}

		$this->isStarted = false;
		if ($this->hasOutput)
		{
			ob_end_flush();
		}
	}

	public function endDataCache($vars = false)
	{
		if (!$this->isStarted)
		{
			return;
		}

		$this->isStarted = false;
		$data = [
			'CONTENT' => $this->hasOutput ? ob_get_contents() : '',
			'VARS' => ($vars !== false ? $vars : $this->vars),
		];

		$this->cacheEngine->write($data, $this->baseDir, $this->initDir, $this->filename, $this->ttl);

		if (static::$showCacheStat)
		{
			$written = 0;
			$path = '';
			if ($this->cacheEngine instanceof ICacheEngineStat)
			{
				$written = $this->cacheEngine->getWrittenBytes();
				$path = $this->cacheEngine->getCachePath();
			}
			elseif ($this->cacheEngine instanceof \ICacheBackend)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				$written = $this->cacheEngine->written;

				/** @noinspection PhpUndefinedFieldInspection */
				$path = $this->cacheEngine->path;
			}
			Diag\CacheTracker::addCacheStatBytes($written);
			Diag\CacheTracker::add($written, $path, $this->baseDir, $this->initDir, $this->filename, 'W');
		}

		if ($this->hasOutput)
		{
			if (ob_get_contents() <> '')
			{
				ob_end_flush();
			}
			else
			{
				ob_end_clean();
			}
		}
	}

	public function isCacheExpired($path)
	{
		return $this->cacheEngine->isCacheExpired($path);
	}

	public function isStarted()
	{
		return $this->isStarted;
	}

	/**
	 * @deprecated Use \Bitrix\Main\Data\Cache::cleanDir().
	 * @param $full
	 * @param $initDir
	 */
	public static function clearCache($full = false, $initDir = ''): void
	{
		if ($initDir === '' && is_string($full))
		{
			$initDir = $full;
			$full = true;
		}

		if ($full === true)
		{
			$obCache = static::createInstance();
			$obCache->cleanDir($initDir, 'cache');
		}
	}

	/**
	 * Sets the forced mode to ignore TTL and rewrite the cache.
	 * @param bool $mode
	 */
	public function forceRewriting($mode)
	{
		$this->forceRewriting = (bool) $mode;
	}
}
