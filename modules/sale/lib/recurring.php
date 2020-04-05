<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class Basket
 * @package Bitrix\Sale
 */
class Recurring
{
	/**
	 * @param Order $order
	 * @param array $resultList
	 *
	 * @throws Main\ObjectNotFoundException
	 */
	public static function repeat(Order $order, array $resultList)
	{
		$recurringID = intval($order->getField("RECURRING_ID"));

		$basket = $order->getBasket();
		foreach ($resultList as $providerName => $basketList)
		{
			foreach ($basketList as $basketCode => $resultData)
			{
				if ($order->isPaid())
				{
					if (!empty($resultData) && is_array($resultData))
					{
						if (empty($resultData['ORDER_ID']) || intval($resultData['ORDER_ID']) < 0)
							$resultData["ORDER_ID"] = $order->getId();

						$resultData["REMAINING_ATTEMPTS"] = (defined("SALE_PROC_REC_ATTEMPTS") ? SALE_PROC_REC_ATTEMPTS : 3);
						$resultData["SUCCESS_PAYMENT"] = "Y";

						if ($recurringID > 0)
							\CSaleRecurring::Update($recurringID, $resultData);
						else
							\CSaleRecurring::Add($resultData);
					}
					elseif ($recurringID > 0)
					{
						\CSaleRecurring::Delete($recurringID);
					}
				}
				else
				{
					if (!$basketItem = $basket->getItemByBasketCode($basketCode))
					{
						throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
					}

					$resRecurring = \CSaleRecurring::GetList(
						array(),
						array(
							"USER_ID" => $order->getUserId(),
							"PRODUCT_ID" => $basketItem->getProductId(),
							"MODULE" => $basketItem->getField("MODULE"),
							"ORDER_ID" => $order->getId(),
						)
					);
					while ($recurringData = $resRecurring->Fetch())
					{
						\CSaleRecurring::Delete($recurringData["ID"]);
					}
				}
			}
		}
	}
}