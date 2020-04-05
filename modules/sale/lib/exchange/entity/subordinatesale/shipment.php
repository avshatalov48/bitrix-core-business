<?php

namespace Bitrix\Sale\Exchange\Entity\SubordinateSale;


use Bitrix\Sale\BasketBase;
use Bitrix\Sale\Exchange\Entity\ShipmentImport;

class Shipment extends ShipmentImport
{

	/**
	 * @param BasketBase $basket
	 * @param array $item
	 * @return \Bitrix\Sale\BasketItem|bool
	 */
	protected function getBasketItemByItem(BasketBase $basket, array $item)
	{
		return Order::getBasketItemByItem($basket, $item);
	}
}