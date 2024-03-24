<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;
use Bitrix\Main;

Loc::loadMessages(__FILE__);

/**
 * Class Basket
 * @package Bitrix\Sale
 */
class Basket extends BasketBase
{
	const BASKET_DELETE_LIMIT = 2000;

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result|mixed
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\BasketTable::getList($parameters);
	}

	/**
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();

		$itemsDataList = static::getList(
			array(
				"filter" => array("=ORDER_ID" => $idOrder),
				"select" => array("ID", "TYPE")
			)
		);

		/** @var BasketItem $itemClassName */
		$itemClassName = static::getItemCollectionClassName();
		while ($item = $itemsDataList->fetch())
		{
			if ($item['TYPE'] === $itemClassName::TYPE_SET)
			{
				$r = Internals\BasketTable::deleteBundle($item['ID']);
			}
			else
			{
				$r = Internals\BasketTable::deleteWithItems($item['ID']);
			}

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param int $days
	 *
	 * @return int
	 */
	public static function deleteOld($days): int
	{
		$days = (int)$days;

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$query = $helper->prepareDeleteLimit(
			Internals\BasketTable::getTableName(),
			['ID'],
			"
				FUSER_ID IN (
				SELECT b_sale_fuser.ID FROM b_sale_fuser WHERE
						b_sale_fuser.DATE_UPDATE < " . $helper->addDaysToDateTime(-$days) . "
						AND b_sale_fuser.USER_ID IS NULL
				) AND ORDER_ID IS NULL
			",
			[],
			static::BASKET_DELETE_LIMIT
		);

		$connection->queryExecute($query);
		$affectRows = $connection->getAffectedRowsCount();

		$query = $helper->prepareDeleteLimit(
			Internals\BasketTable::getTableName(),
			['ID'],
			"
				FUSER_ID NOT IN (SELECT b_sale_fuser.ID FROM b_sale_fuser)
				AND ORDER_ID IS NULL
			",
			[],
			static::BASKET_DELETE_LIMIT
		);

		$connection->queryExecute($query);
		$affectRows = max($affectRows, $connection->getAffectedRowsCount());

		$query = $helper->prepareDeleteLimit(
			Internals\BasketPropertyTable::getTableName(),
			['ID'],
			"
				BASKET_ID NOT IN (
					SELECT b_sale_basket.ID FROM b_sale_basket
				)
			",
			[],
			static::BASKET_DELETE_LIMIT
		);

		$connection->queryExecute($query);

		return max($affectRows, $connection->getAffectedRowsCount());
	}

	/**
	 * @param $days
	 * @param int $speed
	 * @return string
	 */
	public static function deleteOldAgent($days, $speed = 0)
	{
		$days = (int)$days;

		$affectRows = static::deleteOld($days);
		Fuser::deleteOld($days);

		$days = (int)Main\Config\Option::get('sale', 'delete_after');
		$result = "\Bitrix\Sale\Basket::deleteOldAgent(" . $days . ");";

		if ($affectRows === static::BASKET_DELETE_LIMIT)
		{
			global $pPERIOD;
			$pPERIOD = 300;
		}

		return $result;
	}

	/**
	 * @return array|bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	// must be moved to notify
	public function getListOfFormatText()
	{
		$list = array();

		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = static::getItemCollectionClassName();

		/** @var BasketItem $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$basketItemData = $basketItem->getField("NAME");

			/** @var \Bitrix\Sale\BasketPropertiesCollection $basketPropertyCollection */
			if ($basketPropertyCollection = $basketItem->getPropertyCollection())
			{
				$basketItemDataProperty = "";
				/** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
				foreach ($basketPropertyCollection as $basketPropertyItem)
				{
					if ($basketPropertyItem->getField('CODE') == "PRODUCT.XML_ID"
						|| $basketPropertyItem->getField('CODE') == "CATALOG.XML_ID"
						|| $basketPropertyItem->getField('CODE') == "SUM_OF_CHARGE"
					)
					{
						continue;
					}

					if (strval(trim($basketPropertyItem->getField('VALUE'))) == "")
						continue;


					$basketItemDataProperty .= (!empty($basketItemDataProperty) ? "; " : "").trim($basketPropertyItem->getField('NAME')).": ".trim($basketPropertyItem->getField('VALUE'));
				}

				if (!empty($basketItemDataProperty))
					$basketItemData .= " [".$basketItemDataProperty."]";
			}

			$measure = (strval($basketItem->getField("MEASURE_NAME")) != '') ? $basketItem->getField("MEASURE_NAME") : Loc::getMessage("SOA_SHT");
			$list[$basketItem->getBasketCode()] = $basketItemData." - ".$basketItemClassName::formatQuantity($basketItem->getQuantity())." ".$measure." x ".SaleFormatCurrency($basketItem->getPrice(), $basketItem->getCurrency());

		}

		return !empty($list) ? $list : false;
	}

	/**
	 * Save basket
	 *
	 * @return Result
	 */
	public function save()
	{
		$result = parent::save();

		$orderId = $this->getOrderId();
		if ($orderId > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::collectEntityFields('BASKET', $orderId);
		}

		return $result;
	}

	/**
	 * @param array $itemValues
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	protected function deleteInternal(array $itemValues)
	{
		$result = new Result();

		/** @var BasketItem $itemClassName */
		$itemClassName = static::getItemCollectionClassName();
		if ($itemValues['TYPE'] == $itemClassName::TYPE_SET)
		{
			$r = Internals\BasketTable::deleteBundle($itemValues['ID']);
		}
		else
		{
			$r = Internals\BasketTable::deleteWithItems($itemValues['ID']);
		}

		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$orderId = $this->getOrderId();
		if ($orderId > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::addLog(
				'BASKET',
				$orderId,
				'BASKET_ITEM_DELETED',
				$itemValues['ID'],
				null,
				array(
					"PRODUCT_ID" => $itemValues["PRODUCT_ID"],
					"NAME" => $itemValues["NAME"],
					"QUANTITY" => $itemValues["QUANTITY"],
				),
				$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
			);
		}

		return $result;
	}

	/**
	 * @param $itemValues
	 * @throws Main\ArgumentException
	 * @return void
	 */
	protected function callEventOnSaleBasketItemDeleted($itemValues)
	{
		parent::callEventOnSaleBasketItemDeleted($itemValues);

		$orderId = $this->getOrderId();
		if ($orderId > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::addAction(
				'BASKET',
				$orderId,
				'BASKET_REMOVED',
				$itemValues['ID'],
				null,
				array(
					'NAME' => $itemValues['NAME'],
					'QUANTITY' => $itemValues['QUANTITY'],
					'PRODUCT_ID' => $itemValues['PRODUCT_ID'],
				)
			);

			/** @var EntityMarker $entityMarker */
			$entityMarker = $registry->getEntityMarkerClassName();
			$entityMarker::deleteByFilter(array(
				'=ORDER_ID' => $orderId,
				'=ENTITY_TYPE' => $entityMarker::ENTITY_TYPE_BASKET_ITEM,
				'=ENTITY_ID' => $itemValues['ID'],
			));
		}
	}

	/**
	 * @internal
	 *
	 * @param $index
	 * @return BasketItemBase
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function deleteItem($index)
	{
		/** @var Order $order */
		if ($order = $this->getOrder())
		{
			/** @var BasketItem $item */
			$item = $this->getItemByIndex($index);

			$order->onBeforeBasketItemDelete($item);
		}

		return parent::deleteItem($index);
	}

}