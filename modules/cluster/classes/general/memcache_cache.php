<?php

class CPHPCacheMemcacheCluster
{
	/** @var Memcache $obMemcache */
	private static $obMemcache;
	private static $baseDirVersion = array();
	private static $arOtherGroups = array();
	var $bQueue = null;
	var $sid;
	//cache stats
	var $written = false;
	var $read = false;
	// unfortunately is not available for memcache...

	/** @var array|false $arList */
	private static $arList = false;

	protected $useLock = true;
	protected $ttlMultiplier = 2;
	protected static $locks = array();

	public static function LoadConfig()
	{
		if (self::$arList === false)
		{
			$arList = false;
			if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/memcache.php"))
				include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/memcache.php");

			if (defined("BX_MEMCACHE_CLUSTER") && is_array($arList))
			{
				foreach ($arList as $i => $arServer)
				{
					$bOtherGroup = defined("BX_CLUSTER_GROUP") && ($arServer["GROUP_ID"] !== BX_CLUSTER_GROUP);

					if (($arServer["STATUS"] !== "ONLINE") || $bOtherGroup)
						unset($arList[$i]);

					if ($bOtherGroup)
						self::$arOtherGroups[$arServer["GROUP_ID"]] = true;
				}

				self::$arList = $arList;
			}
			else
				self::$arList = array();

		}
		return self::$arList;
	}

	function __construct()
	{
		if (!is_object(self::$obMemcache))
		{
			self::$obMemcache = new Memcache;
			$arServerList = CPHPCacheMemcacheCluster::LoadConfig();

			if (count($arServerList) == 1)
			{
				$arServer = array_pop($arServerList);
				self::$obMemcache->connect(
					$arServer["HOST"]
					, $arServer["PORT"]
				);
			}
			else
			{
				foreach ($arServerList as $arServer)
				{
					self::$obMemcache->addServer(
						$arServer["HOST"]
						, $arServer["PORT"]
						, true //persistent
						, ($arServer["WEIGHT"] > 0? $arServer["WEIGHT"]: 1)
						, 1 //timeout
					);
				}
			}
		}

		if (defined("BX_CACHE_SID"))
			$this->sid = BX_MEMCACHE_CLUSTER.BX_CACHE_SID;
		else
			$this->sid = BX_MEMCACHE_CLUSTER;

		if (defined("BX_CLUSTER_GROUP"))
			$this->bQueue = true;

		$cacheConfig = \Bitrix\Main\Config\Configuration::getValue("cache");
		if ($cacheConfig && is_array($cacheConfig))
		{
			if (isset($cacheConfig["use_lock"]))
			{
				$this->useLock = (bool)$cacheConfig["use_lock"];
			}

			if (isset($cacheConfig["sid"]) && ($cacheConfig["sid"] != ""))
			{
				if (!defined("BX_CACHE_SID"))
				{
					$this->sid = BX_MEMCACHE_CLUSTER.$cacheConfig["sid"];
				}
			}

			if (isset($cacheConfig["ttl_multiplier"]) && $this->useLock)
			{
				$this->ttlMultiplier = (integer)$cacheConfig["ttl_multiplier"];
			}
		}

		$this->sid .= ($this->useLock? 2: 3);

		if (!$this->useLock)
		{
			$this->ttlMultiplier = 1;
		}
	}

	function IsAvailable()
	{
		return count(self::$arList) > 0;
	}

	function QueueRun($param1, $param2, $param3)
	{
		$this->bQueue = false;
		$this->clean($param1, $param2, $param3);
	}

	protected function lock($baseDir, $initDir, $key, $TTL)
	{
		if (
			isset(self::$locks[$baseDir])
			&& isset(self::$locks[$baseDir][$initDir])
			&& isset(self::$locks[$baseDir][$initDir][$key])
		)
		{
			return true;
		}
		elseif (self::$obMemcache->add($key, 1, 0, intval($TTL)))
		{
			self::$locks[$baseDir][$initDir][$key] = true;
			return true;
		}

		return false;
	}

	protected function unlock($baseDir, $initDir = false, $key = false, $TTL = 0)
	{
		if ($key !== false)
		{
			if ($TTL > 0)
			{
				self::$obMemcache->set($key."~", 1, 0, time() + intval($TTL));
			}
			else
			{
				self::$obMemcache->replace($key."~", "", 0, 1);
			}

			unset(self::$locks[$baseDir][$initDir][$key]);
		}
		elseif ($initDir !== false)
		{
			if (isset(self::$locks[$baseDir][$initDir]))
			{
				foreach (self::$locks[$baseDir][$initDir] as $subKey)
				{
					$this->unlock($baseDir, $initDir, $subKey, $TTL);
				}
				unset(self::$locks[$baseDir][$initDir]);
			}
		}
		elseif ($baseDir !== false)
		{
			if (isset(self::$locks[$baseDir]))
			{
				foreach (self::$locks[$baseDir] as $subInitDir)
				{
					$this->unlock($baseDir, $subInitDir, false, $TTL);
				}
			}
		}
	}

	function clean($basedir, $initdir = false, $filename = false)
	{
		$key = false;
		if (is_object(self::$obMemcache))
		{
			if (
				$this->bQueue
				&& class_exists('CModule')
				&& CModule::IncludeModule('cluster')
			)
			{
				foreach (self::$arOtherGroups as $group_id => $tmp)
				{
					CClusterQueue::Add($group_id, 'CPHPCacheMemcacheCluster', $basedir, $initdir, $filename);
				}
			}

			if (strlen($filename))
			{
				if (!isset(self::$baseDirVersion[$basedir]))
					self::$baseDirVersion[$basedir] = self::$obMemcache->get($this->sid.$basedir);

				if (self::$baseDirVersion[$basedir] === false || self::$baseDirVersion[$basedir] === '')
					return;

				if ($initdir !== false)
				{
					$initdir_version = self::$obMemcache->get(self::$baseDirVersion[$basedir]."|".$initdir);
					if ($initdir_version === false || $initdir_version === '')
						return true;
				}
				else
				{
					$initdir_version = "";
				}

				$key = self::$baseDirVersion[$basedir]."|".$initdir_version."|".$filename;
				self::$obMemcache->replace($key, "", 0, 1);
			}
			else
			{
				if (strlen($initdir))
				{
					if (!isset(self::$baseDirVersion[$basedir]))
						self::$baseDirVersion[$basedir] = self::$obMemcache->get($this->sid.$basedir);

					if (self::$baseDirVersion[$basedir] === false || self::$baseDirVersion[$basedir] === '')
						return;

					self::$obMemcache->replace(self::$baseDirVersion[$basedir]."|".$initdir, "", 0, 1);
				}
				else
				{
					if (isset(self::$baseDirVersion[$basedir]))
						unset(self::$baseDirVersion[$basedir]);

					self::$obMemcache->replace($this->sid.$basedir, "", 0, 1);
				}
			}
			$this->unlock($basedir, $initdir, $key."~");
		}
	}

	function read(&$arAllVars, $basedir, $initdir, $filename, $TTL)
	{
		if (!isset(self::$baseDirVersion[$basedir]))
			self::$baseDirVersion[$basedir] = self::$obMemcache->get($this->sid.$basedir);

		if (self::$baseDirVersion[$basedir] === false || self::$baseDirVersion[$basedir] === '')
			return false;

		if ($initdir !== false)
		{
			$initdir_version = self::$obMemcache->get(self::$baseDirVersion[$basedir]."|".$initdir);
			if ($initdir_version === false || $initdir_version === '')
				return false;
		}
		else
		{
			$initdir_version = "";
		}

		$key = self::$baseDirVersion[$basedir]."|".$initdir_version."|".$filename;

		if ($this->useLock)
		{
			$cachedData = self::$obMemcache->get($key);
			if (!is_array($cachedData))
			{
				return false;
			}

			if ($cachedData["datecreate"] < (time() - $TTL)) //has expired
			{
				if ($this->lock($baseDir, $initDir, $key."~", $TTL))
				{
					return false;
				}
			}

			$arAllVars = $cachedData["content"];
		}
		else
		{
			$arAllVars = self::$obMemcache->get($key);
		}

		if ($arAllVars === false || $arAllVars === '')
		{
			return false;
		}

		return true;
	}

	function write($arAllVars, $basedir, $initdir, $filename, $TTL)
	{
		if (!isset(self::$baseDirVersion[$basedir]))
			self::$baseDirVersion[$basedir] = self::$obMemcache->get($this->sid.$basedir);

		if (self::$baseDirVersion[$basedir] === false || self::$baseDirVersion[$basedir] === '')
		{
			self::$baseDirVersion[$basedir] = $this->sid.md5(mt_rand());
			self::$obMemcache->set($this->sid.$basedir, self::$baseDirVersion[$basedir]);
		}

		if ($initdir !== false)
		{
			$initdir_version = self::$obMemcache->get(self::$baseDirVersion[$basedir]."|".$initdir);
			if ($initdir_version === false || $initdir_version === '')
			{
				$initdir_version = md5(mt_rand());
				self::$obMemcache->set(self::$baseDirVersion[$basedir]."|".$initdir, $initdir_version);
			}
		}
		else
		{
			$initdir_version = "";
		}

		$key = self::$baseDirVersion[$basedir]."|".$initdir_version."|".$filename;
		$time = time();
		$exp = $this->ttlMultiplier > 0? $time + intval($TTL) * $this->ttlMultiplier: 0;

		if ($this->useLock)
		{
			self::$obMemcache->set($key, array("datecreate" => $time, "content" => $arAllVars), 0, $exp);
			$this->unlock($basedir, $initdir, $key."~", $TTL);
		}
		else
		{
			self::$obMemcache->set($key, $arAllVars, 0, $exp);
		}
	}

	function IsCacheExpired($path)
	{
		return false;
	}
}
