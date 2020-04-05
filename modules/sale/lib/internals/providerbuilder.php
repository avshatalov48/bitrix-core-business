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
			'QUANTITY' => $basketItem->getQuantity(),
			'RESERVED_QUANTITY' => $basketItem->getReservedQuantity(),
			'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
			'BUNDLE_CHILD' => false,
			'SUBSCRIBE' => ($basketItem->getField('SUBSCRIBE') == 'Y'),
		);

		if ($basketItem instanceof Sale\BasketItem)
		{
			$fields['BUNDLE_CHILD'] = $basketItem->isBundleChild();
		}

		$this->addItem($basketItem->getProductId(), $fields);
	}

	/**
	 * @param array $basketItemProductData
	 */
	public function addProductByBasketItemProductData(array $basketItemProductData)
	{
		$this->addItem($basketItemProductData['PRODUCT_ID'], $basketItemProductData);
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 */
	public function addProductByShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		$basketItem = $shipmentItem->getBasketItem();
		$productId = $basketItem->getProductId();
		$fields = array(
			'ITEM_CODE' => $productId,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $shipmentItem->getQuantity(),
			'RESERVED_QUANTITY' => $shipmentItem->getReservedQuantity(),
			'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
			'BUNDLE_CHILD' => $basketItem->isBundleChild(),
			'SHIPMENT_ITEM_DATA' => array(
				$shipmentItem->getInternalIndex() => $shipmentItem->getQuantity()
			),
			'SHIPMENT_ITEM' => $shipmentItem,
			'NEED_RESERVE' => array(
				$shipmentItem->getInternalIndex() => $shipmentItem->needReserve()
			),
		);

		if (Sale\Configuration::useStoreControl())
		{
			$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem);

			$fields['STORE_DATA'] = array(
				$shipmentItem->getInternalIndex() => $storeData
			);
		}

		$this->addItem($productId, $fields);
	}

	/**
	 * @param array $shipmentProductData
	 *
	 * @return bool
	 */
	public function addProductByShipmentProductData(array $shipmentProductData)
	{
		if ($shipmentProductData['QUANTITY'] == 0)
		{
			return false;
		}

		/** @var Sale\ShipmentItem $shipmentItem */
		$shipmentItem = $shipmentProductData['SHIPMENT_ITEM'];


		$basketItem = $shipmentItem->getBasketItem();
		$productId = $basketItem->getProductId();

		$fields = array(
			'ITEM_CODE' => $productId,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $shipmentProductData['QUANTITY'],
			'BUNDLE_PARENT' => $basketItem->isBundleParent(),
			'BUNDLE_CHILD' => $basketItem->isBundleChild(),
			'IS_BARCODE_MULTI' => $basketItem->isBarcodeMulti(),
			'SHIPMENT_ITEM_DATA' => array(
				$shipmentItem->getInternalIndex() => $shipmentItem->getQuantity()
			),
			'SHIPMENT_ITEM' => $shipmentItem,
			'RESERVED_QUANTITY' => $shipmentProductData['RESERVED_QUANTITY'],
			'NEED_RESERVE' => array(
				$shipmentItem->getInternalIndex() => $shipmentProductData["NEED_RESERVE"]
			),
		);

		if (Sale\Configuration::useStoreControl())
		{
			$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem);

			if (!empty($storeData))
			{
				$fields['STORE_DATA'] = array(
					$shipmentItem->getInternalIndex() => $storeData
				);
			}
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
				continue;

			if (empty($productData['SHIPMENT_ITEM_LIST']))
				continue;

			/**
			 * @var int $shipmentItemIndex
			 * @var Sale\ShipmentItem $shipmentItem
			 */
			foreach ($productData['SHIPMENT_ITEM_DATA_LIST'] as $shipmentItemIndex => $shipmentItemQuantity)
			{
				$shipmentItem = null;
				$shipment = null;

				$coefficient = -1;
				if (isset($productData['SHIPMENT_ITEM_LIST'][$shipmentItemIndex]))
				{
					/** @var Sale\ShipmentItem $shipmentItem */
					$shipmentItem = $productData['SHIPMENT_ITEM_LIST'][$shipmentItemIndex];

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

					if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
					{
						$coefficient = 1;
					}
				}

				$quantity = $coefficient * $shipmentItemQuantity;
				$pool->add(PoolQuantity::POOL_QUANTITY_TYPE, $productId, $quantity);

				if ($shipmentItem && $shipment)
				{
					$order = $shipment->getParentOrder();
					if (!$order)
					{
						throw new Main\ObjectNotFoundException('Entity "Order" not found');
					}

					$foundItem = false;
					$poolItems = Sale\Internals\ItemsPool::get($order->getInternalId(), $productId);
					if (!empty($poolItems))
					{
						/** @var Sale\ShipmentItem $poolItem */
						foreach ($poolItems as $poolItem)
						{
							if ($poolItem->getInternalIndex() == $shipmentItem->getInternalIndex())
							{
								$foundItem = true;
								break;
							}
						}
					}

					if (!$foundItem)
					{
						Sale\Internals\ItemsPool::add($order->getInternalId(), $productId, $shipmentItem);
					}

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