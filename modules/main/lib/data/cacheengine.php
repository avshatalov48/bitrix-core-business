<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Config;
use Bitrix\Main\Application;

abstract class CacheEngine implements CacheEngineInterface, CacheEngineStatInterface, LocalStorage\Storage\CacheEngineInterface
{
	const BX_BASE_LIST = '|bx_base_list|';
	const BX_DIR_LIST = '|bx_dir_list|';

	protected static \Redis|\Memcache|null|\Memcached $engine = null;
	protected static array $locks = [];
	protected static bool $isConnected = false;
	protected static array $baseDirVersion = [];
	protected string $sid = 'BX';
	protected bool $useLock = false;
	protected int $ttlMultiplier = 2;
	protected int $ttlOld = 60;
	protected bool $old = false;
	protected bool $fullClean = false;

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
		$hash = sha1(serialize($options));

		static $config = [];
		if (self::$engine == null || empty($config[$hash]))
		{
				$config[$hash] = $this->configure($options);
		}

		$this->connect($config[$hash]);
	}

	protected function connect($config)
	{
		$connectionPool = Application::getInstance()->getConnectionPool();
		$connectionPool->setConnectionParameters($this->getConnectionName(), $config);

		/** @var RedisConnection|MemcacheConnection|MemcachedConnection $engineConnection */
		$engineConnection = $connectionPool->getConnection($this->getConnectionName());
		self::$engine = $engineConnection->getResource();
		self::$isConnected = $engineConnection->isConnected();
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

		// Only redis
		if (isset($cacheConfig['serializer']))
		{
			$config['serializer'] = (int) $cacheConfig['serializer'];
		}

		if (isset($cacheConfig['connectionTimeout']))
		{
			$config['connectionTimeout'] = (int) $cacheConfig['connectionTimeout'];
		}

		$config['persistent'] = true;
		if (isset($cacheConfig['persistent']) && $cacheConfig['persistent'] == 0)
		{
			$config['persistent'] = false;
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

		$this->sid .= '|v1|';

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
		return '|' . substr(sha1($key), 0, 4) . '|';
	}

	/**
	 * Return InitDirVersion
	 *
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param bool|string $initDir Directory within base.
	 * @return string
	 */
	protected function getInitDirVersion($baseDir, $initDir = false): string
	{
		return sha1($baseDir . '|' . $initDir);
	}

	/**
	 * Return BaseDirVersion
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 *
	 * @return string
	 */
	protected function getBaseDirVersion($baseDir): string
	{
		$baseDirHash = sha1($baseDir);
		$key = $this->sid . '|base_dir|' . $baseDirHash;

		if (!isset(static::$baseDirVersion[$key]))
		{
			static::$baseDirVersion[$key] = $this->get($key);
		}

		if (static::$baseDirVersion[$key] === false || (static::$baseDirVersion[$key] == ''))
		{
			static::$baseDirVersion[$key] = sha1($baseDirHash . '|' . mt_rand() . '|' . microtime());
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
		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir);

		$dir = sha1($baseDirVersion . '|' . $initDirVersion);
		$key = $this->sid. '|' . $dir . '|' . $filename;

		$initListKey = $this->sid . '|' . $dir . self::BX_DIR_LIST;
		$initPartition = $this->getPartition($filename);
		$initListKeyPartition = $initListKey . $initPartition;

		if ($this->useLock)
		{
			$cachedData = $this->get($key);
			if (
				($cachedData !== null && $cachedData !== false)
				&& !$this->checkInSet($initListKey, $initPartition)
				&& !$this->checkInSet($initListKeyPartition, $filename)
			)
			{
				$this->del($key);
				return false;
			}

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
			if ($vars !== null && $vars !== false)
			{
				if (
					!$this->checkInSet($initListKey, $initPartition)
					|| !$this->checkInSet($initListKeyPartition, $filename)
				)
				{
					$this->del($key);
					return false;
				}
			}
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
		$key = $this->sid. '|' . $dir . '|' . $filename;
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

		$initListKey = $this->sid . '|' . $dir . self::BX_DIR_LIST;
		$initPartition = $this->getPartition($filename);
		$initListKeyPartition = $initListKey . $initPartition;

		$this->addToSet($initListKeyPartition, $filename);
		$this->addToSet($initListKey, $initPartition);

		if ($this->fullClean)
		{
			$baseListKey = $this->sid . '|' . $baseDirVersion . self::BX_BASE_LIST;
			$baseListKeyPartition = $this->getPartition($initListKeyPartition);
			$this->addToSet($baseListKey . $baseListKeyPartition, $initListKeyPartition);
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
		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir);

		$dir = sha1($baseDirVersion . '|' . $initDirVersion);
		$initListKey = $this->sid . '|' .$dir . self::BX_DIR_LIST;

		if ($this->fullClean)
		{
			$baseListKey = $this->sid . '|' .$baseDirVersion . self::BX_BASE_LIST;
		}

		if ($filename <> '')
		{
			$key = $this->sid . '|' .$dir . '|' . $filename;
			$this->delFromSet($initListKey . $this->getPartition($filename), $filename);

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
			$keyPrefix = $this->sid . '|' .$dir . '|';
			$partitionKeys = $this->getSet($initListKey);

			// A temporary optimization solution
			if ($this->getConnectionName() != 'cache.redis')
			{
				$this->delFromSet($initListKey, $partitionKeys);
			}
			$this->del($initListKey);

			foreach ($partitionKeys as $partition)
			{
				$delKey = $initListKey . $partition;
				$this->deleteBySet($delKey, $keyPrefix);
				if ($this->fullClean)
				{
					$this->delFromSet($baseListKey . $this->getPartition($delKey), $delKey);
				}
			}
		}
		else
		{
			$baseDirKey = $this->sid . '|base_dir|' . sha1 ($baseDir);
			$this->del($baseDirKey);
			unset(static::$baseDirVersion[$baseDirKey]);

			if ($this->fullClean)
			{
				$useLock = $this->useLock;
				$this->useLock = false;

				$keyPrefix = $this->sid . '|' .$dir . '|';
				$partitionKeys = $this->getSet($baseListKey);

				foreach ($partitionKeys as $partition)
				{
					$baseListKeyPartition = $baseListKey . $partition;
					$keys = $this->getSet($baseListKeyPartition);

					foreach ($keys as $initKey)
					{
						$this->deleteBySet($initKey, $keyPrefix);
					}

					$this->del($baseListKeyPartition);
					unset($keys);
				}

				$this->del($baseListKey);
				$this->useLock = $useLock;
			}
		}
	}
}