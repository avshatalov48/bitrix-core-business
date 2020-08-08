<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\Input;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PropertyValueCollectionBase
 * @package Bitrix\Sale
 */
abstract class PropertyValueCollectionBase extends Internals\EntityCollection
{
	/** @var OrderBase */
	protected $order;

	protected $propertyGroups = null;

	/**
	 * @param OrderBase $order
	 * @return PropertyValueCollectionBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function load(OrderBase $order)
	{
		/** @var PropertyValueCollectionBase $propertyCollection */
		$propertyCollection = static::createPropertyValueCollectionObject();
		$propertyCollection->setOrder($order);

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var PropertyValueBase $propertyValueClassName */
		$propertyValueClassName = $registry->getPropertyValueClassName();

		$props = $propertyValueClassName::loadForOrder($order);

		/** @var PropertyValueBase $prop */
		foreach ($props as $prop)
		{
			$prop->setCollection($propertyCollection);
			$propertyCollection->addItem($prop);
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
	 * @param array $prop
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public function createItem(array $prop)
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyValueBase $propertyValueClass */
		$propertyValueClass = $registry->getPropertyValueClassName();
		$property = $propertyValueClass::create($this, $prop);
		$this->addItem($property);

		return $property;
	}

	/**
	 * @param Internals\CollectableEntity $property
	 * @return Internals\CollectableEntity|Result
	 * @throws Main\ArgumentTypeException
	 */
	public function addItem(Internals\CollectableEntity $property)
	{
		/** @var PropertyValueBase $property */
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

		/** @var OrderBase $order */
		$order = $this->getOrder();
		return $order->onPropertyValueCollectionModify(EventActions::DELETE, $oldItem);
	}

	/**
	 * @param Internals\CollectableEntity $item
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	public function onItemModify(Internals\CollectableEntity $item, $name = null, $oldValue = null, $value = null)
	{
		if (!$item instanceof PropertyValueBase)
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
	 * @return static
	 */
	private static function createPropertyValueCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$propertyValueCollectionClassName = $registry->getPropertyValueCollectionClassName();

		return new $propertyValueCollectionClassName();
	}

	/**
	 * @param $name
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getAttribute($name)
	{
		/** @var PropertyValueBase $item */
		foreach ($this->collection as $item)
		{
			$property = $item->getPropertyObject();
			if ($property->getField($name) === 'Y')
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getUserEmail()
	{
		return $this->getAttribute('IS_EMAIL');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getPayerName()
	{
		return $this->getAttribute('IS_PAYER');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getDeliveryLocation()
	{
		return $this->getAttribute('IS_LOCATION');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getTaxLocation()
	{
		return $this->getAttribute('IS_LOCATION4TAX');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getProfileName()
	{
		return $this->getAttribute('IS_PROFILE_NAME');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getDeliveryLocationZip()
	{
		return $this->getAttribute('IS_ZIP');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getPhone()
	{
		return $this->getAttribute('IS_PHONE');
	}

	/**
	 * @return PropertyValueBase
	 * @throws ArgumentOutOfRangeException
	 */
	public function getAddress()
	{
		return $this->getAttribute('IS_ADDRESS');
	}

	/**
	 * @param $post
	 * @param $files
	 * @return Result
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setValuesFromPost($post, $files)
	{
		$post = Input\File::getPostWithFiles($post, $files);

		$result = new Result();

		/** @var PropertyValueBase $property */
		foreach ($this->collection as $property)
		{
			$r = $property->setValueFromPost($post);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $fields
	 * @param $files
	 * @param bool $skipUtils
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function checkErrors($fields, $files, $skipUtils = false)
	{
		$result = new Result();

		$fields = Input\File::getPostWithFiles($fields, $files);

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			if ($skipUtils && $propertyValue->isUtil())
			{
				continue;
			}

			if ($propertyValue->getField('ORDER_PROPS_ID') > 0)
			{
				$key = $propertyValue->getField('ORDER_PROPS_ID');
			}
			else
			{
				$key = "n".$propertyValue->getInternalIndex();
			}

			$value = isset($fields['PROPERTIES'][$key]) ? $fields['PROPERTIES'][$key] : null;

			if (!isset($fields['PROPERTIES'][$key]))
			{
				$value = $propertyValue->getValue();
			}

			$r = $propertyValue->checkValue($key, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param array $rules
	 * @param array $fields
	 *
	 * @return Result
	 */
	public function checkRequired(array $rules, array $fields)
	{
		$result = new Result();

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			if ($propertyValue->getField('ORDER_PROPS_ID') > 0)
			{
				$key = $propertyValue->getField('ORDER_PROPS_ID');
			}
			else
			{
				$key = "n".$propertyValue->getInternalIndex();
			}

			if (!in_array($key, $rules))
			{
				continue;
			}

			$value = isset($fields['PROPERTIES'][$key]) ? $fields['PROPERTIES'][$key] : null;
			if (!isset($fields['PROPERTIES'][$key]))
			{
				$value = $propertyValue->getValue();
			}

			$r = $propertyValue->checkRequiredValue($key, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getGroups()
	{
		$result = [];

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$property = $propertyValue->getPropertyObject();
			$group = $property->getGroupInfo();
			if (!isset($result[$group['ID']]))
			{
				$result[$group['ID']] = $group;
			}
		}

		return $result;
	}

	/**
	 * @param $groupId
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPropertiesByGroupId($groupId)
	{
		$result = [];

		$groups = $this->getGroups();

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$property = $propertyValue->getPropertyObject();
			if (!$property)
			{
				continue;
			}

			$propertyGroupId = (int)$property->getGroupId();
			if (!isset($groups[$propertyGroupId]))
			{
				$propertyGroupId = 0;
			}

			if ($propertyGroupId === (int)$groupId)
			{
				$result[] = $propertyValue;
			}
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getArray()
	{
		$groups = $this->getGroups();

		$properties = array();

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$p = $propertyValue->getProperty();

			if (!isset($p["ID"]))
			{
				if ($propertyValue->getField("ORDER_PROPS_ID") > 0)
				{
					$p["ID"] = $propertyValue->getField('ORDER_PROPS_ID');
				}
				else
				{
					$p["ID"] = "n".$propertyValue->getInternalIndex();
				}
			}

			$value = $propertyValue->getValue();

			$value = $propertyValue->getValueId() ? $value : ($value ? $value : $p['DEFAULT_VALUE']);

			$value = array_values(Input\Manager::asMultiple($p, $value));

			$p['VALUE'] = $value;

			$properties[] = $p;
		}

		return array('groups' => $groups, 'properties' => $properties);
	}

	/**
	 * @param $orderPropertyId
	 * @return PropertyValueBase|null
	 */
	public function getItemByOrderPropertyId($orderPropertyId)
	{
		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			if ($propertyValue->getField('ORDER_PROPS_ID') == $orderPropertyId)
			{
				return $propertyValue;
			}
		}

		return null;
	}

	/**
	 * @param string $propertyCode
	 * @return PropertyValueBase[]
	 */
	public function getItemsByOrderPropertyCode(string $propertyCode)
	{
		return $this->getItemsByFilter(
			function ($propertyValue) use ($propertyCode)
			{
				return (
					$propertyValue->getField('CODE') == $propertyCode
				);
			}
		);
	}

	/**
	 * @param string $propertyCode
	 * @return PropertyValueBase|null
	 */
	public function getItemByOrderPropertyCode(string $propertyCode)
	{
		$items = $this->getItemsByOrderPropertyCode($propertyCode);

		return empty($items) ? null : current($items);
	}

	/**
	 * @param callable $filter
	 * @return PropertyValueBase[]
	 */
	public function getItemsByFilter(callable $filter)
	{
		$results = [];

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			if (!$filter($propertyValue))
			{
				continue;
			}

			$results[] = $propertyValue;
		}

		return $results;
	}

	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = new Result();

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$r = $propertyValue->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		$itemsFromDb = $this->getOriginalItemsValues();
		foreach ($itemsFromDb as $k => $v)
		{
			if ($this->getItemById($k))
			{
				continue;
			}

			$this->callEventOnBeforeSalePropertyValueDeleted($v);

			$r = self::delete($v);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			$this->callEventOnSalePropertyValueDeleted($v);
		}

		/** @var PropertyValue $property */
		foreach ($this->collection as $property)
		{
			$r = $property->save();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $values
	 * @throws Main\NotImplementedException
	 */
	protected function callEventOnSalePropertyValueDeleted($values)
	{
		$values['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		/** @var Main\Event $event */
		$event = new Main\Event(
			'sale',
			'OnSalePropertyValueDeleted',
			array('VALUES' => $values)
		);

		$event->send();
	}

	/**
	 * @param $values
	 * @throws Main\NotImplementedException
	 */
	protected function callEventOnBeforeSalePropertyValueDeleted($values)
	{
		$values['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		/** @var Main\Event $event */
		$event = new Main\Event(
			'sale',
			'OnBeforeSalePropertyValueDeleted',
			array('VALUES' => $values)
		);

		$event->send();
	}

	/**
	 * @return array
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	private function getOriginalItemsValues()
	{
		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$itemsFromDb = array();
		if ($order->getId() > 0)
		{
			$itemsFromDbList = static::getList(
				array(
					"filter" => array("ORDER_ID" => $this->getOrder()->getId()),
					"select" => array("ID", "NAME", "CODE", "VALUE", "ORDER_PROPS_ID")
				)
			);
			while ($itemsFromDbItem = $itemsFromDbList->fetch())
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
		}

		return $itemsFromDb;
	}

	/**
	 * @param $primary
	 * @throws Main\NotImplementedException
	 * @return Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @internal
	 *
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();

		$propertiesDataList = static::getList(
			array(
				"filter" => array('=ORDER_ID' => $idOrder),
				"select" => array('ID', 'ORDER_PROPS_ID')
			)
		);

		while ($propertyValue = $propertiesDataList->fetch())
		{
			$r = self::delete($propertyValue);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	private static function delete(array $value)
	{
		$result = new Result();

		$r = static::deleteInternal($value['ID']);

		if ($r->isSuccess())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			$propertyClass = $registry->getPropertyClassName();
			/** @var PropertyBase $property */
			$property = $propertyClass::getObjectById($value['ORDER_PROPS_ID']);
			if ($property)
			{
				$property->onValueDelete($value['VALUE']);
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @throws ArgumentOutOfRangeException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function refreshRelated()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PropertyValueBase $propertyValueClassName */
		$propertyValueClassName = $registry->getPropertyValueClassName();

		$props = $propertyValueClassName::loadForOrder($this->getOrder());

		/** @var PropertyValueBase $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$property = $propertyValue->getPropertyObject();
			if (!$property->getRelations())
			{
				continue;
			}

			if ($propertyValue->getId() <= 0
				&& !isset($props[$propertyValue->getPropertyId()])
			)
			{
				$propertyValue->delete();
			}
		}

		/** @var PropertyValueBase $propertyValue */
		foreach ($props as $propertyValue)
		{
			$property = $propertyValue->getPropertyObject();
			if (!$property->getRelations())
			{
				continue;
			}

			if (!$this->getItemByOrderPropertyId($propertyValue->getPropertyId()))
			{
				$propertyValue->setCollection($this);
				$this->addItem($propertyValue);
			}
		}
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
