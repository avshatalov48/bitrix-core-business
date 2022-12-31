<?php
namespace Bitrix\Sale\Archive\Recovery;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Sale\Archive,
	Bitrix\Sale\Internals;

/**
 * Contain realization of Archive\Order object creation from archive.
 * Value of archive version is "1" or "2".
 * 
 * @package Bitrix\Sale\Archive\Recovery
 */
class FirstSchemeBuilder extends Builder
{
	public function tryUnpack()
	{
		$result = new Main\Result();
		if ($this->packedOrder)
		{
			$r = $this->packedOrder->tryUnpack();
			if (!$r->isSuccess())
			{
				$errorData = [
					'TYPE' => 'ORDER',
					'FIELD' => $this->packedOrder->getPackedValue()
				];
				$result->addError(new Main\Error('Unavailable order data for unpacking', 0, $errorData));
			}
		}
		if ($this->packedBasketItems)
		{
			/** @var PackedField $item */
			foreach ($this->packedBasketItems as $itemId => $item)
			{
				$r = $item->tryUnpack();
				if (!$r->isSuccess())
				{
					$errorData = [
						'TYPE' => 'BASKET_ITEM',
						'ID' => $itemId,
						'FIELD' => $item->getPackedValue()
					];
					$result->addError(new Main\Error('Unavailable basket item data for unpacking', 0, $errorData));
				}
			}
		}

		return $result;
	}

	public function buildOrder()
	{
		$archivedOrderData = [];
		$orderFields = $this->entitiesFields['ORDER'];
		if (!empty($this->packedOrder))
		{
			$archivedOrderData = $this->packedOrder->unpack();
			if (is_array($archivedOrderData['ORDER']))
			{
				$orderFields = array_merge($archivedOrderData['ORDER'], $orderFields);
			}
		}

		$this->order = Archive\Order::create($orderFields['LID'], $orderFields['USER_ID'], $orderFields['CURRENCY']);
		$this->order->setPersonTypeId($orderFields['PERSON_TYPE_ID']);

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();

		$basket = $basketClassName::create($orderFields['LID']);
		$this->order->setBasket($basket);
		$basketItemsFields = $this->entitiesFields['BASKET'];
		if (!empty($this->packedBasketItems))
		{
			foreach ($this->packedBasketItems as $basketArchiveId => $basketItem)
			{
				/** @var PackedField $basketItem*/
				$basketItemData = $basketItem->unpack();
				if (is_array($basketItemData))
				{
					$basketItemsFields[$basketArchiveId] = array_merge($basketItemsFields[$basketArchiveId], $basketItemData);
				}
			}
		}
		$basketItemsMap = $this->riseBasket($basketItemsFields);

		$this->order->initFields($orderFields);
		if (is_array($archivedOrderData['PAYMENT']) && !empty($archivedOrderData['PAYMENT']))
		{
			$this->risePayment($archivedOrderData['PAYMENT']);
		}
		if (is_array($archivedOrderData['SHIPMENT']) && !empty($archivedOrderData['SHIPMENT']))
		{
			$basketItemStoreMap = [];
			if (is_array($basketItemsFields))
			{
				foreach ($basketItemsFields as $item)
				{
					$basketItemStoreMap[$item['ID']] = $item['SHIPMENT_BARCODE_ITEMS'];
				}
			}

			$this->riseShipment($archivedOrderData['SHIPMENT'], $basketItemsMap, $basketItemStoreMap);
		}

		if (is_array($archivedOrderData['PROPERTIES']))
		{
			$this->riseOrderProperties($archivedOrderData['PROPERTIES']);
		}
		if (is_array($archivedOrderData['DISCOUNT']))
		{
			$this->riseDiscount($archivedOrderData['DISCOUNT']);
		}
		return $this->order;
	}


	/**
	 * @param array $propertyCollectionArchived
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function riseOrderProperties(array $propertyCollectionArchived = [])
	{
		$propertyCollection = $this->order->getPropertyCollection();
		foreach ($propertyCollectionArchived as $propertyArchived)
		{
			$property = $propertyCollection->getItemByOrderPropertyId($propertyArchived['ORDER_PROPS_ID']);
			if ($property)
			{
				$property->setField('VALUE', $propertyArchived['VALUE']);
			}
		}
		return;
	}

	/**
	 * Load basket from archive.
	 *
	 * @param array $archivedBasketItems
	 *
	 * @return array $basketItemsMap
	 */
	protected function riseBasket(array $archivedBasketItems = [])
	{
		$basketItemsMap = array();
		$basket = $this->order->getBasket();
		foreach ($archivedBasketItems as &$archivedItem)
		{
			if (empty($archivedItem['SET_PARENT_ID']))
			{
				/** @var Sale\BasketItem $item */
				$item = $basket->createItem($archivedItem['MODULE'], $archivedItem['PRODUCT_ID'], $archivedItem['ID']);
				$this->riseBasketItem($item, $archivedItem);
				$basketItemsMap[$archivedItem['ID']] = $item;
				$type = $archivedItem['TYPE'];
				unset($archivedItem);

				if ($type == Sale\BasketItem::TYPE_SET)
				{
					$bundleCollection = $item->getBundleCollection();
					foreach ($archivedBasketItems as &$bundle)
					{
						if ($item->getId() !== (int)$bundle['SET_PARENT_ID'])
							continue;

						/** @var Sale\BasketItem $itemBundle */
						$itemBundle = $bundleCollection->createItem($bundle['MODULE'], $bundle['PRODUCT_ID']);
						$this->riseBasketItem($itemBundle, $bundle);
						$basketItemsMap[$bundle['ID']] = $itemBundle;
						unset($bundle);
					}
				}
			}
		}

		return $basketItemsMap;
	}

	/**
	 * Load basket items with properties from archive.
	 *
	 * @param Sale\BasketItem $item
	 * @param array $data
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function riseBasketItem(&$item, $data = array())
	{
		$basketItemProperties = $data["PROPERTY_ITEMS"];
		unset($data["PROPERTY_ITEMS"], $data["SHIPMENT_BARCODE_ITEMS"]);
		$item->setFieldsNoDemand($data);
		$newPropertyCollection = $item->getPropertyCollection();
		if (is_array($basketItemProperties))
		{
			foreach ($basketItemProperties as $oldPropertyFields)
			{
				$propertyItem = $newPropertyCollection->createItem();
				unset($oldPropertyFields['ID'], $oldPropertyFields['BASKET_ID']);

				/** @var Sale\BasketPropertyItem $propertyItem*/
				$propertyItem->setFieldsNoDemand($oldPropertyFields);
			}
		}
	}

	/**
	 * Load payments from archive.
	 *
	 * @param array $paymentCollectionArchived
	 *
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function risePayment(array $paymentCollectionArchived)
	{
		$paymentCollection = $this->order->getPaymentCollection();
		foreach ($paymentCollectionArchived as $oldPayment)
		{
			/** @var Sale\Payment $newPaymentItem */
			$newPaymentItem = $paymentCollection->createItem();
			$newPaymentItem->setFieldsNoDemand($oldPayment);
		}
		return;
	}

	/**
	 * Load shipments with items from archive.
	 * 
	 * @param array $shipmentCollectionArchived
	 * @param array $basketItemsMap
	 * @param array $itemsStoeMap
	 *
	 * @throws Main\NotSupportedException
	 */
	protected function riseShipment(array $shipmentCollectionArchived, array $basketItemsMap, array $itemsStoreMap = [])
	{
		/** @var Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $this->order->getShipmentCollection();
		foreach ($shipmentCollectionArchived as $oldShipment)
		{
			$oldShipmentCollections = $oldShipment['SHIPMENT_ITEM'];
			unset($oldShipment['SHIPMENT_ITEM']);
			/** @var Sale\Shipment $newShipmentItem */
			$newShipmentItem = $shipmentCollection->createItem();
			$newShipmentItemCollection = $newShipmentItem->getShipmentItemCollection();
			if (is_array($oldShipmentCollections))
			{
				foreach ($oldShipmentCollections as $oldItemStore)
				{
					$basketItemId = $oldItemStore['BASKET_ID'];

					if (empty($basketItemsMap[$basketItemId]))
						continue;

					/** @var Sale\ShipmentItem $shipmentItem */
					$shipmentItem = $newShipmentItemCollection->createItem($basketItemsMap[$basketItemId]);
					$shipmentItem->setFieldsNoDemand($oldItemStore);
					$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
					if ($shipmentItemStoreCollection)
					{
						/** @var Sale\ShipmentItemStore $itemStore */
						$itemStore = $shipmentItemStoreCollection->createItem($basketItemsMap[$basketItemId]);
						$oldBasketBarcodeData = $itemsStoreMap[$basketItemId][$oldItemStore['ID']];
						if (is_array($oldBasketBarcodeData) && empty($oldBasketBarcodeData))
						{
							$itemStore->setFieldsNoDemand($oldBasketBarcodeData);
						}
					}
				}
			}

			$newShipmentItem->setFieldsNoDemand($oldShipment);
		}
	}

	/**
	 * Return discount's array from archive.
	 * The array includes information about discounts of base order.
	 * 
	 * @param array $discountData
	 *
	 * @return mixed
	 */
	protected function riseDiscount($discountData)
	{
		$discountResultList = array();

		$resultData = array(
			'BASKET' => array(),
			'ORDER' => array(),
			'APPLY_BLOCKS' => array(),
			'DISCOUNT_LIST' => array(),
			'DISCOUNT_MODULES' => array(),
			'COUPON_LIST' => array(),
			'SHIPMENTS_ID' => array(),
			'DATA' => array()
		);

		$resultData['DATA'] = $this->prepareDiscountOrderData($discountData);

		$orderDiscountData = $resultData['DATA']['ORDER'];

		$orderDiscountIndex =
		$appliedBlocks =
		$orderDiscountLink =
		$couponAppliedList = [];

		foreach ($discountData['RULES_DATA'] as $rule)
		{
			$discountList[] = $rule["DISCOUNT_DATA"];

			if ($rule['APPLY_BLOCK_COUNTER'] < 0)
				continue;

			if ($rule['APPLY'] === 'Y'	&& (int)$rule['COUPON_ID'] > 0)
			{
				$couponAppliedList[] = (int)$rule['COUPON_ID'];
			}

			$blockCounter = $rule['APPLY_BLOCK_COUNTER'];

			if (!isset($orderDiscountIndex[$blockCounter]))
				$orderDiscountIndex[$blockCounter] = 0;

			if (!isset($appliedBlocks[$blockCounter]))
			{
				$appliedBlocks[$blockCounter] = array(
					'BASKET' => array(),
					'BASKET_ROUND' => array(),
					'ORDER' => array(),
					'ORDER_ROUND' => array()
				);
			}

			if ($rule['MODULE_ID'] == 'sale')
			{
				$orderDiscountId = (int)$rule['ORDER_DISCOUNT_ID'];
				$orderDiscountItem = $orderDiscountLink[$orderDiscountId];
				if (!isset($orderDiscountItem))
				{
					$appliedBlocks[$blockCounter]['ORDER'][$orderDiscountIndex[$blockCounter]] = array(
						'ORDER_ID' => $rule['ORDER_ID'],
						'DISCOUNT_ID' => $rule['ORDER_DISCOUNT_ID'],
						'ORDER_COUPON_ID' => $rule['ORDER_COUPON_ID'],
						'COUPON_ID' => ($rule['COUPON_ID'] > 0 ? $rule['COUPON_ID'] : ''),
						'RESULT' => array(),
						'ACTION_BLOCK_LIST' => true
					);
					$orderDiscountItem = &$appliedBlocks[$blockCounter]['ORDER'][$orderDiscountIndex[$blockCounter]];
					$orderDiscountIndex[$blockCounter]++;
				}

				$ruleItem = array(
					'RULE_ID' => $rule['ID'],
					'APPLY' => $rule['APPLY'],
					'RULE_DESCR_ID' => $rule['RULE_DESCR_ID'],
					'ACTION_BLOCK_LIST' => (!empty($rule['ACTION_BLOCK_LIST']) && is_array($rule['ACTION_BLOCK_LIST']) ? $rule['ACTION_BLOCK_LIST'] : null)
				);
				
				if (!empty($rule['RULE_DESCR']) && is_array($rule['RULE_DESCR']))
				{
					$ruleItem['DESCR_DATA'] = $rule['RULE_DESCR'];
					$ruleItem['DESCR'] = Sale\OrderDiscountManager::formatArrayDescription($rule['RULE_DESCR']);
					$ruleItem['DESCR_ID'] = $rule['RULE_DESCR_ID'];
				}

				switch ($rule['ENTITY_TYPE'])
				{
					case Internals\OrderRulesTable::ENTITY_TYPE_BASKET_ITEM:
						$ruleItem['BASKET_ID'] = $rule['ENTITY_ID'];
						$index = $rule['ENTITY_ID'];
						if (!isset($orderDiscountItem['RESULT']['BASKET']))
							$orderDiscountItem['RESULT']['BASKET'] = array();

						$orderDiscountItem['RESULT']['BASKET'][$index] = $ruleItem;
						if ($ruleItem['ACTION_BLOCK_LIST'] === null)
							$orderDiscountItem['ACTION_BLOCK_LIST'] = false;

						$discountResultList['BASKET'][$ruleItem['BASKET_ID']][] = array(
							'DISCOUNT_ID' => $orderDiscountItem['DISCOUNT_ID'],
							'COUPON_ID' => $orderDiscountItem['COUPON_ID'],
							'APPLY' => $ruleItem['APPLY'],
							'DESCR' => $ruleItem['DESCR']
						);
						break;

					case Internals\OrderRulesTable::ENTITY_TYPE_DELIVERY:
						if (!isset($orderDiscountItem['RESULT']['DELIVERY']))
							$orderDiscountItem['RESULT']['DELIVERY'] = array();

						$ruleItem['DELIVERY_ID'] = ($rule['ENTITY_ID'] > 0 ? $rule['ENTITY_ID'] : (string)$rule['ENTITY_VALUE']);
						$orderDiscountItem['RESULT']['DELIVERY'] = $ruleItem;

						$discountResultList['DELIVERY'][] = array(
							'DISCOUNT_ID' => $orderDiscountItem['DISCOUNT_ID'],
							'COUPON_ID' => $orderDiscountItem['COUPON_ID'],
							'APPLY' => $orderDiscountItem['RESULT']['DELIVERY']['APPLY'],
							'DESCR' => $orderDiscountItem['RESULT']['DELIVERY']['DESCR'][0],
							'DELIVERY_ID' => $orderDiscountItem['RESULT']['DELIVERY']['DELIVERY_ID']
						);

						foreach ($orderDiscountData as $data)
						{
							if ((int)$data['DELIVERY_ID'] == (int)$orderDiscountItem['RESULT']['DELIVERY']['DELIVERY_ID'])
								$resultData['SHIPMENTS_ID'][] = (int)$data['SHIPMENT_ID'];
						}
						break;
				}

				$orderDiscountLink[$orderDiscountId] = $orderDiscountItem;
				unset($ruleItem, $orderDiscountId);
			}
			else
			{
				if ($rule['ENTITY_ID'] <= 0 || $rule['ENTITY_TYPE'] != Internals\OrderRulesTable::ENTITY_TYPE_BASKET_ITEM)
					continue;

				$index = $rule['ENTITY_ID'];

				$ruleResult = array(
					'BASKET_ID' => $rule['ENTITY_ID'],
					'RULE_ID' => $rule['ID'],
					'ORDER_ID' => $rule['ORDER_ID'],
					'DISCOUNT_ID' => $rule['ORDER_DISCOUNT_ID'],
					'ORDER_COUPON_ID' => $rule['ORDER_COUPON_ID'],
					'COUPON_ID' => ($rule['ORDER_COUPON_ID'] > 0 ? $rule['COUPON_ID'] : ''),
					'RESULT' => array(
						'APPLY' => $rule['APPLY']
					),
					'RULE_DESCR_ID' => $rule['RULE_DESCR_ID'],
					'ACTION_BLOCK_LIST' => (isset($rule['ACTION_BLOCK_LIST']) ? $rule['ACTION_BLOCK_LIST'] : null)
				);

				if (!empty($rule['RULE_DESCR']) && is_array($rule['RULE_DESCR']))
				{
					$ruleResult['RESULT']['DESCR_DATA'] = $rule['RULE_DESCR'];
					$ruleResult['RESULT']['DESCR'] = Sale\OrderDiscountManager::formatArrayDescription($rule['RULE_DESCR']);
					$ruleResult['DESCR_ID'] = $rule['RULE_DESCR_ID'];
				}

				if (!isset($appliedBlocks[$blockCounter]['BASKET'][$index]))
					$appliedBlocks[$blockCounter]['BASKET'][$index] = array();
				$appliedBlocks[$blockCounter]['BASKET'][$index][] = $ruleResult;

				$discountResultList['BASKET'][$index][] = array(
					'DISCOUNT_ID' => $ruleResult['DISCOUNT_ID'],
					'COUPON_ID' => $ruleResult['COUPON_ID'],
					'APPLY' => $ruleResult['RESULT']['APPLY'],
					'DESCR' => $ruleResult['RESULT']['DESCR']
				);

				unset($ruleResult);
			}
		}

		$resultData['APPLY_BLOCKS'] = $appliedBlocks;

		$resultData['COUPON_LIST'] = is_array($discountData['COUPON_LIST']) ? $discountData['COUPON_LIST'] : [];
		foreach ($resultData['COUPON_LIST'] as &$coupon)
		{
			if (in_array((int)$coupon['ID'], $couponAppliedList))
			{
				$coupon['APPLY'] = 'Y';
				$coupon['JS_STATUS'] = 'APPLYED';
			}
		}

		$resultData['PRICES'] = $this->prepareDiscountPrices();
		$resultData['RESULT'] = $this->prepareDiscountResult($discountResultList);

		if (!empty($discountList))
		{
			$resultData['DISCOUNT_LIST'] = $this->prepareDiscountList($discountList, $resultData['RESULT']);
		}

		$this->order->setDiscountData($resultData);
		return;
	}

	/**
	 * Prepare discount price's array from restored entities.
	 *
	 * @return mixed
	 */
	protected function prepareDiscountPrices()
	{
		$resultData = array();
		$basket = $this->order->getBasket();
		$basketItems = $basket->getBasketItems();

		/** @var Sale\BasketItem $item */
		foreach ($basketItems as $item)
		{
			$resultData['BASKET'][$item->getId()] = array(
				'BASE_PRICE' => $item->getBasePrice(),
				'PRICE' => $item->getPrice(),
				'DISCOUNT' => $item->getDiscountPrice(),
			);
		}

		$shipmentCollection = $this->order->getShipmentCollection();

		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem())
				continue;

			$resultData['DELIVERY'][$shipment->getId()] = array(
				'BASE_PRICE' => $shipment->getField("BASE_PRICE_DELIVERY"),
				'PRICE' => $shipment->getPrice(),
				'DISCOUNT' => $shipment->getField("BASE_PRICE_DELIVERY") - $shipment->getPrice(),
			);
		}

		return $resultData;
	}

	/**
	 * Prepare discount result's array from restored entities.
	 *
	 * @param $discountData
	 *
	 * @return mixed
	 */
	protected function prepareDiscountResult($discountData)
	{
		$resultData = array();
		$basket = $this->order->getBasket();
		$basketItems = $basket->getBasketItems();

		/** @var Sale\BasketItem $item */
		foreach ($basketItems as $item)
		{
			if (is_array($discountData['BASKET'][$item->getId()]))
			{
				$resultData['BASKET'][$item->getId()] = $discountData['BASKET'][$item->getId()];
			}
		}
		$resultData['DELIVERY'] = $discountData['DELIVERY'];

		return $resultData;
	}

	/**
	 * Prepare discount data
	 *
	 * @param $dataRow
	 *
	 * @return array
	 */
	protected function prepareDiscountOrderData($dataRow)
	{
		$resultData = array();

		foreach ($dataRow['ORDER_DATA'] as $data)
		{
			if (
				$data['ENTITY_TYPE'] = Internals\OrderDiscountDataTable::ENTITY_TYPE_ORDER
				&& $data['ENTITY_ID'] = $this->order->getId()
				&& isset($data['ENTITY_DATA']['DELIVERY']['SHIPMENT_ID'])
			)
			{
				$resultData['ORDER'][$data['ENTITY_DATA']['DELIVERY']['SHIPMENT_ID']] = $data['ENTITY_DATA']['DELIVERY'];
			}

			if ($data['ENTITY_TYPE'] == Internals\OrderDiscountDataTable::ENTITY_TYPE_BASKET_ITEM)
			{
				if (!isset($resultData['DATA']['BASKET']))
					$resultData['BASKET'] = array();
				$data['ENTITY_ID'] = (int)$data['ENTITY_ID'];
				$resultData['BASKET'][$data['ENTITY_ID']] = $data['ENTITY_DATA'];
			}
		}
		return $resultData;
	}

	/**
	 * Prepare discount description array
	 *
	 * @param $discounts
	 * @param $discountResult
	 *
	 * @return mixed
	 */
	protected function prepareDiscountList(array $discounts, array $discountResult)
	{
		$resultData = [];
		$appliedDiscountIds = $this->getAppliedDiscountIds($discountResult);

		foreach ($discounts as $discount)
		{
			$discount['ID'] = (int)$discount['ID'];
			$discount['APPLY'] = in_array($discount['ID'], $appliedDiscountIds) ? 'Y' : 'N';
			$discount['ORDER_DISCOUNT_ID'] = $discount['ID'];
			$discount['SIMPLE_ACTION'] = true;
			if (is_array($discount['ACTIONS_DESCR']['BASKET']))
			{
				foreach ($discount['ACTIONS_DESCR']['BASKET'] as &$description)
				{
					$description = Sale\OrderDiscountManager::formatDescription($description);
				}
			}

			if ($discount['MODULE_ID'] == 'sale')
			{
				$discount['EDIT_PAGE_URL'] = Sale\OrderDiscountManager::getEditUrl(array('ID' => $discount['DISCOUNT_ID']));
			}
			$discount['DISCOUNT_ID'] = $discount['ID'];
			$resultData[$discount['ID']] = $discount;
		}

		return $resultData;
	}

	/**
	 * @param array $discountResult
	 *
	 * @return array
	 */
	protected function getAppliedDiscountIds(array $discountResult)
	{
		$idList = [];
		if (is_array($discountResult['BASKET']))
		{
			foreach ($discountResult['BASKET'] as $discountList)
			{
				if (is_array($discountList))
				{
					foreach ($discountList as $discount)
					{
						if ($discount['APPLY'] === 'Y')
							$idList[] = $discount['DISCOUNT_ID'];
					}
				}
			}
		}

		if (is_array($discountResult['DELIVERY']))
		{
			foreach ($discountResult['DELIVERY'] as $discount)
			{
				if ($discount['APPLY'] === 'Y')
					$idList[] = $discount['DISCOUNT_ID'];
			}
		}

		return array_unique($idList);
	}
}	