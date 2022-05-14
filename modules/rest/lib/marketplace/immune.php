<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\Json;

/**
 * Class Immune
 * @package Bitrix\Rest\Marketplace
 */
class Immune
{
	private const OPTION_APP_IMMUNE_LIST = 'app_immune';
	private const MODULE_ID = 'rest';
	private const CACHE_TTL_TIMEOUT = 120;
	private const CACHE_DIR = '/rest/';
	private static $immuneAppList;

	/**
	 * @return array
	 */
	public static function getList() : array
	{
		if (!is_array(static::$immuneAppList))
		{
			static::$immuneAppList = [];
			try
			{
				$option = Option::get(static::MODULE_ID, static::OPTION_APP_IMMUNE_LIST, null);

				if ($option === null)
				{
					$option = static::getExternal();
				}

				if (!empty($option))
				{
					static::$immuneAppList = Json::decode($option);
				}
				else
				{
					static::$immuneAppList = [];
				}
			}
			catch (\Exception $exception)
			{
				static::$immuneAppList = [];
			}
		}

		return static::$immuneAppList;
	}

	private static function getExternal()
	{
		$result = false;
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
				$result = Json::encode($res['ITEMS']);
				Option::set(static::MODULE_ID, static::OPTION_APP_IMMUNE_LIST, $result);
			}

			$cache->endDataCache($result);
		}

		return $result;
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