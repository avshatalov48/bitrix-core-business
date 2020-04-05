<?php

namespace Bitrix\Sale;

use Bitrix\Currency;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class BasketItemCollection
 * @package Bitrix\Sale
 */
abstract class BasketItemCollection extends Internals\EntityCollection
{
	/**
	 * @param $moduleId
	 * @param $productId
	 * @param null| string $basketCode
	 * @return BasketItemBase
	 */
	public function createItem($moduleId, $productId, $basketCode = null)
	{
		$basketItem = static::createItemInternal($this, $moduleId, $productId, $basketCode);

		$basketItem->setCollection($this);
		$this->addItem($basketItem);

		return $basketItem;
	}

	/**
	 * @param BasketItemCollection $basket
	 * @param $moduleId
	 * @param $productId
	 * @param $basketCode
	 * @return BasketItemBase
	 */
	abstract protected function createItemInternal(BasketItemCollection $basket, $moduleId, $productId, $basketCode = null);

	/**
	 * @return OrderBase
	 */
	public function getOrder()
	{
		$basket = $this->getBasket();
		if ($basket)
			return $basket->getOrder();

		return null;
	}

	/**
	 * @return BasketItemCollection
	 */
	abstract public function getBasket();

	/**
	 * @param array $itemList
	 */
	public function loadFromArray(array $itemList)
	{
		/** @var BasketItemBase $itemClassName */
		$itemClassName = $this->getBasketItemCollectionElementClassName();

		foreach ($itemList as $item)
		{
			$basketItem = $itemClassName::load($this, $item);
			$this->addItem($basketItem);
		}
	}

	/**
	 * @return string
	 */
	abstract protected function getBasketItemCollectionElementClassName();

	/**
	 * @param $itemCode
	 * @return BasketItemBase|null
	 */
	public function getItemByBasketCode($itemCode)
	{
		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$basketItem = $basketItem->findItemByBasketCode($itemCode);
			if ($basketItem != null)
				return $basketItem;
		}

		return null;
	}

	/**
	 * @param $id
	 * @return BasketItemBase|null
	 */
	public function getItemById($id)
	{
		if ($id <= 0)
			return null;

		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$item = $basketItem->findItemById($id);
			if ($item !== null)
				return $item;
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function getBasketItems()
	{
		return $this->collection;
	}


	/**
	 * @param $moduleId
	 * @param $productId
	 * @param array $properties
	 * @return BasketItem|null
	 */
	public function getExistsItem($moduleId, $productId, array $properties = array())
	{
		/** @var BasketItem $basketItem */
		foreach ($this->collection as $basketItem)
		{
			if ($basketItem->getField('PRODUCT_ID') == $productId && $basketItem->getField('MODULE') == $moduleId)
			{
				/** @var BasketPropertiesCollection $basketPropertyCollection */
				$basketPropertyCollection = $basketItem->getPropertyCollection();
				if (!empty($properties) && is_array($properties))
				{
					if ($basketPropertyCollection->isPropertyAlreadyExists($properties))
					{
						return $basketItem;
					}
				}
				elseif (count($basketPropertyCollection) == 0)
				{
					return $basketItem;
				}
			}
		}

		return null;
	}

	/**
	 * @return int
	 */
	public function getOrderId()
	{
		$order = $this->getOrder();
		if ($order)
			return $order->getId();

		return 0;
	}

	/**
	 * @return array
	 */
	public function getContext()
	{
		global $USER;
		$context = array();

		/** @var BasketItem $basketItem */
		$basketItem = $this->rewind();
		if ($basketItem)
		{

			$siteId = $basketItem->getField('LID');
			$fuserId = $basketItem->getFUserId();
			$currency = $basketItem->getCurrency();

			$userId = Fuser::getUserIdById($fuserId);

			if (empty($context['SITE_ID']))
			{
				$context['SITE_ID'] = $siteId;
			}

			if (empty($context['USER_ID']) && $userId > 0)
			{
				$context['USER_ID'] = $userId;
			}

			if (empty($context['CURRENCY']) && !empty($siteId))
			{
				if (empty($currency))
				{
					$currency = Internals\SiteCurrencyTable::getSiteCurrency($siteId);
				}

				if (!empty($currency) && Currency\CurrencyManager::checkCurrencyID($currency))
				{
					$context['CURRENCY'] = $currency;
				}
			}
		}

		if (empty($context['SITE_ID']))
		{
			$context['SITE_ID'] = SITE_ID;
		}

		if (empty($context['USER_ID']))
		{
			$context['USER_ID'] = $USER->GetID() > 0 ? $USER->GetID() : 0;
		}

		if (empty($context['CURRENCY']))
		{
			Loader::includeModule('currency');
			$context['CURRENCY'] = Currency\CurrencyManager::getBaseCurrency();
		}

		return $context;
	}
}