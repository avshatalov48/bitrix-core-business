<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Result;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\ShippingItem;

/**
 * Class GetShippingItemsResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class GetShippingItemsResult extends Result
{
	/** @var ShippingItem[] */
	private $items = [];

	/**
	 * @param ShippingItem $item
	 * @return $this
	 */
	public function addItem(ShippingItem $item)
	{
		$this->items[] = $item;

		return $this;
	}

	/**
	 * @return ShippingItem[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}
}
