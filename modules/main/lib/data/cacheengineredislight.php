<?php

namespace Bitrix\Main\Data;

class CacheEngineRedisLight extends CacheEngineRedis
{
	public function __construct(array $options = [])
	{
		parent::__construct($options);
		$this->useLock = false;
	}

	public function clean($baseDir, $initDir = false, $filename = false)
	{
		if (!self::isAvailable())
		{
			return;
		}

		$baseDirVersion = $this->getBaseDirVersion($baseDir);

		$keyPrefix = $this->getKeyPrefix($baseDirVersion, $initDir);
		$initListKey = $keyPrefix . '|' . self::BX_INIT_DIR_LIST;

		if ($filename <> '')
		{
			$key = $keyPrefix . '|' . $filename;
			$this->del($key);
			$this->delFromSet($initListKey, $filename);
		}
		elseif ($initDir != '')
		{
			$initDirList = $keyPrefix . '|' . static::BX_INIT_DIR_LIST;
			$keys = $this->getSet($initDirList);
			if (!empty($keys))
			{
				$this->del($keys);
				$this->delFromSet($initListKey, $keys);
			}
		}
		else
		{
			if ($this->fullClean)
			{
				$useLock = $this->useLock;
				$this->useLock = false;

				$baseDirVersion = $this->getBaseDirVersion($baseDir);
				$baseList = $this->sid . '|' . $baseDirVersion . '|' . self::BX_BASE_LIST;

				$paths = $this->getSet($baseList);
				foreach ($paths as $path)
				{
					$this->addCleanPath(
						[
						'PREFIX' => $path,
						'CLEAN_FROM' =>  (new \Bitrix\Main\Type\DateTime()),
						'CLUSTER_GROUP' => static::$clusterGroup
					]);
				}

				unset($paths);

				$this->set($this->sid . '|needClean', 3600, 'Y');
				$this->del($baseList);
				$this->useLock = $useLock;
			}

			$baseDirKey = $this->getBaseDirKey($baseDir);
			$this->del($baseDirKey);
			unset(static::$baseDirVersion[$baseDirKey]);
		}
	}

	public function write($vars, $baseDir, $initDir, $filename, $ttl)
	{
		$baseDirVersion = $this->getBaseDirVersion($baseDir);

		$keyPrefix = $this->getKeyPrefix($baseDirVersion, $initDir);
		$key = $keyPrefix . '|' . $filename;

		$exp = $this->ttlMultiplier * (int) $ttl;
		$this->set($key, $exp, $vars);
		$initListKey = $keyPrefix . '|' . self::BX_INIT_DIR_LIST;
		$this->addToSet($initListKey, $key);

		if ($this->fullClean)
		{
			$baseListKey = $this->sid . '|' . $baseDirVersion . '|' . self::BX_BASE_LIST;
			$this->addToSet($baseListKey, $initListKey);
		}

		if (Cache::getShowCacheStat())
		{
			$this->written = strlen(serialize($vars));
			$this->path = $baseDir . $initDir . $filename;
		}
	}

	public function read(&$vars, $baseDir, $initDir, $filename, $ttl)
	{
		$baseDirVersion = $this->getBaseDirVersion($baseDir);

		$key = $this->getKeyPrefix($baseDirVersion, $initDir) . '|' . $filename;
		$vars = $this->get($key);

		if (Cache::getShowCacheStat())
		{
			$this->read = strlen(serialize($vars));
			$this->path = $baseDir . $initDir . $filename;
		}

		return $vars !== false;
	}
}
