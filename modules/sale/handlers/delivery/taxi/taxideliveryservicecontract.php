<?php

namespace Sale\Handlers\Delivery\Taxi;

use Bitrix\Main\Result;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Yandex\CancellationResult;

/**
 * Interface Taxi
 * @package Sale\Handlers\Delivery\Taxi
 */
interface TaxiDeliveryServiceContract
{
	/**
	 * @param Shipment $shipment
	 * @return SendTaxiRequestResult
	 */
	public function sendTaxiRequest(Shipment $shipment): SendTaxiRequestResult;

	/**
	 * @param int $requestId
	 * @return CancellationResult
	 */
	public function cancelTaxiRequest(int $requestId): CancellationResult;
}
