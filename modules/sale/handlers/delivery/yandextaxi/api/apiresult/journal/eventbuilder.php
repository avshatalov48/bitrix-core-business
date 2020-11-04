<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal;

/**
 * Class EventBuilder
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal
 * @internal
 */
final class EventBuilder
{
	/**
	 * @param array $fields
	 * @return Event|null
	 */
	public function build(array $fields)
	{
		if (!isset($fields['change_type']))
		{
			return null;
		}

		$event = null;
		$code = $fields['change_type'];

		switch ($code)
		{
			case StatusChanged::EVENT_CODE:
				$event = (new StatusChanged())
					->setNewStatus($fields['new_status']);

				if (isset($fields['resolution']))
				{
					$event->setResolution($fields['resolution']);
				}
				break;
			case PriceChanged::EVENT_CODE:
				$event = (new PriceChanged())
					->setNewPrice($fields['new_price'])
					->setNewCurrency($fields['new_currency']);
				break;
		}

		if (!is_null($event))
		{
			$event
				->setClaimId($fields['claim_id'])
				->setUpdatedTs($fields['updated_ts']);
		}

		return $event;
	}
}
