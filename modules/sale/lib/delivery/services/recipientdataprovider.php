<?php

namespace Bitrix\Sale\Delivery\Services;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Sale\EventActions;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

/**
 * Class RecipientDataProvider
 * @package Bitrix\Sale\Delivery\Services
 * @internal
 */
final class RecipientDataProvider
{
	/**
	 * @param Shipment $shipment
	 * @return Contact|null
	 */
	public static function getContact(Shipment $shipment): ?Contact
	{
		$event = new Event(
			'sale',
			EventActions::EVENT_ON_NEED_DELIVERY_RECIPIENT_CONTACT,
			['SHIPMENT' => $shipment]
		);

		$event->send();
		$eventResults = $event->getResults();
		foreach ($eventResults as $eventResult)
		{
			if ($eventResult->getType() === EventResult::SUCCESS)
			{
				$recipientContact = $eventResult->getParameters();
				if ($recipientContact instanceof Contact)
				{
					return $recipientContact;
				}
			}
		}

		$order = $shipment->getOrder();
		if (!$order)
		{
			return null;
		}

		return self::getContactFromOrder($order);
	}

	/**
	 * @param Order $order
	 * @return Contact|null
	 */
	private static function getContactFromOrder(Order $order): ?Contact
	{
		$contact = new Contact();

		$buyerName = $order->getPropertyCollection()->getAttribute('IS_PAYER');
		if ($buyerName)
		{
			$contact->setName((string)$buyerName->getValue());
		}

		$buyerPhone = $order->getPropertyCollection()->getAttribute('IS_PHONE');
		if ($buyerPhone)
		{
			$contact->addPhone(new Phone('OTHER', (string)$buyerPhone->getValue()));
		}

		return $contact;
	}
}
