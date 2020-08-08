<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Api;

/**
 * Class ClaimCanceler
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ClaimCanceler
{
	/** @var Api */
	protected $api;

	/**
	 * CancelRequestSender constructor.
	 * @param Api $api
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @param string $externalId
	 * @return CancellationResult
	 */
	public function cancelClaim(string $externalId): CancellationResult
	{
		$result = new CancellationResult();

		$getClaimResult = $this->api->getClaim($externalId);

		if (!$getClaimResult->isSuccess())
		{
			return $result->addErrors($getClaimResult->getErrors());
		}

		$claim = $getClaimResult->getClaim();
		if (is_null($claim))
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_TMP_ERROR'))
			);
		}

		$availableCancelState = $claim->getAvailableCancelState();
		if (!$availableCancelState)
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_TMP_ERROR'))
			);
		}

		$cancellationResult = $this->api->cancelClaim(
			$externalId,
			$claim->getVersion(),
			$claim->getAvailableCancelState()
		);

		if (!$cancellationResult->isSuccess())
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_CANCELLATION_FATAL_ERROR'))
			);
		}

		return $result->setIsPaid(($availableCancelState == 'paid'));
	}
}
