<?php

namespace Bitrix\Main\Data\Cache;

use Bitrix\Main\Config;
use Bitrix\Main\Application;
use Bitrix\Main\Data;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Data\CacheEngineInterface;
use Bitrix\Main\Data\CacheEngineStatInterface;
use Bitrix\Main\Data\Internal\CacheCleanPathTable;
use Bitrix\Main\Type\DateTime;

abstract class KeyValueEngine implements CacheEngineInterface, CacheEngineStatInterface
{
	const BX_BASE_LIST = 'BL:';
	const BX_INIT_DIR_LIST = 'IL:';

	protected static $engine = null;
	protected static array $locks = [];
	protected static bool $isConnected = false;
	protected static array $baseDirVersion = [];
	protected static array $initDirPartitions = [];
	protected string $sid = 'BX';
	protected bool $useLock = false;
	protected int $ttlMultiplier = 2;
	protected int $ttlOld = 60;
	protected bool $old = false;
	protected bool $fullClean = false;
	protected static int $clusterGroup = 0;

	/** Cache stat */
	protected int $written = 0;
	protected int $read = 0;
	protected string $path = '';

	abstract public function getConnectionName(): string;
	abstract public static function getConnectionClass();

	abstract public function set($key, $ttl, $value);
	abstract public function get($key);
	abstract public function del($key);

	abstract public function setNotExists($key, $ttl , $value);
	abstract public function checkInSet($key, $value): bool;
	abstract public function addToSet($key, $value);
	abstract public function getSet($key): array;
	abstract public function delFromSet($key, $member);
	abstract public function deleteBySet($key, $prefix = '');

	/**
	 * CacheEngine constructor.
	 * @param array $options Cache options.
	 */
	public function __construct(array $options = [])
	{
		$config = $this->configure($options);

		$this->connect($config);

		static::$clusterGroup = (defined('BX_CLUSTER_GROUP') ? (int)constant('BX_CLUSTER_GROUP') : 0);

		if (self::$isConnected)
		{
			if ($this->lock($this->sid . '|cacheClean', 10))
			{
				Application::getInstance()->addBackgroundJob([$this, 'delayedDelete'], [], Application::JOB_PRIORITY_LOW);
			}
		}
	}

	protected function connect($config)
	{
		if (!self::$isConnected)
		{
			$connectionPool = Application::getInstance()->getConnectionPool();
			$connectionPool->setConnectionParameters($this->getConnectionName(), $config);

			/** @var Data\RedisConnection | Data\MemcacheConnection | Data\MemcachedConnection $engineConnection */
			$engineConnection = $connectionPool->getConnection($this->getConnectionName());

			self::$engine = $engineConnection->getResource();
			self::$isConnected = $engineConnection->isConnected();
		}
	}

	protected function configure($options = []): array
	{
		$config = [];
		$cacheConfig = Config\Configuration::getValue('cache');

		if (!$cacheConfig || !is_array($cacheConfig))
		{
			return $config;
		}

		if (isset($options['type']))
		{
			$type = $options['type'];
		}
		else
		{
			if (is_array($cacheConfig['type']) && isset($cacheConfig['type']['extension']))
			{
				$type = $cacheConfig['type']['extension'];
			}
			else
			{
				$type = $cacheConfig['type'];
			}
		}

		$config['type'] = $type;
		$config['className'] = static::getConnectionClass();
		$config['servers'] = [];

		if (!empty($cacheConfig[$type]['host']))
		{
			$config['servers'][] = [
				'host' => $cacheConfig[$type]['host'],
				'port' => (int)($cacheConfig[$type]['port'] ?? 0)
			];
		}

		// Settings from .settings.php
		if (isset($cacheConfig['servers']) && is_array($cacheConfig['servers']))
		{
			$config['servers'] = array_merge($config['servers'], $cacheConfig['servers']);
		}

		// Setting from cluster config
		if (isset($options['servers']) && is_array($options['servers']))
		{
			$config['servers'] = array_merge($config['servers'], $options['servers']);
		}

		if (isset($options['actual_data']))
		{
			$cacheConfig['actual_data'] = $options['actual_data'];
		}

		if (isset($cacheConfig['use_lock']))
		{
			$this->useLock = (bool)$cacheConfig['use_lock'];
		}

		if (!empty($options['sid']))
		{
			$this->sid = $options['sid'];
		}
		elseif (!empty($cacheConfig['sid']))
		{
			$this->sid = $cacheConfig['sid'];
		}
		$this->sid .= '|v1.1';

		if (isset($cacheConfig['actual_data']) && !$this->useLock)
		{
			$this->useLock = !$cacheConfig['actual_data'];
		}

		if (!$this->useLock)
		{
			$this->ttlMultiplier = 1;
		}

		if (isset($cacheConfig['ttl_multiplier']) && $this->useLock)
		{
			$this->ttlMultiplier = (int)$cacheConfig['ttl_multiplier'];
			if ($this->ttlMultiplier < 1)
			{
				$this->ttlMultiplier = 1;
			}
		}

		if (isset($cacheConfig['full_clean']))
		{
			$this->fullClean = (bool)$cacheConfig['full_clean'];
		}

		if (isset($cacheConfig['ttlOld']) && (int)$cacheConfig['ttlOld'] > 0)
		{
			$this->ttlOld = (int)$cacheConfig['ttlOld'];
		}

		return $config;
	}

	/**
	 * @inheritdoc
	 */
	public function getReadBytes()
	{
		return $this->read;
	}

	/**
	 * @inheritdoc
	 */
	public function getWrittenBytes()
	{
		return $this->written;
	}

	/**
	 * @inheritdoc
	 */
	public function getCachePath()
	{
		return $this->path;
	}

	/**
	 * Tries to put non-blocking exclusive lock on the cache entry.
	 * Returns true on success.
	 *
	 * @param string $key Calculated cache key.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return boolean
	 */
	protected function lock(string $key = '', int $ttl = 0): bool
	{
		if ($key == '')
		{
			return false;
		}

		$key .= '~';
		if (isset(self::$locks[$key]))
		{
			return true;
		}
		else
		{
			if ($this->setNotExists($key, $ttl, $this->ttlOld))
			{
				self::$locks[$key] = true;
				return true;
			}
		}
		return false;
	}

	/**
	 * Releases the lock obtained by lock method.
	 * @param string $key Calculated cache key.
	 * @return void
	 */
	protected function unlock(string $key = ''): void
	{
		if ($key != '')
		{
			$key .= '~';
			$this->del($key);
			unset(self::$locks[$key]);
		}
	}

	/**
	 * Closes opened connection.
	 * @return void
	 */
	function close(): void
	{
		if (self::$engine != null)
		{
			self::$engine->close();
			self::$engine = null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function isAvailable()
	{
		return self::$isConnected;
	}

	/**
	 * Stub function, always returns false.
	 *
	 * @return boolean
	 */
	public function isCacheExpired($path)
	{
		return false;
	}

	protected function getPartition($key): string
	{
		return substr(sha1($key), 0, 4);
	}

	protected function getInitDirKey($baseDirVersion, $baseDir, $initDir): string
	{
		return $this->sid . '|BDV:' . $baseDirVersion  . '|IDH:' . sha1($baseDir . '|' . $initDir);
	}

	protected function getBaseDirKey($baseDir): string
	{
		return $this->sid . '|BDV:' . sha1($baseDir);
	}

	protected function getKeyPrefix($baseDirVersion, $initDirVersion): string
	{
		return $this->sid . '|' . sha1($baseDirVersion . '|' . $initDirVersion);
	}

	/**
	 * Return InitDirVersion
	 *
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param bool|string $initDir Directory within base.
	 * @param bool $create
	 * @return string
	 */
	protected function getInitDirVersion($baseDir, $initDir = false, bool $create = true): string
	{
		$baseDirVersion = $this->getBaseDirVersion($baseDir);
		$initDirHash = sha1($baseDir . '|' . $initDir);

		$key = $this->getInitDirKey($baseDirVersion, $baseDir, $initDir);
		$initDirVersion = $this->get($key);

		if ($initDirVersion == '' && $create)
		{
			$initDirVersion = sha1($initDirHash . '|' . mt_rand() . '|' . microtime());
			$this->set($key, 0, $initDirVersion);
		}

		return $initDirVersion;
	}

	/**
	 * Return BaseDirVersion
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 *
	 * @return string
	 */
	protected function getBaseDirVersion($baseDir): string
	{
		$key = $this->getBaseDirKey($baseDir);

		if (!isset(static::$baseDirVersion[$key]))
		{
			static::$baseDirVersion[$key] = $this->get($key);
		}

		if (static::$baseDirVersion[$key] == '')
		{
			static::$baseDirVersion[$key] = sha1(sha1($baseDir) . '|' . mt_rand() . '|' . microtime());
			$this->set($key, 0, static::$baseDirVersion[$key]);
		}

		return static::$baseDirVersion[$key];
	}

	/**
	 * @inheritdoc
	 */
	public function read(&$vars, $baseDir, $initDir, $filename, $ttl)
	{
		$baseDirVersion = $this->getBaseDirVersion($baseDir);
		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir, false);

		if ($initDirVersion == '')
		{
			if ($this->useLock)
			{
				$initDirVersion = $this->get($this->getInitDirKey($baseDirVersion, $baseDir, $initDir) . '~');
				if ($initDirVersion == '')
				{
					$vars = false;
					return false;
				}
				else
				{
					$this->old = true;
				}
			}
			else
			{
				$vars = false;
				return false;
			}
		}

		$key = $this->getKeyPrefix($baseDirVersion, $initDirVersion) . '|' . $filename;

		if ($this->useLock)
		{
			$cachedData = $this->get($key);

			if (!is_array($cachedData))
			{
				$cachedData = $this->get($key . '|old');

				if (is_array($cachedData))
				{
					$this->old = true;
				}
			}

			if (!is_array($cachedData))
			{
				return false;
			}

			if (($cachedData['expire'] < time() || $this->old) && $this->lock($key, $ttl))
			{
				return false;
			}

			$vars = $cachedData['content'];
		}
		else
		{
			$vars = $this->get($key);
		}

		if (Cache::getShowCacheStat())
		{
			$this->read = strlen(serialize($vars));
			$this->path = $baseDir . $initDir . $filename;
		}

		return $vars !== false;
	}

	/**
	 * @inheritdoc
	 */
	public function write($vars, $baseDir, $initDir, $filename, $ttl)
	{
		$baseDirVersion = $this->getBaseDirVersion($baseDir);
		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir);

		$keyPrefix = $this->getKeyPrefix($baseDirVersion, $initDirVersion);
		$key = $keyPrefix . '|' . $filename;

		$exp = $this->ttlMultiplier * (int) $ttl;

		if ($this->useLock)
		{
			$this->set($key, $exp, ['expire' => time() + $ttl, 'content' => $vars]);
			$this->del($key . '|old');
			$this->unlock($key);
		}
		else
		{
			$this->set($key, $exp, $vars);
		}

		$initListKey = $keyPrefix . '|' . self::BX_INIT_DIR_LIST;
		$initPartition = $this->getPartition($filename);
		$initListKeyPartition = $initListKey . '|' . $initPartition;

		$this->addToSet($initListKeyPartition, $key);
		if (empty(static::$initDirPartitions[$initListKey][$initPartition]))
		{
			static::$initDirPartitions[$initListKey][$initPartition] = true;
			$this->addToSet($initListKey, $initPartition);
		}

		if ($this->fullClean)
		{
			$baseListKey = $this->sid . '|' . $baseDirVersion . '|' . self::BX_BASE_LIST;
			$baseListKeyPartition = $this->getPartition($initListKeyPartition);
			$this->addToSet($baseListKey . $baseListKeyPartition, $keyPrefix);
			$this->addToSet($baseListKey, $baseListKeyPartition);
		}

		if (Cache::getShowCacheStat())
		{
			$this->written = strlen(serialize($vars));
			$this->path = $baseDir . $initDir . $filename;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function clean($baseDir, $initDir = false, $filename = false)
	{
		if (!self::isAvailable())
		{
			return;
		}

		$baseDirVersion = $this->getBaseDirVersion($baseDir);
		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir, false);

		if ($initDirVersion == '' && $initDir != '')
		{
			return;
		}

		$keyPrefix = $this->getKeyPrefix($baseDirVersion, $initDirVersion);
		$initListKey = $keyPrefix . '|' . self::BX_INIT_DIR_LIST;

		if ($filename <> '')
		{
			$key = $keyPrefix . '|' . $filename;
			$this->delFromSet($initListKey . '|' . $this->getPartition($filename), $filename);

			if ($this->useLock && $cachedData = $this->get($key))
			{
				$this->set($key . '|old', $this->ttlOld, $cachedData);
			}

			$this->del($key);
			if ($this->useLock)
			{
				$this->unlock($key);
			}
		}
		elseif ($initDir != '')
		{
			$key = $this->getInitDirKey($baseDirVersion, $baseDir, $initDir);
			$this->del($key);

			if ($this->useLock)
			{
				$this->set($key . '~', $this->ttlOld, $initDirVersion);
				$cleanFrom = (new DateTime())->add('+' . $this->ttlOld . ' seconds');
			}
			else
			{
				$cleanFrom = (new DateTime());
			}

			$this->addCleanPath([
				'PREFIX' => $keyPrefix,
				'CLEAN_FROM' => $cleanFrom,
				'CLUSTER_GROUP' => static::$clusterGroup
			]);

			$this->set($this->sid . '|needClean', 3600, 'Y');
		}
		else
		{
			if ($this->fullClean)
			{
				$useLock = $this->useLock;
				$this->useLock = false;

				$baseDirVersion = $this->getBaseDirVersion($baseDir);
				$baseListKey = $this->sid . '|' . $baseDirVersion . '|' . self::BX_BASE_LIST;

				$partitionKeys = $this->getSet($baseListKey);
				foreach ($partitionKeys as $partition)
				{
					$baseListKeyPartition = $baseListKey . $partition;
					$paths = $this->getSet($baseListKeyPartition);
					foreach ($paths as $path)
					{
						$this->addCleanPath([
							'PREFIX' => $path,
							'CLEAN_FROM' =>  (new DateTime()),
							'CLUSTER_GROUP' => static::$clusterGroup
						]);
					}

					unset($paths);
				}

				$this->set($this->sid . '|needClean', 3600, 'Y');
				$this->del($baseListKey);
				$this->useLock = $useLock;
			}

			$baseDirKey = $this->getBaseDirKey($baseDir);
			$this->del($baseDirKey);
			unset(static::$baseDirVersion[$baseDirKey]);
		}
	}

	public function addCleanPath(array $data): void
	{
		CacheCleanPathTable::add($data);
	}

	public function delayedDelete(): void
	{
		$delta = 10;
		$deleted = 0;
		$etime = time() + 5;
		$needClean = $this->get($this->sid . '|needClean');
		if ($needClean !== 'Y')
		{
			$this->unlock($this->sid . '|cacheClean');
			return;
		}

		$count = (int) $this->get($this->sid . '|delCount');
		if ($count < 1)
		{
			$count = 1;
		}

		$paths = CacheCleanPathTable::query()
			->setSelect(['ID', 'PREFIX'])
			->where('CLEAN_FROM', '<=', new DateTime())
			->where('CLUSTER_GROUP', static::$clusterGroup)
			->setLimit($count + $delta)
			->exec();

		while ($path = $paths->fetch())
		{
			$finished = true;
			$partitionKeys = $this->getSet($path['PREFIX'] . '|' . static::BX_INIT_DIR_LIST);
			foreach ($partitionKeys as $partition)
			{
				if (time() > $etime)
				{
					$finished = false;
					break;
				}

				$delKey = $path['PREFIX'] . '|' . static::BX_INIT_DIR_LIST . '|' . $partition;
				$keys = $this->getSet($delKey);
				if (!empty($keys))
				{
					$this->deleteBySet($delKey, $path['PREFIX']);
				}
			}

			if ($finished)
			{
				CacheCleanPathTable::delete($path['ID']);
				$deleted++;
			}
			if (time() > $etime)
			{
				break;
			}
		}

		if ($deleted > $count)
		{
			$this->set($this->sid . '|delCount', 604800, $deleted);
		}
		elseif ($deleted < $count && $count > 1)
		{
			$this->set($this->sid . '|delCount', 604800, --$count);
		}

		if ($deleted == 0)
		{
			$this->set($this->sid . '|needClean', 3600, 'N');
		}

		$this->unlock($this->sid . '|cacheClean');
	}
}
