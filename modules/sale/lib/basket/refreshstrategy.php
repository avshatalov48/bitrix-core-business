<?php

namespace Bitrix\Sale\Basket;

use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemBase;
use Bitrix\Sale\BundleCollection;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;

abstract class RefreshStrategy extends BaseRefreshStrategy
{
	/**
	 * @param BasketItemBase $item
	 * @param                $fields
	 *
	 * @return Result
	 */
	protected function applyRefreshResult(BasketItemBase $item, $fields)
	{
		$bundleItemList = array();
		if (isset($fields['ITEMS']))
		{
			$bundleItemList = $fields['ITEMS'];
			unset($fields['ITEMS']);
		}

		/**
		 * Adds quantity which purchased
		 */
		if (isset($fields['QUANTITY']))
		{
			$delta = $item->getQuantity() - $item->getNotPurchasedQuantity();
			$fields['QUANTITY'] += max($delta, 0);
		}

		$result = parent::applyRefreshResult($item, $fields);
		if ($result->isSuccess())
		{
			if ($bundleItemList)
			{
				$r = $this->applyBundleRefreshResult($item, $bundleItemList);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param BasketItemBase $item
	 * @param                $bundleItemList
	 *
	 * @return Result
	 */
	protected function applyBundleRefreshResult(BasketItemBase $item, $bundleItemList)
	{
		/** @var BasketItem $item */
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = $registry->getBasketItemClassName();

		/** @var BundleCollection $bundleCollection */
		$bundleCollection = $item->getBundleCollection();
		$bundleIndexList = array();
		/** @var BasketItem $bundleItem */
		foreach ($bundleCollection as $bundleItem)
		{
			$bundleIndexList[$bundleItem->getBasketCode()] = $bundleItem;
		}

		/** @var array $bundleBasketItemData */
		foreach ($bundleItemList as $bundleBasketItemData)
		{
			if (empty($bundleBasketItemData['MODULE']) || empty($bundleBasketItemData['PRODUCT_ID']))
				return null;

			$props = array();
			if (!empty($bundleBasketItemData['PROPS']) && is_array($bundleBasketItemData['PROPS']))
			{
				$props = $bundleBasketItemData['PROPS'];
			}

			/** @var BasketItem $bundleBasketItem */
			$bundleItem = $bundleCollection->getExistsItem($bundleBasketItemData['MODULE'], $bundleBasketItemData['PRODUCT_ID'], $props);
			if (!$bundleItem)
			{
				$bundleItem = $basketItemClassName::create($bundleCollection, $bundleBasketItemData['MODULE'], $bundleBasketItemData['PRODUCT_ID']);
			}

			$fields = array_intersect_key($bundleBasketItemData, $basketItemClassName::getSettableFieldsMap());
			$r = $this->applyRefreshResult($item, $fields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			unset($bundleIndexList[$bundleItem->getBasketCode()]);
		}

		if ($bundleIndexList)
		{
			/** @var BasketItemBase $bundleItem */
			foreach ($bundleIndexList as $bundleItem)
			{
				$bundleItem->delete();
			}
		}

		return $result;
	}
}