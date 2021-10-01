<?php

namespace Sale\Handlers\Delivery\Rest\DataProviders;

use Bitrix\Sale;

/**
 * Class RecipientContact
 * @package Sale\Handlers\Delivery\Rest\DataProviders
 * @internal
 */
final class RecipientContact
{
	/**
	 * @param Shipment $shipment
	 * @return array|null
	 */
	public static function getData(Sale\Shipment $shipment): ?array
	{
		$recipientContact = Sale\Delivery\Services\RecipientDataProvider::getContact($shipment);
		if (!$recipientContact instanceof Sale\Delivery\Services\Contact)
		{
			return null;
		}

		$result = [
			'NAME' => $recipientContact->getName(),
		];

		$phones = $recipientContact->getPhones();
		if (!empty($phones))
		{
			$result['PHONES'] = [];
			foreach ($phones as $phone)
			{
				$result['PHONES'][] = [
					'TYPE' => $phone->getType(),
					'VALUE' => $phone->getValue(),
				];
			}
		}

		return $result;
	}
}
