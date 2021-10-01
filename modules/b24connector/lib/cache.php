<?php

namespace Bitrix\B24Connector;

use Bitrix\Main\Application;

/**
 * Simple wrapper around data cache
 * @internal
 * @package Bitrix\B24Connector
 */
final class Cache
{
	const CACHE_ROOT = '/b24connector';

	/**
	 * Returns data from cache if it's valid. Stores data returned by $callback in cache and returns them otherwise.
	 * @param string $cacheId
	 * @param int $ttl
	 * @param callable $callback
	 * @return mixed
	 */
	public static function remember(string $cacheId, int $ttl, callable $callback)
	{
		$cachePath = self::CACHE_ROOT . '/' . $cacheId;
		$manager = Application::getInstance()->getCache();
		if ($manager->initCache($ttl, $cacheId, $cachePath))
		{
			$cached = $manager->getVars();
			return $cached['PROXY'];
		}
		else
		{
			$result = $callback();
			$manager->startDataCache();
			$manager->endDataCache(['PROXY' => $result]);
			return $result;
		}
	}
}
