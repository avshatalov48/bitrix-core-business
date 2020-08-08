<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Sale\Delivery\Services\Base;

/**
 * Interface ContextContract
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
interface ContextContract
{
	public function subscribeToEvents();

	/**
	 * @param Base $deliveryService
	 * @return ContextContract
	 */
	public function setDeliveryService($deliveryService);
}
