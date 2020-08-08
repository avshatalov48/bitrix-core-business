<?php

namespace Bitrix\Sale;

use Bitrix\Currency;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
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
	 * @param null $basketCode
	 * @return BasketItemBase
	 * @throws NotImplementedException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
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
	 * @param null $basketCode
	 * @return BasketItemBase
	 * @throws NotImplementedException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function createItemInternal(BasketItemCollection $basket, $moduleId, $productId, $basketCode = null)
	{
		/** @var BasketItem $basketItemClassName */
		$basketItemClassName = static::getItemCollectionClassName();
		return $basketItemClassName::create($basket, $moduleId, $productId, $basketCode);
	}

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
	 * @return BasketBase
	 */
	abstract public function getBasket();

	/**
	 * @param array $itemList
	 * @throws NotImplementedException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function loadFromArray(array $itemList)
	{
		/** @var BasketItemBase $itemClassName */
		$itemClassName = static::getItemCollectionClassName();

		foreach ($itemList as $item)
		{
			$basketItem = $itemClassName::load($this, $item);
			$this->addItem($basketItem);
		}

		$controller = Internals\CustomFieldsController::getInstance();
		$controller->initializeCollection($this);
	}

	/**
	 * @return string
	 * @throws NotImplementedException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected static function getItemCollectionClassName()
	{
		$registry  = Registry::getInstance(static::getRegistryType());
		return $registry->getBasketItemClassName();
	}

	/**
	 * @param $code
	 * @return BasketItemBase|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getItemByBasketCode($code)
	{
		/** @var BasketItemBase $basketItem */
		foreach ($this->collection as $basketItem)
		{
			$basketItem = $basketItem->findItemByBasketCode($code);
			if ($basketItem != null)
			{
				return $basketItem;
			}
		}

		return null;
	}

	/**
	 * @param $id
	 * @return BasketItemBase|null
	 * @throws \Bitrix\Main\ArgumentNullException
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
	 * @throws NotImplementedException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
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
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
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

			$userId = Fuser::getUserIdById($fuserId);

			if (empty($context['SITE_ID']))
			{
				$context['SITE_ID'] = $siteId;
			}

			if (empty($context['USER_ID']) && $userId > 0)
			{
				$context['USER_ID'] = $userId;
			}
		}

		if (empty($context['SITE_ID']))
		{
			$context['SITE_ID'] = SITE_ID;
		}

		if (empty($context['USER_ID']))
		{
			$context['USER_ID'] = isset($USER) && $USER instanceof \CUser ? (int)$USER->GetID() : 0;
		}

		if (Loader::includeModule('currency'))
		{
			if (!empty($context['SITE_ID']))
			{
				$currency = Internals\SiteCurrencyTable::getSiteCurrency($context['SITE_ID']);
			}

			if (empty($currency))
			{
				$currency = Currency\CurrencyManager::getBaseCurrency();
			}

			if (!empty($currency) && Currency\CurrencyManager::checkCurrencyID($currency))
			{
				$context['CURRENCY'] = $currency;
			}
		}

		return $context;
	}
}