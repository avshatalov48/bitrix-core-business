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
	 * @throws Main\NotImplementedException
	 * @return Basket
	 */
	protected static function createBasketObject()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$basketClassName = $registry->getBasketClassName();

		return new $basketClassName;
	}

	/**
	 * @param BasketItemCollection $basket
	 * @param $moduleId
	 * @param $productId
	 * @param $basketCode
	 * @return BasketItemBase
	 */
	protected function createItemInternal(BasketItemCollection $basket, $moduleId, $productId, $basketCode = null)
	{
		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = $this->getBasketItemCollectionElementClassName();
		return $basketItemClassName::create($basket, $moduleId, $productId, $basketCode);
	}

	/**
	 * @param array $filter
	 *
	 * @return Basket
	 */
	public function loadFromDb(array $filter)
	{
		$select = array(
			"ID", "LID", "MODULE", "PRODUCT_ID", "QUANTITY", "WEIGHT",
			"DELAY", "CAN_BUY", "PRICE", "CUSTOM_PRICE", "BASE_PRICE",
			'PRODUCT_PRICE_ID', 'PRICE_TYPE_ID', "CURRENCY", 'BARCODE_MULTI',
			"RESERVED", "RESERVE_QUANTITY",	"NAME", "CATALOG_XML_ID",
			"VAT_RATE", "NOTES", "DISCOUNT_PRICE","PRODUCT_PROVIDER_CLASS",
			"CALLBACK_FUNC", "ORDER_CALLBACK_FUNC", "PAY_CALLBACK_FUNC",
			"CANCEL_CALLBACK_FUNC", "DIMENSIONS", "TYPE", "SET_PARENT_ID",
			"DETAIL_PAGE_URL", "FUSER_ID", 'MEASURE_CODE', 'MEASURE_NAME',
			'ORDER_ID', 'DATE_INSERT', 'DATE_UPDATE', 'PRODUCT_XML_ID',
			'SUBSCRIBE', 'RECOMMENDATION', 'VAT_INCLUDED', 'SORT',
			'DATE_REFRESH', 'DISCOUNT_NAME', 'DISCOUNT_VALUE', 'DISCOUNT_COUPON'
		);

		$itemList = array();
		$first = true;

		$res = static::getList(array(
			"select" => $select,
			"filter" => $filter,
			"order" => array('SORT' => 'ASC', 'ID' => 'ASC'),
		));
		while ($item = $res->fetch())
		{
			if ($first)
			{
				$this->setSiteId($item['LID']);
				$this->setFUserId($item['FUSER_ID']);
				$first = false;
			}

			$itemList[$item['ID']] = $item;
		}

		foreach ($itemList as $id => $item)
		{
			if ($item['SET_PARENT_ID'] > 0)
			{
				$itemList[$item['SET_PARENT_ID']]['ITEMS'][$id] = &$itemList[$id];
			}
		}

		$result = array();
		foreach ($itemList as $id => $item)
		{
			if ($item['SET_PARENT_ID'] == 0)
				$result[$id] = $item;
		}

		$this->loadFromArray($result);

		return $this;
	}

	/**
	 * @param array $parameters
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\BasketTable::getList($parameters);
	}

	/**
	 * @internal
	 *
	 * Delete basket items without demands.
	 *
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();

		$itemsDataList = Internals\BasketTable::getList(
			array(
				"filter" => array("=ORDER_ID" => $idOrder),
				"select" => array("ID")
			)
		);

		while ($item = $itemsDataList->fetch())
		{
			$r = Internals\BasketTable::deleteWithItems($item['ID']);
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
	 * @return bool
	 */
	public static function deleteOld($days)
	{
		$expired = new Main\Type\DateTime();
		$expired->add('-'.$days.' days');
		$expiredValue = $expired->format('Y-m-d H:i:s');

		/** @var Main\DB\Connection $connection */
		$connection = Main\Application::getConnection();
		/** @var Main\DB\SqlHelper $sqlHelper */
		$sqlHelper = $connection->getSqlHelper();

		$sqlExpiredDate = $sqlHelper->getDateToCharFunction("'" . $expiredValue . "'");

		$query = "DELETE FROM b_sale_basket	WHERE
			FUSER_ID IN (
				SELECT b_sale_fuser.id FROM b_sale_fuser WHERE
						b_sale_fuser.DATE_UPDATE < ".$sqlExpiredDate."
						AND b_sale_fuser.USER_ID IS NULL
				) AND ORDER_ID IS NULL LIMIT ". static::BASKET_DELETE_LIMIT;

		$connection->queryExecute($query);
		$affectRows = $connection->getAffectedRowsCount();

		$query = "DELETE FROM b_sale_basket	
			WHERE
				FUSER_ID NOT IN (SELECT b_sale_fuser.id FROM b_sale_fuser)
				AND 
				ORDER_ID IS NULL
			LIMIT ". static::BASKET_DELETE_LIMIT;

		$connection->queryExecute($query);
		$affectRows = max($affectRows, $connection->getAffectedRowsCount());

		$query = "
			DELETE
			FROM b_sale_basket_props 
			WHERE b_sale_basket_props.BASKET_ID NOT IN (
				SELECT b_sale_basket.ID FROM b_sale_basket
			)
			LIMIT ".static::BASKET_DELETE_LIMIT;

		$connection->queryExecute($query);

		return max($affectRows, $connection->getAffectedRowsCount());
	}

	/**
	 * @param $days
	 * @param int $speed
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function deleteOldAgent($days, $speed = 0)
	{
		if (!isset($GLOBALS["USER"]) || !is_object($GLOBALS["USER"]))
		{
			$tmpUser = True;
			$GLOBALS["USER"] = new \CUser();
		}

		$affectRows = static::deleteOld($days);
		Fuser::deleteOld($days);

		$days = intval(Main\Config\Option::get("sale", "delete_after", "30"));
		$result = "\Bitrix\Sale\Basket::deleteOldAgent(".$days.");";

		if ($affectRows === static::BASKET_DELETE_LIMIT)
		{
			global $pPERIOD;
			$pPERIOD = 300;
		}

		if (isset($tmpUser))
		{
			unset($GLOBALS["USER"]);
		}

		return $result;
	}

	/**
	 * @internal
	 * @return array|bool
	 */
	// must be moved to notify
	public function getListOfFormatText()
	{
		$list = array();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = $registry->getBasketItemClassName();

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
					if ($basketPropertyItem->getField('CODE') == "PRODUCT.XML_ID" || $basketPropertyItem->getField('CODE') == "CATALOG.XML_ID")
						continue;

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
	 * @param array $requestBasket
	 * @return Basket
	 * @throws UserMessageException
	 */
	public static function createFromRequest(array $requestBasket)
	{
		if (array_key_exists('SITE_ID', $requestBasket) && strval($requestBasket['SITE_ID']) != '')
		{
			throw new UserMessageException('site_id not found');
		}

		/** @var Basket $basket */
		$basket = static::create($requestBasket['SITE_ID']);

		foreach ($requestBasket as $requestBasketItem)
		{
			$basketItem = static::createItemInternal($basket, $requestBasketItem['MODULE'], $requestBasketItem['PRODUCT_ID']);
			$basketItem->initFields($requestBasketItem);

			$basket->addItem($basketItem);
		}

		return $basket;
	}

	/**
	 * @return array
	 */
	protected function getOriginalItemsValues()
	{
		$result = array();

		/** @var Order $order */
		$order = $this->getOrder();
		$isNew = $order && $order->isNew();

		$filter = array();
		if (!$isNew && $order && $order->getId() > 0)
		{
			$filter['ORDER_ID'] = $order->getId();
		}
		else
		{
			if ($this->isLoadForFUserId())
			{
				$filter = array(
					'FUSER_ID' => $this->getFUserId(),
					'ORDER_ID' => null,
					'LID' => $this->getSiteId()
				);
			}

			if ($isNew)
			{
				$fUserId = $this->getFUserId(true);
				if ($fUserId <= 0)
				{
					$userId = $order->getUserId();
					if (intval($userId) > 0)
					{
						$fUserId = Fuser::getIdByUserId($userId);
						if ($fUserId > 0)
							$this->setFUserId($fUserId);
					}
				}
			}
		}

		if ($filter)
		{
			$dbRes = static::getList(
				array(
					"select" => array("ID", 'TYPE', 'SET_PARENT_ID', 'PRODUCT_ID', 'NAME', 'QUANTITY', 'FUSER_ID', 'ORDER_ID'),
					"filter" => $filter,
				)
			);

			while ($item = $dbRes->fetch())
			{
				if ((int)$item['SET_PARENT_ID'] > 0 && (int)$item['SET_PARENT_ID'] != $item['ID'])
				{
					continue;
				}

				$result[$item["ID"]] = $item;
			}
		}

		return $result;
	}

	/**
	 * @param array $itemValues
	 */
	protected function deleteInternal(array $itemValues)
	{
		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = $this->getBasketItemCollectionElementClassName();
		if ($itemValues['TYPE'] == $basketItemClassName::TYPE_SET)
		{
			Internals\BasketTable::deleteBundle($itemValues['ID']);
		}
		else
		{
			Internals\BasketTable::deleteWithItems($itemValues['ID']);
		}
	}

	/**
	 * @return string
	 */
	protected function getItemEventName()
	{
		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = $this->getBasketItemCollectionElementClassName();
		return $basketItemClassName::getEntityEventName();
	}

	/**
	 * @return string
	 */
	protected function getBasketItemCollectionElementClassName()
	{
		$registry  = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		return $registry->getBasketItemClassName();
	}

	/**
	 * @return array
	 */
	public function getContext()
	{
		$context = array();

		$order = $this->getOrder();
		/** @var OrderBase $order */
		if ($order)
		{
			$context['USER_ID'] = $order->getUserId();
			$context['SITE_ID'] = $order->getSiteId();
			$context['CURRENCY'] = $order->getCurrency();
		}
		else
		{
			$context = parent::getContext();
		}

		return $context;
	}

}