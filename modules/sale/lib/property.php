<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\OrderPropsTable;

/**
 * Class PropertyValueBase
 * @package Bitrix\Sale
 */
class Property extends PropertyBase
{
	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		if (!isset($parameters['filter']))
		{
			$parameters['filter'] = [
				'=ENTITY_REGISTRY_TYPE' => static::getRegistryType()
			];
		}
		else
		{
			$parameters['filter'] = [
				'=ENTITY_REGISTRY_TYPE' => static::getRegistryType(),
				$parameters['filter']
			];
		}

		return OrderPropsTable::getList($parameters);
	}

}
