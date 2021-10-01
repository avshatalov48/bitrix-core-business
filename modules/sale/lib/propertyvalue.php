<?php

namespace Bitrix\Sale;

use Bitrix\Sale\Internals\Entity;
use Bitrix\Main;

/**
 * Class PropertyValue
 * @package Bitrix\Sale
 */
class PropertyValue extends PropertyValueBase
{
	/**
	 * @param Entity $order
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function extractPaySystemIdList(Entity $order)
	{
		if (!$order instanceof Order)
		{
			return [];
		}

		return $order->getPaySystemIdList();
	}

	/**
	 * @param Entity $order
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	protected static function extractDeliveryIdList(Entity $order)
	{
		if (!$order instanceof Order)
		{
			return [];
		}

		return $order->getDeliveryIdList();
	}

	protected static function extractTpLandingIdList(Entity $order) : array
	{
		if (!$order instanceof Order)
		{
			return [];
		}

		return $order->getTradeBindingCollection()->getTradingPlatformIdList();
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
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

		$logFields = [];
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
		$logFields = [
			"NAME" => $this->getField("NAME"),
			"VALUE" => $this->getField("VALUE"),
			"CODE" => $this->getField("CODE"),
		];

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
		$logFields = [
			"NAME" => $this->getField("NAME"),
			"VALUE" => $this->getField("VALUE"),
			"CODE" => $this->getField("CODE"),
		];

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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
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

		$logFields = [];
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
}
