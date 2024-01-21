<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Catalog;
use Bitrix\Sale;
use Bitrix\Main;


/**
 * Class TransferProvider
 */
class TransferProvider extends TransferProviderBase
{

	/**
	 * @param $methodName
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function callProviderMethod($methodName, array $products = array())
	{
		/** @var Sale\SaleProviderBase $providerClass */
		$providerClass = $this->getProviderClass();
		if (!method_exists($providerClass, $methodName))
		{
			throw new Main\ArgumentOutOfRangeException('methodName');
		}
		return $providerClass->$methodName($products);
	}

	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function tryShip(array $products)
	{
		return $this->callProviderMethod('tryShip', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function isNeedShip(array $products)
	{
		return $this->callProviderMethod('isNeedShip', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function ship(array $products)
	{
		return $this->callProviderMethod('ship', $products);
	}

	/**
	 * @param array $items
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function checkBarcode(array $items)
	{
		return $this->callProviderMethod('checkBarcode', $items);
	}


	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function reserve(array $products)
	{
		return $this->callProviderMethod('reserve', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return mixed
	 * @throws Main\SystemException
	 */
	public function deliver(array $products)
	{
		return $this->callProviderMethod('deliver', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function viewProduct(array $products)
	{
		return $this->callProviderMethod('viewProduct', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function recurring(array $products)
	{
		return $this->callProviderMethod('recurring', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getProductListStores(array $products)
	{
		return $this->callProviderMethod('getProductListStores', $products);
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $products
	 * @param array $productTryList
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function setItemsResultAfterTryShip(PoolQuantity $pool, array $products, array $productTryList)
	{
		return static::setItemsResultAfterTryShipByCoefficient($pool, $products, $productTryList, 1);
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $products
	 * @param array $productTryList
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function setItemsResultAfterTryUnship(PoolQuantity $pool, array $products, array $productTryList)
	{
		return static::setItemsResultAfterTryShipByCoefficient($pool, $products, $productTryList, -1);
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $products
	 * @param array $productTryList
	 * @param $coefficient
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	private function setItemsResultAfterTryShipByCoefficient(PoolQuantity $pool, array $products, array $productTryList, $coefficient)
	{
		foreach ($products as $productId => $productData)
		{
			if (!isset($productTryList[$productId]))
			{
				continue;
			}

			if (empty($productData['SHIPMENT_ITEM_DATA_LIST']))
				continue;

			/**
			 * @var int $shipmentItemIndex
			 * @var Sale\ShipmentItem $shipmentItem
			 */
			foreach ($productData['SHIPMENT_ITEM_DATA_LIST'] as $shipmentItemIndex => $shipmentItemQuantity)
			{
				$quantity = $coefficient * $shipmentItemQuantity;
				$pool->add(PoolQuantity::POOL_QUANTITY_TYPE, $productId, $quantity);
			}
		}

		return new Sale\Result();
	}

	/**
	 * @param array $products
	 * @param Sale\Result $reserveResult
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function setItemsResultAfterGetData(array $products, Sale\Result $reserveResult)
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
		$result = $this->callProviderMethod('getAvailableQuantity', $products);

		$resultData = $result->getData();
		if (!array_key_exists('AVAILABLE_QUANTITY_LIST', $resultData))
		{
			return $result;
		}

		return $result->setData(['AVAILABLE_QUANTITY_LIST' => $resultData['AVAILABLE_QUANTITY_LIST']]);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getAvailableQuantityByStore(array $products)
	{
		$result = $this->callProviderMethod('getAvailableQuantityByStore', $products);

		$resultData = $result->getData();
		if (!array_key_exists('AVAILABLE_QUANTITY_LIST_BY_STORE', $resultData))
		{
			return $result;
		}

		return $result->setData(['AVAILABLE_QUANTITY_LIST_BY_STORE' => $resultData['AVAILABLE_QUANTITY_LIST_BY_STORE']]);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function getAvailableQuantityAndPrice(array $products)
	{
		$result = $this->callProviderMethod('getAvailableQuantityAndPrice', $products);

		$resultData = $result->getData();

		return $result->setData([
			'PRODUCT_DATA_LIST' => [
				'PRICE_LIST' => $resultData['PRODUCT_DATA_LIST']['PRICE_LIST'],
				'AVAILABLE_QUANTITY_LIST' => $resultData['PRODUCT_DATA_LIST']['AVAILABLE_QUANTITY_LIST']
			]
		]);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	public function getProductData(array $products)
	{
		return $this->callProviderMethod('getProductData', $products);
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	public function getBundleItems(array $products)
	{
		return $this->callProviderMethod('getBundleItems', $products);
	}

	/**
	 * @return Sale\Result
	 */
	public function getStoresCount()
	{
		return $this->callProviderMethod('getStoresCount');
	}

	/**
	 * @return Sale\Result
	 */
	public function writeOffProductBatches(array $products): Sale\Result
	{
		return $this->callProviderMethod('writeOffProductBatches', $products);
	}

	/**
	 * @return Sale\Result
	 */
	public function returnProductBatches(array $products): Sale\Result
	{
		return $this->callProviderMethod('returnProductBatches', $products);
	}
}