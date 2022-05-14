<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;

/**
 * Class ProviderCreator
 * @package Bitrix\Sale\Internals
 */
class ProviderCreator
{
	private $context = array();
	private $pool = array();

	/**
	 * @param array $context
	 *
	 * @return static
	 */
	public static function create(array $context)
	{
		$creator = new static();
		$creator->context = $context;

		return $creator;
	}

	/**
	 * @param Sale\BasketItemBase $basketItem
	 */
	public function addBasketItem(Sale\BasketItemBase $basketItem)
	{
		$providerName = $basketItem->getProviderName();
		if (empty($providerName))
		{
			$providerName = $basketItem->getCallbackFunction();
		}
		$builder = $this->createBuilder($providerName);
		$builder->addProductByBasketItem($basketItem);
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 */
	public function addShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		$basketItem = $shipmentItem->getBasketItem();
		if (!$basketItem)
		{
			return;
		}

		$providerName = $basketItem->getProviderName();
		if (empty($providerName))
		{
			$providerName = $basketItem->getCallbackFunction();
		}
		$builder = $this->createBuilder($providerName);

		$builder->addProductByShipmentItem($shipmentItem);
	}

	/**
	 * @param array $productData
	 */
	public function addProductData(array $productData)
	{
		$builder = $this->createBuilder($productData['PROVIDER_NAME']);
		$builder->addProductData($productData);
	}

	/**
	 * @param Sale\BasketItem $basketItem
	 * @param array $barcodeParams
	 */
	public function addBasketItemBarcodeData(Sale\BasketItem $basketItem, array $barcodeParams)
	{
		$providerName = $basketItem->getProviderName();
		if (empty($providerName))
		{
			$providerName = $basketItem->getCallbackFunction();
		}
		$builder = $this->createBuilder($providerName);
		$builder->addBasketItemBarcodeData($barcodeParams);
	}
	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param array $needShipList
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	public function createItemForShip(Sale\ShipmentItem $shipmentItem, array $needShipList = [])
	{
		$basketItem = $shipmentItem->getBasketItem();

		$shipment = $shipmentItem->getCollection()->getShipment();

		$quantity = $shipmentItem->getQuantity();

		if ($shipment->needShip() == Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_SHIP)
		{
			if ($quantity > 0)
			{
				$quantity *= -1;
			}
		}

		$needUseReserve = $quantity < 0 || Sale\Configuration::isEnableAutomaticReservation();

		$providerName = $basketItem->getProviderName();
		$providerName = $this->clearProviderName($providerName);
		if (empty($needShipList[$providerName]) && $shipmentItem->getReservedQuantity() > 0)
		{
			$quantity = 0;
		}

		$item = [
			'PROVIDER_NAME' => $basketItem->getProviderName(),
			'SHIPMENT_ITEM' => $shipmentItem,
			'BASKET_ITEM' => $basketItem,
			'QUANTITY' =>  $quantity,
			'RESERVED_QUANTITY_BY_STORE' => [
				$shipmentItem->getInternalIndex() => []
			],
		];

		$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem, $needUseReserve);
		if ($storeData)
		{
			$item['STORE_DATA'] = $storeData;

			$needReserveByStore = [];
			foreach ($storeData as $data)
			{
				$item['RESERVED_QUANTITY_BY_STORE'][$shipmentItem->getInternalIndex()][$data['STORE_ID']] = $data['RESERVED_QUANTITY'];

				$needReserveByStore[$data['STORE_ID']] = $data['RESERVED_QUANTITY'] > 0;
			}

			$item['RESERVED_QUANTITY'] = array_sum($item['RESERVED_QUANTITY_BY_STORE'][$shipmentItem->getInternalIndex()]);
			$item['NEED_RESERVE'] = $item['RESERVED_QUANTITY'] > 0;
			$item['NEED_RESERVE_BY_STORE'] = [
				$shipmentItem->getInternalIndex() => $needReserveByStore
			];
		}
		else
		{
			$item['RESERVED_QUANTITY'] = $needUseReserve ? $basketItem->getReservedQuantity() : 0;
			$item['NEED_RESERVE'] = $item['RESERVED_QUANTITY'] > 0;
		}

		return $item;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 */
	public function createItemForReserveByShipmentItem(Sale\ShipmentItem $shipmentItem) : array
	{
		$basketItem = $shipmentItem->getBasketItem();

		$result = [
			'PROVIDER_NAME' => $basketItem->getProviderName(),
			'BASKET_ITEM' => $basketItem,
			'SHIPMENT_ITEM' => $shipmentItem,
			'QUANTITY' => 0,
			'RESERVED_QUANTITY' => 0,
		];

		$shipmentItemQuantity = $shipmentItem->getQuantity();
		if ($shipmentItemQuantity == 0)
		{
			$storeId = Sale\Configuration::getDefaultStoreId();

			$result['QUANTITY_BY_STORE'][$storeId] = $shipmentItemQuantity - $shipmentItem->getReservedQuantity();
			$result['QUANTITY'] += $result['QUANTITY_BY_STORE'][$storeId];

			$result['RESERVED_QUANTITY_BY_STORE'][$storeId] = $shipmentItem->getReservedQuantity();
			$result['RESERVED_QUANTITY'] += $result['RESERVED_QUANTITY_BY_STORE'][$storeId];
		}
		else
		{
			$quantityStoreList = [];
			/** @var Sale\ShipmentItemStore $itemStore */
			foreach ($shipmentItem->getShipmentItemStoreCollection() as $itemStore)
			{
				$storeId = $itemStore->getStoreId();
				if (!isset($quantityStoreList[$storeId]))
				{
					$quantityStoreList[$storeId] = 0;
				}

				$quantityStoreList[$storeId] += $itemStore->getQuantity();
				$shipmentItemQuantity -= $itemStore->getQuantity();
			}

			if ($shipmentItemQuantity > 0)
			{
				$storeId = Sale\Configuration::getDefaultStoreId();
				if (!isset($quantityStoreList[$storeId]))
				{
					$quantityStoreList[$storeId] = 0;
				}

				$quantityStoreList[$storeId] += $shipmentItemQuantity;
			}

			$result['QUANTITY_BY_STORE'] = [];
			$result['RESERVED_QUANTITY_BY_STORE'] = [];

			$shipmentItemReservedQuantity = $shipmentItem->getReservedQuantity();
			foreach ($quantityStoreList as $storeId => $quantity)
			{
				$reserveQuantity = 0;

				if ($shipmentItemReservedQuantity > 0)
				{
					$reserveQuantity = $basketItem->getReserveQuantityCollection()->getQuantityByStoreId($storeId);
					if ($reserveQuantity > $shipmentItemReservedQuantity)
					{
						$reserveQuantity = $shipmentItemReservedQuantity;
					}

					$shipmentItemReservedQuantity -= $reserveQuantity;
				}

				$result['QUANTITY_BY_STORE'][$storeId] = $quantity - $reserveQuantity;
				$result['QUANTITY'] += $result['QUANTITY_BY_STORE'][$storeId];

				$result['RESERVED_QUANTITY_BY_STORE'][$storeId] = $reserveQuantity;
				$result['RESERVED_QUANTITY'] += $result['RESERVED_QUANTITY_BY_STORE'][$storeId];
			}
		}

		return $result;
	}

	/**
	 * @param Sale\ReserveQuantity $reserve
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	public function createItemForReserve(Sale\ReserveQuantity $reserve)
	{
		$basketItem = $reserve->getCollection()->getBasketItem();

		$originalFields = $reserve->getFields()->getOriginalValues();
		$reservedQuantity = $originalFields['QUANTITY'] ?? 0;
		$quantity = $reserve->getField('QUANTITY') - $reservedQuantity;

		return [
			'PROVIDER_NAME' => $basketItem->getProviderName(),
			'RESERVE_ITEM' => $reserve,
			'BASKET_ITEM' => $basketItem,
			'QUANTITY' => $quantity,
			'QUANTITY_BY_STORE' => [
				$reserve->getStoreId() => $quantity
			],
			'RESERVED_QUANTITY' => $reservedQuantity,
			'RESERVED_QUANTITY_BY_STORE' => [
				$reserve->getStoreId() => $reservedQuantity
			]
		];
	}

	/**
	 * @return Sale\Result
	 */
	public function getProductData()
	{
		return $this->callBuilderMethod('getProductData', 'PRODUCT_DATA_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function getAvailableQuantity()
	{
		return $this->callBuilderMethod('getAvailableQuantity', 'AVAILABLE_QUANTITY_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function getAvailableQuantityByStore()
	{
		return $this->callBuilderMethod('getAvailableQuantityByStore', 'AVAILABLE_QUANTITY_LIST_BY_STORE');
	}

	/**
	 * @return Sale\Result
	 */
	public function getAvailableQuantityAndPrice()
	{
		return $this->callBuilderMethod('getAvailableQuantityAndPrice', 'PRODUCT_DATA_LIST');
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 */
	public function setItemsResultAfterTryShip(PoolQuantity $pool, array $productTryShipList)
	{
		$result = new Sale\Result();

		/** @var ProviderBuilderBase $builder */
		foreach ($this->pool as $builder)
		{
			$providerName = $builder->getProviderName();

			if (!$productTryShipList[$providerName])
			{
				continue;
			}

			$r = $builder->setItemsResultAfterTryShip($pool, $productTryShipList[$providerName]);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Result $resultAfterShip
	 *
	 * @return Sale\Result
	 */
	public function setItemsResultAfterShip(Sale\Result $resultAfterShip)
	{
		return $this->callBuilderMethod('setItemsResultAfterShip', 'RESULT_AFTER_SHIP_LIST', $resultAfterShip);
	}

	/**
	 * @param Sale\Result $resultAfterDeliver
	 *
	 * @return Sale\Result
	 */
	public function createItemsResultAfterDeliver(Sale\Result $resultAfterDeliver)
	{
		return $this->callBuilderMethod('createItemsResultAfterDeliver', 'RESULT_AFTER_DELIVER_LIST', $resultAfterDeliver);
	}


	/**
	 * @return Sale\Result
	 */
	public function tryShip()
	{
		return $this->callBuilderMethod('tryShip', 'TRY_SHIP_PRODUCTS_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function isNeedShip()
	{
 		return $this->callBuilderMethod('isNeedShip', 'IS_NEED_SHIP');
	}


	/**
	 * @return Sale\Result
	 */
	public function checkBarcode()
	{
		return $this->callBuilderMethod('checkBarcode', 'BARCODE_CHECK_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function reserve()
	{
		return $this->callBuilderMethod('reserve', 'RESERVED_PRODUCTS_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function ship()
	{
		return $this->callBuilderMethod('ship', 'SHIPPED_PRODUCTS_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function getBundleItems()
	{
		return $this->callBuilderMethod('getBundleItems', 'BUNDLE_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function deliver()
	{
		return $this->callBuilderMethod('deliver', 'DELIVER_PRODUCTS_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function viewProduct()
	{
		return $this->callBuilderMethod('viewProduct', 'VIEW_PRODUCTS_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function getProductStores()
	{
		return $this->callBuilderMethod('getProductStores', 'PRODUCT_STORES_LIST');
	}

	/**
	 * @return Sale\Result
	 */
	public function recurring()
	{
		return $this->callBuilderMethod('recurring', 'RECURRING_PRODUCTS_LIST');
	}

	/**
	 * @param Sale\Result $resultAfterDeliver
	 *
	 * @return Sale\Result
	 */
	public function createItemsResultAfterRecurring(Sale\Result $resultAfterDeliver)
	{
		return $this->callBuilderMethod('createItemsResultAfterDeliver', 'RESULT_AFTER_DELIVER_LIST', $resultAfterDeliver);
	}

	/**
	 * @param $method
	 * @param $outputName
	 * @param null $methodParameters
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function callBuilderMethod($method, $outputName, $methodParameters = null)
	{
		$result = new Sale\Result();

		$resultList = array();

		/** @var ProviderBuilderBase $builder */
		foreach ($this->pool as $builder)
		{
			if (!method_exists($builder, $method))
			{
				throw new Main\ArgumentOutOfRangeException('method');
			}

			if (!$methodParameters)
			{
				/** @var Sale\Result $r */
				$r = $builder->$method($outputName);
			}
			else
			{
				/** @var Sale\Result $r */
				$r = $builder->$method($methodParameters);
			}

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			$data = $r->getData();
			if (!empty($data))
			{
				$providerName = null;

				$providerClass = $builder->getProviderClass();
				if ($providerClass)
				{
					$reflect = new \ReflectionClass($providerClass);
					$providerName = $this->clearProviderName($reflect->getName());
				}

				if (strval($providerName) == '')
				{
					$providerName = $builder->getCallbackFunction();
				}

				if (!empty($data[$outputName]))
				{
					$resultList[$providerName] = $data[$outputName];
				}
			}
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					$outputName => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param $providerName
	 *
	 * @return ProviderBuilderBase
	 */
	private function createBuilder($providerName)
	{
		if (!$this->isExistsProvider($providerName))
		{
			$providerClass = null;

			if (class_exists($providerName))
			{
				$providerClass = new $providerName($this->getContext());
			}

			if (!$providerClass)
			{
				$providerClass = $providerName;
			}

			$builder = ProviderBuilderBase::createBuilder($providerClass, $this->getContext());

			$this->addBuilder($providerName, $builder);
		}
		else
		{
			$builder = $this->getBuilder($providerName);
		}

		return $builder;
	}

	/**
	 * @param string $providerName
	 * @param ProviderBuilderBase $builder
	 */
	private function addBuilder($providerName, ProviderBuilderBase $builder)
	{
		$providerName = $this->clearProviderName($providerName);

		$this->pool[$providerName] = $builder;
	}

	/**
	 * @param $providerName
	 *
	 * @return ProviderBuilderBase|bool
	 */
	private function getBuilder($providerName)
	{
		$providerName = $this->clearProviderName($providerName);

		if ($this->isExistsProvider($providerName))
		{
			return $this->pool[$providerName];
		}

		return false;
	}
	/**
	 * @param $providerName
	 *
	 * @return bool
	 */
	private function isExistsProvider($providerName)
	{
		$providerName = $this->clearProviderName($providerName);
		return (isset($this->pool[$providerName]));
	}

	/**
	 * @return array
	 */
	private function getContext()
	{
		return $this->context;
	}

	/**
	 * @param $providerName
	 *
	 * @return string
	 */
	private function clearProviderName($providerName)
	{
		if (!empty($providerName) && $providerName[0] == "\\")
		{
			$providerName = ltrim($providerName, '\\');
		}

		return $providerName;
	}
}