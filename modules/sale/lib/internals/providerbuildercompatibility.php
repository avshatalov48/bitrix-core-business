<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Sale;

/**
 * Class ProviderBuilderCompatibility
 * @package Bitrix\Sale\Internals
 */
class ProviderBuilderCompatibility extends ProviderBuilderBase
{
	/**
	 * @param Sale\BasketItemBase $basketItem
	 */
	public function addProductByBasketItem(Sale\BasketItemBase $basketItem)
	{
		$productId = $basketItem->getProductId();
		$providerName = $basketItem->getProviderName();

		$isOrdable = ($basketItem->getField("CAN_BUY") == 'Y' && $basketItem->getField("DELAY") == 'N' && $basketItem->getField("SUBSCRIBE") == 'N');

		$fields = array(
			'BASKET_ITEM' => $basketItem,
			'ITEM_CODE' => $basketItem->getBasketCode(),
			'BASKET_ID' => $basketItem->getId(),
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'PRODUCT_ID' => $productId,
			'QUANTITY' => $basketItem->getQuantity(),

			'MODULE' => $basketItem->getField('MODULE'),
			'IS_ORDERABLE' => $isOrdable,

			'IS_BUNDLE_PARENT' => false,
			'IS_BUNDLE_CHILD' => false,
			'IS_NEW' => ($basketItem->getId() == 0),
			'SUBSCRIBE' => ($basketItem->getField('SUBSCRIBE') == 'Y'),
		);

		if ($basketItem instanceof Sale\BasketItem)
		{
			$fields['IS_BUNDLE_PARENT'] = $basketItem->isBundleParent();
			$fields['IS_BUNDLE_CHILD'] = $basketItem->isBundleChild();
		}

		if (strval(trim($providerName)) == '')
		{
			$callbackFunction = $basketItem->getCallbackFunction();
			if (!empty($callbackFunction))
			{
				$fields['CALLBACK_FUNC'] = $callbackFunction;
			}
		}

		$this->addItem($productId, $fields);
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 */
	public function addProductByShipmentItem(Sale\ShipmentItem $shipmentItem)
	{
		$basketItem = $shipmentItem->getBasketItem();

		$productId = $basketItem->getProductId();
		$providerName = $basketItem->getProviderName();

		$fields = array(
			'PRODUCT_ID' => $productId,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'QUANTITY' => $basketItem->getQuantity(),

			'MODULE' => $basketItem->getField('MODULE'),
			'SHIPMENT_ITEM' => $shipmentItem
		);

		if (strval(trim($providerName)) == '')
		{
			$callbackFunction = $basketItem->getCallbackFunction();
			if (!empty($callbackFunction))
			{
				$fields['CALLBACK_FUNC'] = $callbackFunction;
			}
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
		$providerName = $basketItem->getProviderName();

		$fields = array(
			'PRODUCT_ID' => $productId,
			'BASKET_ITEM' => $basketItem,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'QUANTITY' => $shipmentProductData['QUANTITY'],

			'MODULE' => $basketItem->getField('MODULE'),
			'SHIPMENT_ITEM' => $shipmentItem,
			'NEED_RESERVE' => array(
				$shipmentItem->getInternalIndex() => $shipmentProductData["NEED_RESERVE"]
			),
		);

		if (strval(trim($providerName)) == '')
		{
			$callbackFunction = $basketItem->getCallbackFunction();
			if (!empty($callbackFunction))
			{
				$fields['CALLBACK'] = $callbackFunction;
			}
		}

		$this->addItem($productId, $fields);
	}


	/**
	 * @param PoolQuantity $pool
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 */
	public function setItemsResultAfterTryShip(PoolQuantity $pool, array $productTryShipList)
	{
		return new Sale\Result();
	}

	/**
	 * @return string
	 */
	public function getTransferClassName()
	{
		return '\Bitrix\Sale\Internals\TransferProviderCompatibility';
	}

}