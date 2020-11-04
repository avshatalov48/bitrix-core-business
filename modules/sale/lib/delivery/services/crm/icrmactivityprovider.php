<?php

namespace Bitrix\Sale\Delivery\Services\Crm;

use Bitrix\Crm\Order\Shipment;

/**
 * Interface ICrmActivityProvider
 * @package Bitrix\Crm\Order\Shipment
 * @internal
 */
interface ICrmActivityProvider
{
	/**
	 * @param Shipment $shipment
	 * @return Activity
	 */
	public function provideCrmActivity(Shipment $shipment): Activity;
}
