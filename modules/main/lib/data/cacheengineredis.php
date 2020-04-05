<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Config;

class CacheEngineRedis implements ICacheEngine
{
	protected static $redis = null;
	private static $isConnected = false;

	private static $baseDirVersion = [];
	protected $sid = "BX";

	protected $useLock = false;
	protected $ttlMultiplier = 2;
	protected static $locks = [];
	protected $old = false;

	/**
	 * CacheEngineRedis constructor.
	 * @param array $options Cache options.
	 */
	function __construct($options = [])
	{
		$config = Config\Configuration::getValue("cache");

		if (!empty($options))
		{
			if (isset($options['HOST']))
			{
				$config['redis']['host'] = $options['HOST'];
			}

			if (isset($options['PORT']))
			{
				$config['redis']['port'] = $options['PORT'];
			}

			if (isset($options['SID']))
			{
				$config['sid'] = $options['SID'];
			}
		}

		if (self::$redis == null)
		{
			self::$redis = new \Redis();
			$v = (isset($config['redis'])) ? $config['redis'] : null;

			if ($v != null && isset($v['host']) && $v['host'] != '')
			{
				if ($v != null && isset($v['port']))
				{
					$port = intval($v['port']);
				}
				else
				{
					$port = 6379;
				}

				// TODO add settings pconnect or connect
				if (self::$redis->pconnect($v['host'], $port))
				{
					self::$redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
					self::$isConnected = true;
				}
			}
		}

		if ($config && is_array($config))
		{
			if (isset($config["use_lock"]))
			{
				$this->useLock = (bool)$config["use_lock"];
			}

			if (isset($config["sid"]) && ($config["sid"] != ""))
			{
				$this->sid = $config["sid"];
			}

			if (isset($config["ttl_multiplier"]) && $this->useLock)
			{
				$this->ttlMultiplier = (integer)$config["ttl_multiplier"];
			}
		}

		if (!empty($options) && isset($options['actual_data']))
		{
			$this->useLock = !((bool) $options['actual_data']);
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
	function isAvailable()
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
				self::$redis->delete($key);
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
	function clean($baseDir, $initDir = false, $filename = false)
	{
		if(!self::isAvailable())
		{
			return;
		}

		$key = false;
		if (strlen($filename))
		{
			$this->getBaseDirVersion($baseDir, true);
			if (self::$baseDirVersion[$baseDir] === false)
			{
				return;
			}

			$initDirVersion = $this->getInitDirVersion($baseDir, $initDir, true);
			$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
			$cachedData = self::$redis->get($key);

			if (is_array($cachedData))
			{
				self::$redis->setex($key.'|old', 1, $cachedData);
			}
			self::$redis->del($key);
		}
		else
		{
			if (strlen($initDir))
			{
				$this->getBaseDirVersion($baseDir, true);

				if (self::$baseDirVersion[$baseDir] === false)
				{
					return;
				}

				$initDirKey = self::$baseDirVersion[$baseDir]."|".$initDir;
				$initDirVersion = self::$redis->get($initDirKey);
				if ($initDirVersion === false)
				{
					self::$redis->setex($initDirKey.'|old', 1,$initDirVersion);
				}
				self::$redis->del($initDirKey);
			}
			else
			{
				$this->getBaseDirVersion($baseDir, true);
				if (isset(self::$baseDirVersion[$baseDir]))
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
	function read(&$allVars, $baseDir, $initDir, $filename, $ttl)
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
	function write($allVars, $baseDir, $initDir, $filename, $ttl)
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
	function isCacheExpired($path)
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
	function getInitDirVersion($baseDir, $initDir = false, $skipOld = false)
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
	function getBaseDirVersion($baseDir, $skipOld = false)
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