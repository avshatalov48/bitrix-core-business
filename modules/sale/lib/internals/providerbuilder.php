<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class ProviderBuilder
 * @package Bitrix\Sale\Internals
 */
class ProviderBuilder extends ProviderBuilderBase
{
	/**
	 * @param Sale\BasketItemBase $basketItem
	 */
	public function addProductByBasketItem(Sale\BasketItemBase $basketItem)
	{
		$fields = array(
			'ITEM_CODE' => $basketItem->getProductId(),
			'BASKET_ID' => $basketItem->getId(),
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $basketItem->getProductId(),
			'QUANTITY' => $basketItem->getNotPurchasedQuantity(),
			'RESERVED_QUANTITY' => $basketItem->getReservedQuantity(),
			'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
			'BUNDLE_CHILD' => false,
			'SUBSCRIBE' => $basketItem->getField('SUBSCRIBE') === 'Y',
		);

		if ($basketItem instanceof Sale\BasketItem)
		{
			$fields['BUNDLE_CHILD'] = $basketItem->isBundleChild();
		}

		$this->addItem($basketItem->getProductId(), $fields);
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 */
	public function addProductByShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		$basketItem = $shipmentItem->getBasketItem();
		$productId = $basketItem->getProductId();

		$fields = [
			'ITEM_CODE' => $productId,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $shipmentItem->getQuantity(),
			'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
			'BUNDLE_CHILD' => $basketItem->isBundleChild(),
			'SHIPMENT_ITEM_DATA' => [
				$shipmentItem->getInternalIndex() => $shipmentItem->getQuantity()
			],
			'SHIPMENT_ITEM' => $shipmentItem,
		];

		if (Sale\Configuration::useStoreControl())
		{
			$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem);

			$reservedQuantity = 0;
			$needReserveByStore = [];
			if ($storeData)
			{
				foreach ($storeData as $item)
				{
					$reservedQuantity += $item['RESERVED_QUANTITY'];

					$needReserveByStore[$item['STORE_ID']] = $item['RESERVED_QUANTITY'] > 0;
				}
			}

			$fields['STORE_DATA'] = array(
				$shipmentItem->getInternalIndex() => $storeData
			);

			$fields['NEED_RESERVE_BY_STORE'] = [
				$shipmentItem->getInternalIndex() => $needReserveByStore
			];
		}
		else
		{
			$reservedQuantity = $basketItem->getReservedQuantity();
		}

		$fields['RESERVED_QUANTITY'] = $reservedQuantity;
		$fields['NEED_RESERVE'] = [
			$shipmentItem->getInternalIndex() => $reservedQuantity > 0
		];

		$this->addItem($productId, $fields);
	}

	/**
	 * @param array $productData
	 * @throws Main\ObjectNotFoundException
	 */
	public function addProductData(array $productData)
	{
		if ($productData['QUANTITY'] == 0)
		{
			return;
		}

		/** @var Sale\ShipmentItem $shipmentItem */
		$shipmentItem = $productData['SHIPMENT_ITEM'] ?? null;
		$basketItem = $productData['BASKET_ITEM'];
		$productId = $productData['PRODUCT_ID'] ?? $basketItem->getProductId();

		$fields = [
			'ITEM_CODE' => $productId,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $productData['QUANTITY'],
			'BUNDLE_PARENT' => $basketItem->isBundleParent(),
			'BUNDLE_CHILD' => $basketItem->isBundleChild(),
			'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
			'RESERVED_QUANTITY' => $productData['RESERVED_QUANTITY'] ?? 0.0,
		];

		if ($shipmentItem)
		{
			$fields['SHIPMENT_ITEM'] = $shipmentItem;
			$fields['SHIPMENT_ITEM_DATA'] = [$shipmentItem->getInternalIndex() => $shipmentItem->getQuantity()];
			$fields['NEED_RESERVE'] = [$shipmentItem->getInternalIndex() => $productData["NEED_RESERVE"] ?? null];
		}

		if (Sale\Configuration::useStoreControl())
		{
			if ($shipmentItem)
			{
				$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem);

				if (!empty($storeData))
				{
					$fields['STORE_DATA'] = [
						$shipmentItem->getInternalIndex() => $storeData
					];
				}
			}
		}

		if (isset($productData['NEED_RESERVE_BY_STORE']))
		{
			$fields['NEED_RESERVE_BY_STORE'] = $productData['NEED_RESERVE_BY_STORE'];
		}

		if (isset($productData['QUANTITY_BY_STORE']))
		{
			$fields['QUANTITY_BY_STORE'] = $productData['QUANTITY_BY_STORE'];
		}

		if (isset($productData['RESERVED_QUANTITY_BY_STORE']))
		{
			$fields['RESERVED_QUANTITY_BY_STORE'] = $productData['RESERVED_QUANTITY_BY_STORE'];
		}

		$this->addItem($productId, $fields);
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function setItemsResultAfterTryShip(PoolQuantity $pool, array $productTryShipList)
	{
		$products = $this->getItems();

		if (empty($products))
		{
			return new Sale\Result();
		}

		foreach ($products as $productId => $productData)
		{
			if (!isset($productTryShipList[$productId]))
			{
				continue;
			}

			if (empty($productData['SHIPMENT_ITEM_DATA_LIST']))
			{
				continue;
			}

			if (empty($productData['SHIPMENT_ITEM_LIST']))
			{
				continue;
			}

			/**
			 * @var int $shipmentItemIndex
			 * @var Sale\ShipmentItem $shipmentItem
			 */
			foreach ($productData['SHIPMENT_ITEM_DATA_LIST'] as $shipmentItemIndex => $shipmentItemQuantity)
			{
				$shipmentItem = $productData['SHIPMENT_ITEM_LIST'][$shipmentItemIndex] ?? null;
				if ($shipmentItem === null)
				{
					continue;
				}

				$shipment = $shipmentItem->getCollection()->getShipment();

				$coefficient = -1;
				if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
				{
					$coefficient = 1;
				}

				$shipmentItemQuantity = $shipmentItem->getQuantity();

				/** @var Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
				$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
				if ($shipmentItemStoreCollection)
				{
					/** @var Sale\ShipmentItemStore $shipmentItemStore */
					foreach ($shipmentItemStoreCollection as $shipmentItemStore)
					{
						$quantity = $coefficient * $shipmentItemStore->getQuantity();
						$pool->addByStore(PoolQuantity::POOL_QUANTITY_TYPE, $productId, $shipmentItemStore->getStoreId(), $quantity);

						$shipmentItemQuantity -= $shipmentItemStore->getQuantity();
					}
				}

				if ($shipmentItemQuantity > 0)
				{
					$pool->add(PoolQuantity::POOL_QUANTITY_TYPE, $productId, $shipmentItemQuantity);
				}

				$foundItem = false;
				$poolItems = Sale\Internals\ItemsPool::get($shipment->getOrder()->getInternalId(), $productId);
				if (!empty($poolItems))
				{
					/** @var Sale\ShipmentItem $poolItem */
					foreach ($poolItems as $poolItem)
					{
						if (
							$poolItem instanceof Sale\ShipmentItem
							&& $poolItem->getInternalIndex() == $shipmentItem->getInternalIndex()
						)
						{
							$foundItem = true;
							break;
						}
					}
				}

				if (!$foundItem)
				{
					Sale\Internals\ItemsPool::add($shipment->getOrder()->getInternalId(), $productId, $shipmentItem);
				}
			}
		}

		return new Sale\Result();
	}

	/**
	 * @return string
	 */
	public function getTransferClassName()
	{
		return '\Bitrix\Sale\Internals\TransferProvider';
	}

}
