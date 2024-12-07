<?php

namespace Bitrix\Sale\Helpers;

use Bitrix\Main\Config\Option;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Update\Stepper;
use Bitrix\Sale;
use Bitrix\Sale\ReserveQuantityCollection;

class ReservedProductCleaner extends Stepper
{
	private const RECORD_LIMIT = 100;

	protected static $moduleId = "sale";

	public function execute(array &$result)
	{
		$processedRecords = 0;

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$daysAgo = (int)Option::get("sale", "product_reserve_clear_period");

		if ($daysAgo > 0)
		{
			global $USER;
			$initUser = false;
			$oldUser = null;

			if (!(isset($USER) && $USER instanceof \CUser))
			{
				$initUser = true;
				$oldUser = $USER ?? null;
				$USER = new \CUser;
			}

			$date = new DateTime();
			$parameters = [
				'select' => [
					'ORDER_ID' => 'ORDER.ID',
					'ID',
					'BASKET_ID'
				],
				'filter' => [
					'>QUANTITY' => 0,
					'<=DATE_RESERVE_END' => $date,
					'=ORDER.PAYED' => 'N',
					'=ORDER.CANCELED' => 'N',
				],
				'runtime' => [
					new Main\Entity\ReferenceField(
						'BASKET',
						Sale\Internals\BasketTable::class,
						[
							'=this.BASKET_ID' => 'ref.ID',
						],
						['join_type' => 'inner']
					),
					new Main\Entity\ReferenceField(
						'ORDER',
						Sale\Internals\OrderTable::class,
						[
							'=this.BASKET.ORDER_ID' => 'ref.ID',
						],
						['join_type' => 'inner']
					),
				],
				'limit' => self::RECORD_LIMIT,
			];

			$orderList = [];
			$res = Sale\ReserveQuantityCollection::getList($parameters);
			while ($data = $res->fetch())
			{
				if (!isset($orderList[$data['ORDER_ID']]))
				{
					$orderList[$data['ORDER_ID']] = [];
				}

				if (!isset($orderList[$data['ORDER_ID']][$data['BASKET_ID']]))
				{
					$orderList[$data['ORDER_ID']][$data['BASKET_ID']] = [];
				}

				$orderList[$data['ORDER_ID']][$data['BASKET_ID']][] = $data['ID'];
			}

			foreach ($orderList as $orderId => $basketItemIds)
			{
				$order = $orderClass::load($orderId);
				if (!$order)
				{
					continue;
				}

				$basket = $order->getBasket();
				foreach ($basketItemIds as $basketItemId => $reserveIds)
				{
					/** @var Sale\BasketItem $basketItem */
					$basketItem = $basket->getItemById($basketItemId);
					if (!$basketItem)
					{
						continue;
					}

					foreach ($reserveIds as $reserveId)
					{
						/** @var ReserveQuantityCollection $reserveCollection */
						$reserveCollection = $basketItem->getReserveQuantityCollection();
						if (!$reserveCollection)
						{
							continue;
						}

						$reserve = $reserveCollection->getItemById($reserveId);
						if (!$reserve)
						{
							continue;
						}

						$reserve->delete();

						$processedRecords++;
					}
				}

				$r = $order->save();
				if (!$r->isSuccess())
				{
					$errorText = (string)$order->getField('REASON_MARKED');
					if ($errorText !== '')
					{
						$errorText .= "\n";
					}

					foreach($r->getErrorMessages() as $error)
					{
						if ((string)$error !== '')
						{
							$errorText .= $error."\n";
						}
					}

					Sale\Internals\OrderTable::update($order->getId(), [
						"MARKED" => "Y",
						"REASON_MARKED" => $errorText
					]);
				}
			}

			// crutch for #120087
			if ($initUser)
			{
				ORM\Entity::destroy(Sale\Internals\OrderTable::getEntity());
				if ($oldUser !== null)
				{
					$USER = $oldUser;
				}
			}
			unset($oldUser);
		}

		if ($processedRecords < self::RECORD_LIMIT)
		{
			return self::FINISH_EXECUTION;
		}

		return self::CONTINUE_EXECUTION;
	}
}
