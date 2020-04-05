<?php

namespace Bitrix\Sale\Basket;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketBase;

class SingleRefreshStrategy extends RefreshStrategy
{
	public function __construct(array $data = null)
	{
		parent::__construct($data);

		if (empty($this->data['BASKET_ITEM']) && empty($this->data['BASKET_ITEM_CODE']))
		{
			throw new ArgumentNullException('Parameters "BASKET_ITEM" or "BASKET_ITEM_CODE" should not be empty.');
		}
	}

	protected function extractItem(BasketBase $basket)
	{
		$basketItem = null;

		if (isset($this->data['BASKET_ITEM']) && $this->data['BASKET_ITEM'] instanceof BasketItem)
		{
			$basketItem = $this->data['BASKET_ITEM'];
		}
		elseif (!empty($this->data['BASKET_ITEM_CODE']))
		{
			$basketItem = $basket->getItemByBasketCode($this->data['BASKET_ITEM_CODE']);
		}

		return $basketItem;
	}

	protected function getItemToRefresh(BasketBase $basket)
	{
		$basketItem = $this->extractItem($basket);
		if ($basketItem === null)
		{
			throw new ObjectNotFoundException('Entity "BasketItem" not found');
		}

		$basketRefreshStart = time();
		$refreshGap = $this->getBasketRefreshGapTime();

		$basketItemLastRefresh = $this->getBasketItemRefreshTimestamp($basketItem);
		if ($basketRefreshStart - $basketItemLastRefresh >= $refreshGap)
		{
			return $basketItem;
		}

		return null;
	}

	protected function getProductData(BasketBase $basket)
	{
		$itemToRefresh = $this->getItemToRefresh($basket);

		$items = array();
		if (!empty($itemToRefresh))
		{
			$items[] = $itemToRefresh;
		}

		return $this->getProviderResult($basket, $items);
	}
}