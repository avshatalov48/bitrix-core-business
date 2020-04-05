<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class TransferProvider
 */
abstract class TransferProviderBase
{
	protected $providerClass = null;
	protected $context = null;

	/**
	 * TransferProvider constructor.
	 */
	protected function __construct()
	{

	}

	/**
	 * @return null|mixed
	 */
	protected function getProviderClass()
	{
		return $this->providerClass;
	}

	/**
	 * @return null|mixed
	 */
	protected function getProviderName()
	{
		$providerName = null;
		$providerClass = $this->providerClass;

		if ($providerClass)
		{
			$reflect = new \ReflectionClass($providerClass);
			$providerName = $reflect->getName();
		}

		return $providerName;
	}

	/**
	 * @return null|array
	 */
	protected function getContext()
	{
		return $this->context;
	}

	/**
	 * @param $providerClass
	 * @param array $context
	 *
	 * @return static
	 * @throws Main\ArgumentNullException
	 */
	public static function create($providerClass, array $context)
	{
		$transferProvider = new static();
		if ($providerClass)
		{
			$transferProvider->providerClass = $providerClass;
		}

		$transferProvider->context = $context;

		return $transferProvider;
	}

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getProductData(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException|Main\SystemException
	 */
	abstract public function tryShip(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException|Main\SystemException
	 */
	abstract public function isNeedShip(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function ship(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function reserve(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\SystemException
	 */
	abstract public function deliver(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\SystemException
	 */
	abstract public function viewProduct(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 * @throws Main\SystemException
	 */
	abstract public function getProductListStores(array $products);

	/**
	 * @param PoolQuantity $pool
	 * @param array $products
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	abstract public function setItemsResultAfterTryShip(PoolQuantity $pool, array $products, array $productTryShipList);

	/**
	 * @param array $products
	 * @param Sale\Result $resultAfterShip
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function setItemsResultAfterShip(array $products, Sale\Result $resultAfterShip)
	{
		$result = new Sale\Result();

		$needReverse = false;
		$shippedProductList = array();
		$resultData = $resultAfterShip->getData();
		if (!empty($resultData['SHIPPED_PRODUCTS_LIST']))
		{
			$shippedProductList = $resultData['SHIPPED_PRODUCTS_LIST'];

			foreach ($shippedProductList as $productId => $isShipped)
			{
				if ($isShipped === false)
				{
					$needReverse = true;
					break;
				}
			}
		}

		$shipmentList = array();
		$productIndex = array();

		foreach ($products as $productId => $itemData)
		{
			if (!empty($itemData['SHIPMENT_ITEM_LIST']))
			{
				/** @var Sale\ShipmentItem $shipmentItem */
				foreach ($itemData['SHIPMENT_ITEM_LIST'] as $shipmentItemIndex => $shipmentItem)
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

					if ($shipment->needReservation())
					{
						$setReservedQuantity = 0;

						if ($shipment->needShip() === Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
						{
							$setReservedQuantity = $shipmentItem->getQuantity();
						}

						if (!$needReverse)
						{
							$shipmentItem->setFieldNoDemand('RESERVED_QUANTITY', $setReservedQuantity);
						}
					}


					$shipmentIndex = $shipment->getInternalIndex();
					if (!array_key_exists($shipmentIndex, $shipmentList))
					{
						$shipmentList[$shipmentIndex] = $shipment;
					}

					if (!isset($productIndex[$shipmentIndex][$shipmentItemIndex]) || !in_array($productId, $productIndex[$shipmentIndex][$shipmentItemIndex]))
					{
						$productIndex[$shipmentIndex][$shipmentItemIndex] = $productId;
					}

				}
			}
		}

		$reverseProducts = array();
		if ($needReverse && !empty($productIndex))
		{
			foreach ($productIndex as $shipmentIndex => $productList)
			{
				foreach ($productList as $shipmentItemIndex => $productId)
				{
					$isExistsProduct = array_key_exists($productId, $shippedProductList);

					if ($isExistsProduct && $shippedProductList[$productId] === true && empty($reverseProducts[$productId]))
					{
						$reverseProducts[$productId] = $products[$productId];
						$reverseProducts[$productId]['QUANTITY'] *= -1;
					}
				}
			}
		}

		if (!empty($reverseProducts))
		{
			$r = $this->ship($reverseProducts);
		}

		return $result;
	}

	/**
	 * @param array $products
	 * @param Sale\Result $reserveResult
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function setItemsResultAfterReserve(array $products, Sale\Result $reserveResult)
	{
		return new Sale\Result();
	}
	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getAvailableQuantity(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getAvailableQuantityAndPrice(array $products);

	/**
	 * @param array $products
	 *
	 * @return Sale\Result
	 */
	abstract public function getBundleItems(array $products);

	/**
	 * @return Sale\Result
	 */
	abstract public function getStoresCount();

}