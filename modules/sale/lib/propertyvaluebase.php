<?php

namespace Bitrix\Sale;

use Bitrix\Sale\Internals\Entity;
use Bitrix\Main;

/**
 * Class PropertyValueBase
 * @package Bitrix\Sale
 */
class PropertyValueBase extends EntityPropertyValue
{
	/**
	 * Returns OnSaved event name
	 * @return string
	 */
	protected static function getOnSavedEventName(): string
	{
		return 'OnSalePropertyValueEntitySaved';
	}

	/**
	 * @return string Property class name.
	 */
	protected static function getPropertyClassName(): string
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getPropertyClassName();
	}

	/**
	 * @return string \Bitrix\Sale\Registry::ENTITY_ORDER
	 */
	protected static function getEntityType(): string
	{
		return \Bitrix\Sale\Registry::ENTITY_ORDER;
	}


	/**
	 * @param OrderBase $order
	 * @return array
	 */
	public static function loadForOrder(OrderBase $order): array
	{
		return static::loadForEntity($order);
	}

	/**
	 * @param array|null $property
	 * @param array $value
	 * @param array|null $relation
	 * @return EntityPropertyValue
	 */
	protected static function createPropertyValueObject(
		array $property = null,
		array $value = [],
		array $relation = null
	): EntityPropertyValue
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueClassName = $registry->getPropertyValueClassName();
		return new $propertyValueClassName($property, $value, $relation);
	}

	/**
	 * @param Entity $order
	 * @return array
	 */
	protected static function extractPaySystemIdList(Entity $order)
	{
		return [$order->getField('PAY_SYSTEM_ID')];
	}

	/**
	 * @param Entity $order
	 * @return array
	 */
	protected static function extractDeliveryIdList(Entity $order)
	{
		return [(int)$order->getField('DELIVERY_ID')];
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Sale\Property::getOptions
	 *
	 * @param $propertyId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadOptions($propertyId)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyBase $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();
		$property = $propertyClassName::getObjectById($propertyId);

		if ($property)
		{
			return $property->getOptions();
		}

		return [];
	}

	/**
	 * @deprecated
	 * @see \Bitrix\Sale\Property::getMeaningfulValues
	 *
	 * @param $personTypeId
	 * @param $request
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getMeaningfulValues($personTypeId, $request)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyBase $propertyClassName */
		$propertyClassName = $registry->getPropertyClassName();
		return $propertyClassName::getMeaningfulValues($personTypeId, $request);
	}

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SalePropertyValue';
	}
}
