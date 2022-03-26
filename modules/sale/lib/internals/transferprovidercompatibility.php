<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Catalog;
use Bitrix\Sale;
use Bitrix\Main;

/**
 * Class TransferProviderCompatibility
 */
class TransferProviderCompatibility extends TransferProviderBase
{
	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function tryShip(array $products)
	{
		Main\Loader::includeModule('catalog');
		$result = new Sale\Result();

		$tryShipmentItemList = array();
		$reservedQuantityList = array();
		$shipmentItemParents = array();

		$shipmentItemList = static::getShipmentItemListFromProducts($products);
		if (!empty($shipmentItemList))
		{
			/** @var Sale\ShipmentItem $shipmentItem */
			foreach ($shipmentItemList as $shipmentItem)
			{
				/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
				$shipmentItemCollection = $shipmentItem->getCollection();
				if (!$shipmentItemCollection)
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
				}

				$shipment = $shipmentItemCollection->getShipment();
				if (!$shipment)
				{
					throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
				}

				$shipmentItemParents[$shipmentItem->getInternalIndex()] = $shipment;

				$basketItem = $shipmentItem->getBasketItem();

				$provider = $basketItem->getProviderEntity();

				if ($provider instanceof Catalog\Product\CatalogProvider)
				{
					continue;
				}

				if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_SHIP
					&& !array_key_exists($shipment->getInternalIndex(), $reservedQuantityList))
				{
					$reservedQuantityList[$shipment->getInternalIndex()] = static::getReservedQuantity($shipment);
				}

				$tryShipmentItemList[$shipmentItem->getInternalIndex()] = $shipmentItem;
			}
		}

		if (!empty($tryShipmentItemList))
		{

			$reservedQuantityList = array();

			$r = Sale\Provider::tryShipmentItemList($tryShipmentItemList);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			$data = $r->getData();
			if (!empty($data) && is_array($data))
			{
				/**
				 * @var string $basketCode
				 * @var Sale\Result $resultTryShipment
				 */
				foreach ($data as $basketCode => $resultTryShipment)
				{
					if (!$resultTryShipment->isSuccess())
					{
						$result->addErrors($resultTryShipment->getErrors());
					}
				}
			}

			if ($result->isSuccess())
			{
				if (!empty($reservedQuantityList))
				{
					static::setReservedQuantityToShipmentItem($tryShipmentItemList, $reservedQuantityList);
				}

				$resultList = static::createListFromTryShipmentResult($tryShipmentItemList, $r);
			}
		}

		if (!empty($resultList))
		{
			$result->addData(
				array(
					'TRY_SHIP_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function isNeedShip(array $products)
	{
		Main\Loader::includeModule('catalog');
		$result = new Sale\Result();

		$resultNeedShipList = array();
		$needShipmentItemList = array();

		$shipmentItemList = static::getShipmentItemListFromProducts($products);
		if (!empty($shipmentItemList))
		{
			/** @var Sale\ShipmentItem $shipmentItem */
			foreach ($shipmentItemList as $shipmentItem)
			{
				/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
				$shipmentItemCollection = $shipmentItem->getCollection();
				if (!$shipmentItemCollection)
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
				}

				$shipment = $shipmentItemCollection->getShipment();
				if (!$shipment)
				{
					throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
				}

				$basketItem = $shipmentItem->getBasketItem();

				$provider = $basketItem->getProviderEntity();

				if ($provider instanceof Catalog\Product\CatalogProvider)
				{
					continue;
				}

				$needShipmentItemList[$shipmentItem->getInternalIndex()] = $shipmentItem;
			}
		}

		if (!empty($needShipmentItemList))
		{
			$r = Sale\Provider::isNeedShip($needShipmentItemList);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			$data = $r->getData();
			if (!empty($data) && is_array($data))
			{
				$needShipmentItemList = $data;
			}

		}

		if (!empty($needShipmentItemList))
		{
			$result->setData(
				array(
					'IS_NEED_SHIP' => $needShipmentItemList
				)
			);
		}

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	private static function getReservedQuantity(Sale\Shipment $shipment)
	{
		$reservedQuantityList = array();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		if (!$shipmentItemCollection)
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
		}

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			$reservedQuantityList[$shipmentItem->getInternalIndex()] = $shipmentItem->getReservedQuantity();
		}

		return $reservedQuantityList;
	}

	/**
	 * @param Sale\ShipmentItem[] $shipmentItemList
	 * @param array $reservedQuantityList
	 *
	 * @throws Main\ObjectNotFoundException
	 */
	private static function setReservedQuantityToShipmentItem($shipmentItemList, array $reservedQuantityList)
	{
		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemList as $shipmentItem)
		{
			$shipmentItemIndex = $shipmentItem->getInternalIndex();
			if (!empty($reservedQuantityList[$shipmentItemIndex]))
			{
				$shipmentItem->setFieldNoDemand('RESERVED_QUANTITY', $reservedQuantityList[$shipmentItemIndex]);
			}
		}

	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function ship(array $products)
	{
		$basketItemList = array();
		$shipmentItemList = array();
		$basketItemShipmentItemList = array();
		$shipmentItemQuantityList = array();

		$oneReserveStatus = true;
		$needReserved = null;
		$reservedList = array();

		$resultList = array();

		foreach ($products as $productId => $itemData)
		{
			$fields = $itemData;

			if (!empty($fields['SHIPMENT_ITEM_LIST']))
			{
				/** @var Sale\ShipmentItem $shipmentItem */
				foreach ($fields['SHIPMENT_ITEM_LIST'] as $shipmentIndexItem => $shipmentItem)
				{
					$shipmentItemList[$shipmentIndexItem] = $shipmentItem;

					$basketItem = $shipmentItem->getBasketItem();
					if (!$basketItem)
					{
						throw new Main\ObjectNotFoundException('Entity "BasketItemBase" not found');
					}
//
					$basketCode = $basketItem->getBasketCode();
					$basketItemList[$basketCode] = $basketItem;
//
//					/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
//					$shipmentItemCollection = $shipmentItem->getCollection();
//					if (!$shipmentItemCollection)
//					{
//						throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
//					}
//
//					$shipment = $shipmentItemCollection->getShipment();
//					if (!$shipment)
//					{
//						throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
//					}
//
					$basketItemShipmentItemList[$basketCode][$shipmentIndexItem] = $shipmentItem;
				}
			}

			if (!empty($fields['NEED_RESERVE_LIST']))
			{
				foreach ($fields['NEED_RESERVE_LIST'] as $shipmentItemIndex => $reserved)
				{
					$reserveValue = $reserved ? 'Y': 'N';

					$reservedList[$reserveValue][] = $shipmentItemIndex;
					if (!empty($reservedList) && !isset($reservedList[$reserveValue]))
					{
						$oneReserveStatus = false;
					}
					elseif ($needReserved === null)
					{
						$needReserved = $reserved;
					}

				}
			}

			if (!empty($fields['SHIPMENT_ITEM_QUANTITY_LIST']))
			{
				foreach ($fields['SHIPMENT_ITEM_QUANTITY_LIST'] as $shipmentItemIndex => $quantity)
				{
					$shipmentItemQuantityList[$fields['BASKET_CODE']][$shipmentItemIndex] = $quantity;
				}
			}

			$resultList[$productId] = false;
		}

		$result = new Sale\Result();

		$r = static::tryShip($products);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		if (!empty($basketItemList))
		{
			/** @var Sale\BasketItemBase $basketItem */
			foreach ($basketItemList as $basketItem)
			{
				$productId = $basketItem->getProductId();
				$productData = $products[$productId];
				$basketCode = $basketItem->getBasketCode();
				$quantity = $productData['QUANTITY_LIST'][$basketCode];

				$shipmentFieldsList = array();
				if (!$oneReserveStatus)
				{
					/**
					 * @var string $reserveValue
					 * @var Sale\ShipmentItem $shipmentItem
					 */
					foreach ($reservedList as $reserveValue => $shipmentItemIndexList)
					{
						$quantity = 0;
						foreach ($shipmentItemIndexList as $shipmentItemIndex)
						{
							if (isset($shipmentItemQuantityList[$basketCode][$shipmentItemIndex]))
							{
								$quantity += $shipmentItemQuantityList[$basketCode][$shipmentItemIndex];
							}
						}


						$shipmentFieldsList[] = array(
							'BASKET_ITEM' => $basketItem,
							'BASKET_CODE' => $basketItem->getBasketCode(),
							'PRODUCT_ID' => $productId,
							'QUANTITY' => abs($quantity),
							'DEDUCTED' => $quantity < 0,
							'RESERVED' => $reserveValue,
						);
					}
				}
				else
				{
					$shipmentFieldsList[] = array(
						'BASKET_ITEM' => $basketItem,
						'BASKET_CODE' => $basketItem->getBasketCode(),
						'PRODUCT_ID' => $productId,
						'QUANTITY' => abs($quantity),
						'DEDUCTED' => $quantity < 0,
						'RESERVED' => $needReserved,
					);
				}



				$provider = $basketItem->getProvider();

				$storeDataList = array();

				if (!empty($productData['STORE_DATA_LIST']))
				{
					$storeDataList = $productData['STORE_DATA_LIST'];
				}

				foreach ($shipmentFieldsList as $shipFields)
				{
					$r = Sale\Provider::shipProductData($provider, $shipFields, $storeDataList);
					if ($r->isSuccess())
					{
						$productId = $basketItem->getProductId();
						$resultList[$productId] = true;
					}
					elseif ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
					}
					else
					{
						$result->addErrors($r->getErrors());
					}
				}
			}
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'SHIPPED_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function reserve(array $products)
	{
		$result = new Sale\Result();
		$resultList = array();

		foreach ($products as $productId => $productData)
		{
			$productQuantity = 0;
			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}

			/**
			 * @var Sale\ProviderBase $product
			 * @var Sale\Result $r
			 */
			$r = Sale\Provider::reserveProduct($this->getProviderClass(), $productId, $productQuantity);
			if ($r->isSuccess())
			{
				$fields = $r->getData();
				if (!empty($fields))
				{
					$resultList[$productId] = array(
						'QUANTITY_RESERVED' => $fields['QUANTITY']
					);
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'RESERVED_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return array
	 */
	private static function getShipmentItemListFromProducts(array $products)
	{
		$resultList = array();

		foreach ($products as $productData)
		{
			/** @var Sale\ShipmentItem $shipmentItem */
			foreach ($productData['SHIPMENT_ITEM_LIST'] as $shipmentItem)
			{
				if (!array_key_exists($shipmentItem->getInternalIndex(), $resultList))
				{
					$resultList[$shipmentItem->getInternalIndex()] = $shipmentItem;
				}
			}
		}

		return $resultList;
	}

	/**
	 * @param Sale\ShipmentItem[] $shipmentItemList
	 * @param Sale\Result $result
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	private static function createListFromTryShipmentResult($shipmentItemList, Sale\Result $result)
	{
		if (!$result->isSuccess())
		{
			return array();
		}

		$basketCodeList = array();
		$basketItemList = array();
		foreach ($shipmentItemList as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();
			$basketCodeList[$shipmentItem->getInternalIndex()] = $basketItem->getBasketCode();

			$basketItemList[$basketItem->getBasketCode()] = $basketItem;
		}

		$resultList = array();

		$data = $result->getData();
		if (!empty($data))
		{
			/**
			 * @var string $basketCode
			 * @var Sale\Result $resultTryShipment
			 */
			foreach ($data as $basketCode => $resultTryShipment)
			{
				if (!isset($basketItemList[$basketCode]))
				{
					throw new Main\ObjectNotFoundException('Entity "Basket" not found');
				}

				$basketItem = $basketItemList[$basketCode];

				$resultList[$basketItem->getProductId()] = $resultTryShipment->isSuccess();
			}
		}

		return $resultList;
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $products
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentException
	 */
	public function setItemsResultAfterTryShip(PoolQuantity $pool, array $products, array $productTryShipList)
	{
		return new Sale\Result();
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $products
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentException
	 */
	public function setItemsResultAfterTryUnship(PoolQuantity $pool, array $products, array $productTryShipList)
	{
		return new Sale\Result();
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getAvailableQuantity(array $products)
	{
		/** @var Sale\SaleProviderBase $providerClass */
		return Sale\Provider::getAvailableQuantity($this->getProviderClass(), $products, $this->getContext());
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getAvailableQuantityAndPrice(array $products)
	{
		/** @var Sale\SaleProviderBase $providerClass */
		return Sale\Provider::getAvailableQuantityAndPrice($this->getProviderClass(), $products, $this->getContext());
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getProductData(array $products)
	{
		$providerName = null;
		$providerClass = $this->getProviderClass();
		if ($providerClass)
		{
			$reflect = new \ReflectionClass($providerClass);
			$providerName = $reflect->getName();
		}

		return Sale\Provider::getProductDataByList($products, $providerName, array('PRICE','QUANTITY','CHECK_DISCOUNT', 'AVAILABLE_QUANTITY', 'COUPONS'), $this->getContext());
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	public function getBundleItems(array $products)
	{
		return Sale\Provider::getBundleChildItems($this->getProviderClass(), $products);
	}

	/**
	 * @return Sale\Result
	 */
	public function getStoresCount()
	{
		$context = $this->getContext();
		$parameters = (isset($context['SITE_ID'])? array("SITE_ID" => $context['SITE_ID']) : array());
		return Sale\Provider::getStoresCount($parameters);
	}


	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function deliver(array $products)
	{
		Main\Loader::includeModule('catalog');
		$result = new Sale\Result();
		$resultList = array();

		$productOrderList = Catalog\Product\CatalogProvider::createOrderListFromProducts($products);
		foreach ($products as $productId => $productData)
		{
			if (empty($productOrderList[$productId]))
			{
				continue;
			}

			/** @var Sale\Order $order */
			foreach ($productOrderList[$productId] as $order)
			{
				$resultList[$productId] = false;
				if (!empty($productData['SHIPMENT_ITEM_LIST']))
				{

					$quantityList = array();

					if (isset($productData['QUANTITY_LIST']))
					{
						$quantityList = $productData['QUANTITY_LIST'];
					}
					/**
					 * @var $shipmentIndex
					 * @var Sale\ShipmentItem $shipmentItem
					 */
					foreach ($productData['SHIPMENT_ITEM_LIST'] as $shipmentIndex => $shipmentItem)
					{

						/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
						$shipmentItemCollection = $shipmentItem->getCollection();
						if (!$shipmentItemCollection)
						{
							throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
						}

						$shipment = $shipmentItemCollection->getShipment();

						$basketItem = $shipmentItem->getBasketItem();

						$basketCode = $basketItem->getBasketCode();
						$quantity = null;

						if (isset($quantityList[$basketCode]))
						{
							$quantity = $quantityList[$basketCode];
						}

						$fields = array(
							"PRODUCT_ID" => $productId,
							"USER_ID"    => $order->getUserId(),
							"PAID"		 => $order->isPaid() ? 'Y' : 'N',
							"ORDER_ID"   => $order->getId(),

							"BASKET_CODE"   => $basketCode,
							"CALLBACK_FUNC"   => $basketItem->getField('CALLBACK_FUNC'),
							"MODULE"   => $basketItem->getField('MODULE'),
							"ALLOW_DELIVERY"   => $shipment->getField('ALLOW_DELIVERY'),
							"QUANTITY"   => $quantity,
						);

						$r = Sale\Provider::deliverProductData($this->getProviderClass(), $fields);
						if ($r->isSuccess())
						{
							$resultData = $r->getData();

							if (array_key_exists($productId, $resultData))
							{
								$resultList[$productId] = $resultData[$productId];
							}
						}
					}
				}

			}

		}


		if (!empty($resultList))
		{
			$result->setData(
				array(
					'DELIVER_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function viewProduct(array $products)
	{
		global $USER;
		Main\Loader::includeModule('catalog');
		$result = new Sale\Result();
		$resultList = array();

		$productOrderList = Catalog\Product\CatalogProvider::createOrderListFromProducts($products);
		foreach ($products as $productId => $productData)
		{
			$productParamsList = array();
			if (!empty($productOrderList[$productId]))
			{
				/** @var Sale\Order $order */
				foreach ($productOrderList[$productId] as $order)
				{
					$hash = $order->getUserId()."|".$order->getSiteId();

					if (!isset($productParamsList[$hash]))
					{
						$productParamsList[$hash] = array(
							'PRODUCT_ID' => $productId,
							'USER_ID' => $order->getUserId(),
							'SITE_ID' => $order->getSiteId(),
						);
					}

				}
			}
			else
			{
				$hash = $USER->getId() . "|" .SITE_ID;
				if (!isset($productParamsList[$hash]))
				{
					$productParamsList[$hash] = array(
						'PRODUCT_ID' => $productId,
						'USER_ID' => $USER->getId(),
						'SITE_ID' => SITE_ID,
					);
				}
			}

			foreach ($productParamsList as $productParams)
			{
				$r = Sale\Provider::getViewProduct($this->getProviderClass(), $productParams);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (array_key_exists($productId, $resultData))
					{
						$resultList[$productId] = $resultData[$productId];
					}

				}
			}

		}


		if (!empty($resultList))
		{
			$result->setData(
				array(
					'VIEW_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function getProductListStores(array $products)
	{
		Main\Loader::includeModule('catalog');
		$result = new Sale\Result();
		$resultList = array();


		$productOrderList = Catalog\Product\CatalogProvider::createOrderListFromProducts($products);
		foreach ($products as $productId => $productData)
		{
			$productParamsList = array();
			if (!empty($productOrderList[$productId]))
			{
				/** @var Sale\Order $order */
				foreach ($productOrderList[$productId] as $order)
				{
					$hash = $order->getSiteId();

					if (!isset($productParamsList[$hash]))
					{
						$productParamsList[$hash] = array(
							'PRODUCT_ID' => $productId,
							'SITE_ID' => $order->getSiteId(),
						);
					}

				}
			}
			else
			{
				$hash = SITE_ID;
				if (!isset($productParamsList[$hash]))
				{
					$productParamsList[$hash] = array(
						'PRODUCT_ID' => $productId,
						'SITE_ID' => SITE_ID,
					);
				}
			}

			foreach ($productParamsList as $productParams)
			{
				$r = Sale\Provider::getStores($this->getProviderClass(), $productParams);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (array_key_exists($productId, $resultData))
					{
						$resultList[$productId] = $resultData[$productId];
					}

				}
			}

		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'PRODUCT_STORES_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $items
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function checkBarcode(array $items)
	{
		$result = new Sale\Result();
		$resultList = array();

		foreach ($items as $productId => $barcodeParams)
		{
			/**
			 * @var Sale\ProviderBase $product
			 * @var Sale\Result $r
			 */
			$r = Sale\Provider::checkBarcode($this->getProviderClass(), $barcodeParams);
			if ($r->isSuccess())
			{
				$resultData = $r->getData();
				if (!empty($resultData) && array_key_exists($productId, $resultData))
				{
					$resultList[$barcodeParams['BARCODE']] = $resultData[$productId];
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'RESERVED_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function recurring(array $products)
	{
		global $USER;
		Main\Loader::includeModule('catalog');

		$result = new Sale\Result();
		$resultList = array();

		$productOrderList = Catalog\Product\CatalogProvider::createOrderListFromProducts($products);
		foreach ($products as $productId => $productData)
		{
			$productParamsList = array();
			if (!empty($productOrderList[$productId]))
			{
				/** @var Sale\Order $order */
				foreach ($productOrderList[$productId] as $order)
				{
					$hash = $order->getUserId();

					if (!isset($productParamsList[$hash]))
					{
						$productParamsList[$hash] = array(
							'PRODUCT_ID' => $productId,
							'USER_ID' => $order->getUserId(),
						);
					}

				}
			}
			else
			{
				$hash = $USER->getId();
				if (!isset($productParamsList[$hash]))
				{
					$productParamsList[$hash] = array(
						'PRODUCT_ID' => $productId,
						'USER_ID' => $USER->getId(),
					);
				}
			}

			foreach ($productParamsList as $productParams)
			{
				$r = Sale\Provider::recurringProduct($this->getProviderClass(), $productParams);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();

					if (array_key_exists($productId, $resultData))
					{
						$resultList[$productId] = $resultData[$productId];
					}
				}
			}

		}


		if (!empty($resultList))
		{
			$result->setData(
				array(
					'RECURRING_PRODUCTS_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getAvailableQuantityByStore(array $products)
	{
		$result = $this->getAvailableQuantity($products);

		$resultData = $result->getData();
		if (!array_key_exists('AVAILABLE_QUANTITY_LIST', $resultData))
		{
			return $result;
		}

		$quantityByStore = [];

		foreach ($products as $productId => $item)
		{
			$quantityByStore[$productId] = $this->distributeQuantityByStore(
				$item['QUANTITY_LIST_BY_STORE'],
				$resultData['AVAILABLE_QUANTITY_LIST'][$productId]
			);
		}

		return $result->setData(['AVAILABLE_QUANTITY_LIST_BY_STORE' => $quantityByStore]);
	}

	private function distributeQuantityByStore($needQuantityList, $availableQuantity) : array
	{
		$result = [];

		foreach ($needQuantityList as $quantityByStore)
		{
			if (is_array($quantityByStore))
			{
				foreach ($quantityByStore as $storeId => $quantity)
				{
					if (abs($quantity) < abs($availableQuantity))
					{
						$result[$storeId] = $quantity;
						$availableQuantity -= $quantity;
					}
					else
					{
						$result[$storeId] = $availableQuantity;
						$availableQuantity = 0;
					}
				}
			}
		}

		return $result;
	}
}