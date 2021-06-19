<?php

namespace Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders;

use Bitrix\Main\Type\DateTime;

/**
 * Interface IOrderProvider
 * @package Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders
 * @internal
 */
interface IOrderProvider
{
	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return array
	 */
	public function provideOrders(DateTime $dateFrom, DateTime $dateTo): array;
}
