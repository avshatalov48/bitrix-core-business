<?php

namespace Bitrix\Sale\TradingPlatform;

use Bitrix\Sale;
use Bitrix\Main;

/**
 * Class Manager
 */
class Manager
{
	/**
	 * Manager constructor.
	 */
	private function __construct() {}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result|Sale\EO_TradingPlatform_Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = [])
	{
		return Sale\TradingPlatformTable::getList($parameters);
	}

	public static function getObjectById(int $id)
	{
		if (!$id)
		{
			return null;
		}

		$platform = Sale\TradingPlatformTable::getRowById($id);
		if (class_exists($platform['CLASS']))
		{
			return $platform['CLASS']::getInstanceByCode($platform['CODE']);
		}

		return null;
	}
}