<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Sale\Internals\ShipmentTable;
use Bitrix\Sale\Order;

/**
 * Trait CanGetShipmentByOrder
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
trait CanGetShipment
{
	/**
	 * @param Order $order
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function getByOrder(Order $order)
	{
		$shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
		foreach ($shipmentCollection as $shipment)
		{
			return $shipment;
		}

		return null;
	}

	/**
	 * @param int $shipmentId
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getByShipmentId(int $shipmentId)
	{
		$shipmentRecord = ShipmentTable::getById($shipmentId)->fetch();

		if (!$shipmentRecord)
		{
			return null;
		}

		return $this->getByOrder(Order::load($shipmentRecord['ORDER_ID']));
	}
}
