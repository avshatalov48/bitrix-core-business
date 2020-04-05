<?php
namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\Entity\DeleteResult;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketPropertiesCollectionBase
 * @package Bitrix\Sale
 */
abstract class BasketPropertiesCollectionBase extends Internals\EntityCollection
{
	/** @var BasketItemBase */
	protected $basketItem;

	/**
	 * @return BasketItemBase
	 */
	protected function getEntityParent()
	{
		return $this->getBasketItem();
	}

	/**
	 * @param BasketItemBase $basketItem
	 */
	public function setBasketItem(BasketItemBase $basketItem)
	{
		$this->basketItem = $basketItem;
	}

	/**
	 * @return BasketItemBase
	 */
	public function getBasketItem()
	{
		return $this->basketItem;
	}

	/**
	 * @return BasketPropertiesCollection
	 * @throws Main\ArgumentException
	 * @throws NotImplementedException
	 */
	private static function createBasketPropertiesCollectionObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$basketPropertiesCollectionClassName = $registry->getBasketPropertiesCollectionClassName();

		return new $basketPropertiesCollectionClassName();
	}

	/**
	 * @param BasketItemBase $basketItem
	 * @return BasketPropertiesCollectionBase|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 * @throws NotImplementedException
	 * @throws ObjectNotFoundException
	 */
	public static function load(BasketItemBase $basketItem)
	{
		$basketPropertyCollection = static::createBasketPropertiesCollectionObject();
		$basketPropertyCollection->setBasketItem($basketItem);
		$basketItem->setPropertyCollection($basketPropertyCollection);

		if ($basketItem->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var BasketPropertyItemBase $basketPropertyItemClass */
			$basketPropertyItemClass = $registry->getBasketPropertyItemClassName();

			$propertyList = $basketPropertyItemClass::loadForBasketItem($basketItem->getId());
			/** @var BasketPropertyItemBase $property */
			foreach ($propertyList as $property)
			{
				$property->setCollection($basketPropertyCollection);
				$basketPropertyCollection->addItem($property);
			}
		}

		return $basketItem->getPropertyCollection();
	}

	/**
	 * @param BasketItemCollection $basket
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	protected static function getBasketIdList(BasketItemCollection $basket)
	{
		$resultList = array();

		/** @var BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			if ($basketItem->getId() > 0)
			{
				$resultList[] = $basketItem->getId();
			}
		}

		return $resultList;
	}

	/**
	 * @param BasketItemCollection $collection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws NotImplementedException
	 */
	public static function loadByCollection(BasketItemCollection $collection)
	{
		$propertyList = [];

		$basketIdList = static::getBasketIdList($collection);

		if (!empty($basketIdList))
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var BasketPropertyItemBase $basketPropertyItemClass */
			$basketPropertyItemClass = $registry->getBasketPropertyItemClassName();

			$propertyList = $basketPropertyItemClass::loadForBasket($basketIdList);
		}

		/** @var BasketItemBase $basketItem */
		foreach ($collection as $basketItem)
		{
			if ($basketItem->isExistPropertyCollection())
			{
				continue;
			}

			$basketPropertyCollection = static::createBasketPropertiesCollectionObject();
			$basketPropertyCollection->setBasketItem($basketItem);

			if (isset($propertyList[$basketItem->getId()]))
			{
				/** @var BasketPropertyItemBase $property */
				foreach ($propertyList[$basketItem->getId()] as $property)
				{
					$property->setCollection($basketPropertyCollection);
					$basketPropertyCollection->addItem($property);
				}
			}

			$basketItem->setPropertyCollection($basketPropertyCollection);
		}
	}

	/**
	 * @return BasketPropertyItem
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws NotImplementedException
	 */
	public function createItem()
	{
		$registry  = Registry::getInstance(static::getRegistryType());

		/** @var BasketPropertyItemBase $basketPropertyItemClassName */
		$basketPropertyItemClassName = $registry->getBasketPropertyItemClassName();

		$basketPropertyItem = $basketPropertyItemClassName::create($this);
		$this->addItem($basketPropertyItem);

		return $basketPropertyItem;
	}

	/**
	 * @param BasketPropertyItemBase $property
	 * @return string
	 */
	private function getPropertyCode(BasketPropertyItemBase $property)
	{
		return $property->getField('NAME')."|".$property->getField("CODE");
	}

	/**
	 * @param array $properties
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws NotImplementedException
	 * @throws ObjectNotFoundException
	 */
	public function redefine(array $properties)
	{
		$indexList = array();

		/** @var BasketPropertyItemBase $propertyItem */
		foreach($this->collection as $propertyItem)
		{
			$code = $this->getPropertyCode($propertyItem);
			$indexList[$code] = $propertyItem->getId();
		}

		foreach ($properties as $value)
		{
			if (!is_array($value) || empty($value))
			{
				continue;
			}

			if (isset($value['ID']) && intval($value['ID']) > 0)
			{
				$propertyItem = $this->getItemById($value['ID']);
			}
			else
			{
				$propertyItem = $this->getPropertyItemByValue($value);
			}

			if (!$propertyItem)
			{
				$propertyItem = $this->createItem();
			}
			else
			{
				$code = $this->getPropertyCode($propertyItem);
				if (isset($indexList[$code]))
				{
					unset($indexList[$code]);
				}
			}

			$availableFields = $propertyItem::getAvailableFields();

			$fields = array();
			foreach ($value as $k => $v)
			{
				if (isset($availableFields[$k]))
				{
					$fields[$k] = $v;
				}
			}

			$propertyItem->setFields($fields);
		}

		if (!empty($indexList))
		{
			foreach($indexList as $code => $id)
			{
				if ($id > 0)
				{
					/** @var BasketPropertyItemBase $propertyItem */
					if ($propertyItem = $this->getItemById($id))
					{
						if ($propertyItem->getField('CODE') == "CATALOG.XML_ID"
							|| $propertyItem->getField('CODE') == "PRODUCT.XML_ID"
						)
						{
							continue;
						}

						$propertyItem->delete();
					}
				}
				else
				{
					/** @var BasketPropertyItemBase $propertyItem */
					foreach ($this->collection as $propertyItem)
					{
						if ($propertyItem->getField('CODE') == "CATALOG.XML_ID"
							|| $propertyItem->getField('CODE') == "PRODUCT.XML_ID"
						)
						{
							continue;
						}

						$propertyCode = $this->getPropertyCode($propertyItem);
						if ($propertyCode == $code)
						{
							$propertyItem->delete();
						}
					}
				}
			}
		}
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws NotImplementedException
	 */
	public function save()
	{
		$result = new Sale\Result();

		$itemsFromDb = [];

		$isItemDeleted = $this->isAnyItemDeleted();
		if ($isItemDeleted)
		{
			$basketItem = $this->getBasketItem();
			$itemsFromDbList = static::getList(
				[
					"select" => ["ID"],
					"filter" => ["BASKET_ID" => ($basketItem) ? $basketItem->getId() : 0]
				]
			);

			while ($itemsFromDbItem = $itemsFromDbList->fetch())
			{
				$itemsFromDb[$itemsFromDbItem["ID"]] = true;
			}
		}

		/** @var BasketPropertyItemBase $basketProperty */
		foreach ($this->collection as $basketProperty)
		{
			$r = $basketProperty->save();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			unset($itemsFromDb[$basketProperty->getId()]);
		}

		foreach ($itemsFromDb as $basketPropertyId => $value)
		{
			static::delete($basketPropertyId);
		}

		if ($isItemDeleted)
		{
			$this->setAnyItemDeleted(false);
		}

		return $result;
	}

	/**
	 * @param array $values
	 * @return bool
	 */
	public function isPropertyAlreadyExists(array $values)
	{
		if (!($propertyValues = $this->getPropertyValues()))
		{
			return false;
		}

		$requestValues = array();
		foreach ($values as $value)
		{
			if (!($propertyValue = static::bringingPropertyValue($value)))
			{
				continue;
			}

			$requestValues[$propertyValue['CODE']] = $propertyValue["VALUE"];
		}

		if (count($requestValues) !== count($propertyValues))
		{
			return false;
		}
		else
		{
			foreach($requestValues as $key => $val)
			{
				if (!array_key_exists($key, $propertyValues) || (array_key_exists($key, $propertyValues) && $propertyValues[$key]['VALUE'] != $val))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param array $value
	 * @return BasketPropertyItemBase|bool
	 */
	public function getPropertyItemByValue(array $value)
	{
		if (!($propertyValue = static::bringingPropertyValue($value)))
		{
			return false;
		}

		/** @var BasketPropertyItemBase $propertyItem */
		foreach ($this->collection as $propertyItem)
		{
			$propertyItemValues = $propertyItem->getFieldValues();

			if (!($propertyItemValue = static::bringingPropertyValue($propertyItemValues)))
			{
				continue;
			}


			if ($propertyItemValue['CODE'] == $propertyValue['CODE'])
			{
				return $propertyItem;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getPropertyValues()
	{
		$result = array();

		/** @var BasketPropertyItemBase $property */
		foreach($this->collection as $property)
		{
			$value = $property->getFieldValues();
			$propertyValue = static::bringingPropertyValue($value);
			if (!$propertyValue)
			{
				continue;
			}

			$result[$propertyValue['CODE']] = $propertyValue;
		}

		return $result;
	}


	/**
	 * @param array $value
	 * @return array
	 */
	private static function bringingPropertyValue(array $value)
	{
		$result = array();
		if (array_key_exists('VALUE', $value))
		{
			$propID = '';
			if (array_key_exists('CODE', $value) && strval($value["CODE"]) != '')
			{
				$propID = $value["CODE"];
			}
			elseif (array_key_exists('NAME', $value) && strval($value["NAME"]) != '')
			{
				$propID = $value["NAME"];
			}

			if (strval($propID) != '')
			{
				$result = array(
					'CODE' => $propID,
					'ID' => $value["ID"],
					'VALUE' => $value["VALUE"],
					'SORT' => $value["SORT"],
					'NAME' => $value["NAME"],
				);
			}
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return BasketPropertiesCollectionBase|Internals\EntityCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @throws NotImplementedException
	 * @throws ObjectNotFoundException
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		/** @var BasketPropertiesCollectionBase $basketPropertiesCollectionClone */
		$basketPropertiesCollectionClone = parent::createClone($cloneEntity);

		/** @var BasketItem $basketItem */
		if ($basketItem = $this->basketItem)
		{
			if (!$cloneEntity->contains($basketItem))
			{
				$cloneEntity[$basketItem] = $basketItem->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($basketItem))
			{
				$basketPropertiesCollectionClone->basketItem = $cloneEntity[$basketItem];
			}
		}

		return $basketPropertiesCollectionClone;
	}

	/**
	 * @return Result
	 * @throws NotImplementedException
	 */
	public function verify()
	{
		$result = new Result();

		/** @var BasketPropertyItemBase $basketPropertyItem */
		foreach ($this->collection as $basketPropertyItem)
		{
			$r = $basketPropertyItem->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		return $result;
	}

	/**
	 * Load basket item properties.
	 *
	 * @param array $parameters
	 * @throws NotImplementedException
	 */
	public static function getList(array $parameters = array())
	{
		throw new NotImplementedException();
	}

	/**
	 * Delete basket item properties.
	 *
	 * @param $primary
	 * @throws NotImplementedException
	 */
	protected static function delete($primary)
	{
		throw new NotImplementedException();
	}

	/**
	 * @deprecated Use \Bitrix\Sale\BasketPropertiesCollectionBase::redefine instead
	 *
	 * @param array $values
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws NotImplementedException
	 * @throws ObjectNotFoundException
	 */
	public function setProperty(array $values)
	{
		$this->redefine($values);
	}
}