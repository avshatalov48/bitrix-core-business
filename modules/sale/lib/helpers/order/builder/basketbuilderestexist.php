<?php


namespace Bitrix\Sale\Helpers\Order\Builder;


/**
 * Class BasketBuildeRestExist
 * @package Bitrix\Sale\Helpers\Order\Builder
 * @internal
 */
final class BasketBuildeRestExist extends BasketBuilderExist
{
	public function getItemFromBasket($basketCode, $productData)
	{
		return $this->builder->getBasket()->getItemByBasketCode($basketCode);
	}
}