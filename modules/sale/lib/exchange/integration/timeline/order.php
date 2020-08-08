<?php
namespace Bitrix\Sale\Exchange\Integration\Timeline;

use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\OrderStatus;

Loc::loadMessages(__FILE__);

class Order extends Base
{
	static public function statusNotify(Event $event)
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $event->getParameters()['ENTITY'];

		if(static::isSync($order) == true)
		{
			if($order->isNew() == false)
			{
				$idOld = $order->getFields()->getOriginalValues()['STATUS_ID'] ?? '';
				$nameOld = OrderStatus::getAllStatusesNames()[$idOld] ?? $idOld;
				$id = $order->getField('STATUS_ID');
				$name = OrderStatus::getAllStatusesNames()[$id] ?? $id;

				$settings = [
					'ENTITY_TYPE_ID' => Integration\CRM\EntityType::ORDER,
					'FIELD_NAME' => 'STATUS_ID',
					'CURRENT_VALUE' => $name,
					'PREVIOUS_VALUE' => $nameOld
				];

				static::onReceive($order->getId(), $settings);
			}
		}
	}
	static public function canceledNotify(Event $event)
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $event->getParameters()['ENTITY'];

		if(static::isSync($order) == true)
		{
			if($order->isCanceled())
			{
				$settings = [
					'ENTITY_TYPE_ID' => Integration\CRM\EntityType::ORDER,
					'FIELD_NAME' => 'CANCELED',
					'CURRENT_VALUE' => 'Y',
					'LEGEND' => Loc::getMessage('SALE_INTEGRATION_B24_TIMELINE_ORDER_NUMBER').$order->getId(),
				];

				static::onReceive($order->getId(), $settings);
			}
		}
	}
}