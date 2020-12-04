<?php
namespace Bitrix\Main\Data;

use Bitrix\Main\Data\LocalStorage;

class CacheEngineApc
	implements ICacheEngine, ICacheEngineStat, LocalStorage\Storage\CacheEngineInterface
{
	private $sid = "BX";
	//cache stats
	private $written = false;
	private $read = false;

	protected $useLock = false;
	protected $ttlMultiplier = 2;
	protected static $locks = array();

	/**
	 * Engine constructor.
	 * @param array $options Cache options.
	 */
	public function __construct($options = [])
	{
		$config = \Bitrix\Main\Config\Configuration::getValue("cache");

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

		$this->sid .= !$this->useLock;

		if (!$this->useLock)
		{
			$this->ttlMultiplier = 1;
		}
	}

	/**
	 * Returns number of bytes read from apc or false if there was no read operation.
	 *
	 * @return integer|false
	 */
	public function getReadBytes()
	{
		return $this->read;
	}

	/**
	 * Returns number of bytes written to apc or false if there was no write operation.
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
	public function isAvailable()
	{
		return function_exists('apc_fetch');
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
		elseif (apc_fetch($key)) //another process has the lock
		{
			return false;
		}
		else
		{
			$lock = apc_add($key, 1, intval($TTL));
			if ($lock) //we are lucky to be the first
			{
				self::$locks[$baseDir][$initDir][$key] = true;
				return true;
			}
			//xcache_dec have to be never called due to concurrency with xcache_set($key."~", 1, intval($TTL));
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
				apc_store($key, 1, intval($TTL));
			}
			else
			{
				apc_delete($key);
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
	public function clean($baseDir, $initDir = false, $filename = false)
	{
		$key = false;
		if($filename <> '')
		{
			$baseDirVersion = apc_fetch($this->sid.$baseDir);
			if($baseDirVersion === false)
			{
				return;
			}

			if($initDir !== false)
			{
				$initDirVersion = apc_fetch($baseDirVersion."|".$initDir);
				if($initDirVersion === false)
				{
					return;
				}
			}
			else
			{
				$initDirVersion = "";
			}

			$key = $baseDirVersion."|".$initDirVersion."|".$filename;
			apc_delete($key);
		}
		else
		{
			if($initDir <> '')
			{
				$baseDirVersion = apc_fetch($this->sid.$baseDir);
				if($baseDirVersion === false)
				{
					return;
				}

				apc_delete($baseDirVersion."|".$initDir);
			}
			else
			{
				apc_delete($this->sid.$baseDir);
			}
		}

		$this->unlock($baseDir, $initDir, $key."~");
	}

	/**
	 * Reads cache from the apc. Returns true if key value exists, not expired, and successfully read.
	 *
	 * @param mixed &$allVars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $TTL Expiration period in seconds.
	 *
	 * @return boolean
	 */
	public function read(&$allVars, $baseDir, $initDir, $filename, $TTL)
	{
		$baseDirVersion = apc_fetch($this->sid.$baseDir);
		if ($baseDirVersion === false)
		{
			return false;
		}

		if ($initDir !== false)
		{
			$initDirVersion = apc_fetch($baseDirVersion."|".$initDir);
			if ($initDirVersion === false)
			{
				return false;
			}
		}
		else
		{
			$initDirVersion = "";
		}

		$key = $baseDirVersion."|".$initDirVersion."|".$filename;
		$allVars = apc_fetch($key);

		if ($allVars === false)
		{
			return false;
		}
		else
		{
			if ($this->useLock)
			{
				if ($this->lock($baseDir, $initDir, $key."~", $TTL))
				{
					return false;
				}
			}

			$this->read = mb_strlen($allVars);
			$allVars = unserialize($allVars);
		}

		return true;
	}

	/**
	 * Puts cache into the apc.
	 *
	 * @param mixed $allVars Cached result.
	 * @param string $baseDir Base cache directory (usually /bitrix/cache).
	 * @param string $initDir Directory within base.
	 * @param string $filename File name.
	 * @param integer $TTL Expiration period in seconds.
	 *
	 * @return void
	 */
	public function write($allVars, $baseDir, $initDir, $filename, $TTL)
	{
		$baseDirVersion = apc_fetch($this->sid.$baseDir);
		if ($baseDirVersion === false)
		{
			$baseDirVersion = md5(mt_rand());
			if (!apc_store($this->sid.$baseDir, $baseDirVersion))
			{
				return;
			}
		}

		if ($initDir !== false)
		{
			$initDirVersion = apc_fetch($baseDirVersion."|".$initDir);
			if ($initDirVersion === false)
			{
				$initDirVersion = md5(mt_rand());
				if (!apc_store($baseDirVersion."|".$initDir, $initDirVersion))
				{
					return;
				}
			}
		}
		else
		{
			$initDirVersion = "";
		}

		$allVars = serialize($allVars);
		$this->written = mb_strlen($allVars);

		$key = $baseDirVersion."|".$initDirVersion."|".$filename;
		apc_store($key, $allVars, intval($TTL) * $this->ttlMultiplier);

		if ($this->useLock)
		{
			$this->unlock($baseDir, $initDir, $key."~", $TTL);
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
}