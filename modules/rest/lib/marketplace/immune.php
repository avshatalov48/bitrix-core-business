<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Json;

/**
 * Class Immune
 * @package Bitrix\Rest\Marketplace
 */
class Immune
{
	private const OPTION_APP_IMMUNE_LIST = 'app_immune';
	private const MODULE_ID = 'rest';
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
				$option = Option::get(static::MODULE_ID, static::OPTION_APP_IMMUNE_LIST, '');
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

	/**
	 * Agent load external app list
	 * @return string
	 */
	public static function load() : string
	{
		$res = Client::getImmuneApp();
		if (!empty($res['ITEMS']))
		{
			$option = Json::encode($res['ITEMS']);
			Option::set(static::MODULE_ID, static::OPTION_APP_IMMUNE_LIST, $option);
		}

		return '\Bitrix\Rest\Marketplace\Immune::load();';
	}
}