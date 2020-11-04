<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

use Sale\Handlers\Delivery\YandexTaxi\Api\StatusDictionary;
use Bitrix\Sale\Delivery\Services\Taxi;

/**
 * Class StatusMapper
 * @package Sale\Handlers\Delivery\YandexTaxi\Common
 * @internal
 */
class StatusMapper
{
	/**
	 * @param string $externalStatus
	 * @return string
	 */
	public function getMappedStatus(string $externalStatus): string
	{
		if (in_array(
			$externalStatus,
			[
				StatusDictionary::NEW,
				StatusDictionary::ESTIMATING,
				StatusDictionary::READY_FOR_APPROVAL,
				StatusDictionary::ACCEPTED,
				StatusDictionary::PERFORMER_LOOKUP,
				StatusDictionary::PERFORMER_DRAFT,
			]
		))
		{
			return Taxi\StatusDictionary::SEARCHING;
		}
		elseif (
			in_array(
				$externalStatus,
				[
					StatusDictionary::FAILED,
					StatusDictionary::ESTIMATING_FAILED,
					StatusDictionary::PERFORMER_NOT_FOUND,
					StatusDictionary::RETURNED_FINISH,
				]
			)
			|| strpos($externalStatus, 'cancelled') === 0
		)
		{
			return Taxi\StatusDictionary::INITIAL;
		}
		elseif (in_array(
			$externalStatus,
			[
				StatusDictionary::PERFORMER_FOUND,
				StatusDictionary::PICKUP_ARRIVED,
				StatusDictionary::READY_FOR_PICKUP_CONFIRMATION,
				StatusDictionary::PICKUPED,
				StatusDictionary::PAY_WAITING,
				StatusDictionary::DELIVERY_ARRIVED,
				StatusDictionary::READY_FOR_DELIVERY_CONFIRMATION,
				StatusDictionary::DELIVERED,
				StatusDictionary::RETURNING,
				StatusDictionary::RETURN_ARRIVED,
				StatusDictionary::READY_FOR_RETURN_CONFIRMATION,
				StatusDictionary::RETURNED,
			]
		))
		{
			return Taxi\StatusDictionary::ON_ITS_WAY;
		}
		elseif ($externalStatus == StatusDictionary::DELIVERED_FINISH)
		{
			return Taxi\StatusDictionary::SUCCESS;
		}

		return Taxi\StatusDictionary::UNKNOWN;
	}
}
