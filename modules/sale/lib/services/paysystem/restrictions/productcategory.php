<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Internals\EntityCollection;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\Services\Base\ProductCategoryRestriction;
use Twilio\TwiML\Voice\Pay;

Loc::loadMessages(__FILE__);

class ProductCategory extends ProductCategoryRestriction
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

		/** @var $collection PaymentCollection */
		if (!$collection = $entity->getCollection())
		{
			return [];
		}

		/** @var $order Order */
		if (!$order =  $collection->getOrder())
		{
			return [];
		}

		/** @var $orderBasket Basket */
		if ($orderBasket = $order->getBasket())
		{
			return $orderBasket->getBasketItems();
		}

		return [];
	}
}