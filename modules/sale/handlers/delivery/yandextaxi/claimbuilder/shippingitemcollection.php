<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\ShippingItem;
use Bitrix\Main\Result;

/**
 * Class ShippingItemCollection
 * @package Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder
 * @internal
 */
final class ShippingItemCollection implements \IteratorAggregate
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
	 * @return Result
	 */
	public function isValid(): Result
	{
		$result = new Result();

		if (!$this->items)
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_EMPTY_PRODUCT_LIST')));
		}

		foreach ($this->items as $item)
		{
			if ($item->getQuantity() <= 0)
			{
				return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_PRODUCT_EMPTY_QUANTITY')));
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}
}
