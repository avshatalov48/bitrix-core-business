<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Application;
use Bitrix\Main\Config;
use Bitrix\Main\Data\LocalStorage;

class CacheEngineRedis implements ICacheEngine, LocalStorage\Storage\CacheEngineInterface
{
	public const SESSION_REDIS_CONNECTION = 'cache.redis';

	/** @var \Redis $redis */
	protected static $redis = null;
	protected static $locks = [];
	protected static $isConnected = false;

	private static $baseDirVersion = [];
	private static $host = '127.0.0.1';
	private static $port = 6379;

	protected $sid = "BX";
	protected $useLock = false;
	protected $ttlMultiplier = 2;
	protected $old = false;
	protected $serializer = \Redis::SERIALIZER_IGBINARY;
	protected $persistent = true;

	/**
	 * CacheEngineRedis constructor.
	 * @param array $options Cache options.
	 */
	public function __construct($options = [])
	{
		$this->configure();

		if (!empty($options))
		{
			if (isset($options['HOST']))
			{
				self::$host = $options['HOST'];
			}

			if (isset($options['PORT']))
			{
				self::$port = $options['PORT'];
			}
		}

		if (self::$redis == null)
		{
			$connectionPool = Application::getInstance()->getConnectionPool();
			$connectionPool->setConnectionParameters(self::SESSION_REDIS_CONNECTION, [
				'className' => RedisConnection::class,
				'host' => self::$host,
				'port' => self::$port,
				'serializer' => $this->serializer,
				'persistent' => $this->persistent,
			]);

			/** @var RedisConnection $redisConnection */
			$redisConnection = $connectionPool->getConnection(self::SESSION_REDIS_CONNECTION);
			self::$redis = $redisConnection->getResource();
			self::$isConnected = $redisConnection->isConnected();
		}
	}

	protected function configure()
	{
		$config = Config\Configuration::getValue("cache");

		if (!$config || !is_array($config))
		{
			return false;
		}

		$v = $config["redis"] ?? null;
		if (!empty($v["host"]))
		{
			self::$host = $v["host"];
			if (isset($v["port"]))
			{
				self::$port = (int)$v["port"];
			}
		}

		if (isset($config["use_lock"]))
		{
			$this->useLock = (bool) $config["use_lock"];
		}

		if (isset($config["sid"]) && ($config["sid"] != ""))
		{
			$this->sid = $config["sid"];
		}

		if (isset($config["serializer"]))
		{
			if ($config["serializer"] == 0)
			{
				$this->serializer = \Redis::SERIALIZER_NONE;
			}
			elseif ($config["serializer"] == 1)
			{
				$this->serializer = \Redis::SERIALIZER_PHP;
			}
			elseif ($config["serializer"] == 2)
			{
				$this->serializer = \Redis::SERIALIZER_IGBINARY;
			}
		}

		if (isset($config["persistent"]) && $config["persistent"] == 0)
		{
			$this->persistent = false;
		}

		if (isset($config["ttl_multiplier"]) && $this->useLock)
		{
			$this->ttlMultiplier = (int) $config["ttl_multiplier"];
		}

		if (isset($config["actual_data"]))
		{
			$this->useLock = !((bool) $config["actual_data"]);
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
	 * Closes opened connection.
	 *
	 * @return void
	 */
	function close()
	{
		if (self::$redis != null)
		{
			self::$redis->close();
			self::$redis = null;
		}
	}

	/**
	 * Returns true if cache can be read or written.
	 *
	 * @return bool
	 */
	public function isAvailable()
	{
		return self::$isConnected;
	}

	/**
	 * Tries to put non blocking exclusive lock on the cache entry.
	 * Returns true on success.
	 *
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $key Calculated cache key.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return boolean
	 */
	protected function lock($baseDir, $initDir, $key, $ttl)
	{
		if (
			isset(self::$locks[$baseDir])
			&& isset(self::$locks[$baseDir][$initDir])
			&& isset(self::$locks[$baseDir][$initDir][$key])
		)
		{
			return true;
		}
		elseif (self::$redis->setnx($key, 1))
		{
			self::$redis->expire($key, intval($ttl));
			self::$locks[$baseDir][$initDir][$key] = true;
			return true;
		}

		return false;
	}

	/**
	 * Releases the lock obtained by lock method.
	 *
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $key Calculated cache key.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return void
	 */
	protected function unlock($baseDir, $initDir = false, $key = false, $ttl = 0)
	{
		$ttl = (int) $ttl;
		if ($key !== false)
		{
			if ($ttl > 0)
			{
				self::$redis->setex($key, $ttl, 1);
			}
			else
			{
				self::$redis->del($key);
			}

			unset(self::$locks[$baseDir][$initDir][$key]);
		}
		elseif ($initDir !== false)
		{
			if (isset(self::$locks[$baseDir][$initDir]))
			{
				foreach (self::$locks[$baseDir][$initDir] as $subKey)
				{
					$this->unlock($baseDir, $initDir, $subKey, $ttl);
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
					$this->unlock($baseDir, $subInitDir, false, $ttl);
				}
			}
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
		if(!self::isAvailable())
		{
			return;
		}

		$key = false;
		if($filename <> '')
		{
			$this->getBaseDirVersion($baseDir, true);
			if(self::$baseDirVersion[$baseDir] === false)
			{
				return;
			}

			$initDirVersion = $this->getInitDirVersion($baseDir, $initDir, true);
			$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
			$cachedData = self::$redis->get($key);

			if(is_array($cachedData))
			{
				self::$redis->setex($key.'|old', 1, $cachedData);
			}
			self::$redis->del($key);
		}
		else
		{
			if($initDir <> '')
			{
				$this->getBaseDirVersion($baseDir, true);

				if(self::$baseDirVersion[$baseDir] === false)
				{
					return;
				}

				$initDirKey = self::$baseDirVersion[$baseDir]."|".$initDir;
				$initDirVersion = self::$redis->get($initDirKey);
				if($initDirVersion === false)
				{
					self::$redis->setex($initDirKey.'|old', 1, $initDirVersion);
				}
				self::$redis->del($initDirKey);
			}
			else
			{
				$this->getBaseDirVersion($baseDir, true);
				if(isset(self::$baseDirVersion[$baseDir]))
				{
					self::$redis->setex($this->sid.$baseDir.'|old', 1, self::$baseDirVersion[$baseDir]);
					unset(self::$baseDirVersion[$baseDir]);
				}

				self::$redis->del($this->sid.$baseDir);
			}
		}

		$this->unlock($baseDir, $initDir, $key."~");
	}

	/**
	 * Reads cache from the memcache. Returns true if key value exists, not expired, and successfully read.
	 *
	 * @param mixed &$allVars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return boolean
	 */
	public function read(&$allVars, $baseDir, $initDir, $filename, $ttl)
	{
		$this->getBaseDirVersion($baseDir);

		if (self::$baseDirVersion[$baseDir] === false)
		{
			return false;
		}

		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir);
		$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
		if ($this->useLock)
		{
			$cachedData = self::$redis->get($key);

			if (!is_array($cachedData))
			{
				$cachedData = self::$redis->get($key.'|old');
				if (is_array($cachedData))
				{
					$this->old = true;
				}
			}

			if (!is_array($cachedData))
			{
				return false;
			}

			if ($this->lock($baseDir, $initDir, $key."~", $ttl))
			{
				if ($this->old || $cachedData["datecreate"] < (time() - $ttl))
				{
					return false;
				}

			}

			$allVars = $cachedData["content"];
		}
		else
		{
			$allVars = self::$redis->get($key);
		}

		if ($allVars === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Puts cache into the memcache.
	 *
	 * @param mixed $allVars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $ttl Expiration period in seconds.
	 *
	 * @return void
	 */
	public function write($allVars, $baseDir, $initDir, $filename, $ttl)
	{
		if (!isset(self::$baseDirVersion[$baseDir]))
		{
			self::$baseDirVersion[$baseDir] = self::$redis->get($this->sid.$baseDir);
		}

		if (self::$baseDirVersion[$baseDir] === false)
		{
			self::$baseDirVersion[$baseDir] = $this->sid.md5(mt_rand());
			self::$redis->set($this->sid.$baseDir, self::$baseDirVersion[$baseDir]);
		}

		if ($initDir !== false)
		{
			$initDirVersion = self::$redis->get(self::$baseDirVersion[$baseDir]."|".$initDir);
			if ($initDirVersion === false)
			{
				$initDirVersion = $this->sid.md5(mt_rand());
				self::$redis->set(self::$baseDirVersion[$baseDir]."|".$initDir, $initDirVersion);
			}
		}
		else
		{
			$initDirVersion = "";
		}

		$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
		$exp = $this->ttlMultiplier > 0 ? intval($ttl) * $this->ttlMultiplier : 0;

		if ($this->useLock)
		{
			self::$redis->setex($key, $exp, ['datecreate' => time(), 'content' => $allVars]);
			self::$redis->del($key.'|old');
			$this->unlock($baseDir, $initDir, $key."~", $ttl);
		}
		else
		{
			self::$redis->setex($key, $exp, $allVars);
		}
	}

	/**
	 * Returns true if cache has been expired.
	 * Stub function always returns true.
	 *
	 * @param string $path Absolute physical path.
	 *
	 * @return boolean
	 */
	public function isCacheExpired($path)
	{
		return false;
	}

	/**
	 * Return InitDirVersion
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param bool|string $initDir Directory within base.
	 * @param bool $skipOld Return cleaned value.
	 * @return array|bool|string
	 */
	protected function getInitDirVersion($baseDir, $initDir = false, $skipOld = false)
	{
		if ($initDir !== false)
		{
			$old = false;
			$initDirKey = self::$baseDirVersion[$baseDir]."|".$initDir;
			$initDirVersion = self::$redis->get($initDirKey);

			if (($initDirVersion === false || $initDirVersion === '') && !$skipOld)
			{
				$initDirVersion = self::$redis->get($initDirKey.'|old');
				$old = true;
			}

			if ($initDirVersion === false || $initDirVersion === '')
			{
				return false;
			}

			if ($old)
			{
				$this->old = true;
			}

			return $initDirVersion;
		}
		else
		{
			return '';
		}
	}

	/**
	 * Return BaseDirVersion
	 * @param bool|string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param bool $skipOld Return cleaned value.
	 *
	 * @return void
	 */
	protected function getBaseDirVersion($baseDir, $skipOld = false)
	{
		$baseDirKey = $this->sid.$baseDir;
		if (!isset(self::$baseDirVersion[$baseDir]))
		{
			self::$baseDirVersion[$baseDir] = self::$redis->get($baseDirKey);
		}

		if (self::$baseDirVersion[$baseDir] === false && !$skipOld)
		{
			self::$baseDirVersion[$baseDir] = self::$redis->get($baseDirKey.'|old');
			if (isset(self::$baseDirVersion[$baseDir]))
			{
				$this->old = true;
			}
		}
	}
}