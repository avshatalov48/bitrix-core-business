<?php

namespace Bitrix\Rest\V3\Structures\Ordering;

use Bitrix\Main\DB\Order;

class OrderItem
{
	protected Order $order;

	/**
	 * @param string $property
	 * @param Order $order
	 */
	public function __construct(protected string $property, Order $order)
	{
		$this->order = $order;
	}

	public function getProperty(): string
	{
		return $this->property;
	}

	public function getOrder(): Order
	{
		return $this->order;
	}
}
