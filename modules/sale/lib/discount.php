<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Compatible,
	Bitrix\Sale\Internals,
	Bitrix\Sale\Discount\Context,
	Bitrix\Sale\Discount\RuntimeCache;

Loc::loadMessages(__FILE__);

class Discount
{
	const EVENT_EXTEND_ORDER_DATA = 'onExtendOrderData';

	const APPLY_MODE_ADD = 0x0001;
	const APPLY_MODE_DISABLE = 0x0002;
	const APPLY_MODE_LAST = 0x0004;
	const APPLY_MODE_FULL_DISABLE = 0x0008;
	const APPLY_MODE_FULL_LAST = 0x0010;

	const USE_MODE_FULL = 0x00001;
	const USE_MODE_APPLY = 0x0002;
	const USE_MODE_MIXED = 0x0004;
	const USE_MODE_COUPONS = 0x0008;

	const ROUND_MODE_BASKET_DISCOUNT = 0x0001;
	const ROUND_MODE_SALE_DISCOUNT = 0x0002;
	const ROUND_MODE_FINAL_PRICE = 0x0004;

	const ERROR_ID = 'BX_SALE_DISCOUNT';

	/* Instances */
	/** @var array of Discount */
	private static $instances = array();

	/* Sale objects */
	/** @var Order|null */
	protected $order = null;
	/** @var Basket|null */
	protected $basket = null;
	/** @var null|Shipment $shipment */
	protected $shipment = null;
	/** @var array */
	protected $shipmentIds = array();

	/** @var  Context\BaseContext */
	protected $context;

	/* Calculate data */
	/** @var array|null */
	protected $orderData = null;

	/* Calculate options */
	/** @var bool */
	protected $newOrder = null;
	/** @var bool */
	protected $convertedOrder = null;
	/** @var int */
	protected $useMode = null;
	/** @var bool */
	protected $orderRefresh = false;
	/** @var array */
	protected $saleOptions = array();
	/** @var array */
	protected $executeModuleFilter = array('all', 'sale', 'catalog');

	/* Product discounts and base prices */
	/** @var array */
	protected $basketBasePrice = array();
	/** @var array */
	protected $basketDiscountList = array();
	/** @var array */
	protected $basketItemsData = array();

	/* Sale discount cache on hit */
	/** @var array|null */
	protected $discountIds = null;
	/** @var array */
	protected $discountByUserCache = array();
	/** @var array */
	protected $saleDiscountCache = array();
	/** @var string */
	protected $saleDiscountCacheKey = '';
	/** @var array */
	protected $cacheDiscountModules = array();

	/* Order discounts and coupons, converted to unified format */
	/** @var array */
	protected $discountsCache = array();
	/** @var array */
	protected $couponsCache = array();

	/* Calculation results and applyed flags */
	/** @var array */
	protected $discountResult = array();
	/** @var int */
	protected $discountResultCounter = 0;
	/** @var array */
	protected $applyResult = array();

	/* Contains additional data used to calculate discounts for an existing order */
	protected $discountStoredActionData = array();

	/** @var array */
	protected $loadedModules = array();
	/** @var array */
	protected $entityList = array();
	/** @var array */
	protected $entityResultCache = array();
	/** @var array */
	protected $currentStep = array();

	/** @var array */
	protected $forwardBasketTable = array();
	/** @var array */
	protected $reverseBasketTable = array();

	/* Round mode and data */
	protected $roundApplyMode = self::ROUND_MODE_FINAL_PRICE;
	protected $roundApplyConfig = array();

	/**
	 * Contains list of discounts which pass condition (@see method checkDiscountConditions()).
	 *
	 * @var array
	 */
	protected $fullDiscountList = array();

	/** @var bool  */
	protected $isClone = false;
	/** @var bool  */
	protected $enableCheckingPrediction = false;

	protected function __construct()
	{

	}

	/**
	 * Enables prediction checking instead real condition.
	 * @return void
	 */
	public function enableCheckingPrediction()
	{
		$this->enableCheckingPrediction = true;
		$this->saleOptions = array(
			'APPLY_MODE' => $this::APPLY_MODE_ADD,
		);
	}

	/**
	 * Disables prediction checking instead real condition.
	 * @return void
	 */
	public function disableCheckingPrediction()
	{
		$this->enableCheckingPrediction = false;
	}

	/**
	 * Get discount by fuser and site.
	 *
	 * @param string|int $fuser			Fuser id.
	 * @param string $site				Site id.
	 * @return null|Discount
	 */
	public static function loadByFuser($fuser, $site)
	{
		$instanceIndex = static::getInstanceIndexByFuser((int)$fuser, (string)$site);
		if (isset(self::$instances[$instanceIndex]))
			return self::$instances[$instanceIndex];

		return null;
	}

	/**
	 * Get discount by basket.
	 *
	 * @deprecated deprecated sinse sale 17.0.11
	 * @see Discount::buildFromBasket
	 *
	 * @param BasketBase $basket		Basket object.
	 * @return null|Discount
	 */
	public static function loadByBasket(BasketBase $basket)
	{
		$order = $basket->getOrder();
		if ($order instanceof Order)
		{
			return self::buildFromOrder($order);
		}

		return self::buildFromBasket($basket, new Context\Fuser($basket->getFUserId(true)));
	}

	/**
	 * Get discount by order.
	 *
	 * @deprecated deprecated sinse sale 17.0.11
	 * @see Discount::buildFromOrder
	 *
	 * @param Order $order		Order object.
	 * @return Discount
	 */
	public static function load(Order $order)
	{
		return self::buildFromOrder($order);
	}

	/**
	 * @return Discount
	 */
	protected static function createDiscountObject()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$discountClassName = $registry->getDiscountClassName();

		return new $discountClassName();
	}

	/**
	 * Builds discounts from order.
	 *
	 * @param Order $order Order object.
	 * @return Discount
	 */
	public static function buildFromOrder(Order $order)
	{
		$instanceIndex = static::getInstanceIndexByOrder($order);
		if (!isset(self::$instances[$instanceIndex]))
		{
			$discount = static::createDiscountObject();
			$discount->order = $order;
			$discount->context = new Context\User($order->getUserId());
			$discount->initInstanceData();
			self::$instances[$instanceIndex] = $discount;
			unset($discount);
		}

		return self::$instances[$instanceIndex];
	}

	/**
	 * Builds discounts from basket. Basket doesn't have to have a order.
	 * Context describes user and user groups which use in
	 *
	 * @param BasketBase $basket Basket.
	 * @param Context\BaseContext $context Context.
	 *
	 * @return mixed|null
	 * @throws Main\InvalidOperationException
	 */
	public static function buildFromBasket(BasketBase $basket, Context\BaseContext $context)
	{
		if ($basket->getOrder())
		{
			throw new Main\InvalidOperationException(
				'Could not build discounts from basket which has the order. You have to use buildFromOrder.'
			);
		}

		if ($basket->count() == 0)
		{
			return null;
		}

		//todo be careful $context may be important in getInstanceIndexByBasket()
		$instanceIndex = static::getInstanceIndexByBasket($basket, $context);
		if (!isset(self::$instances[$instanceIndex]))
		{
			$discount = static::createDiscountObject();
		}
		else
		{
			$discount = self::$instances[$instanceIndex];
		}

		/** @var Discount $discount */
		$discount->basket = $basket;
		$discount->context = $context;
		$discount->initInstanceData();

		self::$instances[$instanceIndex] = $discount;

		return self::$instances[$instanceIndex];
	}

	/**
	 * Get discount by order basket.
	 *
	 * @param Basket $basket		Basket.
	 * @return Discount
	 */
	public static function setOrder(Basket $basket)
	{
		$instanceIndex = static::getInstanceIndexByBasket($basket);
		if (!isset(self::$instances[$instanceIndex]))
			return static::loadByBasket($basket);
		$order = $basket->getOrder();
		if (!($order instanceof Order))
			return self::$instances[$instanceIndex];
		$newInstanceIndex = static::getInstanceIndexByOrder($order);
		if (!isset(self::$instances[$newInstanceIndex]))
		{
			/** @var Discount $discount */
			$discount = self::$instances[$instanceIndex];
			unset(self::$instances[$instanceIndex]);
			$discount->basket = null;
			$discount->order = $order;
			$discount->setNewOrder();
			$discount->loadShipment();
			$discount->fillShipmentData();
			self::$instances[$newInstanceIndex] = $discount;
			unset($discount);
			return self::$instances[$newInstanceIndex];
		}
		else
		{
			unset(self::$instances[$instanceIndex]);
			unset(self::$instances[$newInstanceIndex]);
			return static::load($order);
		}
	}

	/**
	 * Return order.
	 *
	 * @return Order|null
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * Return flag is order exists.
	 *
	 * @return bool
	 */
	public function isOrderExists()
	{
		return ($this->order instanceof Order);
	}

	/**
	 * Clone entity.
	 *
	 * @internal
	 * @param \SplObjectStorage $cloneEntity	Clone repository.
	 *
	 * @return Discount
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
			return $cloneEntity[$this];

		$discountClone = clone $this;
		$discountClone->isClone = true;

		if (!$cloneEntity->contains($this))
			$cloneEntity[$this] = $discountClone;

		if ($this->isOrderExists())
		{
			if ($cloneEntity->contains($this->order))
				$discountClone->order = $cloneEntity[$this->order];
		}
		elseif ($this->isBasketExist())
		{
			if ($cloneEntity->contains($this->basket))
				$discountClone->basket = $cloneEntity[$this->basket];
		}

		if ($this->isShipmentExists())
		{
			if ($cloneEntity->contains($this->shipment))
				$discountClone->shipment = $cloneEntity[$this->shipment];
		}

		return $discountClone;
	}

	/**
	 * Return true if discount is cloned.
	 *
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}
	/**
	 * Return apply mode list.
	 *
	 * @param bool $extendedMode			Get mode list with names.
	 * @return array
	 */
	public static function getApplyModeList($extendedMode = false)
	{
		$extendedMode = ($extendedMode === true);
		if ($extendedMode)
		{
			return array(
				self::APPLY_MODE_ADD => Loc::getMessage('BX_SALE_DISCOUNT_APPLY_MODE_ADD_EXT'),
				self::APPLY_MODE_LAST => Loc::getMessage('BX_SALE_DISCOUNT_APPLY_MODE_LAST_EXT'),
				self::APPLY_MODE_DISABLE => Loc::getMessage('BX_SALE_DISCOUNT_APPLY_MODE_DISABLE_EXT'),
				self::APPLY_MODE_FULL_LAST => Loc::getMessage('BX_SALE_DISCOUNT_APPLY_MODE_FULL_LAST'),
				self::APPLY_MODE_FULL_DISABLE => Loc::getMessage('BX_SALE_DISCOUNT_APPLY_MODE_FULL_DISABLE')
			);
		}
		return array(
			self::APPLY_MODE_ADD,
			self::APPLY_MODE_LAST,
			self::APPLY_MODE_DISABLE,
			self::APPLY_MODE_FULL_LAST,
			self::APPLY_MODE_FULL_DISABLE
		);
	}

	/**
	 * Returns current sale discount apply mode.
	 *
	 * @return int
	 * @throws Main\ArgumentNullException
	 */
	public static function getApplyMode()
	{
		$applyMode = self::APPLY_MODE_ADD;
		if ((string)Main\Config\Option::get('sale', 'use_sale_discount_only') != 'Y')
		{
			$applyMode = (int)Main\Config\Option::get('sale', 'discount_apply_mode');
			if (!in_array($applyMode, self::getApplyModeList(false)))
				$applyMode = self::APPLY_MODE_ADD;
		}
		return $applyMode;
	}

	/**
	 * Set calculate mode.
	 *
	 * @param int $useMode			Calculate mode.
	 * @return void
	 */
	public function setUseMode($useMode)
	{
		$useMode = (int)$useMode;
		if ($useMode <= 0)
			return;
		$this->useMode = $useMode;
	}

	/**
	 * Return calculate mode.
	 *
	 * @return int
	 */
	public function getUseMode()
	{
		return $this->useMode;
	}

	/**
	 * Set full refresh status from edit order form.
	 *
	 * @param bool $state		Refresh or not order.
	 * @return void
	 */
	public function setOrderRefresh($state)
	{
		if ($state !== true && $state !== false)
			return;
		$this->orderRefresh = $state;
	}

	/**
	 * Return full refresh status.
	 *
	 * @return bool
	 */
	public function isOrderRefresh()
	{
		return $this->orderRefresh;
	}

	/**
	 * Return flag new order.
	 *
	 * @return bool
	 */
	public function isOrderNew()
	{
		return $this->newOrder;
	}

	/**
	 * Sets list of execute module which will be used to filter discount.
	 *
	 * @internal
	 * @param array $moduleList		Allowed execute module list.
	 * @return void
	 */
	public function setExecuteModuleFilter(array $moduleList)
	{
		$this->executeModuleFilter = $moduleList;
	}

	/* public basket item data tools */

	/**
	 * Set base price for basket item.
	 *
	 * @param int|string $code				Basket code.
	 * @param float $price			Price.
	 * @param string $currency		Currency.
	 * @return void
	 * @throws Main\ArgumentNullException
	 */
	public function setBasketItemBasePrice($code, $price, $currency)
	{
		$basketCurrency = (string)$this->getBasketCurrency($code);
		if ($basketCurrency == '')
			throw new Main\ArgumentNullException('basket item currency');
		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection PhpInternalEntityUsedInspection */
		$this->basketBasePrice[$code] = (
			$currency == $basketCurrency
			? $price
			: PriceMaths::roundPrecision(\CCurrencyRates::convertCurrency($price, $currency, $basketCurrency))
		);
		unset($basketCurrency);
	}

	/**
	 * Set base price for all basket items.
	 *
	 * @param array $basket					Basket.
	 * @return void
	 * @throws Main\ArgumentNullException
	 */
	public function setBasketBasePrice($basket)
	{
		$this->basketBasePrice = array();
		if (empty($basket) || !is_array($basket))
			return;
		foreach ($basket as $code => $basketItem)
		{
			$basketCurrency = (string)$this->getBasketCurrency($code);
			if ($basketCurrency == '')
				throw new Main\ArgumentNullException('basket item currency');
			/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection PhpInternalEntityUsedInspection */
			$this->basketBasePrice[$code] = (
				$basketItem['CURRENCY'] == $basketCurrency
				? $basketItem['PRICE']
				: PriceMaths::roundPrecision(\CCurrencyRates::convertCurrency($basketItem['PRICE'], $basketItem['CURRENCY'], $basketCurrency))
			);
			unset($basketCurrency);
		}
		unset($code, $basketItem);
	}

	/**
	 * Get base price for basket item.
	 *
	 * @param int|string $code				Basket code.
	 * @return float|null
	 */
	public function getBasketItemBasePrice($code)
	{
		return (isset($this->basketBasePrice[$code]) ? $this->basketBasePrice[$code] : null);
	}

	/**
	 * Set product discounts for basket item.
	 *
	 * @param int|string $code				Basket code.
	 * @param array $discountList			Discount list.
	 * @return void
	 */
	public function setBasketItemDiscounts($code, $discountList)
	{
		if ((string)Main\Config\Option::get('sale', 'use_sale_discount_only') != 'Y')
		{
			if (!is_array($discountList))
				return;
			$this->basketDiscountList[$code] = $discountList;
		}
	}

	/**
	 * @param int|string $code				Basket code.
	 * @param array $providerData			Product data from provider.
	 * @throws Main\ArgumentNullException
	 * @return void
	 */
	public function setBasketItemData($code, $providerData)
	{
		$code = (string)$code;
		if ($code == '' || empty($providerData) || !is_array($providerData))
			return;
		if (isset($providerData['BASE_PRICE']) && isset($providerData['CURRENCY']))
			$this->setBasketItemBasePrice($code, $providerData['BASE_PRICE'], $providerData['CURRENCY']);
		if (isset($providerData['DISCOUNT_LIST']))
		{
			if (!empty($providerData['DISCOUNT_LIST']) || isset($this->basketDiscountList[$code]))
				$this->setBasketItemDiscounts($code, $providerData['DISCOUNT_LIST']);
		}
		$this->basketItemsData[$code] = $providerData;
	}

	/**
	 * Clear basket item data.
	 *
	 * @param int|string $code				Basket code.
	 * @return void
	 */
	public function clearBasketItemData($code)
	{
		if (isset($this->basketBasePrice[$code]))
			unset($this->basketBasePrice[$code]);
		if (isset($this->basketDiscountList[$code]))
			unset($this->basketDiscountList[$code]);
		if (isset($this->basketItemsData[$code]))
			unset($this->basketItemsData[$code]);

	}

	/* public basket item data tools finish */

	/**
	 * Set calculate shipments.
	 *
	 * @param Shipment $shipment							Current shipment.
	 * @return void
	 */
	public function setCalculateShipments(Shipment $shipment = null)
	{
		$this->shipment = $shipment;
	}

	/**
	 * Return shipment id list for existing order.
	 *
	 * @return array
	 */
	public function getShipmentsIds()
	{
		return $this->shipmentIds;
	}

	/**
	 * Calculate discounts.
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public function calculate()
	{
		/** @var Result $result */
		$result = new Result;
		$process = true;

		if ($this->stopCalculate())
			return $result;

		$this->discountsCache = array();
		$this->couponsCache = array();

		if (Compatible\DiscountCompatibility::isUsed())
			return $result;

		$this->setUseMode(self::USE_MODE_FULL);
		if ($this->isOrderExists() && !$this->isOrderNew())
		{
			if ($this->isOrderRefresh())
				$this->setUseMode(self::USE_MODE_FULL);
			elseif ($this->isMixedBasket())
				$this->setUseMode(self::USE_MODE_MIXED);
			elseif ($this->getOrder()->getCalculateType() == Order::SALE_ORDER_CALC_TYPE_REFRESH)
				$this->setUseMode(self::USE_MODE_FULL);
			else
				$this->setUseMode(self::USE_MODE_APPLY);
			if ($this->isOrderRefresh())
			{
				$this->setApplyResult(array());
				DiscountCouponsManager::useSavedCouponsForApply(true);
			}
		}

		$this->orderData = null;
		$orderResult = $this->loadOrderData();

		if (!$orderResult->isSuccess())
		{
			$process = false;
			$result->addErrors($orderResult->getErrors());
		}
		unset($orderResult);

		if ($this->convertedOrder)
			return $result;

		if ($process)
		{
			$this->resetBasketPrices();
			switch ($this->getUseMode())
			{
				case self::USE_MODE_APPLY:
					$calculateResult = $this->calculateApply();
					break;
				case self::USE_MODE_MIXED:
					$calculateResult = $this->calculateMixed();
					break;
				case self::USE_MODE_FULL:
					$calculateResult = $this->calculateFull();
					break;
				default:
					$calculateResult = new Result;
					$calculateResult->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_BAD_USE_MODE'),
						self::ERROR_ID
					));
					break;
			}
			if (!$calculateResult->isSuccess())
				$result->addErrors($calculateResult->getErrors());
			else
				$result->setData($this->fillDiscountResult());
			unset($calculateResult);
		}

		return $result;
	}

	/* apply result methods */
	/**
	 * Change applied discount list.
	 *
	 * @param array $applyResult		Change apply result.
	 * @return void
	 */
	public function setApplyResult($applyResult)
	{
		if (is_array($applyResult))
			$this->applyResult = $applyResult;

		if (!empty($this->applyResult['DISCOUNT_LIST']))
		{
			if (!empty($this->applyResult['BASKET']) && is_array($this->applyResult['BASKET']))
			{
				foreach ($this->applyResult['BASKET'] as $discountList)
				{
					if (empty($discountList) || !is_array($discountList))
						continue;
					foreach ($discountList as $orderDiscountId => $apply)
					{
						if ($apply == 'Y')
							$this->applyResult['DISCOUNT_LIST'][$orderDiscountId] = 'Y';
					}
					unset($apply, $orderDiscountId);
				}
				unset($discountList);
			}
			if (!empty($this->applyResult['DELIVERY']) && is_array($this->applyResult['DELIVERY']))
			{
				foreach ($this->applyResult['DELIVERY'] as $orderDiscountId => $apply)
				{
					if ($apply == 'Y')
						$this->applyResult['DISCOUNT_LIST'][$orderDiscountId] = 'Y';
				}
				unset($apply, $orderDiscountId);
			}
		}
	}

	/**
	 * Return discount list description.
	 *
	 * @param bool $extMode			Extended mode.
	 * @return array
	 */
	public function getApplyResult($extMode = false)
	{
		if (Compatible\DiscountCompatibility::isUsed())
			return Compatible\DiscountCompatibility::getApplyResult($extMode);

		$extMode = ($extMode === true);

		if (!$this->isOrderNew() && empty($this->orderData))
			$this->loadOrderData();

		$this->getApplyDiscounts();
		$this->getApplyPrices();
		$this->getApplyDeliveryList();
		if ($extMode)
			$this->remakingDiscountResult();

		$result = $this->discountResult;
		$result['CONVERTED_ORDER'] = ($this->convertedOrder ? 'Y' : 'N');
		$result['FULL_DISCOUNT_LIST'] = $this->fullDiscountList;

		if ($extMode)
		{
			unset($result['APPLY_BLOCKS']);
		}
		else
		{
			/* for compatibility only */
			if (isset($this->discountResult['APPLY_BLOCKS'][0]['BASKET']))
				$result['BASKET'] = $this->discountResult['APPLY_BLOCKS'][0]['BASKET'];
			if (isset($this->discountResult['APPLY_BLOCKS'][0]['ORDER']))
				$result['ORDER'] = $this->discountResult['APPLY_BLOCKS'][0]['ORDER'];
		}
		return $result;
	}

	/* apply result methods finish */

	/**
	 * Save discount result.
	 *
	 * @return Result
	 */
	public function save()
	{
		$process = true;
		$result = new Result;
		if (!$this->isOrderExists() || !$this->isBasketNotEmpty())
			return $result;
		$orderId = (int)$this->getOrder()->getId();

		if ($this->isUsedDiscountCompatibility())
		{
			if (Compatible\DiscountCompatibility::isRepeatSave())
				return $result;
			$compatibleResult = Compatible\DiscountCompatibility::getResult();
			if ($compatibleResult === false)
				return $result;
			if (empty($compatibleResult))
				return $result;

			$this->setUseMode($compatibleResult['CALCULATE']['USE_MODE']);
			$this->newOrder = $compatibleResult['CALCULATE']['NEW_ORDER'];
			$this->basketBasePrice = $compatibleResult['BASE_PRICE'];
			$this->discountsCache = $compatibleResult['DISCOUNT_LIST'];
			$this->couponsCache = $compatibleResult['COUPONS_LIST'];
			$this->discountResult = $compatibleResult['DISCOUNT_RESULT'];
			$this->forwardBasketTable = $compatibleResult['FORWARD_BASKET_TABLE'];
			$this->reverseBasketTable = $compatibleResult['REVERSE_BASKET_TABLE'];
			if ($this->isOrderNew())
			{
				$shipmentResult = $this->loadShipment();
				if (!$shipmentResult->isSuccess())
				{

				}
				unset($shipmentResult);
				$this->fillShipmentData();
			}
			unset($compatibleResult);
		}

		if ($this->getUseMode() === null)
			return $result;

		if ($process)
		{
			switch ($this->getUseMode())
			{
				case self::USE_MODE_FULL:
					$saveResult = $this->saveFull();
					break;
				case self::USE_MODE_APPLY:
					$saveResult = $this->saveApply();
					break;
				case self::USE_MODE_MIXED:
					$saveResult = $this->saveMixed();
					break;
				default:
					$saveResult = new Result;
					$saveResult->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_BAD_USE_MODE'),
						self::ERROR_ID
					));
			}
			if (!$saveResult->isSuccess())
			{
				$result->addErrors($saveResult->getErrors());
			}
			else
			{
				if ($orderId > 0)
					OrderHistory::addLog('DISCOUNT', $orderId, 'DISCOUNT_SAVED', null, null, array(), OrderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1);
			}
			unset($saveResult);
		}

		if ($orderId > 0)
			OrderHistory::collectEntityFields('DISCOUNT', $orderId);

		return $result;
	}

	/**
	 * Initial instance data.
	 *
	 * @return void
	 */
	protected function initInstanceData()
	{
		$this->orderData = null;
		$this->isClone = false;
		$this->entityResultCache = array();
		$this->setNewOrder();
		$this->fillEmptyDiscountResult();

		if ($this->isOrderExists())
		{
			$orderDiscountConfig = array(
				'SITE_ID' => $this->getOrder()->getSiteId(),
				'CURRENCY' => $this->getOrder()->getCurrency()
			);
		}
		else
		{
			/** @var BasketItem $basketItem */
			$basketItem = $this->getBasket()->rewind();
			$orderDiscountConfig = array(
				'SITE_ID' => $basketItem->getField('LID'),
				'CURRENCY' => $basketItem->getCurrency()
			);
			unset($basketItem);
		}
		OrderDiscountManager::init();
		OrderDiscountManager::setManagerConfig($orderDiscountConfig);
		unset($orderDiscountConfig);
	}

	/**
	 * Return is allow discount calculate.
	 *
	 * @return bool
	 */
	protected function stopCalculate()
	{
		if (!$this->isBasketNotEmpty())
			return true;
		if ($this->isOrderExists() && $this->getOrder()->isExternal())
			return true;
		if ($this->isOrderExists() && !$this->isShipmentExists())
			return false;
		return false;
	}

	/**
	 * Set new order flag.
	 *
	 * @return void
	 */
	protected function setNewOrder()
	{
		if ($this->newOrder !== null)
			return;
		$this->newOrder = true;
		if ($this->isOrderExists())
			$this->newOrder = ((int)$this->getOrder()->getId() <= 0);
	}

	/**
	 * Return true, if only sale discounts is allowed. For new order or refreshed order use sale option, otherwise use order option.
	 *
	 * @return bool
	 */
	protected function useOnlySaleDiscounts()
	{
		if (!$this->isOrderExists() || $this->isOrderNew() || $this->isOrderRefresh())
			return (string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y';
		else
			return (isset($this->saleOptions['SALE_DISCOUNT_ONLY']) && $this->saleOptions['SALE_DISCOUNT_ONLY'] == 'Y');
	}

	/**
	 * Return basket item currency.
	 *
	 * @param string|int $basketCode	Basket item code.
	 * @return string
	 */
	protected function getBasketCurrency($basketCode)
	{
		if ($this->isOrderExists())
			return $this->getOrder()->getCurrency();

		$currency = '';
		/** @var BasketItem $basketItem */
		if ($this->isBasketNotEmpty())
		{
			$basket = $this->getBasket();
			foreach ($basket as $basketItem)
			{
				if ($basketItem->getBasketCode() == $basketCode)
				{
					$currency = $basketItem->getCurrency();
					break;
				}
			}
			unset($basket, $basketItem);
		}
		if ($currency == '')
			$currency = (string)Main\Config\Option::get('sale', 'default_currency');
		return $currency;
	}

	/**
	 * Return current basket.
	 *
	 * @return Basket
	 */
	protected function getBasket()
	{
		if ($this->isOrderExists())
			return $this->getOrder()->getBasket();
		else
			return $this->basket;
	}

	/**
	 * Return exists basket.
	 *
	 * @return bool
	 */
	protected function isBasketExist()
	{
		if ($this->isOrderExists())
			return ($this->getOrder()->getBasket() instanceof Basket);
		else
			return ($this->basket instanceof Basket);
	}

	/**
	 * Returns the existence of a non-empty basket.
	 *
	 * @return bool
	 */
	protected function isBasketNotEmpty()
	{
		if ($this->isOrderExists())
		{
			$basket = $this->getOrder()->getBasket();
			$result = ($basket instanceof Basket && $basket->count() > 0);
			unset($basket);
		}
		else
		{
			$result = ($this->basket instanceof Basket && $this->basket->count() > 0);
		}
		return $result;
	}

	/**
	 * Load order information.
	 *
	 * @return Result
	 */
	protected function loadOrderData()
	{
		$result = new Result;
		$orderId = 0;
		if ($this->isOrderExists())
			$orderId = $this->getOrder()->getId();

		if (empty($this->orderData))
			$this->fillEmptyOrderData();
		$this->shipmentIds = array();

		$basketResult = $this->loadBasket();
		if (!$basketResult->isSuccess())
		{
			$result->addErrors($basketResult->getErrors());
			return $result;
		}
		unset($basketResult);

		if ($this->isOrderExists() && $orderId > 0)
		{
			$basketResult = $this->getBasketTables();
			if (!$basketResult->isSuccess())
			{
				$result->addErrors($basketResult->getErrors());
				return $result;
			}
			unset($basketResult);
		}

		$this->loadOrderConfig();

		$discountResult = $this->loadOrderDiscounts();
		if (!$discountResult->isSuccess())
			$result->addErrors($discountResult->getErrors());
		unset($discountResult);

		if (!$this->isShipmentExists())
		{
			$shipmentResult = $this->loadShipment();
			if (!$shipmentResult->isSuccess())
			{
				$result->addErrors($shipmentResult->getErrors());

				return $result;
			}
			unset($shipmentResult);
		}
		$this->fillShipmentData();

		return $result;
	}

	/**
	 * Fill empty order data.
	 *
	 * @return void
	 */
	protected function fillEmptyOrderData()
	{
		/** @var Basket $basket*/
		$basket = $this->getBasket();
		if ($this->isOrderExists())
		{
			$order = $this->getOrder();
			$this->orderData = array(
				'ID' => $order->getId(),
				'USER_ID' => $order->getUserId(),
				'SITE_ID' => $order->getSiteId(),
				'LID' => $order->getSiteId(), // compatibility only
				'ORDER_PRICE' => $order->getPrice(),
				'ORDER_WEIGHT' => $basket->getWeight(),
				'CURRENCY' => $order->getCurrency(),
				'PERSON_TYPE_ID' => $order->getPersonTypeId(),
				'RECURRING_ID' => $order->getField('RECURRING_ID'),
				'BASKET_ITEMS' => array(),
				'PRICE_DELIVERY' => 0,
				'PRICE_DELIVERY_DIFF' => 0,
				'DELIVERY_ID' => 0,
				'CUSTOM_PRICE_DELIVERY' => 'N',
				'SHIPMENT_CODE' => 0,
				'SHIPMENT_ID' => 0,
				'ORDER_PROP' => array()
			);
			$paymentCollection = $order->getPaymentCollection();
			/** @var Payment $payment */
			foreach ($paymentCollection as $payment)
			{
				if ($payment->isInner())
					continue;
				if (!isset($this->orderData['PAY_SYSTEM_ID']))
				{
					$this->orderData['PAY_SYSTEM_ID'] = (int)$payment->getPaymentSystemId();
					break;
				}
			}
			unset($payment, $paymentCollection);
			if (!isset($this->orderData['PAY_SYSTEM_ID']))
				$this->orderData['PAY_SYSTEM_ID'] = 0;

			/** @var \Bitrix\Sale\PropertyValueCollection $shipmentCollection */
			$propertyCollection = $order->getPropertyCollection();
			/** @var \Bitrix\Sale\PropertyValue $orderProperty */
			foreach ($propertyCollection as $orderProperty)
				$this->orderData['ORDER_PROP'][$orderProperty->getPropertyId()] = $orderProperty->getValue();
			unset($orderProperty);
			foreach (static::getOrderPropertyCodes() as $propertyCode => $attribute)
			{
				$this->orderData[$propertyCode] = '';
				$orderProperty = $propertyCollection->getAttribute($attribute);
				if ($orderProperty instanceof PropertyValue)
					$this->orderData[$propertyCode] = $orderProperty->getValue();
				unset($orderProperty);
			}
			unset($propertyCode, $attribute);
			unset($propertyCollection);
		}
		else
		{
			if ($this->isBasketNotEmpty())
			{
				/** @var BasketItem $basketItem */
				$basketItem = $basket->rewind();
				$currency = $basketItem->getCurrency();
				unset($basketItem);
			}
			else
			{
				$currency = Internals\SiteCurrencyTable::getCurrency($basket->getSiteId());
			}

			$this->orderData = array(
				'ID' => 0,
				'USER_ID' => $this->context->getUserId(),
				'SITE_ID' => $basket->getSiteId(),
				'ORDER_PRICE' => $basket->getPrice(),
				'ORDER_WEIGHT' => $basket->getWeight(),
				'CURRENCY' => $currency,
				'PERSON_TYPE_ID' => 0,
				'BASKET_ITEMS' => array(),
				'PRICE_DELIVERY' => 0,
				'PRICE_DELIVERY_DIFF' => 0,
				'DELIVERY_ID' => 0,
				'CUSTOM_PRICE_DELIVERY' => 'N',
				'SHIPMENT_CODE' => 0,
				'SHIPMENT_ID' => 0,
				'PAY_SYSTEM_ID' => 0
			);
			unset($currency);
		}
		unset($basket);
	}

	/**
	 * Get basket data.
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function loadBasket()
	{
		$result = new Result;

		$process = true;

		if (!$this->isBasketExist())
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		elseif (!$this->isBasketNotEmpty())
			return $result;

		/** @var Basket $basket */
		$basket = $this->getBasket();

		if ($process)
		{
			/** @var BasketItem $basketItem */
			foreach ($basket as $basketItem)
			{
				if (!$basketItem->canBuy())
					continue;
				$code = $basketItem->getBasketCode();
				$this->orderData['BASKET_ITEMS'][$code] = $basketItem->getFieldValues();
				unset($this->orderData['BASKET_ITEMS'][$code]['DATE_INSERT']);
				unset($this->orderData['BASKET_ITEMS'][$code]['DATE_UPDATE']);
				$this->orderData['BASKET_ITEMS'][$code]['PROPERTIES'] = $basketItem->getPropertyCollection()->getPropertyValues();
				if (!isset($this->orderData['BASKET_ITEMS'][$code]['DISCOUNT_PRICE']))
					$this->orderData['BASKET_ITEMS'][$code]['DISCOUNT_PRICE'] = 0;
				$this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'] = $basketItem->getField('BASE_PRICE');
				if ($this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'] === null)
				{
					$this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'] = (isset($this->basketBasePrice[$code])
						? $this->basketBasePrice[$code]
						: $this->orderData['BASKET_ITEMS'][$code]['PRICE'] + $this->orderData['BASKET_ITEMS'][$code]['DISCOUNT_PRICE']
					);
				}
				else
				{
					$this->basketBasePrice[$code] = $this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'];
				}
				if (empty($this->orderData['BASKET_ITEMS'][$code]['PRICE_TYPE_ID']))
					$this->orderData['BASKET_ITEMS'][$code]['PRICE_TYPE_ID'] = $this->getBasketItemValue($code, 'PRICE_TYPE_ID');
				if ($basketItem->isBundleParent())
				{
					$bundle = $basketItem->getBundleCollection();
					if (empty($bundle))
					{
						$process = false;
						$result->addError(new Main\Entity\EntityError(
							Loc::getMessage('BX_SALE_DISCOUNT_ERR_BASKET_BUNDLE_EMPTY'),
							self::ERROR_ID
						));
						break;
					}
					/** @var BasketItem $bundleItem */
					foreach ($bundle as $bundleItem)
					{
						$code = $bundleItem->getBasketCode();
						$this->orderData['BASKET_ITEMS'][$code] = $bundleItem->getFieldValues();
						$this->orderData['BASKET_ITEMS'][$code]['PROPERTIES'] = $bundleItem->getPropertyCollection()->getPropertyValues();
						$this->orderData['BASKET_ITEMS'][$code]['IN_SET'] = 'Y';
						if (!isset($this->orderData['BASKET_ITEMS'][$code]['DISCOUNT_PRICE']))
							$this->orderData['BASKET_ITEMS'][$code]['DISCOUNT_PRICE'] = 0;
						$this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'] = $bundleItem->getField('BASE_PRICE');
						if ($this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'] === null)
						{
							$this->orderData['BASKET_ITEMS'][$code]['BASE_PRICE'] = (isset($this->basketBasePrice[$code])
								? $this->basketBasePrice[$code]
								: $this->orderData['BASKET_ITEMS'][$code]['PRICE'] + $this->orderData['BASKET_ITEMS'][$code]['DISCOUNT_PRICE']
							);
						}
					}
					unset($bundle, $bundleItem);
				}
			}
			unset($code, $basketItem);
		}
		unset($basket, $process);

		return $result;
	}

	/**
	 * Return is exists discount shipment.
	 *
	 * @return bool
	 */
	protected function isShipmentExists()
	{
		return ($this->shipment instanceof Shipment);
	}

	/**
	 * Load shipment.
	 *
	 * @return Result
	 */
	protected function loadShipment()
	{
		$result = new Result;
		if (!$this->isOrderExists())
			return $result;
		if (!$this->isShipmentExists())
		{
			$loadDelivery = false;
			$order = $this->getOrder();
			/** @var ShipmentCollection $orderShipmentList */
			$orderShipmentList = $order->getShipmentCollection();
			/** @var Shipment $shipment */
			if ($this->isOrderNew())
			{
				foreach ($orderShipmentList as $shipment)
				{
					if ($shipment->isSystem())
						continue;

					if (!$loadDelivery)
					{
						$this->shipment = $shipment;
						$loadDelivery = true;
					}
					else
					{
						$result->addError(new Main\Entity\EntityError(
							Loc::getMessage('BX_SALE_DISCOUNT_ERR_TOO_MANY_SHIPMENT'),
							self::ERROR_ID
						));

						return $result;
					}
				}
			}
			else
			{
				$shipmentId = false;
				foreach ($orderShipmentList as $shipment)
				{
					if ($shipment->isSystem())
						continue;

					$currentShipmentId = (int)$shipment->getId();
					if ($shipmentId === false || $shipmentId > $currentShipmentId)
						$shipmentId = $currentShipmentId;
				}
				unset($currentShipmentId, $shipment);
				if (!empty($shipmentId))
				{
					$this->shipment = $orderShipmentList->getItemById($shipmentId);
					$loadDelivery = true;
				}
				unset($shipmentId);
			}
			unset($loadDelivery);
		}
		return $result;
	}

	/**
	 * Fill data from shipment.
	 *
	 * @return void
	 */
	protected function fillShipmentData()
	{
		if (!$this->isShipmentExists())
			return;

		$this->orderData['DELIVERY_ID'] = $this->shipment->getDeliveryId();
		$this->orderData['CUSTOM_PRICE_DELIVERY'] = ($this->shipment->isCustomPrice() ? 'Y' : 'N');
		$this->orderData['BASE_PRICE_DELIVERY'] = $this->shipment->getField('BASE_PRICE_DELIVERY');
		$this->orderData['PRICE_DELIVERY'] = $this->orderData['BASE_PRICE_DELIVERY'];
		$this->orderData['SHIPMENT_CODE'] = $this->shipment->getShipmentCode();
		$this->orderData['SHIPMENT_ID'] = (int)$this->shipment->getId();
	}

	/**
	 * Load order config for exists order.
	 *
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	protected function loadOrderConfig()
	{
		$this->convertedOrder = false;
		$this->shipment = null;
		$this->saleOptions = array(
			'USE_BASE_PRICE' => Main\Config\Option::get('sale', 'get_discount_percent_from_base_price'),
			'SALE_DISCOUNT_ONLY' => Main\Config\Option::get('sale', 'use_sale_discount_only'),
			'APPLY_MODE' => Main\Config\Option::get('sale', 'discount_apply_mode')
		);

		$this->fillCompatibleOrderFields();

		if (!$this->isOrderExists())
			return;

		$this->convertedOrder = ($this->isOrderNew() === false);
		if ($this->getUseMode() == self::USE_MODE_FULL)
			$this->convertedOrder = false;

		$order = $this->getOrder();
		$orderId = $order->getId();
		if ($this->isOrderNew() || $this->getUseMode() == self::USE_MODE_FULL)
			return;

		$data = Internals\OrderDiscountDataTable::getList(array(
			'select' => array('*'),
			'filter' => array(
				'=ORDER_ID' => $orderId,
				'=ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_ORDER,
				'=ENTITY_ID' => $orderId
			)
		))->fetch();
		if (empty($data))
			return;

		$entityData = &$data['ENTITY_DATA'];
		if (!empty($entityData['DELIVERY']))
		{
			$this->orderData['DELIVERY_ID'] = $entityData['DELIVERY']['DELIVERY_ID'];
			if (isset($entityData['DELIVERY']['CUSTOM_PRICE_DELIVERY']))
				$this->orderData['CUSTOM_PRICE_DELIVERY'] = $entityData['DELIVERY']['CUSTOM_PRICE_DELIVERY'];
			if (isset($entityData['DELIVERY']['SHIPMENT_ID']))
			{
				$entityData['DELIVERY']['SHIPMENT_ID'] = (int)$entityData['DELIVERY']['SHIPMENT_ID'];
				if ($entityData['DELIVERY']['SHIPMENT_ID'] > 0)
				{
					$this->shipmentIds[] = $entityData['DELIVERY']['SHIPMENT_ID'];
					/** @var ShipmentCollection $orderShipmentList */
					$orderShipmentList = $order->getShipmentCollection();
					$this->shipment = $orderShipmentList->getItemById($entityData['DELIVERY']['SHIPMENT_ID']);
					if (empty($this->shipment))
					{
						$this->shipment = null;
						$this->shipmentIds[] = array();
					}
				}
			}
		}
		if (!empty($entityData['OPTIONS']) && is_array($entityData['OPTIONS']))
		{
			foreach (array_keys($this->saleOptions) as $key)
			{
				if (isset($entityData['OPTIONS'][$key]))
					$this->saleOptions[$key] = $entityData['OPTIONS'][$key];
			}
			unset($key);
			$this->fillCompatibleOrderFields();
		}
		if (!isset($entityData['OLD_ORDER']))
			$this->convertedOrder = false;
		unset($entityData);
		unset($data, $orderId, $order);

		$this->loadRoundConfig();
	}

	/**
	 * Load discounrs for exists order.
	 *
	 * @return Result
	 */
	protected function loadOrderDiscounts()
	{
		$result = new Result;
		$this->discountsCache = array();
		$this->couponsCache = array();

		if (!$this->isOrderExists())
			return $result;

		$order = $this->getOrder();
		if ($this->isOrderNew() || ($this->getUseMode() == self::USE_MODE_FULL && !$this->isMixedBasket()))
			return $result;

		$applyResult = OrderDiscountManager::loadResultFromDatabase(
			$order->getId(),
			true,
			$this->reverseBasketTable,
			$this->orderData['BASKET_ITEMS']
		);

		if (!$applyResult->isSuccess())
			$result->addErrors($applyResult->getErrors());

		$applyResultData = $applyResult->getData();

		if (!empty($applyResultData['DISCOUNT_LIST']))
		{
			foreach ($applyResultData['DISCOUNT_LIST'] as $orderDiscountId => $discountData)
			{
				$discountData['ACTIONS_DESCR_DATA'] = false;
				if (!empty($discountData['ACTIONS_DESCR']) && is_array($discountData['ACTIONS_DESCR']))
				{
					$discountData['ACTIONS_DESCR_DATA'] = $discountData['ACTIONS_DESCR'];
					$discountData['ACTIONS_DESCR'] = self::formatDescription($discountData['ACTIONS_DESCR']);
				}
				else
				{
					$discountData['ACTIONS_DESCR'] = false;
				}
				if (empty($discountData['ACTIONS_DESCR']))
				{
					$discountData['ACTIONS_DESCR'] = false;
					$discountData['ACTIONS_DESCR_DATA'] = false;
				}
				$this->discountsCache[$orderDiscountId] = $discountData;

				if (isset($applyResultData['DISCOUNT_MODULES'][$orderDiscountId]))
					$this->cacheDiscountModules[$orderDiscountId] = $applyResultData['DISCOUNT_MODULES'][$orderDiscountId];
			}
			unset($orderDiscountId, $discountData);
		}
		if (!empty($applyResultData['COUPON_LIST']))
			$this->couponsCache = $applyResultData['COUPON_LIST'];

		$this->discountResultCounter = 0;
		$this->discountResult['APPLY_BLOCKS'] = $applyResultData['APPLY_BLOCKS'];
		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (!empty($applyBlock['BASKET']))
				{
					foreach ($applyBlock['BASKET'] as $discountList)
					{
						foreach ($discountList as $discount)
						{
							if ($discount['COUPON_ID'] == '')
								continue;
							DiscountCouponsManager::setApplyByProduct($discount, array($discount['COUPON_ID']));
						}
					}
					unset($discountList);
				}

				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if ($discount['COUPON_ID'] != '')
							DiscountCouponsManager::setApply($discount['COUPON_ID'], $discount['RESULT']);
					}
					unset($discount);
				}

				$this->discountResultCounter = $counter + 1;
			}
			unset($counter, $applyBlock);
		}

		if (!empty($applyResultData['DATA']['BASKET']))
		{
			foreach ($applyResultData['DATA']['BASKET'] as $basketCode => $basketData)
			{
				if (!isset($this->orderData['BASKET_ITEMS'][$basketCode]))
					continue;
				if (!isset($basketData['BASE_PRICE']))
					continue;
				if (!isset($this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE']))
				{
					$basketData['BASE_PRICE'] = (float)$basketData['BASE_PRICE'];
					$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'] = $basketData['BASE_PRICE'];
					$this->basketBasePrice[$basketCode] = $basketData['BASE_PRICE'];
				}
				else
				{
					$this->basketBasePrice[$basketCode] = $this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'];
				}
			}
			unset($basketCode, $basketData);
		}

		if (!empty($applyResultData['DATA']['STORED_ACTION_DATA']) && is_array($applyResultData['DATA']['STORED_ACTION_DATA']))
			$this->discountStoredActionData = $applyResultData['DATA']['STORED_ACTION_DATA'];

		unset($applyResultData, $applyResult);

		return $result;
	}

	/**
	 * Return basket item data value from provider.
	 * @internal
	 *
	 * @param int|string $code				Basket code.
	 * @param string $field					Field name.
	 * @return null|mixed
	 */
	protected function getBasketItemValue($code, $field)
	{
		if (!isset($this->basketItemsData[$code]))
			return null;
		return (isset($this->basketItemsData[$code][$field]) ? $this->basketItemsData[$code][$field] : null);
	}

	/**
	 * Return basket item data from provider.
	 * @internal
	 *
	 * @param int|string $code				Basket code.
	 * @param array $fields					Field names.
	 * @return array|null
	 */
	protected function getBasketItemValueList($code, array $fields)
	{
		if (!isset($this->basketItemsData[$code]) || empty($fields))
			return null;

		$result = array();
		foreach ($fields as $fieldName)
		{
			$result[$fieldName] = (
				isset($this->basketItemsData[$code][$fieldName])
				? $this->basketItemsData[$code][$fieldName]
				: null
			);
		}
		unset($fieldName);
		return $result;
	}

	/**
	 * Calculate discount by new order.
	 *
	 * @return Result
	 */
	protected function calculateFull()
	{
		$result = new Result;
		if (!$this->isBasketNotEmpty())
			return $result;

		$this->discountIds = array();
		Discount\Actions::setUseMode(
			Discount\Actions::MODE_CALCULATE,
			array(
				'USE_BASE_PRICE' => $this->saleOptions['USE_BASE_PRICE'],
				'SITE_ID' => $this->orderData['SITE_ID'],
				'CURRENCY' => $this->orderData['CURRENCY']
			)
		);

		$this->fillEmptyDiscountResult();

		$basket = $this->getBasket();
		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$code = $basketItem->getBasketCode();
			if ($this->isCustomPriceByCode($code))
			{
				if (array_key_exists($code, $this->basketDiscountList))
					unset($this->basketDiscountList[$code]);
			}
			else
			{
				if (!isset($this->basketDiscountList[$code]))
				{
					$this->basketDiscountList[$code] = $basketItem->getField('DISCOUNT_LIST');
					if ($this->basketDiscountList[$code] === null)
						unset($this->basketDiscountList[$code]);
				}
			}
		}
		unset($code, $basketItem, $basket);

		DiscountCouponsManager::clearApply();
		$basketDiscountResult = $this->calculateFullBasketDiscount();
		if (!$basketDiscountResult->isSuccess())
		{
			$result->addErrors($basketDiscountResult->getErrors());
			unset($basketDiscountResult);
			return $result;
		}
		unset($basketDiscountResult);

		if ($this->isRoundMode(self::ROUND_MODE_BASKET_DISCOUNT))
			$this->roundFullBasketPrices();

		$this->fillBasketLastDiscount();

		$this->loadDiscountByUserGroups();
		$this->loadDiscountList();
		$executeResult = $this->executeDiscountList();
		if (!$executeResult->isSuccess())
		{
			$result->addErrors($executeResult->getErrors());
			unset($executeResult);
			return $result;
		}
		unset($executeResult);

		if ($this->isRoundMode(self::ROUND_MODE_FINAL_PRICE))
			$this->roundFullBasketPrices();

		return $result;
	}

	/**
	 * Calculate discount by exist order.
	 *
	 * @return Result
	 */
	protected function calculateApply()
	{
		$result = new Result;
		if (!$this->isOrderExists())
			return $result;

		if ($this->convertedOrder)
			return $result;

		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			Discount\Actions::setUseMode(
				Discount\Actions::MODE_MANUAL,
				array(
					'USE_BASE_PRICE' => $this->saleOptions['USE_BASE_PRICE'],
					'SITE_ID' => $this->orderData['SITE_ID'],
					'CURRENCY' => $this->orderData['CURRENCY']
				)
			);

			$currentCounter = $this->discountResultCounter;

			foreach (array_keys($this->discountResult['APPLY_BLOCKS']) as $counter)
			{
				$this->discountResultCounter = $counter;
				$blockResult = $this->calculateApplyDiscountBlock();
				if (!$blockResult->isSuccess())
				{
					$result->addErrors($blockResult->getErrors());
					unset($blockResult);

					return $result;
				}
				unset($blockResult);
			}

			$this->discountResultCounter = $currentCounter;
			unset($currentCounter);
		}

		if ($result->isSuccess())
		{
			Discount\Actions::setUseMode(
				Discount\Actions::MODE_CALCULATE,
				array(
					'USE_BASE_PRICE' => $this->saleOptions['USE_BASE_PRICE'],
					'SITE_ID' => $this->orderData['SITE_ID'],
					'CURRENCY' => $this->orderData['CURRENCY']
				)
			);

			$this->clearCurrentApplyBlock();

			$couponsResult = $this->calculateApplyAdditionalCoupons();
			if (!$couponsResult->isSuccess())
			{
				$result->addErrors($couponsResult->getErrors());
				unset($couponsResult);
				return $result;
			}
			unset($couponsResult);

			if ($this->isRoundMode(self::ROUND_MODE_FINAL_PRICE))
				$this->roundFullBasketPrices();
		}

		return $result;
	}

	/**
	 * Calculate discount by exist order with new items.
	 *
	 * @return Result
	 */
	protected function calculateMixed()
	{
		$result = new Result;

		if (!$this->isOrderExists())
			return $result;

		if ($this->convertedOrder)
			return $result;

		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			Discount\Actions::setUseMode(
				Discount\Actions::MODE_MANUAL,
				array(
					'USE_BASE_PRICE' => $this->saleOptions['USE_BASE_PRICE'],
					'SITE_ID' => $this->orderData['SITE_ID'],
					'CURRENCY' => $this->orderData['CURRENCY']
				)
			);

			$currentCounter = $this->discountResultCounter;

			foreach (array_keys($this->discountResult['APPLY_BLOCKS']) as $counter)
			{
				$this->discountResultCounter = $counter;
				$blockResult = $this->calculateApplyDiscountBlock();
				if (!$blockResult->isSuccess())
				{
					$result->addErrors($blockResult->getErrors());
					unset($blockResult);

					return $result;
				}
				unset($blockResult);
			}

			$this->discountResultCounter = $currentCounter;
			unset($currentCounter);
		}

		if ($result->isSuccess())
		{
			Discount\Actions::setUseMode(
				Discount\Actions::MODE_CALCULATE,
				array(
					'USE_BASE_PRICE' => $this->saleOptions['USE_BASE_PRICE'],
					'SITE_ID' => $this->orderData['SITE_ID'],
					'CURRENCY' => $this->orderData['CURRENCY']
				)
			);

			$this->clearCurrentApplyBlock();

			$basketDiscountResult = $this->calculateFullBasketDiscount();
			if (!$basketDiscountResult->isSuccess())
			{
				$result->addErrors($basketDiscountResult->getErrors());
				unset($basketDiscountResult);
				return $result;
			}
			unset($basketDiscountResult);

			if ($this->isRoundMode(self::ROUND_MODE_BASKET_DISCOUNT))
				$this->roundFullBasketPrices();

			$couponsResult = $this->calculateApplyAdditionalCoupons();
			if (!$couponsResult->isSuccess())
			{
				$result->addErrors($couponsResult->getErrors());
				unset($couponsResult);
				return $result;
			}
			unset($couponsResult);

			if ($this->isRoundMode(self::ROUND_MODE_FINAL_PRICE))
				$this->roundFullBasketPrices();
		}

		return $result;
	}

	/**
	 * Save discount result for new order.
	 *
	 * @return Result
	 */
	protected function saveFull()
	{
		$result = new Result;

		$process = true;
		$order = $this->getOrder();
		$orderId = $order->getId();
		$orderCurrency = $order->getCurrency();
		unset($order);

		if (!Compatible\DiscountCompatibility::isUsed() || !Compatible\DiscountCompatibility::isInited())
		{
			$basketResult = $this->getBasketTables();
			if (!$basketResult->isSuccess())
			{
				$process = false;
				$result->addErrors($basketResult->getErrors());
			}
		}

		if ($process)
		{
			DiscountCouponsManager::finalApply();
			DiscountCouponsManager::saveApplied();
			$couponsResult = $this->saveCoupons();
			if (!$couponsResult->isSuccess())
			{
				$process = false;
				$result->addErrors($couponsResult->getErrors());
			}
		}

		if ($process)
		{
			Internals\OrderRulesTable::clearByOrder($orderId);
			Internals\OrderDiscountDataTable::clearByOrder($orderId);

			$lastApplyBlockResult = $this->saveLastApplyBlock();
			if (!$lastApplyBlockResult->isSuccess())
			{
				$process = false;
				$result->addErrors($lastApplyBlockResult->getErrors());
			}
			unset($lastApplyBlockResult);
		}

		if ($process)
		{
			$fields = array(
				'ORDER_ID' => $orderId,
				'ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_ORDER,
				'ENTITY_ID' => $orderId,
				'ENTITY_VALUE' => $orderId,
				'ENTITY_DATA' => array(
					'OPTIONS' => array(
						'USE_BASE_PRICE' => Main\Config\Option::get('sale', 'get_discount_percent_from_base_price'),
						'SALE_DISCOUNT_ONLY' => Main\Config\Option::get('sale', 'use_sale_discount_only'),
						'APPLY_MODE' => Main\Config\Option::get('sale', 'discount_apply_mode')
					),
					'DELIVERY' => array(
						'DELIVERY_ID' => $this->orderData['DELIVERY_ID'],
						'CUSTOM_PRICE_DELIVERY' => $this->orderData['CUSTOM_PRICE_DELIVERY'],
						'SHIPMENT_ID' => 0
					)
				)
			);
			if ($this->shipment instanceof Shipment)
				$fields['ENTITY_DATA']['DELIVERY']['SHIPMENT_ID'] = $this->shipment->getId();
			$dataResult = Internals\OrderDiscountDataTable::add($fields);
			if (!$dataResult->isSuccess())
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
					self::ERROR_ID
				));
			}
			unset($dataResult, $fields);

			foreach ($this->forwardBasketTable as $basketCode => $basketId)
			{
				$fields = array();
				if (isset($this->basketBasePrice[$basketCode]))
				{
					$fields['BASE_PRICE'] = (string)$this->basketBasePrice[$basketCode];
					$fields['BASE_PRICE_CURRENCY'] = $orderCurrency;
				}
				if (!empty($fields))
				{
					Internals\OrderDiscountDataTable::saveBasketItemData(
						$orderId,
						$this->forwardBasketTable[$basketCode],
						$fields,
						false
					);
				}
				unset($fields);
			}
			unset($basketCode);
			unset($orderCurrency);

			$fields = array(
				'ORDER_ID' => $orderId,
				'ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_ROUND,
				'ENTITY_ID' => $orderId,
				'ENTITY_VALUE' => $orderId,
				'ENTITY_DATA' => array(
					'MODE' => $this->roundApplyMode,
					'CONFIG' => $this->roundApplyConfig
				)
			);
			$dataResult = Internals\OrderDiscountDataTable::add($fields);
			if (!$dataResult->isSuccess())
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
					self::ERROR_ID
				));
			}
			unset($dataResult, $fields);

			if (!empty($this->discountStoredActionData))
			{
				$fields = array(
					'ORDER_ID' => $orderId,
					'ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_DISCOUNT_STORED_DATA,
					'ENTITY_ID' => $orderId,
					'ENTITY_VALUE' => $orderId,
					'ENTITY_DATA' => $this->discountStoredActionData
				);
				$dataResult = Internals\OrderDiscountDataTable::add($fields);
				if (!$dataResult->isSuccess())
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
				}
				unset($dataResult, $fields);
			}
		}

		if ($process)
		{
			if (DiscountCouponsManager::usedByManager())
				DiscountCouponsManager::clear(true);
		}

		return $result;
	}

	/**
	 * Save discount result for exist order.
	 *
	 * @return Result
	 */
	protected function saveApply()
	{
		$result = new Result;

		$process = true;
		$orderId = $this->getOrder()->getId();

		if ($this->convertedOrder)
			return $result;

		$basketResult = $this->getBasketTables();
		if (!$basketResult->isSuccess())
		{
			$process = false;
			$result->addErrors($basketResult->getErrors());
		}

		$deleteList = array();
		$rulesIterator = Internals\OrderRulesTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=ORDER_ID' => $orderId)
		));
		while ($rule = $rulesIterator->fetch())
		{
			$rule['ID'] = (int)$rule['ID'];
			$deleteList[$rule['ID']] = $rule['ID'];
		}
		unset($rule, $rulesIterator);

		$deleteRoundList = array();
		$iterator = Internals\OrderRoundTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=ORDER_ID' => $orderId)
		));
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$deleteRoundList[$row['ID']] = $row['ID'];
		}
		unset($row, $iterator);

		$rulesList = array();
		foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
		{
			if ($counter == $this->discountResultCounter)
				continue;

			if (!empty($applyBlock['BASKET']))
			{
				foreach ($applyBlock['BASKET'] as $basketCode => $discountList)
				{
					foreach ($discountList as $discount)
					{
						if (!isset($discount['RULE_ID']) || (int)$discount['RULE_ID'] < 0)
						{
							$process = false;
							$result->addError(new Main\Entity\EntityError(
								Loc::getMessage('BX_SALE_DISCOUNT_ERR_EMPTY_RULE_ID_EXT_DISCOUNT'),
								self::ERROR_ID
							));
							continue;
						}
						$orderDiscountId = $discount['DISCOUNT_ID'];
						$ruleData = array(
							'RULE_ID' => $discount['RULE_ID'],
							'APPLY' => $discount['RESULT']['APPLY'],
							'DESCR_ID' => (isset($discount['RULE_DESCR_ID']) ? (int)$discount['RULE_DESCR_ID'] : 0),
							'DESCR' => $discount['RESULT']['DESCR_DATA']['BASKET'],
						);
						if ($ruleData['DESCR_ID'] <= 0)
						{
							$ruleData['DESCR_ID'] = 0;
							$ruleData['MODULE_ID'] = $this->discountsCache[$orderDiscountId]['MODULE_ID'];
							$ruleData['ORDER_DISCOUNT_ID'] = $orderDiscountId;
							$ruleData['ORDER_ID'] = $orderId;
						}
						$rulesList[] = $ruleData;
						unset($ruleData);
					}
					unset($discount);
				}
				unset($basketCode, $discountList);
			}

			if (!empty($applyBlock['ORDER']))
			{
				foreach ($applyBlock['ORDER'] as $discount)
				{
					$orderDiscountId = $discount['DISCOUNT_ID'];
					if (!empty($discount['RESULT']['BASKET']))
					{
						foreach ($discount['RESULT']['BASKET'] as $basketCode => $applyData)
						{
							if (!isset($applyData['RULE_ID']) || (int)$applyData['RULE_ID'] < 0)
							{
								$process = false;
								$result->addError(new Main\Entity\EntityError(
									Loc::getMessage('BX_SALE_DISCOUNT_ERR_EMPTY_RULE_ID_SALE_DISCOUNT'),
									self::ERROR_ID
								));
								continue;
							}
							$ruleData = array(
								'RULE_ID' => $applyData['RULE_ID'],
								'APPLY' => $applyData['APPLY'],
								'DESCR_ID' => (isset($applyData['RULE_DESCR_ID']) ? (int)$applyData['RULE_DESCR_ID'] : 0),
								'DESCR' => $applyData['DESCR_DATA'],
							);
							if ($ruleData['DESCR_ID'] <= 0)
							{
								$ruleData['DESCR_ID'] = 0;
								$ruleData['MODULE_ID'] = $this->discountsCache[$orderDiscountId]['MODULE_ID'];
								$ruleData['ORDER_DISCOUNT_ID'] = $orderDiscountId;
								$ruleData['ORDER_ID'] = $orderId;
							}
							if (!$discount['ACTION_BLOCK_LIST'])
								$ruleData['ACTION_BLOCK_LIST'] = $applyData['ACTION_BLOCK_LIST'];
							$rulesList[] = $ruleData;
							unset($ruleData);
						}
						unset($basketCode, $applyData);
					}
					if (!empty($discount['RESULT']['DELIVERY']))
					{
						if (!isset($discount['RESULT']['DELIVERY']['RULE_ID']) || (int)$discount['RESULT']['DELIVERY']['RULE_ID'] < 0)
						{
							$process = false;
							$result->addError(new Main\Entity\EntityError(
								Loc::getMessage('BX_SALE_DISCOUNT_ERR_EMPTY_RULE_ID_SALE_DISCOUNT'),
								self::ERROR_ID
							));
							continue;
						}
						$ruleData = array(
							'RULE_ID' => $discount['RESULT']['DELIVERY']['RULE_ID'],
							'APPLY' => $discount['RESULT']['DELIVERY']['APPLY'],
							'DESCR_ID' => (isset($discount['RESULT']['DELIVERY']['RULE_DESCR_ID']) ? (int)$discount['RESULT']['DELIVERY']['RULE_DESCR_ID'] : 0),
							'DESCR' => $discount['RESULT']['DELIVERY']['DESCR_DATA']
						);
						if ($ruleData['DESCR_ID'] <= 0)
						{
							$ruleData['DESCR_ID'] = 0;
							$ruleData['MODULE_ID'] = $this->discountsCache[$orderDiscountId]['MODULE_ID'];
							$ruleData['ORDER_DISCOUNT_ID'] = $orderDiscountId;
							$ruleData['ORDER_ID'] = $orderId;
						}
						$rulesList[] = $ruleData;
						unset($ruleData);
					}
				}
				unset($discount);
			}

			if (!empty($applyBlock['BASKET_ROUND']))
			{
				foreach ($applyBlock['BASKET_ROUND'] as $row)
				{
					if (isset($deleteRoundList[$row['RULE_ID']]))
						unset($deleteRoundList[$row['RULE_ID']]);
				}
				unset($row);
			}
		}

		if ($process && !empty($rulesList))
		{
			foreach ($rulesList as $ruleRow)
			{
				$rowUpdate = array('APPLY' => $ruleRow['APPLY']);
				if (isset($ruleRow['ACTION_BLOCK_LIST']))
					$rowUpdate['ACTION_BLOCK_LIST'] = $ruleRow['ACTION_BLOCK_LIST'];
				$ruleResult = Internals\OrderRulesTable::update($ruleRow['RULE_ID'], $rowUpdate);
				unset($rowUpdate);
				if ($ruleResult->isSuccess())
				{
					if (isset($deleteList[$ruleRow['RULE_ID']]))
						unset($deleteList[$ruleRow['RULE_ID']]);
					if ($ruleRow['DESCR_ID'] > 0)
					{
						$descrResult = Internals\OrderRulesDescrTable::update($ruleRow['DESCR_ID'], array('DESCR' => $ruleRow['DESCR']));
					}
					else
					{
						$descrData = array(
							'DESCR' => $ruleRow['DESCR'],
							'MODULE_ID' => $ruleRow['MODULE_ID'],
							'ORDER_DISCOUNT_ID' => $ruleRow['ORDER_DISCOUNT_ID'],
							'ORDER_ID' => $ruleRow['ORDER_ID']
						);
						$descrResult = Internals\OrderRulesDescrTable::add($descrData);
					}
					if (!$descrResult->isSuccess(true))
					{
						$process = false;
						$result->addError(new Main\Entity\EntityError(
							Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
							self::ERROR_ID
						));
						unset($descrResult);
						break;
					}
					unset($descrResult);
				}
				else
				{
					$process = false;
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
					unset($ruleResult);
					break;
				}
				unset($ruleResult);
			}
			unset($ruleRow);
		}

		if ($process && (!empty($deleteList) || !empty($deleteRoundList)))
		{
			$conn = Main\Application::getConnection();
			$helper = $conn->getSqlHelper();
			if (!empty($deleteList))
			{
				$mainQuery = 'delete from '.$helper->quote(Internals\OrderRulesTable::getTableName()).' where '.$helper->quote('ID');
				$descrQuery = 'delete from '.$helper->quote(Internals\OrderRulesDescrTable::getTableName()).' where '.$helper->quote('RULE_ID');
				foreach (array_chunk($deleteList, 500) as $row)
				{
					$conn->queryExecute($mainQuery.' in ('.implode(', ', $row).')');
					$conn->queryExecute($descrQuery.' in ('.implode(', ', $row).')');
				}
				unset($row, $descrQuery, $mainQuery);
			}
			if (!empty($deleteRoundList))
			{
				$query = 'delete from '.$helper->quote(Internals\OrderRoundTable::getTableName()).' where '.$helper->quote('ID');
				foreach (array_chunk($deleteRoundList, 500) as $row)
				{
					$conn->queryExecute($query.' in ('.implode(', ', $row).')');
				}
				unset($row, $query);
			}
			unset($helper, $conn);
		}
		unset($deleteRoundList, $deleteList);

		if ($process)
		{
			if (!empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]))
			{
				DiscountCouponsManager::finalApply();
				DiscountCouponsManager::saveApplied();
				$couponsResult = $this->saveCoupons();
				if (!$couponsResult->isSuccess())
				{
					$process = false;
					$result->addErrors($couponsResult->getErrors());
				}

				if ($process)
				{
					$lastApplyBlockResult = $this->saveLastApplyBlock();
					if (!$lastApplyBlockResult->isSuccess())
					{
						$process = false;
						$result->addErrors($lastApplyBlockResult->getErrors());
					}
					unset($lastApplyBlockResult);
				}
			}
		}

		if ($process)
		{
			$configId = 0;
			$roundData = Internals\OrderDiscountDataTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=ORDER_ID' => $orderId,
					'=ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_ROUND,
					'=ENTITY_ID' => $orderId
				)
			))->fetch();
			if (!empty($roundData))
				$configId = (int)$roundData['ID'];
			if ($configId > 0)
			{
				$fields = array(
					'ENTITY_DATA' => array(
						'MODE' => $this->roundApplyMode,
						'CONFIG' => $this->roundApplyConfig
					)
				);
				$dataResult = Internals\OrderDiscountDataTable::update($configId, $fields);
				if (!$dataResult->isSuccess())
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
				}
				unset($dataResult, $fields);
			}
			else
			{
				$fields = array(
					'ORDER_ID' => $orderId,
					'ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_ROUND,
					'ENTITY_ID' => $orderId,
					'ENTITY_VALUE' => $orderId,
					'ENTITY_DATA' => array(
						'MODE' => $this->roundApplyMode,
						'CONFIG' => $this->roundApplyConfig
					)
				);
				$dataResult = Internals\OrderDiscountDataTable::add($fields);
				if (!$dataResult->isSuccess())
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
				}
				unset($dataResult, $fields);
			}

			if (!empty($this->discountStoredActionData))
			{
				$storedDataId = 0;
				$iterator = Internals\OrderDiscountDataTable::getList(array(
					'select' => array('ID'),
					'filter' => array(
						'=ORDER_ID' => $orderId,
						'=ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_DISCOUNT_STORED_DATA,
						'=ENTITY_ID' => $orderId
					)
				));
				$row = $iterator->fetch();
				if (!empty($row))
					$storedDataId = (int)$row['ID'];
				if ($storedDataId > 0)
				{
					$dataResult = Internals\OrderDiscountDataTable::update(
						$storedDataId,
						array(
							'ENTITY_DATA' => $this->discountStoredActionData
						)
					);
				}
				else
				{
					$dataResult = Internals\OrderDiscountDataTable::add(array(
						'ORDER_ID' => $orderId,
						'ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_DISCOUNT_STORED_DATA,
						'ENTITY_ID' => $orderId,
						'ENTITY_VALUE' => $orderId,
						'ENTITY_DATA' => $this->discountStoredActionData
					));
				}
				if (!$dataResult->isSuccess())
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
				}
				unset($dataResult);
			}
		}

		if ($process)
		{
			if (DiscountCouponsManager::usedByManager())
				DiscountCouponsManager::clear(true);
		}

		return $result;
	}

	/**
	 * Save discount result for mixed order.
	 *
	 * @return Result
	 */
	protected function saveMixed()
	{
		return $this->saveApply();
	}

	/**
	 * Save coupons for order.
	 *
	 * @return Result
	 */
	protected function saveCoupons()
	{
		$result = new Result;
		if (!$this->isOrderExists())
			return $result;
		if (!empty($this->couponsCache))
		{
			$orderId = $this->getOrder()->getId();
			foreach ($this->couponsCache as $orderCouponId => $couponData)
			{
				if ($couponData['ID'] > 0)
					continue;
				$fields = $couponData;
				$fields['ORDER_ID'] = $orderId;
				$couponResult = OrderDiscountManager::saveCoupon($fields);
				if (!$couponResult->isSuccess())
				{
					$result->addErrors($couponResult->getErrors());
					unset($couponResult);
					continue;
				}
				$this->couponsCache[$orderCouponId]['ID'] = $couponResult->getId();
				unset($couponResult);
			}
			unset($orderId);
		}
		return $result;
	}

	public function saveExternalLastApplyblock(BasketItem $basketItem, $orderDiscountId)
	{
		$basket = $basketItem->getCollection();
		$this->order = $basket->getOrder();

		$this->loadOrderData();

		$listItems[$basketItem->getBasketCode()] = array('APPLY'=>'Y', 'ACTION_BLOCK_LIST'=>array(), 'DESCR_DATA'=>array(), 'DESCR'=>array());

		$this->discountsCache[$orderDiscountId]['MODULE_ID'] = 'sale';
		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter];
		$applyBlock['ORDER'][] = array(
				'DISCOUNT_ID'=>$orderDiscountId,
				'COUPON_ID'=>0,
				'RESULT'=>array('BASKET'=>$listItems),
		);

		$this->saveLastApplyBlock();
	}
	/**
	 * Save result last apply block discount.
	 *
	 * @return Result
	 * @throws \Exception
	 */
	protected function saveLastApplyBlock()
	{
		$result = new Result;

		$process = true;

		$orderId = $this->getOrder()->getId();

		$rulesList = array();
		$ruleDescr = array();
		$ruleIndex = 0;

		$roundList = array();
		$roundIndex = 0;

		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter];

		if (!empty($applyBlock['BASKET']))
		{
			foreach ($this->forwardBasketTable as $basketCode => $basketId)
			{
				if (empty($applyBlock['BASKET'][$basketCode]))
					continue;
				$discountList = $applyBlock['BASKET'][$basketCode];
				foreach ($discountList as $discount)
				{
					$orderDiscountId = $discount['DISCOUNT_ID'];
					$rulesList[$ruleIndex] = array(
						'MODULE_ID' => $this->discountsCache[$orderDiscountId]['MODULE_ID'],
						'ORDER_DISCOUNT_ID' => $orderDiscountId,
						'ORDER_ID' => $orderId,
						'ENTITY_TYPE' => Internals\OrderRulesTable::ENTITY_TYPE_BASKET,
						'ENTITY_ID' => $basketId,
						'ENTITY_VALUE' => $basketId,
						'COUPON_ID' => ($discount['COUPON_ID'] != '' ? $this->couponsCache[$discount['COUPON_ID']]['ID'] : 0),
						'APPLY' => $discount['RESULT']['APPLY'],
						'APPLY_BLOCK_COUNTER' => $this->discountResultCounter
					);
					$ruleDescr[$ruleIndex] = array(
						'MODULE_ID' => $this->discountsCache[$orderDiscountId]['MODULE_ID'],
						'ORDER_DISCOUNT_ID' => $orderDiscountId,
						'ORDER_ID' => $orderId,
						'DESCR' => $discount['RESULT']['DESCR_DATA']
					);
					$ruleIndex++;
				}
				unset($discount, $discountList);
			}
			unset($basketCode, $basketId);
		}
		if (!empty($applyBlock['ORDER']))
		{
			foreach ($applyBlock['ORDER'] as $discount)
			{
				$orderDiscountId = $discount['DISCOUNT_ID'];
				if (!empty($discount['RESULT']['BASKET']))
				{
					foreach ($this->forwardBasketTable as $basketCode => $basketId)
					{
						if (empty($discount['RESULT']['BASKET'][$basketCode]))
							continue;
						$applyData = $discount['RESULT']['BASKET'][$basketCode];
						$rulesList[$ruleIndex] = array(
							'MODULE_ID' => $this->discountsCache[$orderDiscountId]['MODULE_ID'],
							'ORDER_DISCOUNT_ID' => $orderDiscountId,
							'ORDER_ID' => $orderId,
							'ENTITY_TYPE' => Internals\OrderRulesTable::ENTITY_TYPE_BASKET,
							'ENTITY_ID' => $basketId,
							'ENTITY_VALUE' => $basketId,
							'COUPON_ID' => ($discount['COUPON_ID'] != '' ? $this->couponsCache[$discount['COUPON_ID']]['ID'] : 0),
							'APPLY' => $applyData['APPLY'],
							'APPLY_BLOCK_COUNTER' => $this->discountResultCounter,
							'ACTION_BLOCK_LIST' => $applyData['ACTION_BLOCK_LIST']
						);
						$ruleDescr[$ruleIndex] = array(
							'MODULE_ID' => $this->discountsCache[$orderDiscountId]['MODULE_ID'],
							'ORDER_DISCOUNT_ID' => $orderDiscountId,
							'ORDER_ID' => $orderId,
							'DESCR' => $applyData['DESCR_DATA']
						);
						$ruleIndex++;
						unset($applyData);
					}
					unset($basketCode, $basketId);
				}
				if (!empty($discount['RESULT']['DELIVERY']))
				{
					$rulesList[$ruleIndex] = array(
						'MODULE_ID' => $this->discountsCache[$orderDiscountId]['MODULE_ID'],
						'ORDER_DISCOUNT_ID' => $orderDiscountId,
						'ORDER_ID' => $orderId,
						'ENTITY_TYPE' => Internals\OrderRulesTable::ENTITY_TYPE_DELIVERY,
						'ENTITY_ID' => (int)$discount['RESULT']['DELIVERY']['DELIVERY_ID'],
						'ENTITY_VALUE' => (string)$discount['RESULT']['DELIVERY']['DELIVERY_ID'],
						'COUPON_ID' => ($discount['COUPON_ID'] != '' ? $this->couponsCache[$discount['COUPON_ID']]['ID'] : 0),
						'APPLY' => $discount['RESULT']['DELIVERY']['APPLY'],
						'APPLY_BLOCK_COUNTER' => $this->discountResultCounter
					);
					$ruleDescr[$ruleIndex] = array(
						'MODULE_ID' => $this->discountsCache[$orderDiscountId]['MODULE_ID'],
						'ORDER_DISCOUNT_ID' => $orderDiscountId,
						'ORDER_ID' => $orderId,
						'DESCR' => $discount['RESULT']['DELIVERY']['DESCR_DATA']
					);
					$ruleIndex++;
				}
			}
			unset($discount);
		}

		if (!empty($applyBlock['BASKET_ROUND']))
		{
			foreach ($applyBlock['BASKET_ROUND'] as $basketCode => $roundData)
			{
				$basketId = $this->forwardBasketTable[$basketCode];
				$roundList[$roundIndex] = array(
					'ORDER_ID' => $orderId,
					'APPLY_BLOCK_COUNTER' => $this->discountResultCounter,
					'ORDER_ROUND' => 'N',
					'ENTITY_TYPE' => Internals\OrderRoundTable::ENTITY_TYPE_BASKET,
					'ENTITY_ID' => $basketId,
					'ENTITY_VALUE' => $basketId,
					'APPLY' => $roundData['APPLY'],
					'ROUND_RULE' => $roundData['ROUND_RULE']
				);
				$roundIndex++;
				unset($basketId);
			}
			unset($roundData, $basketCode);
		}

		unset($applyBlock);

		if (!empty($rulesList))
		{
			foreach ($rulesList as $index => $ruleRow)
			{
				$ruleResult = Internals\OrderRulesTable::add($ruleRow);
				if ($ruleResult->isSuccess())
				{
					$ruleDescr[$index]['RULE_ID'] = $ruleResult->getId();
					$descrResult = Internals\OrderRulesDescrTable::add($ruleDescr[$index]);
					if (!$descrResult->isSuccess())
					{
						$process = false;
						$result->addError(new Main\Entity\EntityError(
							Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
							self::ERROR_ID
						));
						unset($descrResult);
						break;
					}
					unset($descrResult);
				}
				else
				{
					$process = false;
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
					unset($ruleResult);
					break;
				}
				unset($ruleResult);
			}
			unset($ruleRow);
		}

		if (!empty($roundList))
		{
			foreach ($roundList as $roundRow)
			{
				$roundResult = Internals\OrderRoundTable::add($roundRow);
				if (!$roundResult->isSuccess())
				{
					$process = false;
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
					unset($roundResult);
					break;
				}
				unset($roundResult);
			}
			unset($roundRow);
		}

		unset($process);

		return $result;
	}

	/**
	 * Check duscount conditions.
	 *
	 * @return bool
	 */
	protected function checkDiscountConditions()
	{
		if (
			!isset($this->currentStep['cacheIndex'])
			|| !isset($this->saleDiscountCache[$this->saleDiscountCacheKey][$this->currentStep['cacheIndex']])
		)
			return false;

		$key = 'UNPACK';
		$executeKey = $key.'_EXECUTE';

		if ($this->enableCheckingPrediction && !empty($this->saleDiscountCache[$this->saleDiscountCacheKey][$this->currentStep['cacheIndex']]['PREDICTIONS_APP']))
		{
			$key = 'PREDICTIONS_APP';
			$executeKey = $key.'_EXECUTE';
		}

		if (empty($this->saleDiscountCache[$this->saleDiscountCacheKey][$this->currentStep['cacheIndex']][$key]))
			return false;

		$discountLink = &$this->saleDiscountCache[$this->saleDiscountCacheKey][$this->currentStep['cacheIndex']];

		if (!array_key_exists($executeKey, $discountLink))
		{
			$checkOrder = null;

			$evalCode = '$checkOrder='.$discountLink[$key].';';
			if (version_compare(PHP_VERSION, '7.0.0', '>='))
			{
				try
				{
					eval($evalCode);
				}
				catch (\ParseError $e)
				{
					$this->showAdminError();
				}
			}
			else
			{
				eval($evalCode);
			}
			unset($evalCode);

			if (!is_callable($checkOrder))
				return false;
			$result = $checkOrder($this->orderData);
			unset($checkOrder);
		}
		else
		{
			if (!is_callable($discountLink[$executeKey]))
				return false;

			$result = $discountLink[$executeKey]($this->orderData);
		}
		unset($discountLink);
		return $result;
	}

	/**
	 * Apply discount rules.
	 *
	 * @return Result
	 */
	protected function applySaleDiscount()
	{
		$result = new Result;

		Discount\Actions::clearApplyCounter();

		$discount = (
			isset($this->currentStep['discountIndex'])
			? $this->discountsCache[$this->currentStep['discountId']]
			: $this->currentStep['discount']
		);
		if (isset($this->currentStep['discountIndex']))
		{
			if (!empty($discount['APPLICATION']) && !$this->loadDiscountModules($this->currentStep['discountId']))
			{
				$discount['APPLICATION'] = null;
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SALE_DISCOUNT_MODULES_ABSENT'),
					self::ERROR_ID
				));
			}
		}

		if (!empty($discount['APPLICATION']))
		{
			if (!array_key_exists('APPLICATION_EXECUTE', $discount))
			{
				$discount['APPLICATION_EXECUTE'] = null;

				$evalCode = '$discount["APPLICATION_EXECUTE"]='.$discount['APPLICATION'].';';
				if (version_compare(PHP_VERSION, '7.0.0', '>='))
				{
					try
					{
						eval($evalCode);
					}
					catch (\ParseError $e)
					{
						$this->showAdminError();
					}
				}
				else
				{
					eval($evalCode);
				}
				unset($evalCode);
			}
			if (is_callable($discount['APPLICATION_EXECUTE']))
			{
				$currentUseMode = $this->getUseMode();
				$this->currentStep['oldData'] = $this->orderData;
				if (
					$currentUseMode == self::USE_MODE_APPLY
					|| $currentUseMode == self::USE_MODE_MIXED
				)
				{
					$discountStoredActionData = $this->getDiscountStoredActionData($this->currentStep['discountId']);
					if (!empty($discountStoredActionData) && is_array($discountStoredActionData))
						Discount\Actions::setStoredData($discountStoredActionData);
					unset($discountStoredActionData);
				}
				$discount['APPLICATION_EXECUTE']($this->orderData);
				switch ($currentUseMode)
				{
					case self::USE_MODE_COUPONS:
					case self::USE_MODE_FULL:
						$actionsResult = $this->calculateFullSaleDiscountResult();
						break;
					case self::USE_MODE_APPLY:
					case self::USE_MODE_MIXED:
						$actionsResult = $this->calculateApplySaleDiscountResult();
						break;
					default:
						$actionsResult = new Result;

				}
				if (!$actionsResult->isSuccess())
					$result->addErrors($actionsResult->getErrors());
				unset($actionsResult);
				unset($currentUseMode);
			}
		}
		unset($discount);
		Discount\Actions::clearAction();

		return $result;
	}

	/**
	 * Apply basket discount in new order.
	 *
	 * @return Result
	 */
	protected function calculateFullBasketDiscount()
	{
		$result = new Result;

		if ((string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y')
			return $result;
		if (empty($this->basketDiscountList))
			return $result;

		$applyExist = $this->isBasketApplyResultExist();

		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET'];

		foreach ($this->getBasketCodes(true) as $basketCode)
		{
			if ($this->isOrderNew() && array_key_exists($basketCode, $applyBlock))
				unset($applyBlock[$basketCode]);
			if (empty($this->basketDiscountList[$basketCode]))
				continue;

			$itemData = array(
				'MODULE_ID' => $this->orderData['BASKET_ITEMS'][$basketCode]['MODULE'],
				'PRODUCT_ID' => $this->orderData['BASKET_ITEMS'][$basketCode]['PRODUCT_ID'],
				'BASKET_ID' => $basketCode
			);
			foreach ($this->basketDiscountList[$basketCode] as $index => $discount)
			{
				$discountResult = $this->convertDiscount($discount);
				if (!$discountResult->isSuccess())
				{
					$result->addErrors($discountResult->getErrors());
					unset($discountResult);
					return $result;
				}
				$orderDiscountId = $discountResult->getId();
				$discountData = $discountResult->getData();
				$orderCouponId = '';
				$this->basketDiscountList[$basketCode][$index]['ORDER_DISCOUNT_ID'] = $orderDiscountId;
				if ($discountData['USE_COUPONS'] == 'Y')
				{
					if (empty($discount['COUPON']))
					{
						$result->addError(new Main\Entity\EntityError(
							Loc::getMessage('BX_SALE_DISCOUNT_ERR_DISCOUNT_WITHOUT_COUPON'),
							self::ERROR_ID
						));
						return $result;
					}
					$couponResult = $this->convertCoupon($discount['COUPON'], $orderDiscountId);
					if (!$couponResult->isSuccess())
					{
						$result->addErrors($couponResult->getErrors());
						unset($couponResult);
						return $result;
					}
					$orderCouponId = $couponResult->getId();

					DiscountCouponsManager::setApplyByProduct($itemData, array($orderCouponId));
					unset($couponResult);
				}
				unset($discountData, $discountResult);
				if (!isset($applyBlock[$basketCode]))
					$applyBlock[$basketCode] = array();
				$applyBlock[$basketCode][$index] = array(
					'DISCOUNT_ID' => $orderDiscountId,
					'COUPON_ID' => $orderCouponId,
					'RESULT' => array(
						'APPLY' => 'Y',
						'DESCR' => false,
						'DESCR_DATA' => false
					)
				);

				$currentProduct = $this->orderData['BASKET_ITEMS'][$basketCode];
				$orderApplication = (
					!empty($this->discountsCache[$orderDiscountId]['APPLICATION'])
					? $this->discountsCache[$orderDiscountId]['APPLICATION']
					: null
				);
				if (!empty($orderApplication))
				{
					$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT'] = (
						!empty($this->discountsCache[$orderDiscountId]['ACTIONS_DESCR_DATA'])
						? $this->discountsCache[$orderDiscountId]['ACTIONS_DESCR_DATA']
						: false
					);

					$applyProduct = null;
					eval('$applyProduct='.$orderApplication.';');
					if (is_callable($applyProduct))
						$applyProduct($this->orderData['BASKET_ITEMS'][$basketCode]);
					unset($applyProduct);

					if (!empty($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']))
					{
						$applyBlock[$basketCode][$index]['RESULT']['DESCR_DATA'] = $this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']['BASKET'];
						$applyBlock[$basketCode][$index]['RESULT']['DESCR'] = self::formatDescription($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
					}
					unset($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
				}
				unset($orderApplication);

				if ($applyExist && !$this->getStatusApplyBasketDiscount($basketCode, $orderDiscountId, $orderCouponId))
				{
					$this->orderData['BASKET_ITEMS'][$basketCode] = $currentProduct;
					$applyBlock[$basketCode][$index]['RESULT']['APPLY'] = 'N';
				}
				unset($disable, $currentProduct);
			}
			unset($discount, $index);
		}
		unset($basketCode);

		unset($applyBlock);

		return $result;
	}

	/**
	 * Apply basket discount in exist order.
	 *
	 * @return Result
	 */
	protected function calculateApplyBasketDiscount()
	{
		$result = new Result;

		if ($this->useOnlySaleDiscounts())
			return $result;
		if (empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET']))
			return $result;

		$applyExist = $this->isBasketApplyResultExist();

		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET'];

		foreach ($this->getBasketCodes(false) as $basketCode)
		{
			if ($this->isCustomPriceByCode($basketCode))
			{
				if (isset($applyBlock[$basketCode]))
					unset($applyBlock[$basketCode]);
				continue;
			}
			if (empty($applyBlock[$basketCode]))
				continue;

			foreach ($applyBlock[$basketCode] as $index => $discount)
			{
				$currentProduct = $this->orderData['BASKET_ITEMS'][$basketCode];
				$orderDiscountId = $discount['DISCOUNT_ID'];
				$orderCouponId = $discount['COUPON_ID'];

				$orderApplication = (
					!empty($this->discountsCache[$orderDiscountId]['APPLICATION'])
					? $this->discountsCache[$orderDiscountId]['APPLICATION']
					: null
				);
				if (!empty($orderApplication) && !$this->loadDiscountModules($orderDiscountId))
					$orderApplication = null;

				if (!empty($orderApplication))
				{
					$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT'] = (
						!empty($this->discountsCache[$orderDiscountId]['ACTIONS_DESCR_DATA'])
						? $this->discountsCache[$orderDiscountId]['ACTIONS_DESCR_DATA']
						: false
					);

					$applyProduct = null;
					eval('$applyProduct='.$orderApplication.';');
					if (is_callable($applyProduct))
						$applyProduct($this->orderData['BASKET_ITEMS'][$basketCode]);
					unset($applyProduct);

					if (!empty($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']))
					{
						$applyBlock[$basketCode][$index]['RESULT']['DESCR_DATA'] = $this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT'];
						$applyBlock[$basketCode][$index]['RESULT']['DESCR'] = self::formatDescription($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
					}
					unset($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
				}
				unset($orderApplication);

				$disable = ($applyBlock[$basketCode][$index]['RESULT']['APPLY'] == 'N');
				if ($applyExist)
				{
					$applyDisable = !$this->getStatusApplyBasketDiscount($basketCode, $orderDiscountId, $orderCouponId);
					if ($applyDisable != $disable)
						$disable = $applyDisable;
					unset($applyDisable);
				}
				if ($disable)
				{
					$this->orderData['BASKET_ITEMS'][$basketCode] = $currentProduct;
					$applyBlock[$basketCode][$index]['RESULT']['APPLY'] = 'N';
				}
				else
				{
					$applyBlock[$basketCode][$index]['RESULT']['APPLY'] = 'Y';
				}
				unset($disable, $currentProduct);

			}
			unset($index, $discount);
		}
		unset($basketCode);

		unset($applyBlock);

		return $result;
	}

	/**
	 * Calculate discount block for existing order.
	 *
	 * @return Result
	 */
	protected function calculateApplyDiscountBlock()
	{
		$result = new Result;

		$basketDiscountResult = $this->calculateApplyBasketDiscount();
		if (!$basketDiscountResult->isSuccess())
		{
			$result->addErrors($basketDiscountResult->getErrors());
			unset($basketDiscountResult);

			return $result;
		}
		unset($basketDiscountResult);

		$roundApply = false;
		if ($this->isRoundMode(self::ROUND_MODE_BASKET_DISCOUNT))
		{
			$roundApply = true;
			$this->roundApplyBasketPrices();
		}

		if ($this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT) && !$roundApply)
		{
			$this->roundApplyBasketPricesByIndex(array(
				'DISCOUNT_INDEX' => -1,
				'DISCOUNT_ID' => 0
			));
		}
		if (!empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER']))
		{
			$index = -1;
			foreach ($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'] as $indexDiscount => $discount)
			{
				$index++;
				$orderDiscountId = $discount['DISCOUNT_ID'];
				if (!isset($this->discountsCache[$orderDiscountId]))
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_APPLY_WITHOUT_SALE_DISCOUNT'),
						self::ERROR_ID
					));

					return $result;
				}
				Discount\Actions::clearAction();
				if (!empty($discount['RESULT']['BASKET']))
				{
					if ($discount['ACTION_BLOCK_LIST'])
					{
						$applyResultMode = Discount\Actions::APPLY_RESULT_MODE_COUNTER;
						$blockList = array();
						foreach ($discount['RESULT']['BASKET'] as $basketCode => $basketItem)
							$blockList[$basketCode] = $basketItem['ACTION_BLOCK_LIST'];
						unset($basketCode, $basketItem);
					}
					else
					{
						if ($this->discountsCache[$orderDiscountId]['SIMPLE_ACTION'])
						{
							$applyResultMode = Discount\Actions::APPLY_RESULT_MODE_SIMPLE;
							$blockList = array_fill_keys(array_keys($discount['RESULT']['BASKET']), true);
						}
						else
						{
							$applyResultMode = Discount\Actions::APPLY_RESULT_MODE_DESCR;
							$blockList = array();
							foreach ($discount['RESULT']['BASKET'] as $basketCode => $basketItem)
								$blockList[$basketCode] = $basketItem['DESCR_DATA'];
							unset($basketCode, $basketItem);
						}
					}
					Discount\Actions::setApplyResultMode($applyResultMode);
					Discount\Actions::setApplyResult(array('BASKET' => $blockList));
					unset($blockList, $applyResultMode);
				}
				if ($this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT) && !$roundApply)
				{
					$this->roundApplyBasketPricesByIndex(array(
						'DISCOUNT_INDEX' => $index,
						'DISCOUNT_ID' => $orderDiscountId
					));
				}
				$this->fillCurrentStep(array(
					'discountIndex' => $indexDiscount,
					'discountId' => $orderDiscountId,
				));
				$actionsResult = $this->applySaleDiscount();
				if (!$actionsResult->isSuccess())
				{
					$result->addErrors($actionsResult->getErrors());
					unset($actionsResult);

					return $result;
				}
				unset($orderDiscountId);
			}
			if ($this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT) && !$roundApply)
			{
				$index++;
				$this->roundApplyBasketPricesByIndex(array(
					'DISCOUNT_INDEX' => $index,
					'DISCOUNT_ID' => 0
				));
				$roundConfig = $this->getRoundIndex('BASKET_ROUND');
				if (is_array($roundConfig))
				{
					if ($roundConfig['DISCOUNT_INDEX'] > $index && $roundConfig['DISCOUNT_ID'] == 0)
					{
						$this->roundApplyBasketPricesByIndex(array(
							'DISCOUNT_INDEX' => $roundConfig['DISCOUNT_INDEX'],
							'DISCOUNT_ID' => 0
						));
					}
				}
				unset($roundConfig);
			}
			unset($discount, $indexDiscount, $currentList);
		}

		if ($this->isRoundMode(self::ROUND_MODE_FINAL_PRICE))
			$this->roundApplyBasketPrices();

		$this->fillEmptyCurrentStep();

		return $result;
	}

	/**
	 * Applyed additional coupons.
	 *
	 * @return Result
	 */
	protected function calculateApplyAdditionalCoupons()
	{
		$result = new Result;

		$useMode = $this->getUseMode();
		if ($useMode != self::USE_MODE_APPLY && $useMode != self::USE_MODE_MIXED)
			return $result;

		if (!$this->useOnlySaleDiscounts())
		{
			$couponList = $this->getAdditionalCoupons(array('!MODULE_ID' => 'sale'));
			if (!empty($couponList))
			{
				$params = array(
					'USE_BASE_PRICE' => $this->saleOptions['USE_BASE_PRICE'],
					'USER_ID' => $this->orderData['USER_ID'],
					'SITE_ID' => $this->orderData['SITE_ID']
				);
				$couponsByModule = array();
				foreach ($couponList as &$coupon)
				{
					if (!isset($couponsByModule[$coupon['MODULE_ID']]))
						$couponsByModule[$coupon['MODULE_ID']] = array();
					$couponsByModule[$coupon['MODULE_ID']][] = array(
						'DISCOUNT_ID' => $coupon['DISCOUNT_ID'],
						'COUPON' => $coupon['COUPON']
					);
				}
				unset($coupon);
				if (!empty($couponsByModule))
				{
					foreach ($couponsByModule as $moduleId => $moduleCoupons)
					{
						if ($useMode == self::USE_MODE_APPLY)
						{
							$currentBasket = $this->orderData['BASKET_ITEMS'];
						}
						else
						{
							$currentBasket = array();
							$basketCodeList = $this->getBasketCodes(false);
							foreach ($basketCodeList as $basketCode)
								$currentBasket[$basketCode] = $this->orderData['BASKET_ITEMS'][$basketCode];
							unset($basketCode, $basketCodeList);
						}
						if (empty($currentBasket))
							continue;
						$couponsApply = OrderDiscountManager::calculateApplyCoupons($moduleId, $moduleCoupons, $currentBasket, $params);
						unset($currentBasket);
						if (!empty($couponsApply))
						{
							$couponsApplyResult = $this->calculateApplyBasketAdditionalCoupons($couponsApply);
							if (!$couponsApplyResult->isSuccess())
								$result->addErrors($couponsApplyResult->getErrors());
							unset($couponsApplyResult);
						}
						unset($couponsApply);
					}
					unset($moduleId, $moduleCoupons);
				}
				unset($couponsByModule, $params);
			}
			unset($couponList);
		}

		$couponList = $this->getAdditionalCoupons(array('MODULE_ID' => 'sale'));
		if (!empty($couponList))
		{
			$couponsApplyResult = $this->calculateApplySaleAdditionalCoupons($couponList);
			if (!$couponsApplyResult->isSuccess())
				$result->addErrors($couponsApplyResult->getErrors());
			unset($couponsApplyResult);
		}
		unset($couponList);

		return $result;
	}

	/**
	 * Calculate step discount result by new order.
	 *
	 * @return Result
	 */
	protected function calculateFullSaleDiscountResult()
	{
		$result = new Result;

		$this->orderData['DISCOUNT_RESULT'] = Discount\Actions::getActionResult();
		$this->orderData['DISCOUNT_DESCR'] = Discount\Actions::getActionDescription();
		if (!empty($this->orderData['DISCOUNT_RESULT']) && is_array($this->orderData['DISCOUNT_RESULT']))
		{
			$stepResult = self::getStepResult($this->orderData);
		}
		else
		{
			$stepResult = self::getStepResultOld($this->orderData, $this->currentStep['oldData']);
			if (!empty($stepResult))
			{
				if (empty($this->orderData['DISCOUNT_DESCR']) || !is_array($this->orderData['DISCOUNT_DESCR']))
					$this->orderData['DISCOUNT_DESCR'] = $this->getSimpleActionDescription($stepResult);
			}
		}

		Discount\Actions::fillCompatibleFields($this->orderData);
		$applied = !empty($stepResult);

		$orderDiscountId = 0;
		$orderCouponId = '';

		if ($applied)
		{
			$this->correctStepResult($stepResult, $this->currentStep['discount']);

			$this->currentStep['discount']['ACTIONS_DESCR'] = $this->orderData['DISCOUNT_DESCR'];
			$discountResult = $this->convertDiscount($this->currentStep['discount']);
			if (!$discountResult->isSuccess())
			{
				$result->addErrors($discountResult->getErrors());
				return $result;
			}
			$orderDiscountId = $discountResult->getId();
			$discountData = $discountResult->getData();

			$this->currentStep['discount']['ORDER_DISCOUNT_ID'] = $orderDiscountId;

			if ($discountData['USE_COUPONS'] == 'Y')
			{
				if (empty($this->currentStep['discount']['COUPON']))
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_DISCOUNT_WITHOUT_COUPON'),
						self::ERROR_ID
					));

					return $result;
				}
				$couponResult = $this->convertCoupon($this->currentStep['discount']['COUPON']['COUPON'], $orderDiscountId);
				if (!$couponResult->isSuccess())
				{
					$result->addErrors($couponResult->getErrors());
					unset($couponResult);

					return $result;
				}
				$orderCouponId = $couponResult->getId();
				DiscountCouponsManager::setApply($orderCouponId, $stepResult);
				unset($couponResult);
			}
			$this->setDiscountStoredActionData($orderDiscountId, Discount\Actions::getStoredData());
		}
		unset($this->orderData['DISCOUNT_DESCR'], $this->orderData['DISCOUNT_RESULT']);

		if ($applied)
		{
			if (
				(
					!empty($this->applyResult['DISCOUNT_LIST'][$orderDiscountId])
					&& $this->applyResult['DISCOUNT_LIST'][$orderDiscountId] == 'N'
				)
				||
				(
					$orderCouponId != ''
					&& !empty($this->applyResult['COUPON_LIST'][$orderCouponId])
					&& $this->applyResult['COUPON_LIST'][$orderCouponId] == 'N'
				)
			)
			{
				$this->orderData = $this->currentStep['oldData'];
				if (!empty($stepResult['BASKET']))
				{
					foreach ($stepResult['BASKET'] as &$basketItem)
						$basketItem['APPLY'] = 'N';
					unset($basketItem);
				}
				if (!empty($stepResult['DELIVERY']))
					$stepResult['DELIVERY']['APPLY'] = 'N';
			}
			else
			{
				if (!empty($this->applyResult['BASKET']) && is_array($this->applyResult['BASKET']))
				{
					foreach ($this->applyResult['BASKET'] as $basketCode => $discountList)
					{
						if (
							is_array($discountList) && !empty($discountList[$orderDiscountId]) && $discountList[$orderDiscountId] == 'N'
						)
						{
							if (empty($stepResult['BASKET'][$basketCode]))
								continue;
							$stepResult['BASKET'][$basketCode]['APPLY'] = 'N';
							$this->orderData['BASKET_ITEMS'][$basketCode] = $this->currentStep['oldData']['BASKET_ITEMS'][$basketCode];
						}
					}
					unset($basketCode, $discountList);
				}
				if (!empty($this->applyResult['DELIVERY']))
				{
					if (
						is_array($this->applyResult['DELIVERY']) && !empty($this->applyResult['DELIVERY'][$orderDiscountId]) && $this->applyResult['DELIVERY'][$orderDiscountId] == 'N'
					)
					{
						$this->orderData['PRICE_DELIVERY'] = $this->currentStep['oldData']['PRICE_DELIVERY'];
						$this->orderData['PRICE_DELIVERY_DIFF'] = $this->currentStep['oldData']['PRICE_DELIVERY_DIFF'];
						$stepResult['DELIVERY']['APPLY'] = 'N';
					}
				}
			}
		}

		if ($applied && $orderCouponId != '')
		{
			$couponApply = DiscountCouponsManager::setApply($this->couponsCache[$orderCouponId]['COUPON'], $stepResult);
			unset($couponApply);
		}

		if ($applied)
		{
			$this->tryToRevertApplyStatusInBlocks($stepResult);

			$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][] = array(
				'DISCOUNT_ID' => $orderDiscountId,
				'COUPON_ID' => $orderCouponId,
				'RESULT' => $stepResult
			);
			if ($this->currentStep['discount']['LAST_DISCOUNT'] == 'Y')
				$this->currentStep['stop'] = true;

			if ($this->currentStep['discount']['LAST_LEVEL_DISCOUNT'] == 'Y')
				$this->currentStep['stopLevel'] = true;
		}

		return $result;
	}

	/**
	 * Tries to revert apply status of discounts.
	 * It depends on current $stepResult. If it has REVERT_APPLY like true, that we have to cancel discounts on basket
	 * items which were affected.
	 * @param array $stepResult
	 * @return void
	 */
	protected function tryToRevertApplyStatusInBlocks(array $stepResult)
	{
		if (empty($stepResult['BASKET']))
		{
			return;
		}

		foreach ($stepResult['BASKET'] as $basketItemId => $item)
		{
			if ($item['APPLY'] !== 'Y')
			{
				continue;
			}

			if (empty($item['DESCR_DATA']))
			{
				continue;
			}

			foreach ($item['DESCR_DATA'] as $rowDescription)
			{
				if (
					!empty($rowDescription['REVERT_APPLY']) &&
					$rowDescription['VALUE_ACTION'] === OrderDiscountManager::DESCR_VALUE_ACTION_CUMULATIVE
				)
				{
					$this->revertApplyBlockForBasketItem($basketItemId);
				}
			}
		}
	}

	/**
	 * Reverts apply flag in blocks for basket items which has for example cumulative discount
	 * which cancels previous discounts on item.
	 * @param int $basketItemId
	 * @return void
	 */
	protected function revertApplyBlockForBasketItem($basketItemId)
	{
		if (empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]))
		{
			return;
		}

		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter];
		foreach ($applyBlock['ORDER'] as &$orderBlock)
		{
			foreach ($orderBlock['RESULT']['BASKET'] as $bid => &$basketItem)
			{
				if ($bid != $basketItemId)
				{
					continue;
				}

				$basketItem['APPLY'] = 'N';
			}
		}
	}

	/**
	 * Calculate step discount result by exist order.
	 *
	 * @return Result
	 */
	protected function calculateApplySaleDiscountResult()
	{
		$result = new Result;

		$this->orderData['DISCOUNT_RESULT'] = Discount\Actions::getActionResult();
		if (!empty($this->orderData['DISCOUNT_RESULT']) && is_array($this->orderData['DISCOUNT_RESULT']))
			$stepResult = self::getStepResult($this->orderData);
		else
			$stepResult = self::getStepResultOld($this->orderData, $this->currentStep['oldData']);
		$applied = !empty($stepResult);

		$orderDiscountId = 0;
		$orderCouponId = '';

		if ($applied)
		{
			$this->correctStepResult($stepResult, $this->discountsCache[$this->currentStep['discountId']]);

			$orderDiscountId = $this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$this->currentStep['discountIndex']]['DISCOUNT_ID'];
			$orderCouponId = $this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$this->currentStep['discountIndex']]['COUPON_ID'];
		}

		unset($this->orderData['DISCOUNT_RESULT']);

		if ($applied)
		{
			if (
				(
					!empty($this->applyResult['DISCOUNT_LIST'][$orderDiscountId])
					&& $this->applyResult['DISCOUNT_LIST'][$orderDiscountId] == 'N'
				)
				||
				(
					$orderCouponId != ''
					&& !empty($this->applyResult['COUPON_LIST'][$orderCouponId])
					&& $this->applyResult['COUPON_LIST'][$orderCouponId] == 'N'
				)
			)
			{
				$this->orderData = $this->currentStep['oldData'];
				if (!empty($stepResult['BASKET']))
				{
					foreach ($stepResult['BASKET'] as &$basketItem)
						$basketItem['APPLY'] = 'N';
					unset($basketItem);
				}
				if (!empty($stepResult['DELIVERY']))
					$stepResult['DELIVERY']['APPLY'] = 'N';
			}
			else
			{
				if (!empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$this->currentStep['discountIndex']]['RESULT']))
				{
					$existDiscountResult = $this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$this->currentStep['discountIndex']]['RESULT'];
					if (!empty($existDiscountResult['BASKET']))
					{
						$basketCodeList = $this->getBasketCodes(false);
						if (!empty($basketCodeList))
						{
							foreach ($basketCodeList as &$basketCode)
							{
								if ($this->isCustomPriceByCode($basketCode))
									continue;
								if (isset($existDiscountResult['BASKET'][$basketCode]))
								{
									$disable = ($existDiscountResult['BASKET'][$basketCode]['APPLY'] == 'N');
									if (isset($this->applyResult['BASKET'][$basketCode][$orderDiscountId]))
									{
										$applyDisable = ($this->applyResult['BASKET'][$basketCode][$orderDiscountId] == 'N');
										if ($disable != $applyDisable)
											$disable = $applyDisable;
										unset($applyDisable);
									}
									if ($disable)
									{
										$stepResult['BASKET'][$basketCode]['APPLY'] = 'N';
										$this->orderData['BASKET_ITEMS'][$basketCode] = $this->currentStep['oldData']['BASKET_ITEMS'][$basketCode];
									}
									else
									{
										$stepResult['BASKET'][$basketCode]['APPLY'] = 'Y';
									}
								}
							}
							unset($disable, $basketCode);
						}
					}
					if (!empty($existDiscountResult['DELIVERY']))
					{
						$disable = ($existDiscountResult['DELIVERY']['APPLY'] == 'N');
						if (!empty($this->applyResult['DELIVERY'][$orderDiscountId]))
						{
							$applyDisable = ($this->applyResult['DELIVERY'][$orderDiscountId] == 'N');
							if ($disable != $applyDisable)
								$disable = $applyDisable;
							unset($applyDisable);
						}
						if ($disable)
						{
							$this->orderData['PRICE_DELIVERY'] = $this->currentStep['oldData']['PRICE_DELIVERY'];
							$this->orderData['PRICE_DELIVERY_DIFF'] = $this->currentStep['oldData']['PRICE_DELIVERY_DIFF'];
							$stepResult['DELIVERY']['APPLY'] = 'N';
						}
						else
						{
							$stepResult['DELIVERY']['APPLY'] = 'Y';
						}
						unset($disable);
					}
				}
			}
		}

		if ($applied && $orderCouponId != '')
		{
			$couponApply = DiscountCouponsManager::setApply($this->couponsCache[$orderCouponId]['COUPON'], $stepResult);
			unset($couponApply);
		}

		if ($applied)
		{
			$this->mergeDiscountActionResult($this->currentStep['discountIndex'], $stepResult);
		}
		else
		{
			if (!empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$this->currentStep['discountIndex']]))
				$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$this->currentStep['discountIndex']]['RESULT'] = array();
		}

		return $result;
	}

	/* rounding tools */

	/**
	 * Return order round apply mode.
	 * @internal
	 *
	 * @return int
	 */
	protected function getRoundMode()
	{
		return $this->roundApplyMode;
	}

	/**
	 * Return true, if selected check round apply mode.
	 * @internal
	 *
	 * @param int $mode		Checked mode.
	 * @return bool
	 */
	protected function isRoundMode($mode)
	{
		return $this->roundApplyMode == $mode;
	}

	/**
	 * Load round apply config for exist order.
	 * @internal
	 *
	 * @return void
	 */
	protected function loadRoundConfig()
	{
		$defaultApplyMode = self::ROUND_MODE_FINAL_PRICE;
		$this->roundApplyMode = $defaultApplyMode;
		$this->roundApplyConfig = array();

		if ($this->isOrderExists() && !$this->isOrderNew() && $this->getUseMode() != self::USE_MODE_FULL)
		{
			$orderId = $this->getOrder()->getId();
			$data = Internals\OrderDiscountDataTable::getList(array(
				'select' => array('*'),
				'filter' => array(
					'=ORDER_ID' => $orderId,
					'=ENTITY_TYPE' => Internals\OrderDiscountDataTable::ENTITY_TYPE_ROUND,
					'=ENTITY_ID' => $orderId
				)
			))->fetch();
			if (empty($data))
				return;

			$entityData = $data['ENTITY_DATA'];
			unset($data, $orderId);

			if (
				is_array($entityData)
				&& isset($entityData['MODE'])
			)
			{
				$this->roundApplyMode = (int)$entityData['MODE'];
				if ($this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT))
					$this->roundApplyConfig = (isset($entityData['CONFIG']) ? $entityData['CONFIG'] : array());
			}
		}

		if (
			$this->roundApplyMode != self::ROUND_MODE_BASKET_DISCOUNT
			&& $this->roundApplyMode != self::ROUND_MODE_SALE_DISCOUNT
			&& $this->roundApplyMode != self::ROUND_MODE_FINAL_PRICE
		)
			$this->roundApplyMode = null;
		if (!is_array($this->roundApplyConfig))
			$this->roundApplyConfig = array();
		unset($defaultApplyMode);
	}

	/**
	 * Set discount index for use round. Only for sale discount mode.
	 * @internal
	 *
	 * @param string $entity		Entity id.
	 * @param array $index			Index data.
	 * @return void
	 */
	protected function setRoundIndex($entity, array $index)
	{
		if (!$this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT))
			return;
		if (!isset($index['DISCOUNT_INDEX']) || !isset($index['DISCOUNT_ID']))
			return;
		if (!isset($this->roundApplyConfig[$this->discountResultCounter]))
			$this->roundApplyConfig[$this->discountResultCounter] = array();
		$this->roundApplyConfig[$this->discountResultCounter][$entity] = $index;
	}

	/**
	 * Return index data for use round.
	 * @internal
	 *
	 * @param string $entity			Entity id.
	 * @param null|int $applyCounter	Apply block counter.
	 * @return null|array
	 */
	protected function getRoundIndex($entity, $applyCounter = null)
	{
		if (!$this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT))
			return null;
		if ($applyCounter === null)
			$applyCounter = $this->discountResultCounter;
		return (isset($this->roundApplyConfig[$applyCounter][$entity]) ? $this->roundApplyConfig[$applyCounter][$entity] : null);
	}

	/**
	 * Round prices.
	 *
	 * @return void
	 */
	protected function roundFullBasketPrices()
	{
		$basketCodeList = $this->getBasketCodes(true);
		if (!empty($basketCodeList))
		{
			$roundBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET_ROUND'];
			$orderData = $this->orderData;
			unset($orderData['BASKET_ITEMS']);
			$basket = array_intersect_key(
				$this->orderData['BASKET_ITEMS'],
				array_fill_keys($basketCodeList, true)
			);
			$result = OrderDiscountManager::roundBasket(
				$basket,
				array(),
				$orderData
			);
			foreach ($result as $basketCode => $roundResult)
			{
				if (empty($roundResult) || !is_array($roundResult))
					continue;
				$this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'] = $roundResult['PRICE'];
				$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_PRICE'] = $roundResult['DISCOUNT_PRICE'];

				$roundBlock[$basketCode] = array(
					'APPLY' => 'Y',
					'ROUND_RULE' => $roundResult['ROUND_RULE']
				);
			}
			unset($basketCode, $roundResult, $result);
			unset($basket, $orderData);
			unset($roundBlock);
		}
		unset($basketCodeList);
	}

	/**
	 * Round prices.
	 *
	 * @return void
	 */
	protected function roundApplyBasketPrices()
	{
		if (empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET_ROUND']))
			return;

		$basketCodeList = $this->getBasketCodes(false);
		if (!empty($basketCodeList))
		{
			$roundBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET_ROUND'];
			$basket = array();
			$roundData = array();
			foreach ($basketCodeList as $basketCode)
			{
				if (empty($roundBlock[$basketCode]))
					continue;
				if ($roundBlock[$basketCode]['APPLY'] != 'Y')
					continue;
				$basket[$basketCode] = $this->orderData['BASKET_ITEMS'][$basketCode];
				$roundData[$basketCode] = $roundBlock[$basketCode]['ROUND_RULE'];
			}
			unset($basketCode);

			if (!empty($basket))
			{
				$orderData = $this->orderData;
				unset($orderData['BASKET_ITEMS']);
				$result = OrderDiscountManager::roundBasket(
					$basket,
					$roundData,
					$orderData
				);
				foreach ($result as $basketCode => $roundResult)
				{
					if (empty($roundResult) || !is_array($roundResult))
						continue;
					$this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'] = $roundResult['PRICE'];
					$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_PRICE'] = $roundResult['DISCOUNT_PRICE'];
				}
				unset($basketCode, $roundResult, $result);
				unset($orderData);
			}
			unset($roundData, $basket);

			unset($roundBlock);
		}
		unset($basketCodeList);
	}

	/**
	 * Round prices in sale discount mode for new order.
	 * @internal
	 *
	 * @param array $index		Index data.
	 * @return void
	 */
	protected function roundFullBasketPriceByIndex(array $index)
	{
		if (!$this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT))
			return;
		if ($this->getUseMode() != self::USE_MODE_FULL)
			return;

		$this->roundFullBasketPrices();
		if (!empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET_ROUND']))
			$this->setRoundIndex('BASKET_ROUND', $index);
	}

	/**
	 * Round prices in sale discount mode for exist order.
	 * @internal
	 *
	 * @param array $index		Index data.
	 * @return void
	 */
	protected function roundApplyBasketPricesByIndex(array $index)
	{
		if (!isset($index['DISCOUNT_INDEX']))
			return;
		if (!$this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT))
			return;
		if ($this->getUseMode() != self::USE_MODE_APPLY && $this->getUseMode() != self::USE_MODE_MIXED)
			return;

		$roundConfig = $this->getRoundIndex('BASKET_ROUND');
		if ($roundConfig === null)
			return;
		if ($roundConfig['DISCOUNT_INDEX'] != $index['DISCOUNT_INDEX'])
			return;
		$this->roundApplyBasketPrices();
	}

	/* rounding tools finish */

	/**
	 * Convert discount for saving in order.
	 *
	 * @param array $discount			Raw discount data.
	 * @return Result
	 */
	protected function convertDiscount($discount)
	{
		$result = new Result;

		$discountResult = OrderDiscountManager::saveDiscount($discount, false);
		if (!$discountResult->isSuccess())
		{
			$result->addErrors($discountResult->getErrors());
			unset($discountResult);
			return $result;
		}
		$orderDiscountId = $discountResult->getId();
		$discountData = $discountResult->getData();
		$resultData = array(
			'ORDER_DISCOUNT_ID' => $orderDiscountId,
			'USE_COUPONS' => $discountData['USE_COUPONS'],
			'MODULE_ID' => $discountData['MODULE_ID'],
			'DISCOUNT_ID' => $discountData['DISCOUNT_ID']
		);
		if (!isset($this->discountsCache[$orderDiscountId]))
		{
			$discountData['ACTIONS_DESCR_DATA'] = false;
			if (!empty($discountData['ACTIONS_DESCR']) && is_array($discountData['ACTIONS_DESCR']))
			{
				$discountData['ACTIONS_DESCR_DATA'] = $discountData['ACTIONS_DESCR'];
				$discountData['ACTIONS_DESCR'] = self::formatDescription($discountData['ACTIONS_DESCR']);
			}
			else
			{
				$discountData['ACTIONS_DESCR'] = false;
			}
			if (empty($discountData['ACTIONS_DESCR']))
			{
				$discountData['ACTIONS_DESCR'] = false;
				$discountData['ACTIONS_DESCR_DATA'] = false;
			}
			$this->discountsCache[$orderDiscountId] = $discountData;
		}

		$result->setId($orderDiscountId);
		$result->setData($resultData);
		unset($discountData, $resultData, $orderDiscountId);

		return $result;
	}

	/**
	 * Convert coupon for saving in order.
	 *
	 * @param string|array $coupon			Coupon.
	 * @param int $discount					Order discount id.
	 * @return Result
	 */
	protected function convertCoupon($coupon, $discount)
	{
		$result = new Result;

		if (!is_array($coupon))
		{
			$couponData = DiscountCouponsManager::getEnteredCoupon($coupon, true);
			if (empty($couponData))
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_COUPON_NOT_FOUND'),
					self::ERROR_ID
				));
				return $result;
			}
			$coupon = array(
				'COUPON' => $couponData['COUPON'],
				'TYPE' => $couponData['TYPE'],
				'COUPON_ID' => $couponData['ID'],
				'DATA' => $couponData
			);
			unset($couponData);
		}
		$coupon['ORDER_DISCOUNT_ID'] = $discount;
		$coupon['ID'] = 0;

		$orderCouponId = $coupon['COUPON'];
		if (!isset($this->couponsCache[$orderCouponId]))
			$this->couponsCache[$orderCouponId] = $coupon;
		$result->setId($orderCouponId);
		$result->setData($coupon);
		unset($coupon, $orderCouponId);
		return $result;
	}

	/**
	 * Returns result after one discount.
	 *
	 * @param array $order			Order current data.
	 * @return array
	 */
	protected static function getStepResult($order)
	{
		$result = array();
		$stepResult = &$order['DISCOUNT_RESULT'];
		if (!empty($stepResult['DELIVERY']) && is_array($stepResult['DELIVERY']))
		{
			$result['DELIVERY'] = array(
				'APPLY' => 'Y',
				'DELIVERY_ID' => (isset($order['DELIVERY_ID']) ? $order['DELIVERY_ID'] : false),
				'SHIPMENT_CODE' => (isset($order['SHIPMENT_CODE']) ? $order['SHIPMENT_CODE'] : false),
				'DESCR' => OrderDiscountManager::formatArrayDescription($stepResult['DELIVERY']),
				'DESCR_DATA' => $stepResult['DELIVERY'],
				'ACTION_BLOCK_LIST' => array_keys($stepResult['DELIVERY'])
			);
			if (is_array($result['DELIVERY']['DESCR']))
				$result['DELIVERY']['DESCR'] = implode(', ', $result['DELIVERY']['DESCR']);
		}
		if (!empty($stepResult['BASKET']) && is_array($stepResult['BASKET']))
		{
			if (!isset($result['BASKET']))
				$result['BASKET'] = array();
			foreach ($stepResult['BASKET'] as $basketCode => $basketResult)
			{
				$result['BASKET'][$basketCode] = array(
					'APPLY' => 'Y',
					'DESCR' => OrderDiscountManager::formatArrayDescription($basketResult),
					'DESCR_DATA' => $basketResult,
					'MODULE' => $order['BASKET_ITEMS'][$basketCode]['MODULE'],
					'PRODUCT_ID' => $order['BASKET_ITEMS'][$basketCode]['PRODUCT_ID'],
					'BASKET_ID' => (isset($order['BASKET_ITEMS'][$basketCode]['ID']) ? $order['BASKET_ITEMS'][$basketCode]['ID'] : $basketCode),
					'ACTION_BLOCK_LIST' => array_keys($basketResult)
				);
				if (is_array($result['BASKET'][$basketCode]['DESCR']))
					$result['BASKET'][$basketCode]['DESCR'] = implode(', ', $result['BASKET'][$basketCode]['DESCR']);
			}
			unset($basketCode, $basketResult);
		}
		unset($stepResult);

		return $result;
	}

	/**
	 * Returns result after one discount in old format.
	 *
	 * @param array $currentOrder			Current order data.
	 * @param array $oldOrder				Old order data.
	 * @return array
	 */
	protected static function getStepResultOld($currentOrder, $oldOrder)
	{
		$result = array();
		if (isset($oldOrder['PRICE_DELIVERY']) && isset($currentOrder['PRICE_DELIVERY']))
		{
			if ($oldOrder['PRICE_DELIVERY'] != $currentOrder['PRICE_DELIVERY'])
			{
				$descr = OrderDiscountManager::createSimpleDescription($currentOrder['PRICE_DELIVERY'], $oldOrder['PRICE_DELIVERY'], $oldOrder['CURRENCY']);
				$result['DELIVERY'] = array(
					'APPLY' => 'Y',
					'DELIVERY_ID' => (isset($currentOrder['DELIVERY_ID']) ? $currentOrder['DELIVERY_ID'] : false),
					'SHIPMENT_CODE' => (isset($currentOrder['SHIPMENT_CODE']) ? $currentOrder['SHIPMENT_CODE'] : false),
					'DESCR' => OrderDiscountManager::formatArrayDescription($descr),
					'DESCR_DATA' => $descr
				);
				unset($descr);
				if (is_array($result['DELIVERY']['DESCR']))
					$result['DELIVERY']['DESCR'] = implode(', ', $result['DELIVERY']['DESCR']);
			}
		}
		if (!empty($oldOrder['BASKET_ITEMS']) && !empty($currentOrder['BASKET_ITEMS']))
		{
			foreach ($oldOrder['BASKET_ITEMS'] as $basketCode => $item)
			{
				if (!isset($currentOrder['BASKET_ITEMS'][$basketCode]))
					continue;
				if ($item['PRICE'] != $currentOrder['BASKET_ITEMS'][$basketCode]['PRICE'])
				{
					if (!isset($result['BASKET']))
						$result['BASKET'] = array();
					$descr = OrderDiscountManager::createSimpleDescription($currentOrder['BASKET_ITEMS'][$basketCode]['PRICE'], $item['PRICE'], $oldOrder['CURRENCY']);
					$result['BASKET'][$basketCode] = array(
						'APPLY' => 'Y',
						'DESCR' => OrderDiscountManager::formatArrayDescription($descr),
						'DESCR_DATA' => $descr,
						'MODULE' => $currentOrder['BASKET_ITEMS'][$basketCode]['MODULE'],
						'PRODUCT_ID' => $currentOrder['BASKET_ITEMS'][$basketCode]['PRODUCT_ID'],
						'BASKET_ID' => (isset($currentOrder['BASKET_ITEMS'][$basketCode]['ID']) ? $currentOrder['BASKET_ITEMS'][$basketCode]['ID'] : $basketCode)
					);
					unset($descr);
					if (is_array($result['BASKET'][$basketCode]['DESCR']))
						$result['BASKET'][$basketCode]['DESCR'] = implode(', ', $result['BASKET'][$basketCode]['DESCR']);
				}
			}
		}
		return $result;
	}

	/**
	 * Correct data for exotic coupon.
	 *
	 * @param array &$stepResult			Currenct discount result.
	 * @param array $discount				Discount data.
	 * @return void
	 */
	protected function correctStepResult(&$stepResult, $discount)
	{
		if ($discount['USE_COUPONS'] == 'Y' && !empty($discount['COUPON']))
		{
			if (
				$discount['COUPON']['TYPE'] == Internals\DiscountCouponTable::TYPE_BASKET_ROW &&
				(!empty($stepResult['BASKET']) && count($stepResult['BASKET']) > 1)
			)
			{
				$maxPrice = 0;
				$maxKey = -1;
				$basketKeys = array();
				foreach ($stepResult['BASKET'] as $key => $row)
				{
					$basketKeys[$key] = $key;
					if ($maxPrice < $this->currentStep['oldData']['BASKET_ITEMS'][$key]['PRICE'])
					{
						$maxPrice = $this->currentStep['oldData']['BASKET_ITEMS'][$key]['PRICE'];
						$maxKey = $key;
					}
				}
				unset($basketKeys[$maxKey]);
				foreach ($basketKeys as $key => $row)
				{
					unset($stepResult['BASKET'][$key]);
					$this->orderData['BASKET_ITEMS'][$row] = $this->currentStep['oldData']['BASKET_ITEMS'][$row];
				}
				unset($row, $key);
			}
		}
	}

	/* discount action reference tools */

	/**
	 * Fill additional discount data.
	 *
	 * @param int $orderDiscountId	Converted discount id.
	 * @param array $data Discount	data.
	 *
	 * @return void
	 */
	protected function setDiscountStoredActionData($orderDiscountId, array $data)
	{
		$orderDiscountId = (int)$orderDiscountId;
		if (!isset($this->discountsCache[$orderDiscountId]))
			return;
		if (empty($data))
			return;
		if (!isset($this->discountStoredActionData[$this->discountResultCounter]))
			$this->discountStoredActionData[$this->discountResultCounter] = array();
		$this->discountStoredActionData[$this->discountResultCounter][$orderDiscountId] = $data;
	}

	/**
	 * Returns stored action data for discount.
	 *
	 * @param int $orderDiscountId Converted discount id.
	 * @return array|null
	 */
	protected function getDiscountStoredActionData($orderDiscountId)
	{
		$orderDiscountId = (int)$orderDiscountId;
		if (isset($this->discountStoredActionData[$this->discountResultCounter][$orderDiscountId]))
			return $this->discountStoredActionData[$this->discountResultCounter][$orderDiscountId];
		return null;
	}

	/* discount action reference tools finish */

	/**
	 * Return true, if exist apply result from form for basket.
	 *
	 * @return bool
	 */
	protected function isBasketApplyResultExist()
	{
		return (
			!empty($this->applyResult['DISCOUNT_LIST'])
			|| !empty($this->applyResult['COUPON_LIST'])
			|| !empty($this->applyResult['BASKET'])
		);
	}

	/**
	 * Returns discount and coupon list.
	 *
	 * @return void
	 */
	protected function getApplyDiscounts()
	{
		$discountApply = array();
		$couponApply = array();

		if (!empty($this->discountsCache))
		{
			foreach ($this->discountsCache as $id => $discount)
			{
				$this->discountResult['DISCOUNT_LIST'][$id] = array(
					'ID' => $id,
					'NAME' => $discount['NAME'],
					'MODULE_ID' => $discount['MODULE_ID'],
					'DISCOUNT_ID' => $discount['ID'],
					'REAL_DISCOUNT_ID' => $discount['DISCOUNT_ID'],
					'USE_COUPONS' => $discount['USE_COUPONS'],
					'ACTIONS_DESCR' => $discount['ACTIONS_DESCR'],
					'ACTIONS_DESCR_DATA' => $discount['ACTIONS_DESCR_DATA'],
					'APPLY' => 'N',
					'EDIT_PAGE_URL' => $discount['EDIT_PAGE_URL']
				);
				$discountApply[$id] = &$this->discountResult['DISCOUNT_LIST'][$id];
			}
			unset($id, $discount);
		}

		if (!empty($this->couponsCache))
		{
			foreach ($this->couponsCache as $id => $coupon)
			{
				$this->discountResult['COUPON_LIST'][$id] = $coupon;
				$this->discountResult['COUPON_LIST'][$id]['APPLY'] = 'N';
				$couponApply[$id] = &$this->discountResult['COUPON_LIST'][$id];
			}
			unset($id, $coupon);
		}

		if (empty($this->discountResult['APPLY_BLOCKS']))
		{
			unset($discountApply, $couponApply);
			return;
		}

		foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
		{
			if (!empty($applyBlock['BASKET']))
			{
				foreach ($applyBlock['BASKET'] as $basketCode => $discountList)
				{
					foreach ($discountList as $discount)
					{
						if ($discount['RESULT']['APPLY'] == 'Y')
						{
							if (isset($discountApply[$discount['DISCOUNT_ID']]))
								$discountApply[$discount['DISCOUNT_ID']]['APPLY'] = 'Y';
							if (isset($couponApply[$discount['COUPON_ID']]))
								$couponApply[$discount['COUPON_ID']]['APPLY'] = 'Y';
						}
					}
					unset($discount);
				}
				unset($basketCode, $discountList);
			}

			if (!empty($applyBlock['ORDER']))
			{
				foreach ($applyBlock['ORDER'] as $discount)
				{
					if (!empty($discount['RESULT']['BASKET']))
					{
						foreach ($discount['RESULT']['BASKET'] as $basketCode => $applyList)
						{
							if ($applyList['APPLY'] == 'Y')
							{
								if (isset($discountApply[$discount['DISCOUNT_ID']]))
									$discountApply[$discount['DISCOUNT_ID']]['APPLY'] = 'Y';
								if (isset($couponApply[$discount['COUPON_ID']]))
									$couponApply[$discount['COUPON_ID']]['APPLY'] = 'Y';
							}
						}
						unset($basketCode, $applyList);
					}
					if (!empty($discount['RESULT']['DELIVERY']) && $discount['RESULT']['DELIVERY']['APPLY'] == 'Y')
					{
						if (isset($discountApply[$discount['DISCOUNT_ID']]))
							$discountApply[$discount['DISCOUNT_ID']]['APPLY'] = 'Y';
						if (isset($couponApply[$discount['COUPON_ID']]))
							$couponApply[$discount['COUPON_ID']]['APPLY'] = 'Y';
					}
				}
				unset($discount);
			}
		}
		unset($counter, $applyBlock);

		unset($discountApply, $couponApply);
	}

	/**
	 * Fill prices in apply results.
	 *
	 * @return void
	 */
	protected function getApplyPrices()
	{
		$this->normalizeDiscountResult();

		$basket = array();
		if (!empty($this->orderData['BASKET_ITEMS']))
		{
			foreach ($this->orderData['BASKET_ITEMS'] as $basketCode => $basketItem)
			{
				$basket[$basketCode] = array(
					'BASE_PRICE' => $basketItem['BASE_PRICE'],
					'PRICE' => $basketItem['PRICE'],
					'DISCOUNT' => $basketItem['DISCOUNT_PRICE']
				);
			}
			unset($basketCode, $basketItem);
		}

		$this->discountResult['PRICES'] = array(
			'BASKET' => $basket,
			'DELIVERY' => array(
				'BASE_PRICE' => $this->orderData['BASE_PRICE_DELIVERY'],
				'PRICE' => $this->orderData['PRICE_DELIVERY'],
				'DISCOUNT' => $this->orderData['PRICE_DELIVERY_DIFF']
			)
		);
		unset($basket);
	}

	/**
	 * Get discount delivery list.
	 *
	 * @return void
	 */
	protected function getApplyDeliveryList()
	{
		$delivery = array();
		$shipment = array();

		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as &$discount)
					{
						if (empty($discount['RESULT']['DELIVERY']))
							continue;
						$delivery[$discount['RESULT']['DELIVERY']['DELIVERY_ID']] = $discount['RESULT']['DELIVERY']['DELIVERY_ID'];
					}
					unset($discount);
				}
			}
			unset($counter, $applyBlock);
		}
		if ($this->shipment instanceof Shipment)
			$shipment[] = $this->shipment->getShipmentCode();

		$this->discountResult['DELIVERY_LIST'] = (
			empty($delivery)
			? array()
			: array_values($delivery)
		);

		$this->discountResult['SHIPMENT_LIST'] = $shipment;
	}

	/**
	 * Change result format.
	 *
	 * @return void
	 */
	protected function remakingDiscountResult()
	{
		$basket = array();
		$delivery = array();

		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (!empty($applyBlock['BASKET']))
				{
					foreach ($applyBlock['BASKET'] as $basketCode => $discountList)
					{
						if (!isset($basket[$basketCode]))
							$basket[$basketCode] = array();
						foreach ($discountList as $discount)
						{
							$basket[$basketCode][] = array(
								'DISCOUNT_ID' => $discount['DISCOUNT_ID'],
								'COUPON_ID' => $discount['COUPON_ID'],
								'APPLY' => $discount['RESULT']['APPLY'],
								'DESCR' => $discount['RESULT']['DESCR']
							);
						}
						unset($discount);
					}
					unset($basketCode, $discountList);
				}

				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if (!empty($discount['RESULT']['BASKET']))
						{
							foreach ($discount['RESULT']['BASKET'] as $basketCode => $applyList)
							{
								if (!isset($basket[$basketCode]))
									$basket[$basketCode] = array();
								$basket[$basketCode][] = array(
									'DISCOUNT_ID' => $discount['DISCOUNT_ID'],
									'COUPON_ID' => $discount['COUPON_ID'],
									'APPLY' => $applyList['APPLY'],
									'DESCR' => $applyList['DESCR']
								);
							}
							unset($basketCode, $applyList);
						}
						if (!empty($discount['RESULT']['DELIVERY']))
						{
							$delivery[] = array(
								'DISCOUNT_ID' => $discount['DISCOUNT_ID'],
								'COUPON_ID' => $discount['COUPON_ID'],
								'DELIVERY_ID' => $discount['RESULT']['DELIVERY']['DELIVERY_ID'],
								'APPLY' => $discount['RESULT']['DELIVERY']['APPLY'],
								'DESCR' => $discount['RESULT']['DELIVERY']['DESCR']
							);
						}
					}
					unset($discount);
				}
			}
			unset($counter, $applyBlock);
		}

		$this->discountResult['RESULT'] = array(
			'BASKET' => $basket,
			'DELIVERY' => $delivery
		);
	}

	/**
	 * Create correspondence between basket ids and basket codes.
	 *
	 * @return Result
	 */
	protected function getBasketTables()
	{
		$result = new Result;

		$this->forwardBasketTable = array();
		$this->reverseBasketTable = array();

		if (!$this->isBasketNotEmpty())
			return $result;

		$basket = $this->getBasket();
		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$code = $basketItem->getBasketCode();
			$id = $basketItem->getField('ID');
			$this->forwardBasketTable[$code] = $id;
			$this->reverseBasketTable[$id] = $code;
			unset($id, $code);

			if ($basketItem->isBundleParent())
			{
				$bundle = $basketItem->getBundleCollection();
				if (empty($bundle))
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_BASKET_BUNDLE_EMPTY'),
						self::ERROR_ID
					));
					break;
				}
				/** @var BasketItem $bundleItem */
				foreach ($bundle as $bundleItem)
				{
					$code = $bundleItem->getBasketCode();
					$id = $bundleItem->getField('ID');
					$this->forwardBasketTable[$code] = $id;
					$this->reverseBasketTable[$id] = $code;
					unset($id, $code);
				}
				unset($bundle, $bundleItem);
			}
		}
		unset($basketItem, $basket);

		return $result;
	}

	/**
	 * Returns exist custom price for basket item code.
	 *
	 * @param int $code			Basket code.
	 * @return bool
	 */
	protected function isCustomPriceByCode($code)
	{
		if (!empty($this->orderData['BASKET_ITEMS'][$code]['CUSTOM_PRICE']) && $this->orderData['BASKET_ITEMS'][$code]['CUSTOM_PRICE'] == 'Y')
			return true;
		return false;
	}

	/**
	 * Returns exist custom price for basket item.
	 *
	 * @param array $item			Basket item.
	 * @return bool
	 */
	protected static function isCustomPrice($item)
	{
		if (!empty($item['CUSTOM_PRICE']) && $item['CUSTOM_PRICE'] == 'Y')
			return true;
		return false;
	}

	/**
	 * Returns check item in set for basket item code.
	 *
	 * @param int $code			Basket code.
	 * @return bool
	 */
	protected function isInSetByCode($code)
	{
		if (!empty($this->orderData['BASKET_ITEMS'][$code]['IN_SET']) && $this->orderData['BASKET_ITEMS'][$code]['IN_SET'] == 'Y')
			return true;
		return false;
	}

	/**
	 * Returns check item in set for basket item.
	 *
	 * @param array $item			Basket item.
	 * @return bool
	 */
	protected static function isInSet($item)
	{
		if (!empty($item['IN_SET']) && $item['IN_SET'] == 'Y')
			return true;
		return false;
	}

	/**
	 * Returns exist new item in basket.
	 *
	 * @return bool
	 */
	protected function isMixedBasket()
	{
		$result = false;
		if (empty($this->orderData['BASKET_ITEMS']))
			return $result;

		foreach ($this->orderData['BASKET_ITEMS'] as $basketItem)
		{
			if (!isset($basketItem['ID']) || (int)$basketItem['ID'] <= 0)
			{
				$result = true;
				break;
			}
		}
		unset($basketItem);

		if (!$result)
		{
			if ($this->isOrderedBasketChanged())
				$result = true;
		}

		return $result;
	}

	/**
	 * Returns check new basket item for basket item code.
	 *
	 * @param int|string $code			Basket code.
	 * @return bool
	 */
	protected function isNewBasketItemByCode($code)
	{
		return (
			$this->getUseMode() == self::USE_MODE_FULL
			|| !isset($this->orderData['BASKET_ITEMS'][$code]['ID'])
			|| $this->orderData['BASKET_ITEMS'][$code]['ID'] <= 0
		);
	}

	/**
	 * Returns check new basket item for basket item.
	 *
	 * @param array $item			Basket item.
	 * @return bool
	 */
	protected static function isNewBasketItem($item)
	{
		return (
			!isset($item['ID'])
			|| $item['ID'] <= 0
		);
	}

	/**
	 * Return true if basket saved order changed (change PRODUCT_ID).
	 *
	 * @return bool
	 */
	protected function isOrderedBasketChanged()
	{
		$result = false;
		if ($this->isOrderExists() && !$this->isOrderNew() && $this->isBasketNotEmpty())
		{
			$basket = $this->getBasket();
			/** @var BasketItem $basketItem */
			foreach ($basket as $basketItem)
			{
				if (!$basketItem->isChanged())
					continue;
				/** @noinspection PhpInternalEntityUsedInspection */
				if (in_array('PRODUCT_ID', $basketItem->getFields()->getChangedKeys()))
				{
					$result = true;
					break;
				}
			}
			unset($basketItem, $basket);
		}
		return $result;
	}

	/**
	 * Return true if ordered basket item changed (change PRODUCT_ID).
	 *
	 * @param int $code			Basket code.
	 * @return bool
	 */
	protected function isBasketItemChanged($code)
	{
		$result = false;
		if ($this->isOrderExists() && !$this->isOrderNew() && $this->isBasketNotEmpty())
		{
			$basketItem = $this->getBasket()->getItemByBasketCode($code);
			if ($basketItem instanceof BasketItem)
			{
				/** @noinspection PhpInternalEntityUsedInspection */
				if (in_array('PRODUCT_ID', $basketItem->getFields()->getChangedKeys()))
					$result = true;
			}
		}
		return $result;
	}

	/**
	 * Returns basket codes for calculate.
	 *
	 * @param bool $full				Full or apply mode.
	 * @return array
	 */
	protected function getBasketCodes($full = true)
	{
		$result = array();
		if (empty($this->orderData['BASKET_ITEMS']))
			return $result;
		switch ($this->getUseMode())
		{
			case self::USE_MODE_FULL:
			case self::USE_MODE_COUPONS:
				foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
				{
					if ($this->isCustomPrice($item) || $this->isInSet($item))
						continue;
					$result[] = $code;
				}
				unset($code, $item);
				break;
			case self::USE_MODE_APPLY:
				foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
				{
					if (
						$this->isCustomPrice($item)
						|| $this->isNewBasketItem($item)
						|| $this->isBasketItemChanged($code)
						|| $this->isInSet($item)
					)
						continue;
					$result[] = $code;
				}
				unset($code, $item);
				break;
			case self::USE_MODE_MIXED:
				$full = ($full === true);
				if ($full)
				{
					foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
					{
						if (
							!$this->isCustomPrice($item)
							&& !$this->isInSet($item)
							&& ($this->isNewBasketItem($item) || $this->isBasketItemChanged($code))
						)
							$result[] = $code;
					}
					unset($code, $item);
				}
				else
				{
					foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
					{
						if (
							$this->isCustomPrice($item)
							|| $this->isNewBasketItem($item)
							|| $this->isBasketItemChanged($code)
							|| $this->isInSet($item)
						)
							continue;
						$result[] = $code;
					}
					unset($code, $item);
				}
				break;
		}

		return $result;
	}

	/**
	 * Merge discount actions result with old data.
	 *
	 * @param int $index				Discount index.
	 * @param array $stepResult			New result.
	 * @return void
	 */
	protected function mergeDiscountActionResult($index, $stepResult)
	{
		if (!isset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]))
			return;
		if (empty($stepResult) || !is_array($stepResult))
			return;
		$basketKeys = array_keys($this->orderData['BASKET_ITEMS']);
		foreach ($basketKeys as &$basketCode)
		{
			if (!$this->isCustomPriceByCode($basketCode))
				continue;
			if (isset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']['BASKET'][$basketCode]))
				unset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']['BASKET'][$basketCode]);
		}
		unset($basketCode);
		if (isset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']['DESCR_DATA']))
			unset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']['DESCR_DATA']);
		if (isset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']['DESCR']))
			unset($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']['DESCR']);
		self::recursiveMerge($stepResult, $this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT']);
		$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'][$index]['RESULT'] = $stepResult;
	}

	/**
	 * Fill empty discount result list.
	 *
	 * @return void
	 */
	protected function fillEmptyDiscountResult()
	{
		$this->discountResultCounter = 0;
		$this->discountResult = array(
			'APPLY_BLOCKS' => array(),
			'DISCOUNT_LIST' => array(),
			'COUPON_LIST' => array(),
			'DELIVERY_LIST' => array(),
			'SHIPMENT_LIST' => array()
		);
		$this->clearCurrentApplyBlock();
		$this->discountStoredActionData = array();
	}

	/**
	 * Filtered result order data.
	 *
	 * @return array
	 */
	protected function fillDiscountResult()
	{
		$this->normalizeDiscountResult();
		$orderKeys = array('PRICE_DELIVERY', 'PRICE_DELIVERY_DIFF', 'CURRENCY');
		$basketKeys = array('PRICE', 'DISCOUNT_PRICE', 'VAT_RATE', 'VAT_VALUE', 'CURRENCY');
		$result = array();
		foreach ($orderKeys as $key)
		{
			if (isset($this->orderData[$key]))
				$result[$key] = $this->orderData[$key];
		}
		unset($key);
		$result['DISCOUNT_PRICE'] = $result['PRICE_DELIVERY_DIFF'];
		unset($result['PRICE_DELIVERY_DIFF']);
		$result['BASKET_ITEMS'] = array();
		foreach ($this->orderData['BASKET_ITEMS'] as $index => $basketItem)
		{
			$result['BASKET_ITEMS'][$index] = array();
			foreach ($basketKeys as $key)
			{
				if (isset($basketItem[$key]))
					$result['BASKET_ITEMS'][$index][$key] = $basketItem[$key];
			}
			unset($key);
		}
		unset($index, $basketItem);

		$result['SHIPMENT'] = null;
		if ($this->shipment instanceof Shipment)
			$result['SHIPMENT'] = $this->shipment->getShipmentCode();

		return $result;
	}

	/**
	 * Internal. Fill current apply block empty data.
	 *
	 * @return void
	 */
	protected function clearCurrentApplyBlock()
	{
		$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter] = static::getEmptyApplyBlock();
	}
	/**
	 * Internal. Clear current step data.
	 *
	 * @return void
	 */
	protected function fillEmptyCurrentStep()
	{
		$this->currentStep = array(
			'oldData' => array(),
			'discount' => array(),
			'discountIndex' => null,
			'discountId' => 0,
			'result' => array(),
			'stop' => false,
		);
	}

	/**
	 * Internal. Fill current step data.
	 *
	 * @param array $data			Only not empty keys.
	 * @return void
	 */
	protected function fillCurrentStep($data)
	{
		$this->fillEmptyCurrentStep();
		if (!empty($data) && is_array($data))
		{
			foreach ($data as $key => $value)
				$this->currentStep[$key] = $value;
			unset($value, $key);
		}
	}

	/**
	 * Load from database need modules list for discounts.
	 *
	 * @return void
	 */
	protected function getDiscountModules()
	{
		if (empty($this->discountIds))
			return;
		$loadList = $this->discountIds;
		if (!empty($this->cacheDiscountModules))
		{
			$loadList = array();
			foreach ($this->discountIds as $discount)
			{
				if (!isset($this->cacheDiscountModules['sale'.$discount]))
					$loadList[] = $discount;
			}
			unset($discount);
		}
		if (empty($loadList))
			return;

		foreach ($loadList as &$discount)
			$this->cacheDiscountModules['sale'.$discount] = array();
		unset($discount);

		$moduleList = RuntimeCache\DiscountCache::getInstance()->getDiscountModules($loadList);
		if (!empty($moduleList))
		{
			foreach ($moduleList as $discount => $discountModule)
				$this->cacheDiscountModules['sale'.$discount] = $discountModule;
			unset($discount, $discountModule, $moduleList);
		}
		unset($moduleList);
	}

	/**
	 * Extend order data for discounts.
	 *
	 * @return void
	 */
	protected function extendOrderData()
	{
		if (empty($this->discountIds))
			return;

		$entityCacheKey = md5(serialize($this->discountIds));
		if (!isset($this->entityResultCache[$entityCacheKey]))
		{
			$this->entityResultCache[$entityCacheKey] = array();
			$this->entityList = RuntimeCache\DiscountCache::getInstance()->getDiscountEntities($this->discountIds);
			if (empty($this->entityList))
				return;

			$event = new Main\Event(
				'sale',
				self::EVENT_EXTEND_ORDER_DATA,
				array(
					'ORDER' => $this->orderData,
					'ENTITY' => $this->entityList
				)
			);
			$event->send();
			$this->entityResultCache[$entityCacheKey] = $event->getResults();
		}
		$resultList = $this->entityResultCache[$entityCacheKey];
		if (empty($resultList) || !is_array($resultList))
			return;
		/** @var Main\EventResult $eventResult */
		foreach ($resultList as &$eventResult)
		{
			if ($eventResult->getType() != Main\EventResult::SUCCESS)
				continue;

			$newData = $eventResult->getParameters();
			if (empty($newData) || !is_array($newData))
				continue;

			$this->modifyOrderData($newData);
		}
		unset($newData, $eventResult, $resultList);
	}

	/**
	 * Modify order data from handlers.
	 *
	 * @param array &$newData			New order data from handler.
	 * @return void
	 */
	protected function modifyOrderData(&$newData)
	{
		if (!empty($newData) && is_array($newData))
			self::recursiveMerge($this->orderData, $newData);
	}

	/**
	 * Return formatted discount description.
	 *
	 * @param array $descr				Description.
	 * @return array
	 */
	protected static function formatDescription($descr)
	{
		$result = array();
		if (empty($descr) || !is_array($descr))
			return $result;
		if (isset($descr['DELIVERY']))
		{
			$result['DELIVERY'] = array();
			foreach ($descr['DELIVERY'] as $index => $value)
			{
				$result['DELIVERY'][$index] = OrderDiscountManager::formatDescription($value);
				if ($result['DELIVERY'][$index] == false)
					unset($result['DELIVERY'][$index]);
			}
			unset($value, $index);
			if (!empty($result['DELIVERY']))
				$result['DELIVERY'] = implode(', ', $result['DELIVERY']);
		}
		if (isset($descr['BASKET']))
		{
			$result['BASKET'] = array();
			foreach ($descr['BASKET'] as $index => $value)
			{
				$result['BASKET'][$index] = OrderDiscountManager::formatDescription($value);
				if ($result['BASKET'][$index] == false)
					unset($result['BASKET'][$index]);
			}
			unset($value, $index);
			if (!empty($result['BASKET']))
				$result['BASKET'] = implode(', ', $result['BASKET']);
		}
		return $result;
	}

	/**
	 * Added keys from source array to destination array.
	 *
	 * @param array &$dest			Destination array.
	 * @param array $src			Source array.
	 * @return void
	 */
	protected static function recursiveMerge(&$dest, $src)
	{
		if (!is_array($dest) || !is_array($src))
			return;
		if (empty($dest))
		{
			$dest = $src;
			return;
		}
		foreach ($src as $key => $value)
		{
			if (!isset($dest[$key]))
			{
				$dest[$key] = $value;
				continue;
			}
			if (is_array($dest[$key]))
				self::recursiveMerge($dest[$key], $value);
		}
		unset($value, $key);
	}

	/**
	 * Fill basket prices from base prices.
	 *
	 * @return void
	 */
	protected function resetBasketPrices()
	{
		foreach ($this->orderData['BASKET_ITEMS'] as &$basketItem)
		{
			if (self::isCustomPrice($basketItem))
				continue;
			$basketItem['DISCOUNT_PRICE'] = 0;
			$basketItem['PRICE'] = $basketItem['BASE_PRICE'];
		}
		unset($basketItem);
	}

	/**
	 * Load from database discount id for user groups.
	 *
	 * @param array $filter			Additional filter.
	 * @return void
	 * @throws Main\ArgumentException
	 */
	protected function loadDiscountByUserGroups(array $filter = array())
	{
		if (!array_key_exists('USER_ID', $this->orderData))
			return;
		$userGroups = $this->context->getUserGroups();
		$filter['@GROUP_ID'] = $userGroups;
		$filter['=ACTIVE'] = 'Y';
		$cacheKey = md5('U'.implode('_', $userGroups).'-F'.serialize($filter));
		if (!isset($this->discountByUserCache[$cacheKey]))
		{
			//RuntimeCache works only with basic filter.
			if(!array_diff_assoc($filter, array(
				'@GROUP_ID' => $userGroups,
				'=ACTIVE' => 'Y',
			)))
			{
				$this->discountByUserCache[$cacheKey] = RuntimeCache\DiscountCache::getInstance()->getDiscountIds($userGroups);
			}
			else
			{
				$discountCache = array();
				$groupDiscountIterator = Internals\DiscountGroupTable::getList(array(
					'select' => array('DISCOUNT_ID'),
					'filter' => $filter,
					'order' => array('DISCOUNT_ID' => 'ASC')
				));
				while ($groupDiscount = $groupDiscountIterator->fetch())
				{
					$groupDiscount['DISCOUNT_ID'] = (int)$groupDiscount['DISCOUNT_ID'];
					if ($groupDiscount['DISCOUNT_ID'] > 0)
						$discountCache[$groupDiscount['DISCOUNT_ID']] = $groupDiscount['DISCOUNT_ID'];
				}
				unset($groupDiscount, $groupDiscountIterator);
				if (!empty($discountCache))
					$this->discountByUserCache[$cacheKey] = $discountCache;
				unset($discountCache);
			}
		}
		$this->discountIds = $this->discountByUserCache[$cacheKey];
		unset($cacheKey, $userGroups);
	}

	/**
	 * Load discount modules.
	 *
	 * @param string|int $discount				Discount key.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	protected function loadDiscountModules($discount)
	{
		$result = true;
		if (empty($this->cacheDiscountModules[$discount]))
			return $result;

		foreach ($this->cacheDiscountModules[$discount] as $moduleID)
		{
			if (!isset($this->loadedModules[$moduleID]))
				$this->loadedModules[$moduleID] = Main\Loader::includeModule($moduleID);
			if (!$this->loadedModules[$moduleID])
			{
				$result = false;
				break;
			}
		}
		unset($moduleID);

		return $result;
	}

	/**
	 * Load sale discount from database
	 *
	 * @return void
	 * @throws Main\ArgumentException
	 */
	protected function loadDiscountList()
	{
		if (empty($this->discountIds))
		{
			$this->discountIds = null;
			return;
		}

		$this->getDiscountModules();

		$couponList = DiscountCouponsManager::getForApply(array('MODULE_ID' => 'sale', 'DISCOUNT_ID' => $this->discountIds), array(), true);

		$this->saleDiscountCacheKey = md5('D'.implode('_', $this->discountIds));
		if (!empty($couponList))
			$this->saleDiscountCacheKey .= '-C'.implode('_', array_keys($couponList));

		$this->saleDiscountCacheKey .= '-MF'.implode('_', $this->executeModuleFilter);

		if (!isset($this->saleDiscountCache[$this->saleDiscountCacheKey]))
		{
			$currentList = RuntimeCache\DiscountCache::getInstance()->getDiscounts(
				$this->discountIds, $this->executeModuleFilter, $this->orderData['SITE_ID'], $couponList?: array()
			);

			if (!empty($currentList))
			{
				$evalCode = '';
				foreach (array_keys($currentList) as $index)
				{
					$discount = $currentList[$index];
					$code = 'sale'.$discount['ID'];
					if (!$this->loadDiscountModules($code))
					{
						unset($currentList[$index]);
						continue;
					}
					if (isset($this->cacheDiscountModules[$code]))
					{
						$currentList[$index]['MODULES'] = $this->cacheDiscountModules[$code];
					}
					if (!$this->enableCheckingPrediction)
					{
						if ($discount['UNPACK'] !== null)
							$evalCode .= '$currentList['.$index.'][\'UNPACK_EXECUTE\'] = '.$discount['UNPACK'].";\n";
					}
					else
					{
						if ($discount['PREDICTIONS_APP'] !== null && $discount['PREDICTIONS_APP'] !== '')
							$evalCode .= '$currentList['.$index.'][\'PREDICTIONS_APP_EXECUTE\'] = '.$discount['PREDICTIONS_APP'].";\n";
					}
					if ($discount['APPLICATION'] !== null)
						$evalCode .= '$currentList['.$index.'][\'APPLICATION_EXECUTE\'] = '.$discount['APPLICATION'].";\n";
				}
				unset($code, $discount, $index);

				if ($evalCode !== '')
				{
					if (version_compare(PHP_VERSION, '7.0.0', '>='))
					{
						try
						{
							eval($evalCode);
						}
						catch (\ParseError $e)
						{
							$this->showAdminError();
						}
					}
					else
					{
						eval($evalCode);
					}
				}
				unset($evalCode);
			}

			$this->saleDiscountCache[$this->saleDiscountCacheKey] = $currentList;
		}
		unset($couponList);
	}

	/**
	 * Execute sale discount list.
	 *
	 * @return Result
	 */
	protected function executeDiscountList()
	{
		$result = new Result;

		$roundApply = true;
		$saleDiscountOnly = $this->useOnlySaleDiscounts();
		$useMode = $this->getUseMode();
		if ($saleDiscountOnly)
		{
			if ($useMode == self::USE_MODE_FULL && $this->isRoundMode(self::ROUND_MODE_SALE_DISCOUNT))
				$roundApply = false;
		}

		$this->discountIds = array();
		if (empty($this->saleDiscountCacheKey) || empty($this->saleDiscountCache[$this->saleDiscountCacheKey]))
		{
			if (!$roundApply)
			{
				$this->roundFullBasketPriceByIndex(array(
					'DISCOUNT_INDEX' => -1,
					'DISCOUNT_ID' => 0
				));
			}
			return $result;
		}

		$currentList = $this->saleDiscountCache[$this->saleDiscountCacheKey];
		$this->discountIds = array_keys($currentList);
		$this->extendOrderData();

		Discount\Actions::clearAction();

		$blackList = array(
			'UNPACK_EXECUTE' => true,
			'APPLICATION_EXECUTE' => true,
			'PREDICTIONS_APP_EXECUTE' => true
		);

		$index = -1;
		$skipPriorityLevel = null;
		foreach ($currentList as $discountIndex => $discount)
		{
			if($skipPriorityLevel == $discount['PRIORITY'])
			{
				continue;
			}
			$skipPriorityLevel = null;

			$this->fillCurrentStep(array(
				'discount' => $discount,
				'cacheIndex' => $discountIndex
			));
			if (!$this->checkDiscountConditions())
				continue;

			$index++;
			if (!$roundApply && $discount['EXECUTE_MODULE'] == 'sale')
			{
				$this->roundFullBasketPriceByIndex(array(
					'DISCOUNT_INDEX' => $index,
					'DISCOUNT_ID' => $discount['ID']
				));
				$roundApply = true;
			}

			if ($useMode == self::USE_MODE_FULL && !isset($this->fullDiscountList[$discount['ID']]))
				$this->fullDiscountList[$discount['ID']] = array_diff_key($discount, $blackList);

			$actionsResult = $this->applySaleDiscount();
			if (!$actionsResult->isSuccess())
			{
				$result->addErrors($actionsResult->getErrors());
				unset($actionsResult);
				return $result;
			}

			if ($this->currentStep['stop'])
				break;

			if ($this->currentStep['stopLevel'])
			{
				$skipPriorityLevel = $discount['PRIORITY'];
			}
		}
		unset($discount, $currentList);
		$this->fillEmptyCurrentStep();

		if (!$roundApply)
		{
			$index++;
			$this->roundFullBasketPriceByIndex(array(
				'DISCOUNT_INDEX' => $index,
				'DISCOUNT_ID' => 0
			));
		}

		return $result;
	}

	/**
	 * Fill last discount flag for basket items. Only for basket or new order or refreshed order.
	 *
	 * @return void
	 */
	protected function fillBasketLastDiscount()
	{
		if ($this->getUseMode() != self::USE_MODE_FULL)
			return;
		$applyMode = self::getApplyMode();
		if ($applyMode == self::APPLY_MODE_ADD)
			return;

		$codeList = array_keys($this->orderData['BASKET_ITEMS']);
		switch ($applyMode)
		{
			case self::APPLY_MODE_DISABLE:
			case self::APPLY_MODE_FULL_DISABLE:
				foreach ($codeList as &$code)
				{
					if (isset($this->basketDiscountList[$code]) && !empty($this->basketDiscountList[$code]))
						$this->orderData['BASKET_ITEMS'][$code]['LAST_DISCOUNT'] = 'Y';
				}
				unset($code);
				break;
			case self::APPLY_MODE_LAST:
			case self::APPLY_MODE_FULL_LAST:
				foreach ($codeList as &$code)
				{
					if (!isset($this->basketDiscountList[$code]) || empty($this->basketDiscountList[$code]))
						continue;
					$lastDiscount = end($this->basketDiscountList[$code]);
					if (!empty($lastDiscount['LAST_DISCOUNT']) && $lastDiscount['LAST_DISCOUNT'] == 'Y')
						$this->orderData['BASKET_ITEMS'][$code]['LAST_DISCOUNT'] = 'Y';
				}
				unset($code);
				break;
		}
		unset($codeList, $applyMode);
	}

	/**
	 * Check last discount flag for basket items. Only for basket or new order or refreshed order.
	 *
	 * @return bool
	 */
	protected function isBasketLastDiscount()
	{
		$result = false;

		if ($this->getUseMode() != self::USE_MODE_FULL)
			return $result;

		$this->fillBasketLastDiscount();
		$applyMode = self::getApplyMode();
		if ($applyMode == self::APPLY_MODE_FULL_LAST || $applyMode == self::APPLY_MODE_FULL_DISABLE)
		{
			foreach ($this->orderData['BASKET_ITEMS'] as $basketItem)
			{
				if (isset($basketItem['LAST_DISCOUNT']) && $basketItem['LAST_DISCOUNT'] == 'Y')
				{
					$result = true;
					break;
				}
			}
			unset($basketItem);
		}
		unset($applyMode);

		return $result;
	}

	/* additional coupons tools */
	/**
	 * Clear coupons from already used discounts.
	 *
	 * @internal
	 * @param array $coupons			Coupons list from \Bitrix\Sale\DiscountCouponsManager::getForApply.
	 * @return array
	 */
	protected function clearAdditionalCoupons(array $coupons)
	{
		if (empty($coupons))
			return array();

		if (empty($this->discountsCache))
			return $coupons;

		$result = array();

		foreach ($coupons as $couponCode => $couponData)
		{
			$found = false;
			foreach ($this->discountsCache as &$discount)
			{
				if (
					$discount['MODULE_ID'] == $couponData['MODULE_ID']
					&& $discount['DISCOUNT_ID'] == $couponData['DISCOUNT_ID']
					&& $discount['USE_COUPONS'] == 'N'
				)
				{
					$found = true;
				}
			}
			unset($discount);

			if (!$found && !empty($this->couponsCache))
			{
				foreach ($this->couponsCache as $existCouponCode => $existCouponData)
				{
					$discount = $this->discountsCache[$existCouponData['ORDER_DISCOUNT_ID']];
					if (
						$discount['MODULE_ID'] != $couponData['MODULE_ID']
						|| $discount['DISCOUNT_ID'] != $couponData['DISCOUNT_ID']
					)
						continue;
					if ($couponCode == $existCouponCode)
					{
						if (
							$existCouponData['ID'] > 0 || $existCouponData['TYPE'] == Internals\DiscountCouponTable::TYPE_BASKET_ROW
						)
							$found = true;
					}
					else
					{
						if (
							$existCouponData['TYPE'] != Internals\DiscountCouponTable::TYPE_BASKET_ROW
							|| $couponData['TYPE'] != Internals\DiscountCouponTable::TYPE_BASKET_ROW
						)
						{
							$found = true;
						}
						else
						{
							if ($discount['MODULE_ID'] == 'sale')
								$found = true;
						}
					}
					unset($discount);
					if ($found)
						break;
				}
				unset($existCouponCode, $existCouponData);
			}

			if (!$found)
				$result[$couponCode] = $couponData;
			unset($found);
		}
		unset($couponCode, $couponData);

		return $result;
	}

	/**
	 * Return additional coupons for exist order.
	 *
	 * @internal
	 * @param array $filter				Coupons filter.
	 * @return array
	 */
	protected function getAdditionalCoupons(array $filter = array())
	{
		if ($this->useOnlySaleDiscounts())
		{
			if (isset($filter['MODULE_ID']) && $filter['MODULE_ID'] != 'sale')
				return array();
			if (isset($filter['!MODULE_ID']) && $filter['!MODULE_ID'] == 'sale')
				return array();
			$filter['MODULE_ID'] = 'sale';
		}

		$useOrderCoupons = DiscountCouponsManager::isUsedOrderCouponsForApply();
		DiscountCouponsManager::useSavedCouponsForApply(false);
		$coupons = DiscountCouponsManager::getForApply($filter, array(), true);
		DiscountCouponsManager::useSavedCouponsForApply($useOrderCoupons);
		unset($useOrderCoupons);

		if (empty($coupons))
			return array();

		return $this->clearAdditionalCoupons($coupons);
	}

	/**
	 * Calculate additional basket coupons.
	 *
	 * @param array $applyCoupons		Apply discount coupons data.
	 * @return Result
	 */
	protected function calculateApplyBasketAdditionalCoupons(array $applyCoupons)
	{
		$result = new Result;

		if ($this->useOnlySaleDiscounts())
			return $result;
		if (empty($applyCoupons))
			return $result;

		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET'];

		$applyExist = $this->isBasketApplyResultExist();

		$basketCodeList = $this->getBasketCodes(false);
		foreach ($basketCodeList as &$basketCode)
		{
			if (array_key_exists($basketCode, $applyBlock))
				unset($applyBlock[$basketCode]);
			if (empty($applyCoupons[$basketCode]))
				continue;

			$itemData = array(
				'MODULE_ID' => $this->orderData['BASKET_ITEMS'][$basketCode]['MODULE'],
				'PRODUCT_ID' => $this->orderData['BASKET_ITEMS'][$basketCode]['PRODUCT_ID'],
				'BASKET_ID' => $basketCode
			);
			$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE_TMP'] = $this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'];
			$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'] = $this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'];
			foreach ($applyCoupons[$basketCode] as $index => $discount)
			{
				$discountResult = $this->convertDiscount($discount);
				if (!$discountResult->isSuccess())
				{
					$result->addErrors($discountResult->getErrors());
					unset($discountResult);
					return $result;
				}
				$orderDiscountId = $discountResult->getId();
				$discountData = $discountResult->getData();
				$applyCoupons[$basketCode][$index]['ORDER_DISCOUNT_ID'] = $orderDiscountId;

				if (empty($discount['COUPON']))
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_DISCOUNT_WITHOUT_COUPON'),
						self::ERROR_ID
					));
					return $result;
				}
				$couponResult = $this->convertCoupon($discount['COUPON'], $orderDiscountId);
				if (!$couponResult->isSuccess())
				{
					$result->addErrors($couponResult->getErrors());
					unset($couponResult);
					return $result;
				}
				$orderCouponId = $couponResult->getId();

				DiscountCouponsManager::setApplyByProduct($itemData, array($orderCouponId));
				unset($couponResult);

				unset($discountData, $discountResult);
				if (!isset($applyBlock[$basketCode]))
					$applyBlock[$basketCode] = array();
				$applyBlock[$basketCode][$index] = array(
					'DISCOUNT_ID' => $orderDiscountId,
					'COUPON_ID' => $orderCouponId,
					'RESULT' => array(
						'APPLY' => 'Y',
						'DESCR' => false,
						'DESCR_DATA' => false
					)
				);

				$currentProduct = $this->orderData['BASKET_ITEMS'][$basketCode];
				$orderApplication = (
					!empty($this->discountsCache[$orderDiscountId]['APPLICATION'])
					? $this->discountsCache[$orderDiscountId]['APPLICATION']
					: null
				);
				if (!empty($orderApplication))
				{
					$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT'] = (
						!empty($this->discountsCache[$orderDiscountId]['ACTIONS_DESCR_DATA'])
						? $this->discountsCache[$orderDiscountId]['ACTIONS_DESCR_DATA']
						: false
					);

					$applyProduct = null;
					eval('$applyProduct='.$orderApplication.';');
					if (is_callable($applyProduct))
						$applyProduct($this->orderData['BASKET_ITEMS'][$basketCode]);
					unset($applyProduct);

					if (!empty($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']))
					{
						$applyBlock[$basketCode][$index]['RESULT']['DESCR_DATA'] = $this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']['BASKET'];
						$applyBlock[$basketCode][$index]['RESULT']['DESCR'] = self::formatDescription($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
					}
					unset($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
				}
				unset($orderApplication);

				if ($applyExist && !$this->getStatusApplyBasketDiscount($basketCode, $orderDiscountId, $orderCouponId))
				{
					$this->orderData['BASKET_ITEMS'][$basketCode] = $currentProduct;
					$applyBlock[$basketCode][$index]['RESULT']['APPLY'] = 'N';
				}
				unset($currentProduct);
			}
			unset($discount, $index);
			$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'] = $this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE_TMP'];
			unset($this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE_TMP']);
		}
		unset($basketCode, $basketCodeList);

		unset($applyBlock);

		return $result;
	}

	/**
	 * Calculate additional sale coupons.
	 *
	 * @param array $applyCoupons			Coupons data.
	 * @return Result
	 */
	protected function calculateApplySaleAdditionalCoupons(array $applyCoupons)
	{
		$result = new Result;

		if (empty($applyCoupons))
			return $result;

		$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['ORDER'] = array();

		$discountId = array();
		foreach ($applyCoupons as $coupon)
			$discountId[] = $coupon['DISCOUNT_ID'];
		unset($coupon);

		$currentUseMode = $this->getUseMode();
		$this->setUseMode(self::USE_MODE_COUPONS);

		$this->loadDiscountByUserGroups(array('@DISCOUNT_ID' => $discountId));
		unset($discountId);

		$basketCodeList = $this->getBasketCodes(false);
		foreach ($basketCodeList as $basketCode)
		{
			$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE_TMP'] = $this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'];
			$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'] = $this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'];
		}
		unset($basketCode);

		$this->loadDiscountList();
		$executeResult = $this->executeDiscountList();
		if (!$executeResult->isSuccess())
			$result->addErrors($executeResult->getErrors());
		unset($executeResult);
		$this->setUseMode($currentUseMode);
		unset($currentUseMode);

		foreach ($basketCodeList as $basketCode)
		{
			$this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'] = $this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE_TMP'];
			unset($this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE_TMP']);
		}
		unset($basketCode);
		unset($basketCodeList);

		return $result;
	}

	/* additional coupons tools finish */
	/* compatibility tools */

	/**
	 * Fill order fields for deprecated discount classes.
	 *
	 * @return void
	 */
	protected function fillCompatibleOrderFields()
	{
		$this->orderData['USE_BASE_PRICE'] = $this->saleOptions['USE_BASE_PRICE'];
	}

	/* apply flag tools */

	/**
	 * Return apply status for basket discount.
	 *
	 * @internal
	 * @param string|int $basketCode		Basket item code.
	 * @param int $orderDiscountId			Order discount id.
	 * @param string $orderCouponId			Coupon.
	 * @return bool
	 */
	protected function getStatusApplyBasketDiscount($basketCode, $orderDiscountId, $orderCouponId)
	{
		$disable = false;
		if (
			$orderCouponId != ''
			&& !empty($this->applyResult['COUPON_LIST'][$orderCouponId])
			&& $this->applyResult['COUPON_LIST'][$orderCouponId] == 'N'
		)
		{
			$disable = true;
		}
		else
		{
			if (
				!empty($this->applyResult['DISCOUNT_LIST'][$orderDiscountId])
				&& $this->applyResult['DISCOUNT_LIST'][$orderDiscountId] == 'N'
			)
			{
				$disable = true;
			}
			if (!empty($this->applyResult['BASKET'][$basketCode]))
			{
				if (is_string($this->applyResult['BASKET'][$basketCode]))
					$disable = ($this->applyResult['BASKET'][$basketCode] == 'N');
				elseif (!empty($this->applyResult['BASKET'][$basketCode][$orderDiscountId]))
					$disable = ($this->applyResult['BASKET'][$basketCode][$orderDiscountId] == 'N');
			}
		}
		return !$disable;
	}

	/* instance tools */

	/**
	 * Round and correct discount calculation results.
	 * @internal
	 *
	 * @return void
	 */
	protected function normalizeDiscountResult()
	{
		$customPrice = isset($this->orderData['CUSTOM_PRICE_DELIVERY']) && $this->orderData['CUSTOM_PRICE_DELIVERY'] == 'Y';
		/** @noinspection PhpInternalEntityUsedInspection */
		$this->orderData['PRICE_DELIVERY_DIFF'] = (!$customPrice
			? PriceMaths::roundPrecision($this->orderData['PRICE_DELIVERY_DIFF'])
			: 0
		);
		if (!$customPrice)
		{
			if ($this->orderData['PRICE_DELIVERY_DIFF'] > 0)
				$this->orderData['PRICE_DELIVERY'] = $this->orderData['BASE_PRICE_DELIVERY'] - $this->orderData['PRICE_DELIVERY_DIFF'];
			else
				$this->orderData['PRICE_DELIVERY'] = PriceMaths::roundPrecision($this->orderData['PRICE_DELIVERY']);
		}
		unset($customPrice);

		if (!empty($this->orderData['BASKET_ITEMS']))
		{
			foreach (array_keys($this->orderData['BASKET_ITEMS']) as $basketCode)
			{
				$customPrice = $this->isCustomPriceByCode($basketCode);
				$basketItem = $this->orderData['BASKET_ITEMS'][$basketCode];
				$basketItem['DISCOUNT_PRICE'] = (!$customPrice
					? PriceMaths::roundPrecision($basketItem['DISCOUNT_PRICE'])
					: 0
				);
				if (!$customPrice)
				{
					if ($basketItem['DISCOUNT_PRICE'] > 0)
						$basketItem['PRICE'] = $basketItem['BASE_PRICE'] - $basketItem['DISCOUNT_PRICE'];
					else
						$basketItem['PRICE'] = PriceMaths::roundPrecision($basketItem['PRICE']);
				}
				$this->orderData['BASKET_ITEMS'][$basketCode] = $basketItem;
			}
			unset($basketItem, $customPrice, $basketCode);
		}
	}

	/**
	 * Return instance index for order.
	 *
	 * @internal
	 * @param Order $order			Order.
	 * @return string
	 */
	protected static function getInstanceIndexByOrder(Order $order)
	{
		return $order->getInternalId().'|0'.'|'.$order->getSiteId();
	}

	/**
	 * Return instance index for basket.
	 *
	 * @internal
	 *
	 * @param BasketBase $basket Basket.
	 * @param Context\BaseContext|null $context
	 *
	 * @return string
	 */
	protected static function getInstanceIndexByBasket(BasketBase $basket, Context\BaseContext $context = null)
	{
		if (!$context)
			return '0|'.$basket->getFUserId(false).'|'.$basket->getSiteId();
		return '0|-1|'.$basket->getSiteId().'|'.$context->getUserGroupsHash();
	}

	/**
	 * Return instance index for fuser.
	 *
	 * @internal
	 * @param string|int $fuser			Fuser id.
	 * @param string $site				Site id.
	 * @return string
	 */
	protected static function getInstanceIndexByFuser($fuser, $site)
	{
		return '0|'.$fuser.'|'.$site;
	}

	/**
	 * Return order property codes for translate to order fields.
	 *
	 * @return array
	 */
	protected static function getOrderPropertyCodes()
	{
		return array(
			'DELIVERY_LOCATION' => 'IS_LOCATION',
			'USER_EMAIL' => 'IS_EMAIL',
			'PAYER_NAME' => 'IS_PAYER',
			'PROFILE_NAME' => 'IS_PROFILE_NAME',
			'DELIVERY_LOCATION_ZIP' => 'IS_ZIP'
		);
	}

	/**
	 * Return empty apply block
	 *
	 * @return array
	 */
	protected static function getEmptyApplyBlock()
	{
		return array(
			'BASKET' => array(),
			'BASKET_ROUND' => array(),
			'ORDER' => array()
		);
	}

	/**
	 * Check use old api.
	 *
	 * @return bool
	 */
	private function isUsedDiscountCompatibility()
	{
		return (Compatible\DiscountCompatibility::isUsed() && Compatible\DiscountCompatibility::isInited());
	}

	/**
	 * Get description for old actions.
	 *
	 * @param array $stepResult		Action results.
	 * @return array
	 */
	private function getSimpleActionDescription(array $stepResult)
	{
		$result = array();
		if (!empty($stepResult['BASKET']))
		{
			$data = OrderDiscountManager::prepareDiscountDescription(
				OrderDiscountManager::DESCR_TYPE_SIMPLE,
				Loc::getMessage('BX_SALE_DISCOUNT_MESS_SIMPLE_DESCRIPTION_BASKET')
			);
			if ($data->isSuccess())
			{
				$result['BASKET'] = array(
					0 => $data->getData()
				);
			}
			unset($data);
		}
		if (!empty($stepResult['DELIVERY']))
		{
			$data = OrderDiscountManager::prepareDiscountDescription(
				OrderDiscountManager::DESCR_TYPE_SIMPLE,
				Loc::getMessage('BX_SALE_DISCOUNT_MESS_SIMPLE_DESCRIPTION_DELIVERY')
			);
			if ($data->isSuccess())
			{
				$result['DELIVERY'] = array(
					0 => $data->getData()
				);
			}
			unset($data);
		}
		if (empty($result))
		{
			$data = OrderDiscountManager::prepareDiscountDescription(
				OrderDiscountManager::DESCR_TYPE_SIMPLE,
				Loc::getMessage('BX_SALE_DISCOUNT_MESS_SIMPLE_DESCRIPTION_UNKNOWN')
			);
			if ($data->isSuccess())
			{
				$result['BASKET'] = array(
					0 => $data->getData()
				);
			}
			unset($data);
		}

		return $result;
	}

	private function showAdminError()
	{
		$iterator = \CAdminNotify::GetList(
			array(),
			array('MODULE_ID' => 'sale', 'TAG' => self::ERROR_ID)
		);
		$notify = $iterator->Fetch();
		unset($iterator);
		if (empty($notify))
		{
			$defaultLang = '';
			$messages = array();
			$languages = Main\Localization\LanguageTable::getList(array(
				'select' => array('ID', 'DEF'),
				'filter' => array('=ACTIVE' => 'Y')
			));
			while ($row = $languages->fetch())
			{
				if ($row['DEF'] == 'Y')
					$defaultLang = $row['ID'];
				$languageId = $row['ID'];
				Main\Localization\Loc::loadLanguageFile(
					__FILE__,
					$languageId
				);
				$messages[$languageId] = Main\Localization\Loc::getMessage(
					'BX_SALE_DISCOUNT_ERR_PARSE_ERROR',
					array('#LINK#' => '/bitrix/admin/settings.php?lang='.$languageId.'&mid=sale'),
					$languageId
				);
			}
			unset($row, $languages);

			\CAdminNotify::Add(array(
				'MODULE_ID' => 'sale',
				'TAG' => self::ERROR_ID,
				'ENABLE_CLOSE' => 'N',
				'NOTIFY_TYPE' => \CAdminNotify::TYPE_ERROR,
				'MESSAGE' => $messages[$defaultLang],
				'LANG' => $messages
			));
			unset($messages, $defaultLang);
		}
		unset($notify);
	}
}