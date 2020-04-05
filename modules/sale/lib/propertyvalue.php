<?php

namespace Bitrix\Sale;

use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Main;

/**
 * Class PropertyValue
 * @package Bitrix\Sale
 */
class PropertyValue extends PropertyValueBase
{

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\OrderPropsValueTable::getMap();
	}

	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function update()
	{
		/** @var PropertyValueCollection $propertyCollection */
		$propertyCollection = $this->getCollection();

		/** @var OrderBase $order */
		if (!$order = $propertyCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$logFields = array();
		if ($order->getId() > 0)
		{
			$logFields = $this->getLogFieldsForUpdate();
		}

		$result = parent::update();
		if ($result->isSuccess())
		{
			if ($order->getId() > 0)
			{
				$this->addToLog('PROPERTY_UPDATE', $logFields);
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getLogFieldsForUpdate()
	{
		$logFields = array(
			"NAME" => $this->getField("NAME"),
			"VALUE" => $this->getField("VALUE"),
			"CODE" => $this->getField("CODE"),
		);

		$fields = $this->getFields();
		$originalValues = $fields->getOriginalValues();
		if (array_key_exists("NAME", $originalValues))
			$logFields['OLD_NAME'] = $originalValues["NAME"];

		if (array_key_exists("VALUE", $originalValues))
			$logFields['OLD_VALUE'] = $originalValues["VALUE"];

		if (array_key_exists("CODE", $originalValues))
			$logFields['OLD_CODE'] = $originalValues["CODE"];

		return $logFields;
	}

	/**
	 * @return array
	 */
	private function getLogFieldsForAdd()
	{
		$logFields = array(
			"NAME" => $this->getField("NAME"),
			"VALUE" => $this->getField("VALUE"),
			"CODE" => $this->getField("CODE"),
		);

		return $logFields;
	}

	/**
	 * @param $type
	 * @param $fields
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 */
	private function addToLog($type, $fields)
	{
		/** @var PropertyValueCollection $propertyCollection */
		$propertyCollection = $this->getCollection();

		/** @var OrderBase $order */
		if (!$order = $propertyCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::addLog(
			'PROPERTY',
			$order->getId(),
			$type,
			$this->getId(),
			$this,
			$fields,
			$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
		);
	}

	/**
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function add()
	{
		/** @var PropertyValueCollection $propertyCollection */
		$propertyCollection = $this->getCollection();

		/** @var OrderBase $order */
		if (!$order = $propertyCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$logFields = array();
		if ($order->getId() > 0)
		{
			$logFields = $this->getLogFieldsForAdd();
		}

		$result = parent::add();
		if ($result->isSuccess())
		{
			if ($order->getId() > 0)
			{
				$this->addToLog('PROPERTY_ADD', $logFields);
			}
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

	/**
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\AddResult
	 */
	protected function addInternal(array $data)
	{
		return OrderPropsValueTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @throws Main\NotImplementedException
	 * @return Main\Entity\UpdateResult
	 */
	protected function updateInternal($primary, array $data)
	{
		return OrderPropsValueTable::update($primary, $data);
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result|Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\OrderPropsValueTable::getList($parameters);
	}
}
