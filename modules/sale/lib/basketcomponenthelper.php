<?php
namespace Bitrix\Sale;

use Bitrix\Catalog;
use Bitrix\Main;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

class BasketComponentHelper
{
	static $cacheRatio = array();
	static $cacheRatioData = array();

	/**
	 * @param int $fuserId
	 * @param string|null $siteId
	 *
	 * @return int|float
	 */
	public static function getFUserBasketQuantity($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$quantity = null;
		if (!empty($_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId]) && is_array($_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId])
			&& array_key_exists($fuserId, $_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId]))
		{
			$quantity = $_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fuserId];
		}

		if ($quantity === null)
		{
			static::updateFUserBasketQuantity($fuserId, $siteId);
			$quantity = $_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fuserId];
		}

		return $quantity;
	}

	/**
	 * @param int $fuserId
	 * @param string|null $siteId
	 *
	 * @return int|float
	 */
	public static function getFUserBasketPrice($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$price = null;
		if (!empty($_SESSION['SALE_USER_BASKET_PRICE'][$siteId]) && is_array($_SESSION['SALE_USER_BASKET_PRICE'][$siteId])
			&& array_key_exists($fuserId, $_SESSION['SALE_USER_BASKET_PRICE'][$siteId]))
		{
			$price = $_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fuserId];
		}

		if ($price === null)
		{
			static::updateFUserBasketPrice($fuserId, $siteId);
			$price = $_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fuserId];
		}

		return $price;
	}

	/**
	 * @param int         $fUserId
	 * @param int|float   $quantity
	 * @param string|null $siteId
	 * @return void
	 */
	protected static function setFUserBasketQuantity($fUserId, $quantity, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fUserId] = $quantity;
	}

	/**
	 * @param      $fUserId
	 * @param null $siteId
	 */
	public static function clearFUserBasketQuantity($fUserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		unset($_SESSION['SALE_USER_BASKET_QUANTITY'][$siteId][$fUserId]);
	}

	/**
	 * @param int         $fUserId
	 * @param int|float   $price
	 * @param string|null $siteId
	 * @return void
	 */
	protected static function setFUserBasketPrice($fUserId, $price, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fUserId] = $price;
	}

	/**
	 * @param      $fUserId
	 * @param null $siteId
	 */
	public static function clearFUserBasketPrice($fUserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		unset($_SESSION['SALE_USER_BASKET_PRICE'][$siteId][$fUserId]);
	}

	/**
	 * @param int $fuserId
	 * @param string|null $siteId
	 * @param array|null $basketList
	 *
	 * @return void
	 */
	public static function updateFUserBasketPrice($fuserId, $siteId = null, $basketList = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$price = 0;

		if ($basketList === null)
		{
			$basketList = static::getFUserBasketList($fuserId, $siteId);
		}

		if (!empty($basketList) && is_array($basketList))
		{
			$orderData = static::calculatePrice($fuserId, $basketList);
			$price = $orderData['ORDER_PRICE'];
		}

		static::setFUserBasketPrice($fuserId, $price, $siteId);
	}

	/**
	 * @param int $fuserId
	 * @param string|null $siteId
	 * @param array|null $basketList
	 *
	 * @return void
	 */
	public static function updateFUserBasketQuantity($fuserId, $siteId = null, $basketList = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$quantity = 0;

		if ($basketList === null)
		{
			$basketList = static::getFUserBasketList($fuserId, $siteId);
		}

		if (!empty($basketList) && is_array($basketList))
		{
			$quantity = count($basketList);
		}

		static::setFUserBasketQuantity($fuserId, $quantity, $siteId);
	}

	/**
	 * @param int $fuserId
	 * @param string|null $siteId
	 *
	 * @return void
	 */
	public static function updateFUserBasket($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$basketList = static::getFUserBasketList($fuserId, $siteId);

		static::updateFUserBasketPrice($fuserId, $siteId, $basketList);
		static::updateFUserBasketQuantity($fuserId, $siteId, $basketList);
	}

	/**
	 * @param int $fuserId
	 * @param string|null $siteId
	 *
	 * @return array
	 */
	protected static function getFUserBasketList($fuserId, $siteId = null)
	{
		if ($siteId === null)
		{
			$siteId = SITE_ID;
		}

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();

		$basketList = array();
		$res = $basketClassName::getList(array(
			'filter' => array(
				'=FUSER_ID' => $fuserId,
				'=ORDER_ID' => null,
				'=LID' => $siteId,
				'=CAN_BUY' => 'Y',
				'=DELAY' => 'N'
			)
		));
		while ($data = $res->fetch())
		{
			if (\CSaleBasketHelper::isSetItem($data))
				continue;

			if (!isset($data['BASE_PRICE']) || (float)$data['BASE_PRICE'] <= 0)
			{
				$data['BASE_PRICE'] = $data['PRICE'] + $data['DISCOUNT_PRICE'];
			}

			$basketList[] = $data;
		}

		return $basketList;
	}

	/**
	 * @param int $fuserId
	 * @param array $basketList
	 *
	 * @return array
	 */
	protected static function calculatePrice($fuserId, array $basketList)
	{
		$totalPrice = 0;
		$totalWeight = 0;

		foreach ($basketList as $basketData)
		{
			$totalPrice += $basketData["PRICE"] * $basketData["QUANTITY"];
			$totalWeight += $basketData["WEIGHT"] * $basketData["QUANTITY"];
		}

		$orderData = array(
			'SITE_ID' => SITE_ID,
			'ORDER_PRICE' => $totalPrice,
			'ORDER_WEIGHT' => $totalWeight,
			'BASKET_ITEMS' => $basketList
		);

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Basket $basketClassName */
		$basketClassName = $registry->getBasketClassName();

		$basket = $basketClassName::create(SITE_ID);
		$basket->setFUserId($fuserId);
		foreach ($basketList as $oldItem)
		{
			$item = $basket->createItem($oldItem['MODULE'], $oldItem['PRODUCT_ID']);
			unset($oldItem['MODULE'], $oldItem['PRODUCT_ID']);
			$item->initFields($oldItem);
		}
		$orderData['ORDER_PRICE'] = self::calculateBasketCost($basket);
		unset($basket);

		return $orderData;
	}


	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleBasketItemEntitySaved(Main\Event $event)
	{
		$fuserId = Fuser::getId(true);
		$basketItem = $event->getParameter('ENTITY');

		$originalValues = $event->getParameter('VALUES');
		if ((!$basketItem instanceof BasketItem))
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		/** @var \Bitrix\Sale\Basket $basket */
		if ((!$basket = $basketItem->getCollection())
			|| ($basketItem->getFUserId() != $fuserId)
		)
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		if ($basketItem->isChanged())
		{
			$updateSessionData = false;

			if (!$basket->getOrder())
			{
				if (!$updateSessionData && array_key_exists('QUANTITY', $originalValues))
				{
					$updateSessionData = true;
				}

				if (!$updateSessionData && (array_key_exists('PRICE', $originalValues) && PriceMaths::roundPrecision($originalValues['PRICE']) !== PriceMaths::roundPrecision($basketItem->getPrice())))
				{
					$updateSessionData = true;
				}

				if (!$updateSessionData && (array_key_exists('DISCOUNT_PRICE', $originalValues) && PriceMaths::roundPrecision($originalValues['DISCOUNT_PRICE']) !== PriceMaths::roundPrecision($basketItem->getDiscountPrice())))
				{
					$updateSessionData = true;
				}
			}

			if (!$updateSessionData && (array_key_exists('ORDER_ID', $originalValues) && (intval($originalValues['ORDER_ID'])) == 0 && intval($basketItem->getField('ORDER_ID') > 0)))
			{
				$updateSessionData = true;
			}

			if (!$updateSessionData
				&& (array_key_exists('CAN_BUY', $originalValues) && ($originalValues['CAN_BUY'] !== $basketItem->getField('CAN_BUY'))))
			{
				$updateSessionData = true;
			}

			if (!$updateSessionData
				&& (array_key_exists('DELAY', $originalValues) && ($originalValues['DELAY'] !== $basketItem->getField('DELAY'))))
			{
				$updateSessionData = true;
			}

			if ($updateSessionData)
			{
				static::clearFUserBasketPrice($fuserId, SITE_ID);
				static::clearFUserBasketQuantity($fuserId, SITE_ID);
			}
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param \Bitrix\Main\Event $event
	 *
	 * @return \Bitrix\Main\EventResult
	 */
	public static function onSaleBasketItemDeleted(Main\Event $event)
	{
		$fuserId = Fuser::getId(true);
		$originalValues = $event->getParameter('VALUES');
		if ($originalValues['FUSER_ID'] != $fuserId)
		{
			return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
		}

		static::clearFUserBasketPrice($fuserId, SITE_ID);
		static::clearFUserBasketQuantity($fuserId, SITE_ID);

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Basket          $basket
	 * @param BasketItem|null $item
	 *
	 * @return Result
	 * @throws Main\LoaderException
	 */
	public static function checkQuantityRatio(Basket $basket, BasketItem $item = null)
	{
		$result = new Result();

		$basketItemRatioList = array();
		$ratioList = array();
		$ratioResult = static::getRatio($basket, $item);

		if ($ratioResult->isSuccess())
		{
			$ratioData = $ratioResult->getData();

			if (!empty($ratioData['RATIO_LIST']) && is_array($ratioData['RATIO_LIST']))
			{
				$ratioList = $ratioData['RATIO_LIST'];
			}
		}

		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$basketItemCode = $basketItem->getBasketCode();

			if ($item === null || $item->getBasketCode() === $basketItemCode)
			{
				$basketItemRatioList[$basketItemCode] = false;

				if (isset($ratioList[$basketItemCode]))
				{
					$basketItemQuantity = $basketItem->getQuantity();
					$basketItemRatio = (float)$ratioList[$basketItemCode];

					$mod = roundEx(($basketItemQuantity / $basketItemRatio - round($basketItemQuantity / $basketItemRatio)), 6);

					if ($mod == 0)
					{
						$basketItemRatioList[$basketItemCode] = true;
					}
				}
			}
		}

		if (!empty($basketItemRatioList))
		{
			$result->addData(array('CHECK_RATIO_LIST' => $basketItemRatioList));
		}

		return $result;
	}

	/**
	 * @param Basket          $basket
	 * @param BasketItem|null $item
	 *
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws \Exception
	 */
	public static function correctQuantityRatio(Basket $basket, BasketItem $item = null)
	{
		$result = new Result();
		$changedItems = array();

		$checkRatioList = array();
		$checkRatioResult = static::checkQuantityRatio($basket, $item);

		if ($checkRatioResult->isSuccess())
		{
			$checkRatioData = $checkRatioResult->getData();

			if (!empty($checkRatioData['CHECK_RATIO_LIST']) && is_array($checkRatioData['CHECK_RATIO_LIST']))
			{
				$checkRatioList = $checkRatioData['CHECK_RATIO_LIST'];
			}
		}

		$basketItemRatioList = array();
		$ratioList = null;

		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$basketItemCode = $basketItem->getBasketCode();

			if ($item === null || $item->getBasketCode() === $basketItemCode)
			{
				$basketItemRatioList[$basketItemCode] = false;

				if (isset($checkRatioList[$basketItemCode]) && $checkRatioList[$basketItemCode] === false)
				{
					if ($ratioList === null)
					{
						$ratioList = array();
						$ratioResult = static::getRatio($basket, $item);

						if ($ratioResult->isSuccess())
						{
							$ratioData = $ratioResult->getData();

							if (!empty($ratioData['RATIO_LIST']) && is_array($ratioData['RATIO_LIST']))
							{
								$ratioList = $ratioData['RATIO_LIST'];
							}
						}
					}

					if (!isset($ratioList[$basketItemCode]))
					{
						$result->addError(new ResultError(Main\Localization\Loc::getMessage('SALE_BASKET_COMPONENT_HELPER_PRODUCT_RATIO_NOT_FOUND', array(
							'#PRODUCT_NAME#' => $basketItem->getField('NAME')
						)), 'SALE_BASKET_COMPONENT_HELPER_PRODUCT_RATIO_NOT_FOUND'));
						continue;
					}

					$basketItemQuantity = $basketItem->getQuantity();
					$basketItemRatio = (float)$ratioList[$basketItemCode];

					$mod = roundEx(($basketItemQuantity / $basketItemRatio - round($basketItemQuantity / $basketItemRatio)), 6);

					if ($mod != 0)
					{
						$changedItems[] = $basketItemCode;

						$closestQuantity = round($basketItemQuantity / $basketItemRatio) * $basketItemRatio;
						if ($closestQuantity < $basketItemRatio)
						{
							$closestQuantity = $basketItemRatio;
						}

						$r = $basketItem->setField('QUANTITY', $closestQuantity);
						if (!$r->isSuccess())
						{
							$floorQuantity = floor(ceil($basketItemQuantity) / $basketItemRatio) * $basketItemRatio;
							if ($floorQuantity < $basketItemRatio)
							{
								$floorQuantity = $basketItemRatio;
							}

							if ($floorQuantity != $closestQuantity)
							{
								$r = $basketItem->setField('QUANTITY', $floorQuantity);
							}
						}

						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());

							$r = $basketItem->setField('CAN_BUY', 'N');
							if (!$r->isSuccess())
							{
								$result->addErrors($r->getErrors());
							}
						}
					}
				}
			}
		}

		$result->addData(array('CHANGED_BASKET_ITEMS' => $changedItems));

		return $result;
	}

	/**
	 * @param Basket $basket
	 * @param BasketItem|null $item
	 *
	 * @return Result
	 * @throws Main\LoaderException
	 */
	public static function getRatio(Basket $basket, BasketItem $item = null)
	{
		$result = new Result();
		$ratioList = array();
		if (Main\Loader::includeModule('catalog'))
		{
			$map = array();
			$elementList = array();

			/** @var BasketItem $basketItem */
			foreach ($basket as $basketItem)
			{
				$code = $basketItem->getBasketCode();
				if ($item !== null && $item->getBasketCode() != $code)
				{
					continue;
				}

				$hash = md5((strval($basketItem->getField("PRODUCT_PROVIDER_CLASS")) != '' ? $basketItem->getField("PRODUCT_PROVIDER_CLASS"): "")."|".(strval($basketItem->getField("MODULE")) != '' ? $basketItem->getField("MODULE"): "")."|".$basketItem->getField("PRODUCT_ID"));

				if (array_key_exists($hash, static::$cacheRatio))
				{
					$ratioList[$code] = static::$cacheRatio[$hash];
				}
				else
				{
					$elementList[$basketItem->getField("PRODUCT_ID")] = $basketItem->getField("PRODUCT_ID");
				}

				if (!isset($map[$basketItem->getField("PRODUCT_ID")]))
				{
					$map[$basketItem->getField("PRODUCT_ID")] = array();
				}

				$map[$basketItem->getField("PRODUCT_ID")][] = $code;
			}

			if (!empty($elementList))
			{
				$res = Catalog\MeasureRatioTable::getList(array(
					'select' => array('*'),
					'filter' => array('@PRODUCT_ID' => $elementList, '=IS_DEFAULT' => 'Y')
				));
				while ($ratioData = $res->fetch())
				{
					if (empty($map[$ratioData["PRODUCT_ID"]]))
						continue;

					foreach ($map[$ratioData["PRODUCT_ID"]] as $key)
					{
						$ratioList[$key] = $ratioData["RATIO"];

						if (!$basketItem = $basket->getItemByBasketCode($key))
							continue;

						$hash = md5((strval($basketItem->getField("PRODUCT_PROVIDER_CLASS")) != '' ? $basketItem->getField("PRODUCT_PROVIDER_CLASS"): "")."|".(strval($basketItem->getField("MODULE")) != '' ? $basketItem->getField("MODULE"): "")."|".$basketItem->getField("PRODUCT_ID"));

						static::$cacheRatio[$hash] = $ratioData["RATIO"];
						static::$cacheRatioData[$hash] = $ratioData;
					}
					unset($key);
				}
				unset($ratioData, $dbRatio);
			}
			unset($elementList, $map);
		}

		if (!empty($ratioList))
			$result->addData(array('RATIO_LIST' => $ratioList));

		return $result;
	}

	/**
	 * @param Basket $basket
	 *
	 * @return int|float
	 */
	protected static function calculateBasketCost(Basket $basket)
	{
		if ($basket->count() == 0)
			return 0;

		$oldApiStatus = Compatible\DiscountCompatibility::isUsed(); // TODO: remove this code after refactoring DiscountCompatibility
		if ($oldApiStatus)
			Compatible\DiscountCompatibility::stopUsageCompatible();
		DiscountCouponsManager::freezeCouponStorage();
		$basket->refreshData(array('PRICE', 'COUPONS'));
		$discounts = Discount::buildFromBasket($basket, new Discount\Context\Fuser($basket->getFUserId(true)));
		$discounts->calculate();
		$discountResult = $discounts->getApplyResult();
		DiscountCouponsManager::unFreezeCouponStorage();
		if ($oldApiStatus)
			Compatible\DiscountCompatibility::revertUsageCompatible();

		if (empty($discountResult['PRICES']['BASKET']))
			return 0;

		$result = 0;
		$discountResult = $discountResult['PRICES']['BASKET'];
		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			if (!$basketItem->canBuy())
				continue;
			$code = $basketItem->getBasketCode();
			if (!empty($discountResult[$code]))
				$result += $discountResult[$code]['PRICE'] * $basketItem->getQuantity();
			unset($code);
		}
		unset($basketItem);
		unset($discountResult);

		return $result;
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getRatioCache()
	{
		return static::$cacheRatio;
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getRatioDataCache()
	{
		return static::$cacheRatioData;
	}
}