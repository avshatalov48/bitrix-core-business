<?php

namespace Bitrix\Sale\Delivery\Services;

use Bitrix\Main;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

class OrderSavedListener
{
	/**
	 * @param Main\Event $event
	 */
	public function onOrderSaved(Main\Event $event)
	{
		$isNew = $event->getParameter('IS_NEW');

		if (!$isNew)
		{
			return;
		}

		/** @var Order $order */
		$order = $event->getParameter('ENTITY');

		$shouldNotifyDeliveryService = false;
		$deliveryService = null;

		$shipmentCollection = $order->getShipmentCollection();
		/** @var Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem())
			{
				continue;
			}
			$deliveryService = Manager::getObjectById($shipment->getDeliveryId());
			if (!$deliveryService)
			{
				continue;
			}

			if ($deliveryService instanceof NewOrderListenerContract)
			{
				$shouldNotifyDeliveryService = true;
				break;
			}
		}

		if ($deliveryService && $shouldNotifyDeliveryService)
		{
			$deliveryService->onNewOrder($order);
		}
	}
}
