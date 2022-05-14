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
	 * @param $providerClass
	 * @param $context
	 *
	 * @return ProviderBuilderBase
	 */
	public static function create($providerClass, $context)
	{
		$builder = parent::create($providerClass, $context);
		if (!$builder->providerClass && is_string($providerClass) && strval($providerClass) != '')
		{
			$builder->callbackFunction = $providerClass;
		}
		return $builder;
	}
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
			'QUANTITY' => $basketItem->getNotPurchasedQuantity(),
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
	 * @param array $productData
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function addProductData(array $productData)
	{
		if ($productData['QUANTITY'] == 0)
		{
			return;
		}

		/** @var Sale\ShipmentItem $shipmentItem */
		$shipmentItem = $productData['SHIPMENT_ITEM'];

		$basketItem = $productData['BASKET_ITEM'];

		$productId = $basketItem->getProductId();
		$providerName = $basketItem->getProviderName();

		$fields = [
			'PRODUCT_ID' => $productId,
			'BASKET_ITEM' => $basketItem,
			'BASKET_CODE' => $basketItem->getBasketCode(),
			'QUANTITY' => $productData['QUANTITY'],
			'MODULE' => $basketItem->getField('MODULE'),
		];

		if ($shipmentItem)
		{
			$fields['SHIPMENT_ITEM'] = $shipmentItem;
			$fields['NEED_RESERVE'] = [
				$shipmentItem->getInternalIndex() => $productData["NEED_RESERVE"]
			];
		}

		if (isset($productData['QUANTITY_BY_STORE']))
		{
			$fields['QUANTITY_BY_STORE'] = $productData['QUANTITY_BY_STORE'];
		}

		if (trim($providerName) == '')
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