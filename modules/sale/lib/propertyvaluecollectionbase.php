<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PropertyValueCollectionBase
 * @package Bitrix\Sale
 */
abstract class PropertyValueCollectionBase extends EntityPropertyValueCollection
{
	/** @var OrderBase */
	protected $order;

	/**
	 * Returns Events name on value deleted.
	 * @return string
	 */
	protected static function getOnValueDeletedEventName(): string
	{
		return 'OnSalePropertyValueDeleted';
	}

	/**
	 * Returns Events name on before value deleted.
	 * @return string
	 */
	protected static function getOnBeforeValueDeletedEventName(): string
	{
		return 'OnBeforeSalePropertyValueDeleted';
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
	 * @param OrderBase $order
	 * @return PropertyValueCollectionBase
	 */
	public static function load(OrderBase $order)
	{
		/** @var PropertyValueCollectionBase $propertyCollection */
		$propertyCollection = static::createPropertyValueCollectionObject();
		$propertyCollection->setOrder($order);

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var EntityPropertyValue $propertyValueClassName */
		$propertyValueClassName = $registry->getPropertyValueClassName();

		$props = $propertyValueClassName::loadForOrder($order);

		/** @var EntityPropertyValue $prop */
		foreach ($props as $prop)
		{
			$prop->setCollection($propertyCollection);
			$propertyCollection->bindItem($prop);
		}

		return $propertyCollection;
	}

	/**
	 * @return OrderBase
	 */
	protected function getEntityParent()
	{
		return $this->getOrder();
	}

	/**
	 * @param CollectableEntity $property
	 * @return CollectableEntity|Result
	 */
	public function addItem(CollectableEntity $property)
	{
		/** @var EntityPropertyValue $property */
		$property = parent::addItem($property);

		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::ADD, $property);
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return Result|mixed
	 * @throws ArgumentOutOfRangeException
	 */
	public function deleteItem($index)
	{
		$oldItem = parent::deleteItem($index);
		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::DELETE, $oldItem);
	}

	/**
	 * @param CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function onItemModify(CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		if (!$item instanceof EntityPropertyValue)
			throw new Main\NotSupportedException();

		/** @var OrderBase $order */
		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::UPDATE, $item, $name, $oldValue, $value);
	}

	/**
	 * @param $name
	 * @param $oldValue
	 * @param $value
	 * @return Result
	 * @todo: no usings - remove?
	 */
	public function onOrderModify($name, $oldValue, $value)
	{
		return new Result();
	}

	/**
	 * @return OrderBase
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @param OrderBase $order
	 */
	public function setOrder(OrderBase $order)
	{
		$this->order = $order;
	}

	/**
	 * @return string ShipmentPropertyValue class name.
	 * @throws Main\ArgumentException
	 */
	protected static function getPropertyValueClassName(): string
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getPropertyValueClassName();
	}

	/**
	 * @return static
	 * @throws Main\ArgumentException
	 */
	protected static function createPropertyValueCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueCollectionClassName = $registry->getPropertyValueCollectionClassName();
		return new $propertyValueCollectionClassName();
	}

	/**
	 * @deprecated
	 * @use \Bitrix\Sale\PropertyValueCollectionBase::getPropertiesByGroupId
	 *
	 * @param $groupId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getGroupProperties($groupId)
	{
		return $this->getPropertiesByGroupId($groupId);
	}
}
