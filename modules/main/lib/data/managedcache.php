<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Data;

use Bitrix\Main;

class ManagedCache
{
	/** @var Cache[] */
	protected $cache = [];
	protected $cacheInit = [];
	protected $cachePath = [];
	protected $vars = [];
	protected $ttl = [];
	protected $dbType;

	public function __construct()
	{
		$this->dbType = strtoupper(Main\Application::getInstance()->getConnection()->getType());
	}

	// Tries to read cached variable value from the file
	// Returns true on success
	// otherwise returns false
	public function read($ttl, $uniqueId, $tableId = false)
	{
		if (!isset($this->cacheInit[$uniqueId]))
		{
			$this->cache[$uniqueId] = Cache::createInstance();
			$this->cachePath[$uniqueId] = $this->dbType . ($tableId === false ? "" : "/" . $tableId);
			$this->ttl[$uniqueId] = $ttl;
			$this->cacheInit[$uniqueId] = $this->cache[$uniqueId]->initCache($ttl, $uniqueId, $this->cachePath[$uniqueId], "managed_cache");
		}
		return $this->cacheInit[$uniqueId] || array_key_exists($uniqueId, $this->vars);
	}

	public function getImmediate($ttl, $uniqueId, $tableId = false)
	{
		$cache = Cache::createInstance();
		$cachePath = $this->dbType . ($tableId === false ? "" : "/" . $tableId);

		if ($cache->initCache($ttl, $uniqueId, $cachePath, "managed_cache"))
		{
			return $cache->getVars();
		}
		return false;
	}

	/**
	 * This method is used to read the variable value
	 * from the cache after successfull Read
	 *
	 * @param string $uniqueId
	 * @return mixed
	 */
	public function get($uniqueId)
	{
		if (array_key_exists($uniqueId, $this->vars))
		{
			return $this->vars[$uniqueId];
		}
		elseif (isset($this->cacheInit[$uniqueId]) && $this->cacheInit[$uniqueId])
		{
			return $this->cache[$uniqueId]->getVars();
		}
		else
		{
			return false;
		}
	}

	// Sets new value to the variable
	public function set($uniqueId, $val)
	{
		if (isset($this->cache[$uniqueId]))
		{
			$this->vars[$uniqueId] = $val;
		}
	}

	public function setImmediate($uniqueId, $val)
	{
		if (isset($this->cache[$uniqueId]))
		{
			$cache = Cache::createInstance();
			$cache->noOutput();
			$cache->startDataCache($this->ttl[$uniqueId], $uniqueId, $this->cachePath[$uniqueId], $val, "managed_cache");
			$cache->endDataCache();

			unset($this->cache[$uniqueId]);
			unset($this->cacheInit[$uniqueId]);
			unset($this->cachePath[$uniqueId]);
			unset($this->vars[$uniqueId]);
		}
	}

	// Marks cache entry as invalid
	public function clean($uniqueId, $tableId = false)
	{
		$cache = Cache::createInstance();
		$cache->clean(
			$uniqueId,
			$this->dbType . ($tableId === false ? "" : "/" . $tableId),
			"managed_cache"
		);
		if (isset($this->cache[$uniqueId]))
		{
			unset($this->cache[$uniqueId]);
			unset($this->cacheInit[$uniqueId]);
			unset($this->cachePath[$uniqueId]);
			unset($this->vars[$uniqueId]);
		}
	}

	// Marks cache entries associated with the table as invalid
	public function cleanDir($tableId)
	{
		$strPath = $this->dbType . "/" . $tableId;
		foreach ($this->cachePath as $uniqueId => $Path)
		{
			if ($Path == $strPath)
			{
				unset($this->cache[$uniqueId]);
				unset($this->cacheInit[$uniqueId]);
				unset($this->cachePath[$uniqueId]);
				unset($this->vars[$uniqueId]);
			}
		}
		$cache = Cache::createInstance();
		$cache->cleanDir($this->dbType . "/" . $tableId, "managed_cache");
	}

	// Clears all managed_cache
	public function cleanAll()
	{
		$this->cache = [];
		$this->cacheInit = [];
		$this->cachePath = [];
		$this->vars = [];
		$this->ttl = [];

		$cache = Cache::createInstance();
		$cache->cleanDir(false, "managed_cache");
	}

	// Use it to flush cache to the files.
	// Causion: only at the end of all operations!
	public static function finalize()
	{
		$cacheManager = Main\Application::getInstance()->getManagedCache();
		$cache = Cache::createInstance();
		foreach ($cacheManager->cache as $uniqueId => $val)
		{
			if (array_key_exists($uniqueId, $cacheManager->vars))
			{
				$cache->startDataCache($cacheManager->ttl[$uniqueId], $uniqueId, $cacheManager->cachePath[$uniqueId], $cacheManager->vars[$uniqueId], "managed_cache");
				$cache->endDataCache();
			}
		}
	}

	public function getCompCachePath($relativePath)
	{
		// TODO: global var!
		global $BX_STATE;

		if ($BX_STATE === "WA")
		{
			$salt = Cache::getSalt();
		}
		else
		{
			$salt = "/" . mb_substr(md5($BX_STATE), 0, 3);
		}

		$path = "/" . SITE_ID . $relativePath . $salt;
		return $path;
	}
}
