<?php

namespace Bitrix\Sale;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Internals\EntityCollection;
use Bitrix\Sale\Internals\Input\File;
use Bitrix\Sale\Internals\Input\Manager;
use Bitrix\Sale\Internals\OrderPropsValueTable;

abstract class EntityPropertyValueCollection extends EntityCollection
{
	abstract protected static function getOnValueDeletedEventName(): string;
	abstract protected static function getOnBeforeValueDeletedEventName(): string;
	/**
	 * @return string Property class name.
	 */
	abstract protected static function getPropertyClassName(): string;

	/**
	 * @return string \Bitrix\Sale\Registry::ENTITY_SHIPMENT | \Bitrix\Sale\Registry::ENTITY_ORDER
	 */
	abstract protected static function getEntityType(): string;

	/**
	 * @return string EntityPropertyValue inheritor class name
	 */
	abstract protected static function getPropertyValueClassName(): string;

	/**
	 * @param int $entityId
	 * @return array
	 */
	protected static function getAllItemsFromDb(int $entityId): array
	{
		return static::getList(
			[
				"filter" => [
					'=ENTITY_TYPE' => static::getEntityType(),
					'=ENTITY_ID' => $entityId
				],
				"select" => ['ID', 'ORDER_PROPS_ID']
			]
		)->fetchAll();
	}

	public function getEntityParentId(): int
	{
		return $this->getEntityParent()->getId();
	}

	/**
	 * @param $orderPropertyId
	 * @return EntityPropertyValue|null
	 */
	public function getItemByOrderPropertyId($orderPropertyId)
	{
		/** @var EntityPropertyValue $propertyValue */
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
	 * @param bool $refreshData
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getArray(bool $refreshData = false)
	{
		$groups = $this->getGroups($refreshData);

		$properties = [];

		/** @var EntityPropertyValue $propertyValue */
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

			$value = array_values(Manager::asMultiple($p, $value));

			$p['VALUE'] = $value;

			$properties[] = $p;
		}

		return ['groups' => $groups, 'properties' => $properties];
	}

	/**
	 * @param $value
	 * @return Result
	 */
	protected static function delete(array $value)
	{
		$result = new Result();

		$r = static::deleteInternal($value['ID']);

		if ($r->isSuccess())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			$propertyClass = static::getPropertyClassName();
			/** @var EntityProperty $property */
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
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	public function createItem(array $prop)
	{
		/** @var EntityPropertyValue $propertyValueClass */
		$propertyValueClass = static::getPropertyValueClassName();
		$property = $propertyValueClass::create($this, $prop);
		$this->addItem($property);

		return $property;
	}

	/**
	 * @param $name
	 * @return EntityPropertyValue|null
	 */
	public function getAttribute($name)
	{
		/** @var EntityPropertyValue $item */
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
	 * @return EntityPropertyValue
	 * @throws ArgumentOutOfRangeException
	 */
	public function getUserEmail()
	{
		return $this->getAttribute('IS_EMAIL');
	}

	/**
	 * @return EntityPropertyValue
	 * @throws ArgumentOutOfRangeException
	 */
	public function getPayerName()
	{
		return $this->getAttribute('IS_PAYER');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getDeliveryLocation()
	{
		return $this->getAttribute('IS_LOCATION');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getTaxLocation()
	{
		return $this->getAttribute('IS_LOCATION4TAX');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getProfileName()
	{
		return $this->getAttribute('IS_PROFILE_NAME');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getDeliveryLocationZip()
	{
		return $this->getAttribute('IS_ZIP');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getPhone()
	{
		return $this->getAttribute('IS_PHONE');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getAddress()
	{
		return $this->getAttribute('IS_ADDRESS');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getAddressFrom()
	{
		return $this->getAttribute('IS_ADDRESS_FROM');
	}

	/**
	 * @return EntityPropertyValue
	 */
	public function getAddressTo()
	{
		return $this->getAttribute('IS_ADDRESS_TO');
	}

	/**
	 * @param bool $refreshData
	 * @return array
	 */
	public function getGroups(bool $refreshData = false)
	{
		$result = [];

		/** @var EntityPropertyValue $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			$property = $propertyValue->getPropertyObject();
			$group = $property->getGroupInfo($refreshData);
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
	 */
	public function getPropertiesByGroupId($groupId)
	{
		$result = [];

		$groups = $this->getGroups();

		/** @var EntityPropertyValue $propertyValue */
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
	 * @param callable $filter
	 * @return EntityPropertyValue[]
	 */
	public function getItemsByFilter(callable $filter)
	{
		$results = [];

		/** @var EntityPropertyValue $propertyValue */
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
	 */
	public function verify()
	{
		$result = new Result();

		/** @var EntityPropertyValue $propertyValue */
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

			$r = static::delete($v);
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
	 * @return array
	 * @throws NotImplementedException
	 * @throws ObjectNotFoundException
	 */
	protected function getOriginalItemsValues()
	{
		/** @var Order $order */
		if (!$entity = $this->getEntityParent())
		{
			throw new ObjectNotFoundException('Entity not found');
		}

		$itemsFromDb = [];
		if ($entity->getId() > 0)
		{
			$itemsFromDbList = static::getList(
				[
					"filter" => [
						"ENTITY_ID" => $entity->getId(),
						"ENTITY_TYPE" => static::getEntityType()
					],
					"select" => [
						"ID", "NAME", "CODE", "VALUE", "ORDER_PROPS_ID", "ENTITY_ID", "ENTITY_TYPE"
					]
				]
			);

			while ($itemsFromDbItem = $itemsFromDbList->fetch())
			{
				$itemsFromDb[$itemsFromDbItem["ID"]] = $itemsFromDbItem;
			}
		}

		return $itemsFromDb;
	}

	/**
	 * @param $values
	 */
	protected function callEventOnSalePropertyValueDeleted($values)
	{
		$values['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		$event = new Event(
			'sale',
			static::getOnValueDeletedEventName(),
			['VALUES' => $values]
		);

		$event->send();
	}

	/**
	 * @param string $propertyCode
	 * @return EntityPropertyValue[]
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
	 * @return EntityPropertyValue|null
	 */
	public function getItemByOrderPropertyCode(string $propertyCode)
	{
		$items = $this->getItemsByOrderPropertyCode($propertyCode);
		return empty($items) ? null : current($items);
	}

	/**
	 * @param $values
	 */
	protected function callEventOnBeforeSalePropertyValueDeleted($values)
	{
		$values['ENTITY_REGISTRY_TYPE'] = static::getRegistryType();

		$event = new Event(
			'sale',
			static::getOnBeforeValueDeletedEventName(),
			['VALUES' => $values]
		);

		$event->send();
	}

	/**
	 * @param $primary
	 */
	protected static function deleteInternal($primary)
	{
		return OrderPropsValueTable::delete($primary);
	}

	/**
	 * @param array $parameters
	 * @return \Bitrix\Main\ORM\Query\Result|\Bitrix\Sale\Internals\EO_OrderPropsValue_Result
	 */
	public static function getList(array $parameters = [])
	{
		return OrderPropsValueTable::getList($parameters);
	}

	/*
	 * Refreshes related properties
	 */
	public function refreshRelated(): void
	{
		/** @var EntityPropertyValue $propertyValueClassName*/
	    $propertyValueClassName = static::getPropertyValueClassName();
		$props = $propertyValueClassName::loadForEntity($this->getEntityParent());

		/** @var EntityPropertyValue $propertyValue */
		foreach ($this->collection as $propertyValue)
		{
			if (!$propertyValue->needDeleteOnRefresh())
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

		/** @var EntityPropertyValue $propertyValue */
		foreach ($props as $propertyValue)
		{
			if (!$this->getItemByOrderPropertyId($propertyValue->getPropertyId()))
			{
				$propertyValue->setCollection($this);
				$this->addItem($propertyValue);
			}
		}
	}

	/**
	 * @param $post
	 * @param $files
	 * @return Result
	 */
	public function setValuesFromPost($post, $files)
	{
		$post = File::getPostWithFiles($post, $files);

		$result = new Result();

		/** @var EntityPropertyValue $property */
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
	 */
	public function checkErrors($fields, $files, $skipUtils = false)
	{
		$result = new Result();

		$fields = File::getPostWithFiles($fields, $files);

		/** @var EntityPropertyValue $propertyValue */
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

		/** @var EntityPropertyValue $propertyValue */
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
	 * @internal
	 *
	 * @param $entityId
	 * @return Result
	 */
	public static function deleteNoDemand($entityId)
	{
		$result = new Result();
		$propertiesDataList = static::getAllItemsFromDb($entityId);

		foreach($propertiesDataList as $propertyValue)
		{
			$res  = self::delete($propertyValue);

			if (!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
			}
		}

		return $result;
	}
}