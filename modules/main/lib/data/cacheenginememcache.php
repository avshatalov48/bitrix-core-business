<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Config;

class CacheEngineMemcache implements ICacheEngine, ICacheEngineStat
{
	protected static $memcache = null;
	private static $isConnected = false;

	private static $baseDirVersion = array();
	protected $sid = "BX";
	//cache stats
	private $written = false;
	private $read = false;
	// unfortunately is not available for memcache...

	protected $useLock = true;
	protected $ttlMultiplier = 2;
	protected static $locks = array();
	protected $old = false;

	/**
	 * Engine constructor.
	 *
	 */
	function __construct()
	{
		$cacheConfig = Config\Configuration::getValue("cache");

		if (self::$memcache == null)
		{
			self::$memcache = new \Memcache;

			$v = (isset($cacheConfig["memcache"]))? $cacheConfig["memcache"]: null;

			if ($v != null && isset($v["host"]) && $v["host"] != "")
			{
				if ($v != null && isset($v["port"]))
					$port = intval($v["port"]);
				else
					$port = 11211;

				if (self::$memcache->pconnect($v["host"], $port))
				{
					self::$isConnected = true;
				}
			}
		}

		if ($cacheConfig && is_array($cacheConfig))
		{
			if (isset($cacheConfig["use_lock"]))
			{
				$this->useLock = (bool)$cacheConfig["use_lock"];
			}

			if (isset($cacheConfig["sid"]) && ($cacheConfig["sid"] != ""))
			{
				$this->sid = $cacheConfig["sid"];
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

	/**
	 * Closes opened connection.
	 *
	 * @return void
	 */
	function close()
	{
		if (self::$memcache != null)
		{
			self::$memcache->close();
			self::$memcache = null;
		}
	}

	/**
	 * Returns number of bytes read from memcache or false if there was no read operation.
	 * Stub function always returns false.
	 *
	 * @return integer|false
	 */
	public function getReadBytes()
	{
		return $this->read;
	}

	/**
	 * Returns number of bytes written to memcache or false if there was no write operation.
	 * Stub function always returns false.
	 *
	 * @return integer|false
	 */
	public function getWrittenBytes()
	{
		return $this->written;
	}

	/**
	 * Returns physical file path after read or write operation.
	 * Stub function always returns '' (empty string).
	 *
	 * @return string
	 */
	public function getCachePath()
	{
		return "";
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
	 * @param integer $TTL Expiration period in seconds.
	 *
	 * @return boolean
	 */
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
		elseif (self::$memcache->add($key, 1, 0, intval($TTL)))
		{
			self::$locks[$baseDir][$initDir][$key] = true;
			return true;
		}
		elseif (!self::$memcache->get($key))
		{
			self::$locks[$baseDir][$initDir][$key] = true;
			self::$memcache->replace($key, 1, 0, $TTL);
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
	 * @param integer $TTL Expiration period in seconds.
	 *
	 * @return void
	 */
	protected function unlock($baseDir, $initDir = false, $key = false, $TTL = 0)
	{
		if ($key !== false)
		{
			if ($TTL > 0)
			{
				self::$memcache->set($key, 1, 0, time() + intval($TTL));
			}
			else
			{
				self::$memcache->replace($key, "", 0, 1);
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
		$key = false;
		if (is_object(self::$memcache))
		{
			if (strlen($filename))
			{
				$this->getBaseDirVersion($baseDir, true);
				if (self::$baseDirVersion[$baseDir] === false || self::$baseDirVersion[$baseDir] === '')
				{
					return;
				}

				$initDirVersion = $this->getInitDirVersion($baseDir, $initDir, true);

				$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
				$cachedData = self::$memcache->get($key);
				if (is_array($cachedData))
				{
					self::$memcache->set($key . '|old', $cachedData, 0, 1);
				}
				self::$memcache->replace($key, "", 0, 1);
			}
			else
			{
				if (strlen($initDir))
				{
					$this->getBaseDirVersion($baseDir, true);

					if (self::$baseDirVersion[$baseDir] === false || self::$baseDirVersion[$baseDir] === '')
						return;

					$initDirKey = self::$baseDirVersion[$baseDir]."|".$initDir;
					$initDirVersion = self::$memcache->get($initDirKey);
					if ($initDirVersion === false || $initDirVersion === '')
					{
						self::$memcache->set($initDirKey.'|old', $initDirVersion, 0, 1);
					}
					self::$memcache->replace($initDirKey, "", 0, 1);
				}
				else
				{
					$this->getBaseDirVersion($baseDir, true);
					if (isset(self::$baseDirVersion[$baseDir]))
					{
						self::$memcache->set($this->sid.$baseDir.'|old', self::$baseDirVersion[$baseDir], 0, 1);
						unset(self::$baseDirVersion[$baseDir]);
					}

					self::$memcache->replace($this->sid.$baseDir, "", 0, 1);
				}
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
	 * @param integer $TTL Expiration period in seconds.
	 *
	 * @return boolean
	 */
	function read(&$allVars, $baseDir, $initDir, $filename, $TTL)
	{
		$this->getBaseDirVersion($baseDir);

		if (self::$baseDirVersion[$baseDir] === false || self::$baseDirVersion[$baseDir] === '')
			return false;

		$initDirVersion = $this->getInitDirVersion($baseDir, $initDir);

		$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
		if ($this->useLock)
		{
			$cachedData = self::$memcache->get($key);
			if (!is_array($cachedData))
			{
				$cachedData = self::$memcache->get($key.'|old');
				if (is_array($cachedData))
				{
					$this->old = true;
				}
			}

			if (!is_array($cachedData))
			{
				return false;
			}

			if ($this->old && $this->lock($baseDir, $initDir, $key."|old|~", $TTL))
			{
				return false;
			}
			elseif ($cachedData["datecreate"] < (time() - $TTL) && $this->lock($baseDir, $initDir, $key."~", $TTL))
			{
				return false;
			}

			$allVars = $cachedData["content"];
		}
		else
		{
			$allVars = self::$memcache->get($key);
		}

		if ($allVars === false || $allVars === '')
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
	 * @param integer $TTL Expiration period in seconds.
	 *
	 * @return void
	 */
	function write($allVars, $baseDir, $initDir, $filename, $TTL)
	{
		if (!isset(self::$baseDirVersion[$baseDir]))
			self::$baseDirVersion[$baseDir] = self::$memcache->get($this->sid.$baseDir);

		if (self::$baseDirVersion[$baseDir] === false || self::$baseDirVersion[$baseDir] === '')
		{
			self::$baseDirVersion[$baseDir] = $this->sid.md5(mt_rand());
			self::$memcache->set($this->sid.$baseDir, self::$baseDirVersion[$baseDir]);
		}

		if ($initDir !== false)
		{
			$initDirVersion = self::$memcache->get(self::$baseDirVersion[$baseDir]."|".$initDir);
			if ($initDirVersion === false || $initDirVersion === '')
			{
				$initDirVersion = $this->sid.md5(mt_rand());
				self::$memcache->set(self::$baseDirVersion[$baseDir]."|".$initDir, $initDirVersion);
			}
		}
		else
		{
			$initDirVersion = "";
		}

		$key = self::$baseDirVersion[$baseDir]."|".$initDirVersion."|".$filename;
		$time = time();
		$exp = $this->ttlMultiplier > 0? $time + intval($TTL) * $this->ttlMultiplier: 0;

		if ($this->useLock)
		{
			self::$memcache->set($key, array("datecreate" => $time, "content" => $allVars), 0, $exp);

			$this->unlock($baseDir, $initDir, $key."~", $TTL);
			$this->unlock($baseDir, $initDir, $key."|old|~");
		}
		else
		{
			self::$memcache->set($key, $allVars, 0, $exp);
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
			$initDirVersion = self::$memcache->get($initDirKey);

			if (($initDirVersion === false || $initDirVersion === '') && !$skipOld)
			{
				$initDirVersion = self::$memcache->get($initDirKey.'|old');
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
			self::$baseDirVersion[$baseDir] = self::$memcache->get($baseDirKey);
		}

		if ((self::$baseDirVersion[$baseDir] === false || self::$baseDirVersion[$baseDir] === '') && !$skipOld)
		{
			self::$baseDirVersion[$baseDir] = self::$memcache->get($baseDirKey.'|old');
			if (isset(self::$baseDirVersion[$baseDir]))
			{
				$this->old = true;
			}
		}
	}
}