<?php

namespace Bitrix\Rest\V3;

use Bitrix\Main\Application;

final class CacheManager
{
	private const CACHE_DIR = 'rest/v3';

	private const CACHE_TTL = 31536000; // One year TTL

	public static function get(string $key): mixed
	{
		$cache = Application::getInstance()->getManagedCache();
		if ($cache->read(self::CACHE_TTL, $key, self::CACHE_DIR))
		{
			return $cache->get($key);
		}
		return null;
	}

	public static function set(string $key, mixed $value): bool
	{
		$cache = Application::getInstance()->getManagedCache();
		$cache->read(self::CACHE_TTL, $key, self::CACHE_DIR);
		$cache->setImmediate($key, $value);
		return true;
	}
}