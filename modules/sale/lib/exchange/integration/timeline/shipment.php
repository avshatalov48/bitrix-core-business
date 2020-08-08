<?php


namespace Bitrix\Sale\Exchange\Integration\Timeline;


use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\DeliveryStatus;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\ShipmentCollection;

Loc::loadMessages(__FILE__);

class Shipment extends Base
{
	static public function statusNotify(Event $event)
	{
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $event->getParameters()['ENTITY'];

		/** @var ShipmentCollection $collection */
		$collection = $shipment->getCollection();
		$order = $collection->getOrder();

		if(static::isSync($order) == true)
		{
			$idOld = $shipment->getFields()->getOriginalValues()['STATUS_ID'] ?? '';
			$nameOld = DeliveryStatus::getAllStatusesNames()[$idOld] ?? $idOld;
			$id = $shipment->getField('STATUS_ID');
			$name = DeliveryStatus::getAllStatusesNames()[$id] ?? $id;

			$settings = [
				'ENTITY_TYPE_ID' => Integration\CRM\EntityType::ORDER_SHIPMENT,
				'FIELD_NAME' => 'STATUS_ID',
				'CURRENT_VALUE' => $name,
				'PREVIOUS_VALUE' => $nameOld,
				'LEGEND' => Loc::getMessage('SALE_INTEGRATION_B24_TIMELINE_SHIPMENT_NUMBER').$shipment->getId().'. '.$shipment->getDeliveryName(),
			];

			static::onReceive($order->getId(), $settings);
		}
	}
	static public function allowDeliveryNotify(Event $event)
	{
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $event->getParameters()['ENTITY'];

		if($shipment->isAllowDelivery())
		{
			/** @var ShipmentCollection $collection */
			$collection = $shipment->getCollection();
			$order = $collection->getOrder();

			if(static::isSync($order) == true)
			{
				$settings = [
					'ENTITY_TYPE_ID' => Integration\CRM\EntityType::ORDER_SHIPMENT,
					'FIELD_NAME' => 'ALLOW_DELIVERY',
					'CURRENT_VALUE' => 'Y',
					'LEGEND' => Loc::getMessage('SALE_INTEGRATION_B24_TIMELINE_SHIPMENT_NUMBER').$shipment->getId().'. '.$shipment->getDeliveryName(),
				];

				static::onReceive($order->getId(), $settings);
			}
		}
	}
	static public function deductedNotify(Event $event)
	{
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $event->getParameters()['ENTITY'];

		if($shipment->isShipped())
		{
			/** @var ShipmentCollection $collection */
			$collection = $shipment->getCollection();
			$order = $collection->getOrder();

			if(static::
				isSync($order) == true)
			{
				$settings = [
					'ENTITY_TYPE_ID' => Integration\CRM\EntityType::ORDER_SHIPMENT,
					'FIELD_NAME' => 'DEDUCTED',
					'CURRENT_VALUE' => 'Y',
					'LEGEND' => Loc::getMessage('SALE_INTEGRATION_B24_TIMELINE_SHIPMENT_NUMBER').$shipment->getId().'. '.$shipment->getDeliveryName(),
				];

				static::onReceive($order->getId(), $settings);
			}
		}
	}
}