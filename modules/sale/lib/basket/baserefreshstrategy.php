<?php

namespace Bitrix\Sale\Basket;

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItemBase;
use Bitrix\Sale\EventActions;
use Bitrix\Sale\Internals\Catalog\Provider;
use Bitrix\Sale\Internals\PoolQuantity;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultError;

Main\Localization\Loc::loadMessages(__FILE__);

abstract class BaseRefreshStrategy
{
	const REFRESH_FIELD = 'DATE_REFRESH';

	protected $data;
	protected $refreshGap;

	public function __construct(array $data = null)
	{
		$this->data = $data;
	}

	protected function getBasketItemRefreshTimestamp(BasketItemBase $basketItem)
	{
		/** @var DateTime $refreshDate */
		$refreshDate = $basketItem->getField(self::REFRESH_FIELD);
		if ($refreshDate)
		{
			return $refreshDate->getTimestamp();
		}

		return 0;
	}

	protected function isBasketItemChanged(BasketItemBase $basketItem)
	{
		$changedValues = $basketItem->getFields()->getChangedValues();

		// remove not meaningful field
		unset($changedValues[self::REFRESH_FIELD]);

		return !empty($changedValues);
	}

	protected function getBasketRefreshGapTime()
	{
		if (!isset($this->refreshGap))
		{
			$this->refreshGap = (int)Option::get('sale', 'basket_refresh_gap', 0);
		}

		return $this->refreshGap;
	}

	protected function getBasketIndexList(BasketBase $basket)
	{
		$basketIndexList = array();

		/** @var BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			$providerName = $basketItem->getProviderName();

			if (strval(trim($providerName)) == '')
			{
				$callbackFunction = $basketItem->getCallbackFunction();
				if (!empty($callbackFunction))
				{
					$providerName = $callbackFunction;
				}
				else
				{
					$providerName = null;
				}
			}

			if (!empty($providerName) && $providerName[0] == "\\")
			{
				$providerName = ltrim($providerName, '\\');
			}

			$basketIndexList[$providerName][$basketItem->getProductId()][] = $basketItem;
		}

		return $basketIndexList;
	}

	protected function updateBasket(BasketBase $basket, array $productDataList)
	{
		$result = new Result();
		$changedBasketItems = array();

		$basketIndexList = $this->getBasketIndexList($basket);

		foreach ($productDataList as $providerName => $productValueList)
		{
			foreach ($productValueList as $productId => $productData)
			{
				if (empty($basketIndexList[$providerName][$productId]))
				{
					$basketIndexList[$providerName][$productId][] = $basket->createItem($productData['MODULE_ID'], $productData['PRODUCT_ID']);
				}

				/** @var BasketItemBase $item */
				foreach ($basketIndexList[$providerName][$productId] as $item)
				{
					if ($item && isset($productData['DELETE']) && $productData['DELETE'])
					{
						$item->delete();
						continue;
					}

					$r = $this->updateBasketItem($item, $productData);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($this->isBasketItemChanged($item))
					{
						$changedBasketItems[] = $item->getBasketCode();
					}
				}
			}
		}

		$result->addData(array(
			'CHANGED_BASKET_ITEMS' => $changedBasketItems
		));

		return $result;
	}

	protected function updateBasketItem(BasketItemBase $item, $data)
	{
		$result = new Result();

		if (!empty($data))
		{
			$preparedData = $this->prepareData($item, $data);

			if (!$preparedData)
			{
				return $result;
			}

			if (!$item->isCustomPrice() && isset($preparedData['DISCOUNT_PRICE']) && isset($preparedData['BASE_PRICE']))
			{
				$preparedData['PRICE'] = $preparedData['BASE_PRICE'] - $preparedData['DISCOUNT_PRICE'];
			}

			if (
				empty($preparedData)
				|| (isset($preparedData['QUANTITY']) && $preparedData['QUANTITY'] <= 0)
				|| (isset($data['ACTIVE']) && $data['ACTIVE'] === 'N')
			)
			{
				$preparedData['CAN_BUY'] = 'N';
				unset($preparedData['QUANTITY']);
			}

			/** @var Main\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_BASKET_ITEM_REFRESH_DATA, array(
				'ENTITY' => $item,
				'VALUES' => $data,
				'PREPARED_VALUES' => $preparedData
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach ($event->getResults() as $eventResult)
				{
					if ($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(
							Main\Localization\Loc::getMessage('SALE_EVENT_ON_BASKET_ITEM_REFRESH_DATA'),
							'SALE_EVENT_ON_BASKET_ITEM_REFRESH_DATA'
						);
						if ($eventResultData = $eventResult->getParameters())
						{
							if (isset($eventResultData) && $eventResultData instanceof ResultError)
							{
								/** @var ResultError $errorMsg */
								$errorMsg = $eventResultData;
							}
						}

						$result->addError($errorMsg);
					}
				}
			}

			if ($this->getBasketRefreshGapTime() !== 0)
			{
				$item->setFieldNoDemand(self::REFRESH_FIELD, new DateTime());
			}
		}
		else
		{
			$preparedData['CAN_BUY'] = 'N';
		}

		/** @var Result $r */
		$r = $this->applyRefreshResult($item, $preparedData);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	protected function prepareData(BasketItemBase $item, $data)
	{
		if (empty($data))
		{
			return false;
		}

		$preparedData = array();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var BasketItemBase $basketItemClassName */
		$basketItemClassName = $registry->getBasketItemClassName();

		if (!empty($data['PRICE_LIST']))
		{
			$basketItemCode = $item->getBasketCode();

			if (!empty($data['PRICE_LIST'][$basketItemCode]))
			{
				$priceData = $data['PRICE_LIST'][$basketItemCode];

				if (!isset($priceData['QUANTITY']) && isset($priceData['AVAILABLE_QUANTITY']))
				{
					$priceData['QUANTITY'] = $priceData['AVAILABLE_QUANTITY'];
				}

				$data = $priceData + $data;
				if (isset($data['QUANTITY']))
				{
					$data['QUANTITY'] = $data['AVAILABLE_QUANTITY'] = static::getAvailableQuantityFromPool($item, $data['QUANTITY']);
				}
			}
			else
			{
				return false;
			}
		}

		$settableFields = $basketItemClassName::getSettableFieldsMap();
		$roundFields = array_fill_keys($basketItemClassName::getRoundFields(), true);

		foreach ($data as $key => $value)
		{
			if (isset($settableFields[$key]))
			{
				if ($key === 'PRICE' && $item->isCustomPrice())
				{
					$value = $item->getPrice();
				}

				if (isset($roundFields[$key]))
				{
					$value = PriceMaths::roundPrecision($value);
				}

				$preparedData[$key] = $value;
			}
		}

		return $preparedData;
	}

	protected static function getAvailableQuantityFromPool(BasketItemBase $item, $quantity)
	{
		$availableQuantity = $quantity;
		/** @var BasketBase $basket */
		$basket = $item->getCollection();
		if (!$basket)
		{
			throw new Main\ObjectNotFoundException('Basket');
		}

		$order = $basket->getOrder();
		if ($order && $order->getId() > 0)
		{
			$productId = $item->getProductId();

			$poolQuantity = 0;

			$pool = PoolQuantity::getInstance($order->getInternalId());
			$reserveQuantityList = $pool->getQuantities(PoolQuantity::POOL_RESERVE_TYPE);
			$quantityList = $pool->getQuantities(PoolQuantity::POOL_QUANTITY_TYPE);

			if ($quantityList[$productId])
				$poolQuantity += $quantityList[$productId];

			if ($reserveQuantityList[$productId])
				$poolQuantity += $reserveQuantityList[$productId];

			if ($poolQuantity < 0)
			{
				$poolQuantity = abs($poolQuantity);
			}

			if (($quantity + $poolQuantity) >= $item->getQuantity())
			{
				$availableQuantity = $item->getQuantity();
			}
		}

		return $availableQuantity;
	}


	protected function getProviderContext(BasketBase $basket)
	{
		return $basket->getContext();
	}

	protected function getBasketItemsToRefresh(BasketBase $basket, $quantity = 0)
	{
		$itemsToRefresh = array();
		$currentItemsCount = 0;

		$basketRefreshStart = time();
		$refreshGap = $this->getBasketRefreshGapTime();

		foreach ($basket as $basketItem)
		{
			if ($quantity > 0 && $currentItemsCount >= $quantity)
			{
				break;
			}

			$basketItemLastRefresh = $this->getBasketItemRefreshTimestamp($basketItem);
			if ($basketRefreshStart - $basketItemLastRefresh >= $refreshGap)
			{
				$itemsToRefresh[] = $basketItem;
				$currentItemsCount++;
			}
		}

		return $itemsToRefresh;
	}

	/**
	 * @param BasketBase $basket
	 * @param array      $itemsToRefresh
	 *
	 * @return Result
	 */
	protected function getProviderResult(BasketBase $basket, $itemsToRefresh = array())
	{
		if (!empty($itemsToRefresh))
		{
			$context = $this->getProviderContext($basket);
			$result = Provider::getProductData($itemsToRefresh, $context);
		}
		else
		{
			$result = new Result();
			$result->setData(array(
				'PRODUCT_DATA_LIST' => array()
			));
		}

		return $result;
	}

	/**
	 * @param BasketItemBase $item
	 * @param                $fields
	 *
	 * @return Result
	 */
	protected function applyRefreshResult(BasketItemBase $item, $fields)
	{
		return $item->setFields($fields);
	}

	/**
	 * @param BasketBase $basket
	 *
	 * @return Result
	 */
	abstract protected function getProductData(BasketBase $basket);

	/**
	 * @param BasketBase $basket
	 *
	 * @return Result
	 */
	public function refresh(BasketBase $basket)
	{
		if (!$basket->isEmpty())
		{
			$result = $this->getProductData($basket);
			if ($result->isSuccess())
			{
				$productData = $result->get('PRODUCT_DATA_LIST');
				if (!empty($productData))
				{
					$r = $this->updateBasket($basket, $productData);
					if ($r->isSuccess())
					{
						$result->addData($r->getData());
					}
					else
					{
						$result->addErrors($r->getErrors());
					}
				}
				else
				{
					$result->addData(array(
						'CHANGED_BASKET_ITEMS' => array()
					));
				}
			}
		}
		else
		{
			$result = new Result();
			$result->setData(array(
				'PRODUCT_DATA_LIST' => array(),
				'CHANGED_BASKET_ITEMS' => array()
			));
		}

		return $result;
	}
}