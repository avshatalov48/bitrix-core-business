<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Sale\Handlers\Delivery\Taxi\Status\Initial;
use Sale\Handlers\Delivery\Taxi\Status\OnItsWay;
use Sale\Handlers\Delivery\Taxi\Status\Searching;
use Sale\Handlers\Delivery\Taxi\Status\StatusContract;
use Sale\Handlers\Delivery\Taxi\Status\Success;
use Sale\Handlers\Delivery\Taxi\Status\Unknown;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\StatusDictionary;

/**
 * Class StatusMapper
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class StatusMapper
{
	/**
	 * @param string $externalStatus
	 * @return StatusContract
	 */
	public function getMappedStatus(string $externalStatus): StatusContract
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
			return new Searching();
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
			return new Initial();
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
			return new OnItsWay();
		}
		elseif ($externalStatus == StatusDictionary::DELIVERED_FINISH)
		{
			return new Success();
		}

		return new Unknown();
	}
}
