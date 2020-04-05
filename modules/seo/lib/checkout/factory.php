<?php

namespace Bitrix\Seo\Checkout;

use Bitrix\Main\ArgumentOutOfRangeException;

/**
 * Class Factory
 * @package Bitrix\Seo\Checkout
 */
class Factory
{
	/**
	 * @param $object
	 * @param $type
	 * @param null $parameters
	 * @return mixed
	 * @throws ArgumentOutOfRangeException
	 */
	public static function create($object, $type, $parameters = null)
	{
		$spaceList = explode('\\', $object);
		$objectClassName = array_pop($spaceList);
		array_push($spaceList, 'Services', $objectClassName);
		$className = implode('\\', $spaceList) . strtoupper(substr($type, 0, 1)) . strtolower(substr($type, 1));

		if (!class_exists($object))
		{
			throw new ArgumentOutOfRangeException('Object');
		}

		if (!class_exists($className))
		{
			throw new ArgumentOutOfRangeException('Type');
		}

		$instance = new $className($parameters);

		return $instance;
	}
}