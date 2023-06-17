<?php

namespace Bitrix\Sale;

use Bitrix\Sale\Internals\Entity;

/**
 * Class ShipmentPropertyValue
 * @package Bitrix\Sale
 */
class ShipmentPropertyValue extends EntityPropertyValue
{
	/**
	 * Returns OnSaved event name
	 * @return string
	 */
	protected static function getOnSavedEventName(): string
	{
		return 'OnSaleShipmentPropertyValueEntitySaved';
	}

	/**
	 * @return string Property class name.
	 */
	protected static function getPropertyClassName(): string
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getShipmentPropertyClassName();
	}

	/**
	 * @return string \Bitrix\Sale\Registry::ENTITY_SHIPMENT
	 */
	protected static function getEntityType(): string
	{
		return \Bitrix\Sale\Registry::ENTITY_SHIPMENT;
	}

	/**
	 * @param Entity $shipment
	 * @return array
	 */
	protected static function extractDeliveryIdList(Entity $shipment)
	{
		if (!$shipment instanceof Shipment)
		{
			return [];
		}

		return [$shipment->getDeliveryId()];
	}

	protected static function extractTpLandingIdList(Entity $entity) : array
	{
		if (!$entity instanceof Shipment)
		{
			return [];
		}

		return $entity->getOrder()->getTradeBindingCollection()->getTradingPlatformIdList();
	}

	protected static function extractTradingPlatformIdList(Entity $entity): array
	{
		if (!$entity instanceof Shipment)
		{
			return [];
		}

		return $entity->getOrder()->getTradeBindingCollection()->getTradingPlatformIdList();
	}

	/**
	 * @param array|null $property
	 * @param array $value
	 * @param array|null $relation
	 * @return EntityPropertyValue
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function createPropertyValueObject(
		array $property = null,
		array $value = [], array
		$relation = null
	): EntityPropertyValue
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueClassName = $registry->getShipmentPropertyValueClassName();
		return new $propertyValueClassName($property, $value, $relation);
	}

	/**
	 * @param Shipment $shipment
	 * @return array
	 */
	public static function loadForShipment(Shipment $shipment): array
	{
		[$properties, $propertyValues, $propRelation, $propertyValuesMap] = static::loadFromDb($shipment);
		return static::createPropertyValuesObjects($properties, $propertyValues, $propRelation, $propertyValuesMap);
	}

	/**
	 * @return string
	 */
	public static function getEntityEventName()
	{
		return 'SaleShipmentPropertyValue';
	}
}
