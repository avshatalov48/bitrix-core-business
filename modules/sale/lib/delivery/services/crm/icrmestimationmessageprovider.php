<?php

namespace Bitrix\Sale\Delivery\Services\Crm;

use Bitrix\Crm\Order\Shipment;

/**
 * Interface ICrmEstimationMessageProvider
 * @package Bitrix\Crm\Order\Shipment
 * @internal
 */
interface ICrmEstimationMessageProvider
{
	/**
	 * @param Shipment $shipment
	 * @return EstimationMessage
	 */
	public function provideCrmEstimationMessage(Shipment $shipment): EstimationMessage;
}
