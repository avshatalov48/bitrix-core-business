<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Sale;

use Bitrix\Main\Entity;
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
			'NAME',
			'VALUE',
			'CODE',
			'SORT',
			'XML_ID'
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
	 * @return int
	 */
	public function getId()
	{
		return $this->getField('ID');
	}

	/**
	 * @throws NotImplementedException
	 */
	public static function getRegistryType()
	{
		throw new NotImplementedException();
	}

	/**
	 * @return BasketPropertyItem
	 */
	private static function createBasketPropertyItemObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$basketPropertyItemClassName = $registry->getBasketPropertyItemClassName();

		return new $basketPropertyItemClassName();
	}

	/**
	 * @param BasketPropertiesCollectionBase $basketPropertiesCollection
	 * @return static
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
	 * @return Result
	 */
	public function save()
	{
		$result = new Result();
		static $map = array();

		$id = $this->getId();

		if (empty($map))
		{
			$map = static::getFieldsMap();
		}

		if ($id > 0)
		{
			$fields = $this->fields->getChangedValues();
		}
		else
		{
			$fields = $this->fields->getValues();
		}

		if (!empty($fields) && is_array($fields))
		{
			foreach ($map as $key => $value)
			{
				if ($value instanceof Entity\StringField)
				{
					$fieldName = $value->getName();
					if (array_key_exists($fieldName, $fields))
					{
						if (!empty($fields[$fieldName]) && strlen($fields[$fieldName]) > $value->getSize())
						{
							$fields[$fieldName] = substr($fields[$fieldName], 0, $value->getSize());
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
					$result->setData($resultData);
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
				$result->setData($resultData);

			$id = $r->getId();
			$this->setFieldNoDemand('ID', $id);
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function verify()
	{
		$result = new Result();

		static $map = array();

		if (empty($map))
		{
			$map = static::getFieldsMap();
		}

		$fieldValues = $fields = $this->fields->getValues();

		$propertyName = (!empty($fieldValues['NAME'])) ? $fieldValues['NAME'] : "";
		if ($this->getId() > 0)
		{
			$fields = $this->fields->getChangedValues();
		}

		foreach ($map as $key => $value)
		{
			if ($value instanceof Entity\StringField)
			{
				$fieldName = $value->getName();
				if (array_key_exists($fieldName, $fields))
				{
					if (array_key_exists($fieldName, $fields))
					{
						if (!empty($fields[$fieldName]) && strlen($fields[$fieldName]) > $value->getSize())
						{
							if ($fieldName === 'NAME')
							{
								$propertyName = substr($propertyName, 0, 50)."...";
							}

							$result->addError(new ResultWarning(Loc::getMessage("SALE_BASKET_ITEM_PROPERTY_MAX_LENGTH_ERROR", array("#PROPERTY_NAME#" => $propertyName, "#FIELD_TITLE#" => $fieldName, "#MAX_LENGTH#" => $value->getSize())), 'SALE_BASKET_ITEM_PROPERTY_MAX_LENGTH_ERROR'));
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return Entity\AddResult
	 */
	abstract protected function addInternal(array $data);

	/**
	 * @param $primary
	 * @param array $data
	 * @return Entity\UpdateResult
	 */
	abstract protected function updateInternal($primary, array $data);

}