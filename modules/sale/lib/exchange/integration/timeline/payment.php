<?php


namespace Bitrix\Sale\Exchange\Integration\Timeline;


use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration;
use Bitrix\Sale\PaymentCollection;

Loc::loadMessages(__FILE__);

class Payment extends Base
{
	static public function paidNotify(Event $event)
	{
		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $event->getParameters()['ENTITY'];

		/** @var PaymentCollection $colletion */
		$colletion = $payment->getCollection();
		$order = $colletion->getOrder();

		if(static::isSync($order) == true)
		{
			if($payment->isPaid())
			{
				$settings = [
					'ENTITY_TYPE_ID' =>Integration\CRM\EntityType::ORDER_PAYMENT,
					'FIELD_NAME' => 'PAID',
					'CURRENT_VALUE' => 'Y',
					'LEGEND' => Loc::getMessage('SALE_INTEGRATION_B24_TIMELINE_PAYMENT_NUMBER').$payment->getId().'. '.$payment->getPaymentSystemName(),
				];

				static::onReceive($order->getId(), $settings);
			}
		}
	}
}