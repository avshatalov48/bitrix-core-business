<?php
namespace Bitrix\Sale\Archive\Process;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Sale\Internals,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * @package Bitrix\Sale\Archive\Process
 */
class OrderArchiveCollection
	extends Internals\CollectionBase
{
	public function loadFromDB(array $parameters)
	{
		$result = new Sale\Result();
		$ordersList = Sale\Order::getList($parameters);

		while ($orderFields = $ordersList->fetch())
		{
			$order = Sale\Order::create($orderFields['LID'], $orderFields['USER_ID'], $orderFields['CURRENCY_ID']);
			$order->initFields($orderFields);
			$newItem = new OrderArchiveItem($order);
			$this->addItem($newItem);
		}

		if ($this->isEmpty())
		{
			$result->setData(array("count" => null));
			$result->addWarning(new Main\Error(Loc::getMessage("ARCHIVE_ORDER_NOT_FOUND")));
			return $result;
		}

		$this->fillItemsData();
		return $result;
	}

	private function addItem(OrderArchiveItem $item)
	{
		$this->collection[$item->getId()] = $item;
	}

	private function fillItemsData()
	{
		$idList = $this->getItemIds();
		if (empty($idList))
			return;

		$idListChunks = array_chunk($idList, 999);
		foreach ($idListChunks as $idOrdersList)
		{
			$sortedOrderProperties = $this->collectOrderProperties($idOrdersList);
			$sortedPayments = $this->collectPayments($idOrdersList);
			$sortedShipments = $this->collectShipments($idOrdersList);
			$sortedBasketItems = $this->collectBaskets($idOrdersList);
			$sortedDataDiscount = $this->collectDiscountData($idOrdersList);

			foreach ($idOrdersList as $orderId)
			{
				/** @var OrderArchiveItem $item */
				$item = $this->getItemById($orderId);
				if (empty($item))
				{
					continue;
				}

				$properties = $sortedOrderProperties[$orderId] ? $sortedOrderProperties[$orderId] : [];
				$item->addOrderDataField('PROPERTIES', $properties);
				$payment = $sortedPayments[$orderId] ? $sortedPayments[$orderId] : [];
				$item->addOrderDataField('PAYMENT', $payment);
				$shipment = $sortedShipments[$orderId] ? $sortedShipments[$orderId] : [];
				$item->addOrderDataField('SHIPMENT', $shipment);
				$discount = $sortedDataDiscount[$orderId] ? $sortedDataDiscount[$orderId] : [];
				$item->addOrderDataField('DISCOUNT', $discount);
				$basketItems = $sortedBasketItems[$orderId] ? $sortedBasketItems[$orderId] : [];
				$item->addBasketDataFields($basketItems);
			}
		}
	}

	private function getItemById($id)
	{
		return $this->collection[$id];
	}

	private function getItemIds()
	{
		return array_keys($this->collection);
	}

	/**
	 * Collect order properties and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 */
	private function collectOrderProperties($orderIds)
	{
		$sortedOrderProperties = [];
		$orderProperties = Internals\OrderPropsValueTable::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds)
			)
		);

		while ($property = $orderProperties->fetch())
		{
			$sortedOrderProperties[$property['ORDER_ID']][$property['ID']] = $property;
		}
		return $sortedOrderProperties;
	}

	/**
	 * Collect payments and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	private function collectPayments(array $orderIds)
	{
		$sortedPayments = [];
		$payments = Sale\Payment::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds)
			)
		);

		while ($payment = $payments->fetch())
		{
			$sortedPayments[$payment['ORDER_ID']][$payment['ID']] = $payment;
		}

		return $sortedPayments;
	}

	/**
	 * Collect shipments with shipment items and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	private function collectShipments(array $orderIds)
	{
		$shipmentItemsList = [];
		$sortedShipments = [];

		$shipments = Sale\Shipment::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds, "SYSTEM" => 'N')
			)
		);

		while ($shipment = $shipments->fetch())
		{
			$shipmentItemsList[$shipment['ID']] = $shipment;
		}

		if (!empty($shipmentItemsList))
		{
			$shipmentsItems = Sale\ShipmentItem::getList(
				array(
					"order" => array("ORDER_DELIVERY_ID"),
					"filter" => array("ORDER_DELIVERY_ID" => array_keys($shipmentItemsList))
				)
			);

			while ($shipmentsItem = $shipmentsItems->fetch())
			{
				$shipmentItemsList[$shipmentsItem['ORDER_DELIVERY_ID']]["SHIPMENT_ITEM"][] = $shipmentsItem;
			}
		}

		foreach ($shipmentItemsList as $item)
		{
			$sortedShipments[$item['ORDER_ID']][$item['ID']] = $item;
		}

		return $sortedShipments;
	}

	/**
	 * Collect basket items with barcodes and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	private function collectBaskets(array $orderIds)
	{
		$sortedBasketItems = [];
		$basketItemsList = [];

		$basketItems = Sale\Basket::getList(
			array(
				"order" => array("ORDER_ID"),
				"filter" => array("=ORDER_ID" => $orderIds)
			)
		);

		while ($element = $basketItems->fetch())
		{
			$basketItemsList[$element['ID']] = $element;
		}

		if (!empty($basketItemsList))
		{
			$basketProperties = Internals\BasketPropertyTable::getList(
				array(
					"filter" => array("BASKET_ID" => array_keys($basketItemsList))
				)
			);

			while ($property = $basketProperties->fetch())
			{
				$basketItemsList[$property["BASKET_ID"]]['PROPERTY_ITEMS'][] = $property;
			}

			$basketProperties = Sale\ShipmentItemStore::getList(
				array(
					"filter" => array("=BASKET_ID" => array_keys($basketItemsList))
				)
			);

			while ($property = $basketProperties->fetch())
			{
				$basketItemsList[$property["BASKET_ID"]]['SHIPMENT_BARCODE_ITEMS'][$property['ORDER_DELIVERY_BASKET_ID']] = $property;
			}
		}

		foreach ($basketItemsList as $basketItem)
		{
			$sortedBasketItems[$basketItem['ORDER_ID']][$basketItem['ID']] = $basketItem;
		}

		return $sortedBasketItems;
	}

	/**
	 * Collect discount data and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 */
	private function collectDiscountData(array $orderIds)
	{
		$sortedDataDiscount = [];
		$discountList = [];

		$couponList = $this->collectCoupons($orderIds);
		$sortedDiscountRules = $this->collectRules($orderIds);

		$dataIterator = Internals\OrderDiscountDataTable::getList(
			array(
				'select' => array('*'),
				'filter' => array('=ORDER_ID' => $orderIds)
			)
		);

		while ($dataDiscount = $dataIterator->fetch())
		{
			$discountList[$dataDiscount['ORDER_ID']][$dataDiscount['ID']] = $dataDiscount;
		}

		foreach ($orderIds as $orderId)
		{
			$sortedDataDiscount[$orderId] = [
				'ORDER_DATA' => isset($discountList[$orderId]) ? $discountList[$orderId] : [],
				'COUPON_LIST' => isset($couponList[$orderId]) ? $couponList[$orderId] : [],
				'RULES_DATA' => isset($sortedDiscountRules[$orderId]) ? $sortedDiscountRules[$orderId] : []
			];
		}

		return $sortedDataDiscount;
	}

	/**
	 * Collect coupons and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 */
	private function collectCoupons($orderIds)
	{
		$couponList = [];

		$couponsIterator = Internals\OrderCouponsTable::getList(array(
			'select' => array(
				'*',
				'MODULE_ID' => 'ORDER_DISCOUNT.MODULE_ID',
				'DISCOUNT_ID' => 'ORDER_DISCOUNT.DISCOUNT_ID',
				'DISCOUNT_NAME' => 'ORDER_DISCOUNT.NAME',
				'DISCOUNT_DESCR' => 'ORDER_DISCOUNT.ACTIONS_DESCR',
			),
			'filter' => array('=ORDER_ID' => $orderIds),
			'order' => array('ID' => 'ASC')
		));

		while ($coupon = $couponsIterator->fetch())
		{
			foreach ($coupon['DISCOUNT_DESCR'] as $discountDescriptionArray)
			{
				foreach ($discountDescriptionArray as $descriptionList)
				{
					if (is_array($descriptionList))
					{
						$coupon['DISCOUNT_SIZE'] = Sale\Discount\Formatter::formatRow($descriptionList);
					}
				}
			}

			$couponList[$coupon['ORDER_ID']][$coupon['COUPON']] = $coupon;
		}

		return $couponList;
	}

	/**
	 * Collect discount data and sort by orders's ids
	 *
	 * @param $orderIds
	 *
	 * @return array
	 */
	private function collectRules($orderIds)
	{
		$sortedRules =
		$discountList =
		$rulesList = [];

		$ruleIterator = Internals\OrderRulesTable::getList(array(
			'filter' => array('=ORDER_ID' => $orderIds),
			'order' => array('ID' => 'ASC'),
			'select' => ['*', 'RULE_DESCR' => 'DESCR.DESCR', 'RULE_DESCR_ID' => 'DESCR.ID']
		));

		while ($rule = $ruleIterator->fetch())
		{
			$discountList[] = $rule['ORDER_DISCOUNT_ID'];
			$rulesList[$rule['ID']] = $rule;
		}

		$discountList = array_unique($discountList);

		if (!empty($discountList))
		{
			$discountIterator = Internals\OrderDiscountTable::getList(array(
				'filter' => array('@ID' => $discountList),
			));

			while ($discount = $discountIterator->fetch())
			{
				$discountList[$discount['ID']] = $discount;
			}
		}

		foreach ($rulesList as $id => $rule)
		{
			$rule["DISCOUNT_DATA"] = $discountList[$rule['ORDER_DISCOUNT_ID']] ? $discountList[$rule['ORDER_DISCOUNT_ID']] : array();
			$sortedRules[$rule['ORDER_ID']][$id] = $rule;
		}

		return $sortedRules;
	}

	/**
	 * @param int $index
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function deleteItem($index)
	{
		if (!isset($this->collection[$index]))
			throw new Main\ArgumentOutOfRangeException("Collection item index wrong");

		unset($this->collection[$index]);
	}
}
