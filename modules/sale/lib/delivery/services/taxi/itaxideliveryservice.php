<?php

namespace Bitrix\Sale\Delivery\Services\Taxi;

use Bitrix\Sale\Shipment;

/**
 * Interface ITaxiDeliveryService
 * @package Bitrix\Sale\Delivery\Services\Taxi
 * @internal
 */
interface ITaxiDeliveryService
{
	/**
	 * @param Shipment $shipment
	 * @return CreationRequestResult
	 */
	public function createTaxiRequest(Shipment $shipment): CreationRequestResult;

	/**
	 * @param int $requestId
	 * @return CancellationRequestResult
	 */
	public function cancelTaxiRequest(int $requestId): CancellationRequestResult;
}
