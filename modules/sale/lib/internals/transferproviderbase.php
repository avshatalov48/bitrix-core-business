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

			foreach ($shippedProductList as $isShipped)
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
					$basketItem = $shipmentItem->getBasketItem();
					$shipment = $shipmentItem->getCollection()->getShipment();

					if (isset($itemData['NEED_RESERVE_BY_STORE_LIST'][$shipmentItemIndex]))
					{
						$needReverseByStore = $itemData['NEED_RESERVE_BY_STORE_LIST'][$shipmentItemIndex];
						foreach ($needReverseByStore as $storeId => $isNeeded)
						{
							/** @var Sale\ReserveQuantityCollection $reserveQuantityCollection */
							$reserveQuantityCollection = $basketItem->getReserveQuantityCollection();

							if (
								$isNeeded
								&& $reserveQuantityCollection
								&& $shipment->needShip() !== Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
							{
								/** @var Sale\ReserveQuantity $reserve */
								foreach ($reserveQuantityCollection as $reserve)
								{
									if ($reserve->getStoreId() !== $storeId)
									{
										continue;
									}

									if (isset($itemData['STORE_DATA_LIST'][$shipmentItemIndex][$storeId]))
									{
										$reserveQuantity = $itemData['STORE_DATA_LIST'][$shipmentItemIndex][$storeId]['RESERVED_QUANTITY'];
									}
									else
									{
										$reserveQuantity = $shipmentItem->getQuantity();
									}

									if ($reserve->getQuantity() > $reserveQuantity)
									{
										$reserve->setFieldNoDemand('QUANTITY', $reserve->getQuantity() - $reserveQuantity);
									}
									else
									{
										$reserve->deleteNoDemand();
									}
								}
							}
						}

						if ($shipment->needShip() !== Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
						{
							$shipmentItem->getFields()->set('RESERVED_QUANTITY', 0);
						}
					}

					$shipmentIndex = $shipment->getInternalIndex();
					if (!array_key_exists($shipmentIndex, $shipmentList))
					{
						$shipmentList[$shipmentIndex] = $shipment;
					}

					if (
						!isset($productIndex[$shipmentIndex][$shipmentItemIndex])
						|| !in_array($productId, $productIndex[$shipmentIndex][$shipmentItemIndex])
					)
					{
						$productIndex[$shipmentIndex][$shipmentItemIndex] = $productId;
					}
				}
			}
		}

		$reverseProducts = array();
		if ($needReverse && !empty($productIndex))
		{
			foreach ($productIndex as $productList)
			{
				foreach ($productList as $productId)
				{
					$isExistsProduct = array_key_exists($productId, $shippedProductList);

					if (
						$isExistsProduct
						&& $shippedProductList[$productId] === true
						&& empty($reverseProducts[$productId])
					)
					{
						$reverseProducts[$productId] = $products[$productId];
						$reverseProducts[$productId]['QUANTITY'] *= -1;
					}
				}
			}
		}

		if ($needReverse && !empty($reverseProducts))
		{
			$r = $this->ship($reverseProducts);
		}

		return $result;
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
	abstract public function getAvailableQuantityByStore(array $products);

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

	/**
	 * @param array $products
	 * @return Sale\Result
	 */
	public function writeOffProductBatches(array $products): Sale\Result
	{
		return new Sale\Result();
	}

	/**
	 * @param array $products
	 * @return Sale\Result
	 */
	public function returnProductBatches(array $products): Sale\Result
	{
		return new Sale\Result();
	}
}