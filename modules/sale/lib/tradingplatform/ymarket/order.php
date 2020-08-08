<?
namespace Bitrix\Sale\TradingPlatform\YMarket;

use Bitrix\Catalog;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Result;
use Bitrix\Main\Error;

Loc::loadMessages(__FILE__);

class Order
{
	/**
	 * @param array $params
	 * @return Result
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\NotSupportedException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public static function create(array $params)
	{
		if(empty($params['CURRENCY']))
			throw new ArgumentNullException('params[CURRENCY]');

		if($params['SITE_ID'] == '')
			throw new ArgumentNullException('params[SITE_ID]');

		if(intval($params['PERSON_TYPE_ID']) <= 0)
			throw new ArgumentNullException('params[PERSON_TYPE_ID]');

		if(empty($params['CART_ITEMS']))
			throw new ArgumentNullException('params[CART_ITEMS]');

		if(empty($params['IS_ACCEPT_OLD_PRICE']))
			$params['IS_ACCEPT_OLD_PRICE'] = \CSaleYMHandler::NOT_ACCEPT_OLD_PRICE;

		$result = new Result();
		$currencyList = \Bitrix\Currency\CurrencyManager::getCurrencyList();

		if($params['CURRENCY'] == 'RUR' && empty($currencyList['RUR']) && !empty($currencyList['RUB']))
			$currency = 'RUB';
		else
			$currency = $params['CURRENCY'];

		if(empty($currencyList[$currency]))
		{
			$result->addError(new Error(Loc::getMessage('SALE_YMARKET_ORDER_CURRENCY_NOT_SUPPORTED', array('#CURRENCY#' => $currency))));
			return $result;
		}

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$order = $orderClass::create($params['SITE_ID'], \CSaleUser::GetAnonymousUserID(), $currency);
		/** @var \Bitrix\Sale\Result $res */
		$res = $order->setPersonTypeId(intval($params['PERSON_TYPE_ID']));

		if (!$res->isSuccess())
			$result->addErrors($res->getErrors());

		if(!empty($params['PROPERTIES']))
		{
			$propCollection = $order->getPropertyCollection();
			$res = $propCollection->setValuesFromPost(array('PROPERTIES' => $params['PROPERTIES']), array());

			if (!$res->isSuccess())
				$result->addErrors($res->getErrors());
		}

		$fUserId = $order->getUserId() > 0 ? \Bitrix\Sale\Fuser::getIdByUserId($order->getUserId()) : null;
		$isStartField = $order->isStartField();

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basket = $basketClass::create($params['SITE_ID']);
		$res = $order->setBasket($basket);

		if (!$res->isSuccess())
			$result->addErrors($res->getErrors());

		$basket->setFUserId($fUserId);
		$discount = $order->getDiscount();

		//Hello from discounts
		//todo: sortByColumn($products, array("BASE_PRICE" => SORT_DESC, "PRICE" => SORT_DESC), '', null, true);

		Loader::includeModule('catalog');

		$itemsMap = array();

		foreach ($params['CART_ITEMS'] as $itemKey => $item)
		{
			$basketItemFields = array(
				'PRODUCT_ID' => $item['offerId'],
				'QUANTITY' => $item['count'],
				'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider'
			);

			$context = array(
				'SITE_ID' => $params['SITE_ID'],
				'CURRENCY' => $params['CURRENCY'],
			);

			if ($order->getUserId() > 0)
			{
				$context['USER_ID'] = $order->getUserId();
			}

			$basketItem = null;

			$res = Catalog\Product\Basket::addProductToBasket($basket, $basketItemFields, $context);
			$resultData = $res->getData();

			if (!empty($resultData['BASKET_ITEM']))
			{
				/** @var \Bitrix\Sale\BasketItemBase $item */
				$basketItem = $resultData['BASKET_ITEM'];
			}

			if (!$res->isSuccess())
			{
				if ($basketItem)
				{
					$basketItem->setField("QUANTITY", 0);
				}
			}

			if ($basketItem)
			{
				$itemsMap[$basketItem->getBasketCode()] = $itemKey;
			}
		}

		/*
		 * 	todo: check if we still need this
			if($arProduct["CURRENCY"] != $currency && \Bitrix\Main\Loader::includeModule('currency'))
			{
					$price = \CCurrencyRates::convertCurrency(
						$arProduct["PRICE"],
						$arProduct["CURRENCY"],
						$currency
					);
			}
		 */

		$r = $basket->refreshData();
		if (!$r->isSuccess())
		{
			return $r;
		}

		/** @var BasketItem $basketItem */
		foreach($basket as $basketItem)
		{
			$basketCode = $basketItem->getBasketCode();
			$item = $params['CART_ITEMS'][$itemsMap[$basketCode]];

			$basketItem->setField("NAME", $item['offerName']);
//
//			if ($discount instanceof \Bitrix\Sale\Discount)
//				$discount->setBasketItemData($basketCode, $providerData[$basketCode]);
		}

		if($basket->count() == 0)
		{
			$result->addError(new Error(Loc::getMessage('SALE_YMARKET_ORDER_PRODUCTS_NOT_AVAILABLE')));
			return $result;
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $order->hasMeaningfulField();
			$res = $order->doFinalAction($hasMeaningfulFields);

			if (!$res->isSuccess())
				$result->addErrors($res->getErrors());
		}

		$result->setData(
			array(
				'ORDER' => $order,
				'ITEMS_MAP' => $itemsMap
			)
		);
		
		return $result;
	}

	public static function createShipment(\Bitrix\Sale\Order &$order, $deliveryId = 0, $deliveryPrice = false)
	{
		$shipments = $order->getShipmentCollection();

		/** @var \Bitrix\Sale\Shipment $shipment */
		if($shipments->count() > 0)
			foreach ($shipments as $shipment)
				if(!$shipment->isSystem())
					$shipment->delete();

		$shipment = $shipments->createItem();

		if(intval($deliveryId) > 0)
		{
			/** @var \Bitrix\Sale\Delivery\Services\Base $dlvSrv */
			if($dlvSrv = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryId))
				$dlvName = $dlvSrv->getNameWithParent();
			else
				$dlvName = 'Not found ('.$deliveryId.')';

			$shipment->setField('DELIVERY_ID', $deliveryId);
			$shipment->setField('DELIVERY_NAME', $dlvName);
		}

		if($deliveryPrice !== false)
		{
			$shipment->setBasePriceDelivery(floatval($deliveryPrice), true);
		}

		$basket = $order->getBasket();

		if($basket)
		{
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			$basketItems = $basket->getBasketItems();

			/** @var BasketItem $basketItem */
			foreach ($basketItems as $basketItem)
			{
				/** @var \Bitrix\Sale\ShipmentItem  $shipmentItem */
				$shipmentItem = $shipmentItemCollection->createItem($basketItem);
				$shipmentItem->setQuantity($basketItem->getField('QUANTITY'));
			}
		}

		return $shipment;
	}
	public static function createPayment(\Bitrix\Sale\Order &$order, $paySystemId = 0)
	{
		$payments = $order->getPaymentCollection();

		/** @var \Bitrix\Sale\Payment $payment */
		if($payments->count() > 0)
		{
			foreach ($payments as $payment)
			{
				if($payment->isPaid())
					$payment->setPaid("N");

				$payment->delete();
			}
		}

		$payment = $payments->createItem();

		if(intval($paySystemId) > 0)
		{
			$psName = 'Not found ('.$paySystemId.')';

			if($ps = PaySystem\Manager::getById($paySystemId))
				$psName = $ps['NAME'];

			$payment->setField('PAY_SYSTEM_ID', $paySystemId);
			$payment->setField('PAY_SYSTEM_NAME', $psName);
		}

		$payment->setField("SUM", $order->getPrice());

		return $payment;
	}
}
