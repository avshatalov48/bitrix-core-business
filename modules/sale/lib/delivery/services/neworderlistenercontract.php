<?php

namespace Bitrix\Sale\Delivery\Services;

use Bitrix\Sale\Order;

/**
 * Interface TakesInterestInNewOrderEvent
 * @package Bitrix\Sale\Delivery\Services
 */
interface NewOrderListenerContract
{
	/**
	 * @param Order $order
	 */
	public function onNewOrder(Order $order);
}
