<?php

namespace Bitrix\Sale\Reservation\Event\Handler;

use Bitrix\Main\Event;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\Catalog\Provider;
use Bitrix\Sale\ReserveQuantity;

class BasketItemUpdateProductReserveHandlers
{
	private static $basketReserves = [];

	/**
	 * Event handler.
	 *
	 * @param Event $event
	 *
	 * @return void
	 */
	public static function OnSaleBasketItemSetField(Event $event): void
	{
		$name = $event->getParameter('NAME');
		$basketItem = $event->getParameter('ENTITY');
		if ($name !== 'PRODUCT_ID' || !($basketItem instanceof BasketItem))
		{
			return;
		}

		$reserveQuantityCollection = $basketItem->getReserveQuantityCollection();
		if (!isset($reserveQuantityCollection) || $reserveQuantityCollection->isEmpty())
		{
			return;
		}

		$reserves = [];
		foreach ($reserveQuantityCollection as $reserveQuantity)
		{
			/**
			 * @var ReserveQuantity $reserveQuantity
			 */

			$reserves[] = [
				'STORE_ID' => $reserveQuantity->getStoreId(),
				'QUANTITY' => $reserveQuantity->getQuantity(),
				'DATE_RESERVE_END' => $reserveQuantity->getField('DATE_RESERVE_END'),
			];
			$result = $reserveQuantity->delete();

			if (!$result->isSuccess())
			{
				$reserveQuantity->setFieldNoDemand('QUANTITY', 0);

				Provider::tryReserve($reserveQuantity);

				$reserveQuantity->deleteNoDemand();
			}
		}

		self::$basketReserves[$basketItem->getBasketCode()] = $reserves;
	}

	/**
	 * Event handler.
	 *
	 * @param Event $event
	 *
	 * @return void
	 */
	public static function OnAfterSaleBasketItemSetField(Event $event): void
	{
		$name = $event->getParameter('NAME');
		$basketItem = $event->getParameter('ENTITY');
		if ($name !== 'PRODUCT_ID' || !($basketItem instanceof BasketItem))
		{
			return;
		}

		$reserveQuantityCollection = $basketItem->getReserveQuantityCollection();
		if (!isset($reserveQuantityCollection))
		{
			return;
		}

		$reserves = self::$basketReserves[$basketItem->getBasketCode()] ?? null;
		if (empty($reserves))
		{
			return;
		}

		foreach ($reserves as $reserve)
		{
			$reserveQuantity = $reserveQuantityCollection->create();
			$result = $reserveQuantity->setFields($reserve);

			if (!$result->isSuccess())
			{
				$reserveQuantity->setFieldsNoDemand($reserve);
				Provider::tryReserve($reserveQuantity);
			}
		}

		unset(self::$basketReserves[$basketItem->getBasketCode()]);
	}
}
