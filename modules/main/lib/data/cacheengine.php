<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Config;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Internal\CacheCleanPathTable;

abstract class CacheEngine implements CacheEngineInterface, CacheEngineStatInterface, LocalStorage\Storage\CacheEngineInterface
{
	const BX_BASE_LIST = 'BL:';
	const BX_INIT_DIR_LIST = 'IL:';

	protected static $engine = null;
	protected static array $locks = [];
	protected static bool $isConnected = false;
	protected static array $cleanQueue = [];
	protected static array $baseDirVersion = [];
	protected static array $initDirPartitions = [];
	protected string $sid = 'BX';
	protected bool $useLock = false;
	protected int $ttlMultiplier = 2;
	protected int $ttlOld = 60;
	protected bool $old = false;
	protected bool $fullClean = false;
	protected array $config;
	protected static int $clusterGroup = 0;

	/** Cache stat */
	private int $written = 0;
	private int $read = 0;
	private string $path = '';

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
		$this->config = $this->configure($options);
		$this->connect($this->config);

		static::$clusterGroup = (int) (defined('BX_CLUSTER_GROUP') ? BX_CLUSTER_GROUP : 0);
		if (self::$isConnected)
		{
			Application::getInstance()->addBackgroundJob(\Bitrix\Main\Data\Cache::class.'::addCleanPath', [], Application::JOB_PRIORITY_LOW);

			if ($this->lock($this->sid . '|cacheClean', 600))
			{
				Application::getInstance()->addBackgroundJob(\Bitrix\Main\Data\Cache::class . '::delayedDelete', [], Application::JOB_PRIORITY_LOW);
			}
		}
	}

	public function getConfig(): array
	{
		return $this->config;
	}

	protected function connect($config)
	{
		if (!self::$isConnected)
		{
			$connectionPool = Application::getInstance()->getConnectionPool();
			$connectionPool->setConnectionParameters($this->getConnectionName(), $config);

			/** @var RedisConnection|MemcacheConnection|MemcachedConnection $engineConnection */
			$engineConnection = $connectionPool->getConnection($this->getConnectionName());

			self::$engine = $engineConnection->getResource();
			self::$isConnected = $engineConnection->isConnected();
		}
	}

	protected function modifyConfigByEngine(array &$config, array $cacheConfig, array $options = []): void
	{
	}

	protected function modifyConfigByClusterEngine(array &$config, array $cacheConfig, array $options = []): void
	{
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
			if (is_array($cacheConfig['type']) && is_set($cacheConfig['type']['extension']))
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

		if (!isset($config['servers']) || !is_array($config['servers']))
		{
			$config['servers'] = [];
		}

		if (isset($cacheConfig[$type]) && is_array($cacheConfig[$type]) && !empty($cacheConfig[$type]['host']))
		{
			$config['servers'][] = [
				'host' => $cacheConfig[$type]['host'],
				'port' => (int) ($cacheConfig[$type]['port'] ?? 0)
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
			$this->useLock = (bool) $cacheConfig['use_lock'];
		}

		if (isset($cacheConfig['sid']) && ($cacheConfig['sid'] != ''))
		{
			$this->sid = $cacheConfig['sid'];
		}

		if (isset($options['sid']) && ($options['sid'] != ''))
		{
			$this->sid = $options['sid'];
		}

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
			$this->ttlMultiplier = (int) $cacheConfig['ttl_multiplier'];
			if ($this->ttlMultiplier < 1)
			{
				$this->ttlMultiplier = 1;
			}
		}

		if (isset($cacheConfig['full_clean']))
		{
			$this->fullClean = (bool) $cacheConfig['full_clean'];
		}

		if (isset($cacheConfig['ttlOld']) && (int) $cacheConfig['ttlOld'] > 0)
		{
			$this->ttlOld = (int) $cacheConfig['ttlOld'];
		}

		$this->modifyConfigByEngine($config, $cacheConfig);
		$this->modifyConfigByClusterEngine($config, $cacheConfig);

		$this->sid .= '|v1';
		$config['sid'] = $this->sid;

		return $config;
	}

	/**
	 * Returns number of bytes read from cache.
	 * @return integer
	 */
	public function getReadBytes()
	{
		return $this->read;
	}

	/**
	 * Returns number of bytes written to cache.
	 * @return integer
	 */
	public function getWrittenBytes()
	{
		return $this->written;
	}

	/**
	 * Returns physical file path after read or write operation.
	 * @return string
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
	 * Returns true if cache can be read or written.
	 * @return bool
	 */
	public function isAvailable()
	{
		return self::$isConnected;
	}

	/**
	 * Returns true if cache has been expired.
	 * Stub function always returns true.
	 * @param string $path Absolute physical path.
	 * @return boolean
	 */
	public function isCacheExpired($path)
	{
		return false;
	}

	protected function getPartition($key): string
	{
		return substr(sha1($key), 0, 2);
	}

	protected function getInitDirKey($baseDirVersion, $baseDir, $initDir): string
	{
		return $this->sid . '|BDV:' . $baseDirVersion  . '|IDH:' . sha1($baseDir . '|' . $initDir);
	}

	protected function getBaseDirKey($baseDir): string
	{
		return $this->sid . '|BDV:' . sha1($baseDir);
	}

	/**
	 * Return InitDirVersion
	 *
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param bool|string $initDir Directory within base.
	 * @return string
	 */
	protected function getInitDirVersion($baseDir, $initDir = false, bool $create = true ): string
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
	 * Reads cache from the memcache. Returns true if key value exists, not expired, and successfully read.
	 *
	 * @param mixed &$vars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return boolean
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

		$dir = sha1($baseDirVersion . '|' . $initDirVersion);
		$key = $this->sid . '|' . $dir . '|' . $filename;

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
	 * Puts cache into the memcache.
	 *
	 * @param mixed $vars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return void
	 */
	public function write($vars, $baseDir, $initDir, $filename, $ttl)
	{
		$baseDirVersion = $this->getBaseDirVersion($baseDir);
		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir);

		$dir = sha1($baseDirVersion . '|' . $initDirVersion);
		$keyPrefix = $this->sid . '|' . $dir;
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
	 * Cleans (removes) cache directory or file.
	 *
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 *
	 * @return void
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

		$dir = sha1($baseDirVersion . '|' . $initDirVersion);
		$keyPrefix = $this->sid . '|' . $dir;
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
				$cleanFrom = (new \Bitrix\Main\Type\DateTime())->add('+' . $this->ttlOld . ' seconds');
			}
			else
			{
				$cleanFrom = (new \Bitrix\Main\Type\DateTime());
			}

			static::$cleanQueue[$keyPrefix] = [
				'PREFIX' => $keyPrefix,
				'CLEAN_FROM' => $cleanFrom,
				'CLUSTER_GROUP' => static::$clusterGroup
			];
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
						static::$cleanQueue[$keyPrefix] = [
							'PREFIX' => $path,
							'CLEAN_FROM' =>  (new \Bitrix\Main\Type\DateTime()),
							'CLUSTER_GROUP' => static::$clusterGroup
						];
					}

					unset($paths);
				}

				$this->del($baseListKey);
				$this->useLock = $useLock;
			}

			$baseDirKey = $this->getBaseDirKey($baseDir);
			$this->del($baseDirKey);
			unset(static::$baseDirVersion[$baseDirKey]);
		}

		$this->set($this->sid . '|needClean', '3600', 'Y');
	}

	public function addCleanPath(): void
	{
		if (empty(static::$cleanQueue))
		{
			return;
		}

		foreach (array_chunk(static::$cleanQueue, 100) as $chunk)
		{
			CacheCleanPathTable::addMulti($chunk, true);
		}

		static::$cleanQueue = [];
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
			->where('CLEAN_FROM', '<=', new \Bitrix\Main\Type\DateTime())
			->where('CLUSTER_GROUP', static::$clusterGroup)
			->setLimit($count + $delta)
			->exec();

		while($path = $paths->fetch())
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
					$this->del($delKey);
				}
			}

			if ($finished)
			{
				CacheCleanPathTable::delete($path['ID']);
				$this->del($partitionKeys);
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
			$this->set($this->sid . '|needClean', '3600', 'N');
		}

		$this->unlock($this->sid . '|cacheClean');
	}
}