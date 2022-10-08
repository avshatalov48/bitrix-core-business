<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketPropertyItemBase
 * @package Bitrix\Sale
 */
abstract class BasketPropertyItemBase extends Internals\CollectableEntity
{
	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return array(
			'NAME' => 'NAME',
			'VALUE' => 'VALUE',
			'CODE' => 'CODE',
			'SORT' => 'SORT',
			'XML_ID' => 'XML_ID'
		);
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array();
	}

	/**
	 * @return string|void
	 */
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_BASKET_PROPERTY_ITEM;
	}

	/**
	 * @param array $fields
	 * @return BasketPropertyItem
	 * @throws NotImplementedException
	 * @throws Main\ArgumentException
	 */
	private static function createBasketPropertyItemObject(array $fields = [])
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$basketPropertyItemClassName = $registry->getBasketPropertyItemClassName();

		return new $basketPropertyItemClassName($fields);
	}

	/**
	 * @param BasketPropertiesCollectionBase $basketPropertiesCollection
	 * @return BasketPropertyItem
	 * @throws NotImplementedException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function create(BasketPropertiesCollectionBase $basketPropertiesCollection)
	{
		$basketPropertyItem = static::createBasketPropertyItemObject();
		$basketPropertyItem->setCollection($basketPropertiesCollection);

		$basketPropertyItem->setField('XML_ID', static::generateXmlId());

		return $basketPropertyItem;
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @internal
	 *
	 * @return Result
	 * @throws NotImplementedException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function save()
	{
		$this->checkCallingContext();

		$result = new Result();

		$id = $this->getId();

		$fields = $this->fields->getChangedValues();

		if (!empty($fields) && is_array($fields))
		{
			$map = static::getFieldsMap();
			foreach ($map as $key => $value)
			{
				if ($value instanceof Main\Entity\StringField)
				{
					$fieldName = $value->getName();
					if (array_key_exists($fieldName, $fields))
					{
						if (!empty($fields[$fieldName]) && mb_strlen($fields[$fieldName]) > $value->getSize())
						{
							$fields[$fieldName] = mb_substr($fields[$fieldName], 0, $value->getSize());
						}
					}
				}
			}
		}

		if ($id > 0)
		{
			if (!empty($fields) && is_array($fields))
			{
				$r = $this->updateInternal($id, $fields);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}

				if ($resultData = $r->getData())
				{
					$result->setData($resultData);
				}
			}
		}
		else
		{
			/** @var BasketPropertiesCollectionBase $collection */
			$collection = $this->getCollection();
			$basketItem = $collection->getBasketItem();
			$fields['BASKET_ID'] = ($basketItem) ? $basketItem->getId() : 0;
			$this->setFieldNoDemand('BASKET_ID', $fields['BASKET_ID']);

			$r = $this->addInternal($fields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($resultData = $r->getData())
			{
				$result->setData($resultData);
			}

			$id = $r->getId();
			$this->setFieldNoDemand('ID', $id);
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		return $result;
	}

	/*
	 * @return void
	 */
	private function checkCallingContext()
	{
		/** @var BasketPropertiesCollectionBase $collection */
		$collection = $this->getCollection();

		$basketItem = $collection->getBasketItem();

		$basket = $basketItem->getBasket();

		$order = $basket->getOrder();

		if ($order)
		{
			if (!$order->isSaveRunning())
			{
				trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Order entity", E_USER_WARNING);
			}
		}
		else
		{
			if (!$basket->isSaveRunning())
			{
				trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Basket entity", E_USER_WARNING);
			}
		}
	}

	/**
	 * @return Result
	 * @throws NotImplementedException
	 */
	public function verify()
	{
		$result = new Result();

		$map = static::getFieldsMap();

		$fieldValues = $fields = $this->fields->getValues();

		$propertyName = (!empty($fieldValues['NAME'])) ? $fieldValues['NAME'] : "";
		if ($this->getId() > 0)
		{
			$fields = $this->fields->getChangedValues();
		}

		foreach ($map as $key => $value)
		{
			if ($value instanceof Main\Entity\StringField)
			{
				$fieldName = $value->getName();
				if (!empty($fields[$fieldName]) && mb_strlen($fields[$fieldName]) > $value->getSize())
				{
					if ($fieldName === 'NAME')
					{
						$propertyName = mb_substr($propertyName, 0, 50)."...";
					}

					$result->addError(
						new ResultWarning(
							Loc::getMessage(
								"SALE_BASKET_ITEM_PROPERTY_MAX_LENGTH_ERROR",
								array(
									"#PROPERTY_NAME#" => $propertyName,
									"#FIELD_TITLE#" => $fieldName,
									"#MAX_LENGTH#" => $value->getSize()
								)
							),
							'SALE_BASKET_ITEM_PROPERTY_MAX_LENGTH_ERROR'
						)
					);
				}
			}
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws NotImplementedException
	 */
	public static function loadForBasketItem($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException("id");
		}

		$result = [];

		$dbRes = static::getList([
			'filter' => ["=BASKET_ID" => $id],
			'order' => ["SORT" => "ASC", "ID" => "ASC"],
		]);

		while ($property = $dbRes->fetch())
		{
			$result[] = static::createBasketPropertyItemObject($property);
		}

		return $result;
	}

	/**
	 * @param $idList
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws NotImplementedException
	 */
	public static function loadForBasket($idList)
	{
		$result = [];

		$dbRes = static::getList(
			array(
				'filter' => array("@BASKET_ID" => $idList),
				'order' => array("SORT" => "ASC", "ID" => "ASC"),
			)
		);

		while ($property = $dbRes->fetch())
		{
			$result[$property['BASKET_ID']][] = static::createBasketPropertyItemObject($property);
		}

		return $result;
	}



	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 */
	abstract protected function addInternal(array $data);

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 */
	abstract protected function updateInternal($primary, array $data);

	/**
	 * @param array $parameters
	 * @throws NotImplementedException
	 */
	public static function getList(array $parameters = [])
	{
		throw new NotImplementedException();
	}

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SaleBasketPropertyItem';
	}
}