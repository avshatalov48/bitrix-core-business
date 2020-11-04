<?php

namespace Bitrix\Sale\Repository;

use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Shipment;

/**
 * Class ShipmentRepository
 * @package Bitrix\Sale\Repository
 * @internal
 */
final class ShipmentRepository
{
	/** @var ShipmentRepository */
	private static $instance;

	/**
	 * ShipmentRepository constructor.
	 */
	private function __construct()
	{}

	/**
	 * @return ShipmentRepository
	 */
	public static function getInstance(): ShipmentRepository
	{
		if (is_null(static::$instance))
		{
			static::$instance = new ShipmentRepository();
		}

		return static::$instance;
	}

	/**
	 * @param int $id
	 * @return \Bitrix\Sale\Shipment|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getById(int $id): ?Shipment
	{
		$shipmentRow = ShipmentTable::getById($id)->fetch();
		if (!$shipmentRow)
		{
			return null;
		}

		$orderClassName = Registry::getInstance(Registry::ENTITY_ORDER)->getOrderClassName();

		/** @var Order $orderClassName */
		$order = $orderClassName::load($shipmentRow['ORDER_ID']);
		if (is_null($order))
		{
			return null;
		}

		$shipmentCollection = $order->getShipmentCollection();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->getId() !== $id)
			{
				continue;
			}

			return $shipment;
		}

		return null;
	}
}
