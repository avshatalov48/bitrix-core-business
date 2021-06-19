<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Crm\ContactTable;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\Order\Shipment;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Contact;
use Bitrix\Main\PhoneNumber;

/**
 * Class ContactToRequiredListener
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm
 * @internal
 */
final class ContactToRequiredListener
{
	/**
	 * @param Event $event
	 */
	public function listen(Event $event)
	{
		/** @var Shipment $shipment */
		$shipment = $event->getParameter('SHIPMENT');

		$order = $shipment->getCollection()->getOrder();

		$deal = null;
		$dealBinding = $order->getDealBinding();
		if (
			$dealBinding
			&& ($dealId = (int)$dealBinding->getDealId())
			&& ($deal = DealTable::getById($dealId)->fetch())
		)
		{
			$contactId = $deal['CONTACT_ID'];
		}
		else
		{
			$contactCompanyCollection = $order->getContactCompanyCollection();
			if (!$contactCompanyCollection)
			{
				$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')));
				return;
			}

			$orderContact = $contactCompanyCollection->getPrimaryContact();
			if (!$orderContact)
			{
				$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')));
				return;
			}

			$contactId = (int)$orderContact->getField('ENTITY_ID');
		}

		if (!$contactId || !($contactRow = ContactTable::getById($contactId)->fetch()))
		{
			$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')));
			return;
		}

		if ($contactRow['HAS_PHONE'] !== 'Y')
		{
			$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_SPECIFIED')));
			return;
		}

		$phoneResults = \CCrmFieldMulti::GetEntityFields(
			'CONTACT',
			$contactRow['ID'],
			'PHONE',
			true
		);
		if (empty($phoneResults))
		{
			$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_SPECIFIED')));
			return;
		}

		$phoneResult = $phoneResults[0];
		if (!isset($phoneResult['VALUE']))
		{
			$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_SPECIFIED')));
			return;
		}

		$fullName = \CCrmContact::PrepareFormattedName(
			array(
				'HONORIFIC' => $contactRow['HONORIFIC'],
				'NAME' => $contactRow['NAME'],
				'LAST_NAME' => $contactRow['LAST_NAME'],
				'SECOND_NAME' => $contactRow['SECOND_NAME']
			)
		);
		$phone = $phoneResult['VALUE'];

		/**
		 * Validate client contact information
		 */
		if (!$fullName)
		{
			$event->addResult(
				new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_FULL_NAME_NOT_SPECIFIED'))
			);
			return;
		}
		if (!$phone)
		{
			$event->addResult(
				new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_SPECIFIED'))
			);
			return;
		}

		$oPhone = PhoneNumber\Parser::getInstance()->parse($phone);
		if (!$oPhone->isValid())
		{
			$event->addResult(
				new EventResult(
					EventResult::ERROR,
					sprintf(
						'%s: %s',
						Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_VALID'),
						(string)$oPhone->format()
					)
				)
			);
		}

		$event->addResult(
			new EventResult(
				EventResult::SUCCESS,
				(new Contact())
					->setName($fullName)
					->setPhone(PhoneNumber\Formatter::format($oPhone, PhoneNumber\Format::E164))
			)
		);
	}
}
