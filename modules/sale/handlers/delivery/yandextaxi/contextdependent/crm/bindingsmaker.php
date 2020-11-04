<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Crm\Order\Shipment;

/**
 * Class BindingsMaker
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm
 * @internal
 */
final class BindingsMaker
{
	/**
	 * @param Shipment $shipment
	 * @param string $prefix
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function makeByShipment(Shipment $shipment, string $prefix = 'ENTITY'): array
	{
		$result = [];

		/**
		 * Deal
		 */
		$dealId = $this->getDealId($shipment);
		if ($dealId)
		{
			$result[] = [
				sprintf('%s_TYPE_ID', $prefix) => \CCrmOwnerType::Deal,
				sprintf('%s_ID', $prefix) => $dealId,
			];
		}

		/**
		 * Order
		 */
		$orderId = $this->getOrderId($shipment);
		if ($orderId)
		{
			$result[] = [
				sprintf('%s_TYPE_ID', $prefix) => \CCrmOwnerType::Order,
				sprintf('%s_ID', $prefix) => $orderId,
			];
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return |null
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private function getDealId(Shipment $shipment)
	{
		$order = $shipment->getOrder();
		if (!$order)
		{
			return null;
		}

		$dealBinding = $order->getDealbinding();
		if (!$dealBinding)
		{
			return null;
		}

		return $dealBinding->getDealId();
	}

	/**
	 * @param Shipment $shipment
	 * @return int|null
	 */
	private function getOrderId(Shipment $shipment)
	{
		$order = $shipment->getOrder();

		return $order ? $order->getId() : null;
	}
}
