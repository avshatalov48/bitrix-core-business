<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Sale\Delivery\Services\Base;

/**
 * Class ContextBase
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
abstract class ContextBase implements ContextContract
{
	/** @var Base */
	protected $deliveryService;

	/**
	 * @param $deliveryService
	 * @return $this
	 */
	public function setDeliveryService($deliveryService)
	{
		$this->deliveryService = $deliveryService;

		return $this;
	}
}
