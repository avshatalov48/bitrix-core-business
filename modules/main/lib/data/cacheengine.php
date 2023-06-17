<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Config;
use Bitrix\Main\Application;
use Bitrix\Main\Data\LocalStorage;

abstract class CacheEngine implements CacheEngineInterface, LocalStorage\Storage\CacheEngineInterface
{
	const BX_BASE_LIST = '|bx_base_list|';
	const BX_DIR_LIST = '|bx_dir_list|';

	/** @var \Redis|\Memcache|\Memcached|null self::$engine */
	protected static $engine = null;
	protected static array $locks = [];
	protected static bool $isConnected = false;
	protected static array $baseDirVersion = [];

	protected string $sid = 'BX';
	protected bool $useLock = false;
	protected int $ttlMultiplier = 1;
	protected int $ttlOld = 1;
	protected bool $old = false;

	abstract public function getConnectionName(): string;
	abstract public static function getConnectionClass();

	abstract public function set($key, $ttl, $value);
	abstract public function get($key);
	abstract public function del($key);

	abstract public function setNotExists($key, $ttl , $value);
	abstract public function addToSet($key, $value);
	abstract public function getSet($key) : array;
	abstract public function delFromSet($key, $member);

	/**
	 * CacheEngine constructor.
	 * @param array $options Cache options.
	 */
	public function __construct(array $options = [])
	{
		static $config = [];
		if (self::$engine == null)
		{
			if (empty($config))
			{
				$config = $this->configure($options);
			}
		}

		$this->connect($config);
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

	protected function configure($options = []) : array
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
				'port' => (int) $cacheConfig[$type]['port']
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

		$config['persistent'] = true;
		if (isset($cacheConfig['persistent']) && $cacheConfig['persistent'] == 0)
		{
			$config['persistent'] = false;
		}

		if (isset($cacheConfig['ttl_multiplier']) && $this->useLock)
		{
			$this->ttlMultiplier = (int) $cacheConfig['ttl_multiplier'];
			if ($this->ttlMultiplier < 0)
			{
				$this->ttlMultiplier = 0;
			}
		}

		if (isset($cacheConfig['actual_data']))
		{
			$this->useLock = !$cacheConfig['actual_data'];
		}

		if ($this->useLock)
		{
			$this->sid .= 2;
		}
		else
		{
			$this->sid .= 3;
			$this->ttlMultiplier = 1;
		}

		return $config;
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
	protected function lock(string $key = '', int $ttl = 0) : bool
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
	 *
	 * @param string $key Calculated cache key.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return void
	 */
	protected function unlock(string $key = '', int $ttl = 0)
	{
		if ($key != '')
		{
			$key .= '~';

			if ($ttl > 0)
			{
				$this->set($key, $ttl, 1);
			}
			else
			{
				$this->del($key);
			}

			unset(self::$locks[$key]);
		}
	}

	/**
	 * Closes opened connection.
	 * @return void
	 */
	function close() : void
	{
		if (self::$engine != null)
		{
			self::$engine->close();
			self::$engine = null;
		}
	}

	/**
	 * Return cache sid
	 * @return string
	 */
	public function getSid() : string
	{
		return $this->sid;
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

	protected function getInitDirKey($baseDir, $initDir = false) : string
	{
		return $this->getBaseDirVersion($baseDir) . '|init_dir|' . sha1($initDir);
	}

	protected function getBaseDirKey($baseDir) : string
	{
		return  $this->sid . '|base_dir|' . sha1 ($baseDir);
	}

	protected function getBaseDirKeyHash($hash) : string
	{
		return  $this->sid . '|base_dir|' . $hash;
	}

	/**
	 * Return InitDirVersion
	 * @param bool|string $initDir Directory within base.
	 * @return array|bool|string
	 */
	protected function getInitDirVersion($baseDir, $initDir = false) : string
	{
		$key = $this->getInitDirKey($baseDir, $initDir);
		$initDirVersion = $this->get($key);

		if ($initDirVersion === false)
		{
			$initDirVersion = $this->set($key,0, sha1(mt_rand() . '|' . microtime()));
		}

		return $initDirVersion;
	}

	/**
	 * Return BaseDirVersion
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 *
	 * @return string
	 */
	protected function getBaseDirVersion($baseDir) : string
	{
		$baseDirHash = sha1($baseDir);
		$key = $this->getBaseDirKeyHash($baseDirHash);

		if (!isset(self::$baseDirVersion[$key]))
		{
			self::$baseDirVersion[$key] = $this->get($key);
		}

		if (self::$baseDirVersion[$key] === false)
		{
			self::$baseDirVersion[$key] = $this->sid . '|' . $baseDirHash . '|' . sha1(mt_rand() . '|' . microtime());
			$this->set($key, 0, self::$baseDirVersion[$key]);
		}

		return self::$baseDirVersion[$key];
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
		$key = $this->getBaseDirVersion($baseDir) . '|' . $this->getInitDirVersion($baseDir, $initDir) . '|' .$filename;

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

			if ($this->lock($key, $ttl))
			{
				if ($this->old || $cachedData['datecreate'] < (time() - $ttl))
				{
					return false;
				}
			}

			$vars = $cachedData['content'];
		}
		else
		{
			$vars = $this->get($key);
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
		$dir = $baseDirVersion . '|' . $this->getInitDirVersion($baseDir, $initDir);

		$baseDirKey = $baseDirVersion . self::BX_BASE_LIST;
		$initDirKey = $dir . self::BX_DIR_LIST;

		$key = $dir . '|' . $filename;
		$exp = $this->ttlMultiplier * (int) $ttl;

		if ($this->useLock)
		{
			$this->set($key, $exp, ['datecreate' => time(), 'content' => $vars]);
			$this->del($key . '|old');
			$this->unlock($key, $ttl);
		}
		else
		{
			$this->set($key, $exp, $vars);
		}

		$this->addToSet($initDirKey, $key);
		$this->addToSet($baseDirKey, $initDirKey);
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
		$dir = $baseDirVersion . '|' . $this->getInitDirVersion($baseDir, $initDir);

		$initListKey = $dir . self::BX_DIR_LIST;
		$baseListKey = $baseDirVersion . self::BX_BASE_LIST;

		if ($filename <> '')
		{
			$key = $dir . '|' . $filename;
			$this->delFromSet($initListKey, $key);

			if ($cachedData = $this->get($key))
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
			$initDirKey = $this->getInitDirKey($baseDir, $initDir);
			$this->del($initDirKey);

			$keys = $this->getSet($initListKey);

			$this->delFromSet($baseListKey, $initListKey);
			$this->del($initListKey);
			$this->del($keys);

			unset($keys);
		}
		else
		{
			$this->del($this->getBaseDirKey($baseDir));
			$this->del($baseDirVersion);

			$keys = $this->getSet($baseListKey);

			foreach ($keys as $initKey)
			{
				$setKey = $this->getSet($initKey);
				$this->del($setKey);
				$this->del($initKey);
			}

			unset($keys, $setKey);

			$this->del($baseListKey);
		}
	}
}