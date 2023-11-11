<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

class CPHPCache
{
	/** @var Bitrix\Main\Data\Cache */
	private $cache;

	public function __construct()
	{
		$this->cache = \Bitrix\Main\Data\Cache::createInstance();
	}

	public function Clean($uniq_str, $initdir = false, $basedir = "cache")
	{
		return $this->cache->clean($uniq_str, $initdir, $basedir);
	}

	public function CleanDir($initdir = false, $basedir = "cache")
	{
		return $this->cache->cleanDir($initdir, $basedir);
	}

	public function InitCache($TTL, $uniq_str, $initdir = false, $basedir = "cache")
	{
		return $this->cache->initCache($TTL, $uniq_str, $initdir, $basedir);
	}

	public function Output()
	{
		$this->cache->output();
	}

	public function GetVars()
	{
		return $this->cache->getVars();
	}

	public function StartDataCache($TTL = false, $uniq_str = false, $initdir = false, $vars = [], $basedir = "cache")
	{
		$narg = func_num_args();
		if ($narg <= 0)
		{
			return $this->cache->startDataCache();
		}
		if ($narg <= 1)
		{
			return $this->cache->startDataCache($TTL);
		}
		if ($narg <= 2)
		{
			return $this->cache->startDataCache($TTL, $uniq_str);
		}
		if ($narg <= 3)
		{
			return $this->cache->startDataCache($TTL, $uniq_str, $initdir);
		}

		return $this->cache->startDataCache($TTL, $uniq_str, $initdir, $vars, $basedir);
	}

	function AbortDataCache()
	{
		$this->cache->abortDataCache();
	}

	/**
	 * Saves the result of calculation to the cache.
	 *
	 * @param mixed $vars
	 * @return void
	 */
	function EndDataCache($vars = false)
	{
		$this->cache->endDataCache($vars);
	}

	function IsCacheExpired($path)
	{
		return $this->cache->isCacheExpired($path);
	}

	public static function ClearCache($full = false, $initdir = '')
	{
		if ($full !== true && $full !== false && $initdir === "" && is_string($full))
		{
			$initdir = $full;
			$full = true;
		}

		$res = true;

		if ($full === true)
		{
			$obCache = new CPHPCache;
			$obCache->CleanDir($initdir, "cache");
		}

		$path = $_SERVER["DOCUMENT_ROOT"] . BX_PERSONAL_ROOT . "/cache" . $initdir;
		if (is_dir($path) && ($handle = opendir($path)))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
				{
					continue;
				}

				if (is_dir($path . "/" . $file))
				{
					if (!BXClearCache($full, $initdir . "/" . $file))
					{
						$res = false;
					}
					else
					{
						@chmod($path . "/" . $file, BX_DIR_PERMISSIONS);
						//We suppress error handle here because there may be valid cache files in this dir
						@rmdir($path . "/" . $file);
					}
				}
				elseif ($full)
				{
					@chmod($path . "/" . $file, BX_FILE_PERMISSIONS);
					if (!unlink($path . "/" . $file))
					{
						$res = false;
					}
				}
				elseif (mb_substr($file, -5) == ".html")
				{
					$obCache = new CPHPCache();
					if ($obCache->IsCacheExpired($path . "/" . $file))
					{
						@chmod($path . "/" . $file, BX_FILE_PERMISSIONS);
						if (!unlink($path . "/" . $file))
						{
							$res = false;
						}
					}
				}
				elseif (mb_substr($file, -4) == ".php")
				{
					$obCache = new CPHPCache();
					if ($obCache->IsCacheExpired($path . "/" . $file))
					{
						@chmod($path . "/" . $file, BX_FILE_PERMISSIONS);
						if (!unlink($path . "/" . $file))
						{
							$res = false;
						}
					}
				}
				else
				{
					//We should skip unknown file
					//it will be deleted with full cache cleanup
				}
			}
			closedir($handle);
		}

		return $res;
	}
}
