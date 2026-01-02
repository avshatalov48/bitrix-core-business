<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\SystemException;
use Bitrix\Rest\Internals\FreeAppTable;

/**
 * Class Immune
 * @package Bitrix\Rest\Marketplace
 */
class Immune
{
	private const CACHE_TTL_TIMEOUT = 86400;
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

			if ($cache->initCache(static::CACHE_TTL_TIMEOUT, 'immuneAppList', static::CACHE_DIR))
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

					static::$immuneAppList = $appList;
				}
				catch (\Exception $exception)
				{
					static::$immuneAppList = [];
				}

				$cache->endDataCache(is_array(static::$immuneAppList) ? static::$immuneAppList : []);
			}

			static::$immuneAppList = array_map(fn($app) => $app['APP_CODE'], static::$immuneAppList);
		}

		return static::$immuneAppList;
	}

	private static function getExternal(): array
	{
		$immuneAppList = Client::getImmuneApp();
		FreeAppTable::updateFreeAppTable($immuneAppList);
		$cache = Cache::createInstance();
		$cache->clean('immuneAppList', static::CACHE_DIR);

		return $immuneAppList;
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
