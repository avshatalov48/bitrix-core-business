<?php

namespace Bitrix\Sale;

/**
 * Class ShipmentPropertyValueCollection
 * @package Bitrix\Sale
 */
class ShipmentPropertyValueCollection extends EntityPropertyValueCollection
{
	/** @var Shipment */
	protected $shipment;

	/**
	 * Returns Events name on value deleted.
	 * @return string
	 */
	protected static function getOnValueDeletedEventName(): string
	{
		return 'OnSaleShipmentPropertyValueDeleted';
	}

	/**
	 * Returns Events name on before value deleted.
	 * @return string
	 */
	protected static function getOnBeforeValueDeletedEventName(): string
	{
		return 'OnBeforeSaleShipmentPropertyValueDeleted';
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
	 * @return Shipment
	 */
	protected function getEntityParent()
	{
		return $this->shipment;
	}

	/**
	 * @return string \Bitrix\Sale\Registry::ENTITY_SHIPMENT
	 */
	protected static function getEntityType(): string
	{
		return \Bitrix\Sale\Registry::ENTITY_SHIPMENT;
	}

	/**
	 * @return string EntityPropertyValue class name.
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function getPropertyValueClassName(): string
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getShipmentPropertyValueClassName();
	}

	/**
	 * @param Shipment $shipment
	 * @return ShipmentPropertyValueCollection
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function load(Shipment $shipment): ShipmentPropertyValueCollection
	{
		$propertyCollection = static::createPropertyValueCollectionObject();
		$propertyCollection->setShipment($shipment);

		/** @var ShipmentPropertyValue $propertyValueClassName */
		$propertyValueClassName = static::getPropertyValueClassName();

		$props = $propertyValueClassName::loadForEntity($shipment);

		/** @var ShipmentPropertyValue $prop */
		foreach ($props as $prop)
		{
			$prop->setCollection($propertyCollection);
			$propertyCollection->addItem($prop);
		}

		return $propertyCollection;
	}

	/**
	 * @return \Bitrix\Sale\Order
	 */
	public function getOrder()
	{
		return $this->shipment->getOrder();
	}

	private static function createPropertyValueCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueCollectionClassName = $registry->getShipmentPropertyValueCollectionClassName();
		return new $propertyValueCollectionClassName();
	}

	/**
	 * @param Shipment $shipment
	 */
	protected function setShipment(Shipment $shipment)
	{
		$this->shipment = $shipment;
	}
}