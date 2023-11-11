<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

class CPageCache
{
	var $_cache;
	var $filename;
	var $content;
	var $ttl;
	var $bStarted = false;
	var $uniq_str = false;
	var $basedir;
	var $initdir = false;

	function __construct()
	{
		$this->_cache = \Bitrix\Main\Data\Cache::createCacheEngine();
	}

	function GetPath($uniq_str)
	{
		$un = md5($uniq_str);
		return mb_substr($un, 0, 2)."/".$un.".html";
	}

	function Clean($uniq_str, $initdir = false, $basedir = "cache")
	{
		if(isset($this) && is_object($this) && is_object($this->_cache))
		{
			$basedir = BX_PERSONAL_ROOT."/".$basedir."/";
			$filename = CPageCache::GetPath($uniq_str);
			if (\Bitrix\Main\Data\Cache::getShowCacheStat())
				\Bitrix\Main\Diag\CacheTracker::add(0, "", $basedir, $initdir, "/".$filename, "C");
			return $this->_cache->clean($basedir, $initdir, "/".$filename);
		}
		else
		{
			$obCache = new CPageCache();
			return $obCache->Clean($uniq_str, $initdir, $basedir);
		}
	}

	function CleanDir($initdir = false, $basedir = "cache")
	{
		$basedir = BX_PERSONAL_ROOT."/".$basedir."/";
		if (\Bitrix\Main\Data\Cache::getShowCacheStat())
			\Bitrix\Main\Diag\CacheTracker::add(0, "", $basedir, $initdir, "", "C");
		return $this->_cache->clean($basedir, $initdir);
	}

	function InitCache($TTL, $uniq_str, $initdir = false, $basedir = "cache")
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;
		if($initdir === false)
			$initdir = $APPLICATION->GetCurDir();

		$this->basedir = BX_PERSONAL_ROOT."/".$basedir."/";
		$this->initdir = $initdir;
		$this->filename = "/".CPageCache::GetPath($uniq_str);
		$this->ttl = $TTL;
		$this->uniq_str = $uniq_str;

		if($TTL<=0)
			return false;

		if(is_object($USER) && $USER->CanDoOperation('cache_control'))
		{
			if(isset($_GET["clear_cache_session"]))
			{
				if(mb_strtoupper($_GET["clear_cache_session"]) == "Y")
					\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_CLEAR_CACHE"] = "Y";
				elseif (!empty($_GET["clear_cache_session"]))
					unset(\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_CLEAR_CACHE"]);
			}

			if(isset($_GET["clear_cache"]) && mb_strtoupper($_GET["clear_cache"]) == "Y")
				return false;
		}

		if(isset(\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_CLEAR_CACHE"]) && \Bitrix\Main\Application::getInstance()->getSession()["SESS_CLEAR_CACHE"] == "Y")
			return false;

		if(!$this->_cache->read($this->content, $this->basedir, $this->initdir, $this->filename, $this->ttl))
			return false;

		if (\Bitrix\Main\Data\Cache::getShowCacheStat())
		{
			$read = 0;
			$path = '';
			if ($this->_cache instanceof \Bitrix\Main\Data\ICacheEngineStat)
			{
				$read = $this->_cache->getReadBytes();
				$path = $this->_cache->getCachePath();
			}
			elseif ($this->_cache instanceof ICacheBackend)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				$read = $this->_cache->read;

				/** @noinspection PhpUndefinedFieldInspection */
				$path = $this->_cache->path;
			}

			\Bitrix\Main\Diag\CacheTracker::addCacheStatBytes($read);
			\Bitrix\Main\Diag\CacheTracker::add($read, $path, $this->basedir, $this->initdir, $this->filename, "R");
		}
		return true;
	}

	function Output()
	{
		echo $this->content;
	}

	function StartDataCache($TTL, $uniq_str=false, $initdir=false, $basedir = "cache")
	{
		if($this->InitCache($TTL, $uniq_str, $initdir, $basedir))
		{
			$this->Output();
			return false;
		}

		if($TTL<=0)
			return true;

		ob_start();
		$this->bStarted = true;
		return true;
	}

	function AbortDataCache()
	{
		if(!$this->bStarted)
			return;
		$this->bStarted = false;

		ob_end_flush();
	}

	function EndDataCache()
	{
		if(!$this->bStarted)
			return;
		$this->bStarted = false;

		$arAllVars = ob_get_contents();

		$this->_cache->write($arAllVars, $this->basedir, $this->initdir, $this->filename, $this->ttl);

		if (\Bitrix\Main\Data\Cache::getShowCacheStat())
		{
			$written = 0;
			$path = '';
			if ($this->_cache instanceof \Bitrix\Main\Data\ICacheEngineStat)
			{
				$written = $this->_cache->getWrittenBytes();
				$path = $this->_cache->getCachePath();
			}
			elseif ($this->_cache instanceof ICacheBackend)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				$written = $this->_cache->written;

				/** @noinspection PhpUndefinedFieldInspection */
				$path = $this->_cache->path;
			}
			\Bitrix\Main\Diag\CacheTracker::addCacheStatBytes($written);
			\Bitrix\Main\Diag\CacheTracker::add($written, $path, $this->basedir, $this->initdir, $this->filename, "W");
		}

		if($arAllVars <> '')
			ob_end_flush();
		else
			ob_end_clean();
	}

	function IsCacheExpired($path)
	{
		return $this->_cache->IsCacheExpired($path);
	}
}
