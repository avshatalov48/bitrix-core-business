<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Crm;

use Bitrix\Crm\ContactTable;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Contact;

/**
 * Class NeedContactToListener
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Crm
 */
class NeedContactToListener
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
		if ($dealBinding && ($dealId = (int)$dealBinding->getDealId()))
		{
			$deal = DealTable::getList(
				[
					'filter' => ['=ID' => $dealId],
					'select' => [
						'RESPONSIBLE_EMAIL' => 'ASSIGNED_BY.EMAIL',
						'RESPONSIBLE_NAME' => 'ASSIGNED_BY.NAME',
						'RESPONSIBLE_LAST_NAME' => 'ASSIGNED_BY.LAST_NAME',
						'RESPONSIBLE_PHONE' => 'ASSIGNED_BY.WORK_PHONE',
						'CONTACT_NAME' => 'CONTACT.NAME',
						'CONTACT_SECOND_NAME' => 'CONTACT.SECOND_NAME',
						'CONTACT_LAST_NAME' => 'CONTACT.LAST_NAME',
						'CONTACT_EMAIL' => 'CONTACT.EMAIL_WORK',
						'CONTACT_PHONE_MOBILE' => 'CONTACT.PHONE_MOBILE',
						'CONTACT_PHONE_WORK' => 'CONTACT.PHONE_WORK',
						'*',
					]
				]
			)->fetch();
		}

		$fullName = null;
		$phone = null;

		if ($deal)
		{
			$fullName = trim(sprintf('%s %s', $deal['CONTACT_NAME'], $deal['CONTACT_LAST_NAME']));
			$phone = $deal['CONTACT_PHONE_WORK'] ? $deal['CONTACT_PHONE_WORK'] : $deal['CONTACT_PHONE_MOBILE'];
		}
		else
		{
			$contactCompanyCollection = $order->getContactCompanyCollection();
			if (!$contactCompanyCollection)
			{
				$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')));
				return;
			}

			$primaryContact = $contactCompanyCollection->getPrimaryContact();
			if (!$primaryContact)
			{
				$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')));
				return;
			}

			$primaryContactId = (int)$primaryContact->getField('ENTITY_ID');
			if (!$primaryContactId || !($primaryContact = ContactTable::getById($primaryContactId)->fetch()))
			{
				$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')));
				return;
			}

			if ($primaryContact['HAS_PHONE'] != 'Y')
			{
				$event->addResult(new EventResult(EventResult::ERROR, Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_SPECIFIED')));
				return;
			}

			$phoneResults = \CCrmFieldMulti::GetEntityFields(
				'CONTACT',
				$primaryContact['ID'],
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
					'HONORIFIC' => $primaryContact['HONORIFIC'],
					'NAME' => $primaryContact['NAME'],
					'LAST_NAME' => $primaryContact['LAST_NAME'],
					'SECOND_NAME' => $primaryContact['SECOND_NAME']
				)
			);
			$phone = $phoneResult['VALUE'];
		}

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

		$event->addResult(
			new EventResult(
				EventResult::SUCCESS,
				(new Contact())
					->setName($fullName)
					->setPhone($phone)
			)
		);
	}
}
