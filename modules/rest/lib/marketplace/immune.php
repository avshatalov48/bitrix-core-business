<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Data\Cache;
use Bitrix\Rest\Internals\FreeAppTable;

/**
 * Class Immune
 * @package Bitrix\Rest\Marketplace
 */
class Immune
{
	private const CACHE_TTL_TIMEOUT = 120;
	private const CACHE_DIR = '/rest/';
	private static $immuneAppList;

	/**
	 * @return array
	 */
	public static function getList(): array
	{
		if (!is_array(static::$immuneAppList))
		{
			static::$immuneAppList = [];
			$cache = Cache::createInstance();

			if ($cache->initCache(86400, 'immuneAppList', static::CACHE_DIR))
			{
				$result = $cache->getVars();
				static::$immuneAppList = is_array($result) ? $result : [];
			}
			elseif ($cache->startDataCache())
			{
				try
				{
					$appList = FreeAppTable::query()
						->setSelect(['APP_CODE'])
						->fetchAll();

					if (empty($appList))
					{
						$appList = static::getExternal();
					}
					else
					{
						$appList = array_map(fn($app) => $app['APP_CODE'], $appList);
					}

					static::$immuneAppList = $appList;
				}
				catch (\Exception $exception)
				{
					static::$immuneAppList = [];
				}

				$cache->endDataCache(is_array(static::$immuneAppList) ? static::$immuneAppList : []);
			}
		}

		return static::$immuneAppList;
	}

	private static function getExternal(): array
	{
		$result = [];
		$cache = Cache::createInstance();

		if ($cache->initCache(static::CACHE_TTL_TIMEOUT, 'immuneLoadsRepeatingTimeout', static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$res = Client::getImmuneApp();

			if (!empty($res['ITEMS']))
			{
				$result = $res['ITEMS'];
				FreeAppTable::updateFreeAppTable($result);
				$cache->clean('immuneAppList', static::CACHE_DIR);
			}

			$cache->endDataCache($result);
		}

		if (is_array($result))
		{
			return $result;
		}

		return [];
	}

	/**
	 * Agent load external app list
	 * @return string
	 */
	public static function load() : string
	{
		static::getExternal();

		return '\Bitrix\Rest\Marketplace\Immune::load();';
	}
}