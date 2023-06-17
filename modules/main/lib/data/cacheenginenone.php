<?php
namespace Bitrix\Main\Data;

class CacheEngineNone implements ICacheEngine, ICacheEngineStat
{
	public function getReadBytes()
	{
		return 0;
	}

	public function getWrittenBytes()
	{
		return 0;
	}

	public function getCachePath()
	{
		return "";
	}

	public function isAvailable()
	{
		return true;
	}

	public function clean($baseDir, $initDir = false, $filename = false)
	{
		return true;
	}

	public function read(&$vars, $baseDir, $initDir, $filename, $ttl)
	{
		return false;
	}

	public function write($vars, $baseDir, $initDir, $filename, $ttl)
	{
	}

	public function isCacheExpired($path)
	{
		return true;
	}
}