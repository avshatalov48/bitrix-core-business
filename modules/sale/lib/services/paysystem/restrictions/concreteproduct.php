<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Services\Base\ConcreteProductRestriction;

Loc::loadMessages(__FILE__);

/**
 * Class ConcreteProduct
 * Restrictions paysystem by concrete products
 * @package Bitrix\Sale\Services\PaySystem\Restrictions
 */
class ConcreteProduct extends ConcreteProductRestriction
{
	/**
	 * @return string
	 */
	protected static function getJsHandler(): string
	{
		return 'BX.Sale.PaySystem';
	}

	/**
	 * Returns the restriction description
	 * @return string
	 */
	public static function getClassDescription() : string
	{
		return '';
	}

	/**
	 * @param Payment $entity
	 * @return array
	 */
	protected static function getBasketItems(Entity $entity): array
	{
		if (!$entity instanceof Payment)
		{
			return [];
		}

		/** @var $collection \Bitrix\Sale\PaymentCollection */
		if (!$collection = $entity->getCollection())
		{
			return [];
		}

		/** @var $order \Bitrix\Sale\Order */
		if (!$order =  $collection->getOrder())
		{
			return [];
		}

		/** @var $orderBasket \Bitrix\Sale\Basket */
		if ($basket = $order->getBasket())
		{
			return $basket->getBasketItems();
		}

		return [];
	}
}
