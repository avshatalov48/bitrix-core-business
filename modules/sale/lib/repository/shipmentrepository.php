<?php

namespace Bitrix\Sale\Repository;

use Bitrix\Sale;

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
	 */
	public function getById(int $id): ?Sale\Shipment
	{
		/** @var Sale\Shipment $shipmentClass */
		$shipmentClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getShipmentClassName();

		$shipmentRow = $shipmentClass::getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $id
			]
		])->fetch();
		if (!$shipmentRow)
		{
			return null;
		}

		return static::getInstance()->getByRow($shipmentRow);
	}

	/**
	 * @param array $ids
	 * @return array
	 */
	public function getByIds(array $ids): array
	{
		$result = [];

		/** @var Sale\Shipment $shipmentClass */
		$shipmentClass = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getShipmentClassName();

		$shipmentList = $shipmentClass::getList([
			'select' => ['ID', 'ORDER_ID'],
			'filter' => [
				'=ID' => $ids
			]
		]);

		while ($shipmentRow = $shipmentList->fetch())
		{
			$shipment = static::getInstance()->getByRow($shipmentRow);
			if (is_null($shipment))
			{
				continue;
			}

			$result[] = $shipment;
		}

		return $result;
	}

	/**
	 * @param array $shipmenRow
	 * @return Sale\Shipment|null
	 */
	private function getByRow(array $shipmenRow): ?Sale\Shipment
	{
		$orderClassName = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER)->getOrderClassName();

		/** @var Sale\Order $orderClassName */
		$order = $orderClassName::load($shipmenRow['ORDER_ID']);
		if ($order === null)
		{
			return null;
		}

		$shipmentCollection = $order->getShipmentCollection();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->getId() !== (int)$shipmenRow['ID'])
			{
				continue;
			}

			return $shipment;
		}

		return null;
	}
}
