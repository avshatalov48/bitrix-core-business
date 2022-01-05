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
	Bitrix\Sale\Discount\Context,
	Bitrix\Sale\Compatible,
	Bitrix\Sale\Discount\RuntimeCache,
	Bitrix\Currency;

Loc::loadMessages(__FILE__);

abstract class DiscountBase
{
	const EVENT_EXTEND_ORDER_DATA = 'onExtendOrderData';

	const USE_MODE_FULL = 0x00001;
	const USE_MODE_APPLY = 0x0002;
	const USE_MODE_MIXED = 0x0004;
	const USE_MODE_COUPONS = 0x0008;

	const EXECUTE_FIELD_PREFIX = 'EXECUTE_';

	const ERROR_ID = 'BX_SALE_DISCOUNT';

	const APPLY_MODE_ADD = 0x0001;
	const APPLY_MODE_DISABLE = 0x0002;
	const APPLY_MODE_LAST = 0x0004;
	const APPLY_MODE_FULL_DISABLE = 0x0008;
	const APPLY_MODE_FULL_LAST = 0x0010;

	const ROUND_MODE_BASKET_DISCOUNT = 0x0001;
	const ROUND_MODE_SALE_DISCOUNT = 0x0002;
	const ROUND_MODE_FINAL_PRICE = 0x0004;

	const ENTITY_BASKET_ITEM = 'BASKET_ITEM';
	const ENTITY_DELIVERY = 'DELIVERY';
	const ENTITY_ORDER = 'ORDER';

	/* Instances */
	/** @var array of DiscountBase and children */
	private static $instances = array();
	/* Instances end */

	/* System variables */
	/** @var bool  */
	protected $isClone = false;
	/** @var bool */
	protected $orderRefresh = false;
	/** @var bool */
	protected $newOrder = null;
	/** @var int */
	protected $useMode = null;
	/** @var  Discount\Context\BaseContext */
	protected $context;
	/** @var OrderBase|null */
	protected $order = null;
	/* System variables end */

	/* Calculate variables */
	/** @var array */
	protected $executeModuleFilter = array('all', 'sale', 'catalog');
	/** @var array */
	protected $loadedModules = array();
	/* Calculate variables end */

	/* Sale discount hit cache */
	/** @var array */
	protected $discountIds = null;
	/** @var array */
	protected $saleDiscountCache = array();
	/** @var string */
	protected $saleDiscountCacheKey = '';
	/* Sale discount hit cache end */

	/* Sale objects */
	/** @var Basket|null */
	protected $basket = null;

	/* Calculate data */
	/** @var array|null */
	protected $orderData = null;

	/* Calculate options */
	/** @var bool */
	protected $valid = true;
	/** @var array */
	protected $saleOptions = array();

	/* Product discounts for basket items */
	/** @var array */
	protected $basketDiscountList = array();
	/* Various basket items data */
	/** @var array */
	protected $basketItemsData = array();

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

	protected function __construct()
	{

	}

	public function __destruct()
	{

	}

	/* system methods */

	/**
	 * Clone entity.
	 *
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return DiscountBase
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

		return $discountClone;
	}

	/**
	 * Returns true if discount entity is cloned.
	 *
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
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
	 * Returns full refresh status value.
	 *
	 * @return bool
	 */
	public function isOrderRefresh()
	{
		return $this->orderRefresh;
	}

	/**
	 * Returns new order flag value.
	 *
	 * @return bool
	 */
	public function isOrderNew()
	{
		return $this->newOrder;
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
	 * Returns true if the data for calculations is loaded.
	 *
	 * @return bool
	 */
	protected function isLoaded()
	{
		return !empty($this->orderData);
	}

	/* system methods end */

	/**
	 * Builds discounts from order.
	 *
	 * @param OrderBase $order Order object.
	 * @return DiscountBase
	 */
	public static function buildFromOrder(OrderBase $order)
	{
		$instanceIndex = static::getInstanceIndexByOrder($order);
		if (!static::instanceExists($instanceIndex))
		{
			/** @var DiscountBase $discount */
			$discount = static::getInstance($instanceIndex);
			$discount->order = $order;
			$discount->context = new Context\User($order->getUserId());
			$discount->initInstanceData();
			unset($discount);
		}
		return static::getInstance($instanceIndex);
	}

	/**
	 * Builds discounts from basket. Basket doesn't have to have a order.
	 * Context describes user and user groups which use in
	 *
	 * @param BasketBase $basket Basket.
	 * @param Context\BaseContext $context Context.
	 *
	 * @return DiscountBase|null
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
			return null;

		$instanceIndex = static::getInstanceIndexByBasket($basket, $context);
		$discount = static::getInstance($instanceIndex);
		$discount->basket = $basket;
		$discount->context = $context;
		$discount->initInstanceData();
		unset($discount);

		return static::getInstance($instanceIndex);
	}

	/**
	 * Get discount by order basket.
	 *
	 * @param BasketBase $basket		Basket.
	 * @return DiscountBase
	 */
	public static function setOrder(BasketBase $basket)
	{
		$order = $basket->getOrder();
		if (!($order instanceof OrderBase))
		{
			throw new Main\InvalidOperationException();
		}
		$instanceIndex = static::getInstanceIndexByBasket($basket);
		if (!static::instanceExists($instanceIndex))
			return static::buildFromOrder($order);

		$newInstanceIndex = static::getInstanceIndexByOrder($order);
		if (!static::instanceExists($newInstanceIndex))
		{
			/** @var Discount $discount */
			$discount = static::getInstance($instanceIndex);
			$discount->basket = null;
			$discount->order = $order;
			$discount->context = new Context\User($order->getUserId());
			$discount->initInstanceFromOrder();
			unset($discount);
			static::migrateInstance($instanceIndex, $newInstanceIndex);
		}
		else
		{
			static::removeInstance($instanceIndex);
		}
		return static::getInstance($newInstanceIndex);
	}

	/* calculate methods */

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
	 * Calculate discounts.
	 *
	 * @return Result
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

		$this->initUseMode();

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();
		if ($this->isOrderExists() && !$this->isOrderNew())
		{
			if ($this->isOrderRefresh())
			{
				$this->setApplyResult(array());
				$couponClassName::useSavedCouponsForApply(true);
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

		if (!$this->isValidState())
			return $result;

		if ($process)
		{
			$couponClassName::setUseOnlySaleDiscounts($this->useOnlySaleDiscounts());
			unset($couponClassName);

			$this->resetOrderState();
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

	/* calculate methods end */

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
		$extMode = ($extMode === true);

		if (!$this->isOrderNew() && !$this->isLoaded())
		{
			$this->initUseMode();
			$this->loadOrderData();
		}

		$this->getApplyDiscounts();
		$this->getApplyPrices();
		if ($extMode)
			$this->remakingDiscountResult();

		$result = $this->discountResult;
		$result['FULL_DISCOUNT_LIST'] = $this->fullDiscountList;

		if ($extMode)
			unset($result['APPLY_BLOCKS']);

		return $result;
	}

	/* apply result methods finish */

	/**
	 * Verifies discounts before order save.
	 *
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	public function verify()
	{
		$result = new Result();

		$useMode = $this->getUseMode();
		if ($useMode == self::USE_MODE_APPLY || $useMode == self::USE_MODE_MIXED)
		{
			if (!$this->isValidState())
				return $result;
		}

		if (empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]))
			return $result;

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		$checkCoupons = $couponClassName::verifyApplied();
		if (!$checkCoupons->isSuccess())
		{
			$result->addError(new Main\Error(
				Loc::getMessage('BX_SALE_DISCOUNT_ERR_BAD_COUPONS_USED')
			));
			$errors = $checkCoupons->getErrors();
			$row = reset($errors);
			foreach ($row->getCustomData() as $coupon => $description)
			{
				$result->addError(new Main\Error(
					$coupon.' : '.$description
				));
			}
			unset($coupon, $description, $row, $errors);
		}
		unset($checkCoupons, $couponClassName);

		return $result;
	}

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
				{
					$registry = Registry::getInstance(static::getRegistryType());

					/** @var OrderHistory $orderHistory */
					$orderHistory = $registry->getOrderHistoryClassName();
					$orderHistory::addLog(
						'DISCOUNT',
						$orderId,
						'DISCOUNT_SAVED',
						null,
						null,
						array(),
						$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
					);
				}

			}
			unset($saveResult);
		}

		if ($orderId > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::collectEntityFields('DISCOUNT', $orderId);
		}

		return $result;
	}

	public function isValidState()
	{
		return $this->valid === true;
	}

	protected function setValidState($value)
	{
		if (!is_bool($value))
			return;
		$this->valid = $value;
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

		$orderDiscountConfig = array(
			'SITE_ID' => $this->getSiteId(),
			'CURRENCY' => $this->getCurrency()
		);
		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();
		$storageClassName::setManagerConfig($orderDiscountConfig);
		unset($storageClassName, $orderDiscountConfig);
	}

	/**
	 * Initial instance data after set order.
	 *
	 * @return void
	 */
	protected function initInstanceFromOrder()
	{
		$this->setNewOrder();
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
		return false;
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
	 * Return current basket.
	 *
	 * @return BasketBase
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
			return ($this->getOrder()->getBasket() instanceof BasketBase);
		else
			return ($this->basket instanceof BasketBase);
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
	 * Initialization of the discount calculation mode.
	 *
	 * @return void
	 */
	protected function initUseMode()
	{
		$this->setUseMode(self::USE_MODE_FULL);
		if ($this->isOrderExists() && !$this->isOrderNew())
		{
			if ($this->isOrderRefresh())
				$this->setUseMode(self::USE_MODE_FULL);
			elseif ($this->isOrderChanged())
				$this->setUseMode(self::USE_MODE_MIXED);
			elseif ($this->getOrder()->getCalculateType() == $this->getOrder()::SALE_ORDER_CALC_TYPE_REFRESH)
				$this->setUseMode(self::USE_MODE_FULL);
			else
				$this->setUseMode(self::USE_MODE_APPLY);
		}
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

		if (!$this->isLoaded())
			$this->fillEmptyOrderData();

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

		$dataResult = $this->loadBasketStoredData();
		if (!$dataResult->isSuccess())
			$result->addErrors($dataResult->getErrors());
		unset($dataResult);

		return $result;
	}

	/**
	 * Fill empty order data.
	 *
	 * @return void
	 */
	protected function fillEmptyOrderData()
	{
		/** @var BasketBase $basket*/
		$basket = $this->getBasket();
		$siteId = $this->getSiteId();
		$this->orderData = [
			'ID' => 0,
			'USER_ID' => $this->context->getUserId(),
			'USER_GROUPS' => $this->context->getUserGroups(),
			'SITE_ID' => $siteId,
			'LID' => $siteId,  // compatibility only
			'ORDER_PRICE' => $basket->getBasePrice(),
			'ORDER_WEIGHT' => $basket->getWeight(),
			'CURRENCY' => $this->getCurrency(),
			'PERSON_TYPE_ID' => 0,
			'RECURRING_ID' => null,
			'BASKET_ITEMS' => [],
			'ORDER_PROP' => []
		];

		if ($this->isOrderExists())
		{
			$order = $this->getOrder();

			$this->orderData['ID'] = $order->getId();
			$this->orderData['USER_ID'] = $order->getUserId();
			$this->orderData['ORDER_PRICE'] = $order->getBasePrice();
			$this->orderData['PERSON_TYPE_ID'] = $order->getPersonTypeId();
			$this->orderData['RECURRING_ID'] = $order->getField('RECURRING_ID');

			/** @var \Bitrix\Sale\PropertyValueCollection $propertyCollection */
			$propertyCollection = $order->getPropertyCollection();
			/** @var \Bitrix\Sale\PropertyValue $orderProperty */
			foreach ($propertyCollection as $orderProperty)
				$this->orderData['ORDER_PROP'][$orderProperty->getPropertyId()] = $orderProperty->getValue();
			unset($orderProperty);
			foreach ($this->getOrderPropertyCodes() as $propertyCode => $attribute)
			{
				$this->orderData[$propertyCode] = '';
				$orderProperty = $propertyCollection->getAttribute($attribute);
				if ($orderProperty instanceof PropertyValue)
					$this->orderData[$propertyCode] = $orderProperty->getValue();
				unset($orderProperty);
			}
			unset($propertyCode, $attribute);
			unset($propertyCollection);

			unset($order);
		}
		unset($siteId);
		unset($basket);
	}

	/**
	 * Get basket data from owner entity.
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function loadBasket()
	{
		$result = new Result;

		if (!$this->isBasketExist())
			throw new Main\ObjectNotFoundException('Entity "Basket" not found');
		elseif (!$this->isBasketNotEmpty())
			return $result;

		/** @var BasketBase $basket */
		$basket = $this->getBasket();
		/** @var BasketItemBase $basketItem */
		foreach ($basket as $basketItem)
		{
			if (!$basketItem->canBuy())
				continue;
			$this->orderData['BASKET_ITEMS'][$basketItem->getBasketCode()] = $this->getBasketItemFields($basketItem);
		}
		unset($basketItem, $basket);

		return $result;
	}

	/**
	 * Returns array with basket item field values.
	 *
	 * @param BasketItemBase $basketItem	Basket collection item.
	 * @return array
	 */
	protected function getBasketItemFields(BasketItemBase $basketItem)
	{
		$item = $basketItem->getFieldValues();
		$item['BASE_PRICE'] = $basketItem->getField('BASE_PRICE');
		unset($item['DATE_INSERT']);
		unset($item['DATE_UPDATE']);
		unset($item['DATE_REFRESH']);
		$item['PROPERTIES'] = $basketItem->getPropertyCollection()->getPropertyValues();
		if (!isset($item['DISCOUNT_PRICE']))
			$item['DISCOUNT_PRICE'] = 0;
		if ($item['BASE_PRICE'] === null)
			$item['BASE_PRICE'] = $item['PRICE'] + $item['DISCOUNT_PRICE'];
		$item['ACTION_APPLIED'] = 'N';
		return $item;
	}

	/**
	 * Load order config for exists order.
	 *
	 * @return void
	 */
	protected function loadOrderConfig()
	{
		$this->setValidState(true);
		$this->loadDefaultOrderConfig();

		if (!$this->isOrderExists()
			|| $this->isOrderNew()
			|| $this->getUseMode() == self::USE_MODE_FULL
		)
			return;

		/** @var OrderDiscountBase $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();
		$entityData = $storageClassName::loadOrderStoredDataFromDb(
			$this->getOrder()->getId(),
			$storageClassName::STORAGE_TYPE_ORDER_CONFIG
		);
		if (!$this->validateLoadedOrderConfig($entityData))
		{
			$this->setValidState(false);
			return;
		}
		$this->applyLoadedOrderConfig($entityData);
		if (isset($entityData['OLD_ORDER']))
			$this->setValidState(false);
		unset($entityData);

		$this->loadRoundConfig();
	}

	/**
	 * Returns the current module settings required for calculating discounts.
	 *
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function getModuleSettings()
	{
		return array(
			'USE_BASE_PRICE' => Main\Config\Option::get('sale', 'get_discount_percent_from_base_price'),
			'SALE_DISCOUNT_ONLY' => Main\Config\Option::get('sale', 'use_sale_discount_only'),
			'APPLY_MODE' => Main\Config\Option::get('sale', 'discount_apply_mode')
		);
	}

	/**
	 * Load default order config for order.
	 *
	 * @return void
	 */
	protected function loadDefaultOrderConfig()
	{
		$this->saleOptions = $this->getModuleSettings();
	}

	/**
	 * Validate loaded order config.
	 *
	 * @param mixed $config		Order configuration.
	 * @return bool
	 */
	protected function validateLoadedOrderConfig($config)
	{
		if (empty($config) || !is_array($config))
			return false;
		if (empty($config['OPTIONS']) || !is_array($config['OPTIONS']))
			return false;
		return true;
	}

	/**
	 * Set loaded order settings.
	 *
	 * @param array $data		Order settings from database.
	 * @return void
	 */
	protected function applyLoadedOrderConfig(array $data)
	{
		if (!empty($data['OPTIONS']) && is_array($data['OPTIONS']))
		{
			foreach (array_keys($this->saleOptions) as $key)
			{
				if (isset($data['OPTIONS'][$key]))
					$this->saleOptions[$key] = $data['OPTIONS'][$key];
			}
			unset($key);
		}
	}

	/**
	 * Load discounts for exists order.
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
		if ($this->isOrderNew() || $this->getUseMode() == self::USE_MODE_FULL)
			return $result;

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();
		$applyResult = $storageClassName::loadResultFromDb(
			$order->getId(),
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
					$discountData['ACTIONS_DESCR'] = $this->formatDescription($discountData['ACTIONS_DESCR']);
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
							$couponClassName::setApplyByProduct($discount, array($discount['COUPON_ID']));
						}
					}
					unset($discountList);
				}

				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if ($discount['COUPON_ID'] != '')
							$couponClassName::setApply($discount['COUPON_ID'], $discount['RESULT']);
					}
					unset($discount);
				}

				$this->discountResultCounter = $counter + 1;
			}
			unset($counter, $applyBlock);
		}

		if (!empty($applyResultData['STORED_ACTION_DATA']) && is_array($applyResultData['STORED_ACTION_DATA']))
			$this->discountStoredActionData = $applyResultData['STORED_ACTION_DATA'];

		unset($applyResultData, $applyResult);

		return $result;
	}

	/**
	 * Load basket stored data for order.
	 *
	 * @return Result
	 */
	protected function loadBasketStoredData()
	{
		$result = new Result;

		$this->basketItemsData = [];

		if (!$this->isOrderExists())
			return $result;

		$order = $this->getOrder();
		if ($this->isOrderNew() || $this->getUseMode() == self::USE_MODE_FULL)
			return $result;

		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();
		$basketData = $storageClassName::loadStoredDataFromDb(
			$order->getId(),
			$storageClassName::STORAGE_TYPE_BASKET_ITEM
		);

		if (empty($basketData))
			return $result;

		$basketCodeList = $this->getBasketCodes(false);
		if (!empty($basketCodeList))
		{
			foreach ($basketCodeList as $basketCode)
			{
				if (!isset($this->forwardBasketTable[$basketCode]))
					continue;
				$basketId = $this->forwardBasketTable[$basketCode];
				if (!isset($basketData[$basketId]) || !is_array($basketData[$basketId]))
					continue;
				$this->addBasketItemValues($basketCode, $basketData[$basketId]);
			}
			unset($basketId, $basketCode);
		}
		unset($basketCodeList);
		unset($basketData, $storageClassName);
		unset($order);

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
		$this->getRoundForBasePrices();
		$this->checkBasketDiscounts();

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();
		$couponClassName::clearApply();
		$basketDiscountResult = $this->calculateFullBasketDiscount();
		if (!$basketDiscountResult->isSuccess())
			$result->addErrors($basketDiscountResult->getErrors());
		unset($basketDiscountResult);
		if (!$result->isSuccess())
			return $result;

		if ($this->isRoundMode(self::ROUND_MODE_BASKET_DISCOUNT))
			$this->roundFullBasketPrices();

		if (!$this->isBasketLastDiscount())
		{
			$this->loadDiscountByUserGroups();
			$this->loadDiscountList();
			$executeResult = $this->executeDiscountList();
			if (!$executeResult->isSuccess())
				$result->addErrors($executeResult->getErrors());
			unset($executeResult);
			if (!$result->isSuccess())
				return $result;
		}

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

		if (!$this->isValidState())
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
				$this->roundChangedBasketPrices();
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

		if (!$this->isValidState())
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

			$this->getRoundForBasePrices();
			$this->checkBasketDiscounts();

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
				$this->roundChangedBasketPrices();
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
		$orderId = $this->getOrder()->getId();

		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();
		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();
		/** @var EntityMarker $entityMarkerClassName */
		$entityMarkerClassName = $this->getEntityMarkerClassName();

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
			$couponClassName::finalApply();
			$couponsResult = $couponClassName::saveApplied();
			if (!$couponsResult->isSuccess())
			{
				$process = false;
				$error = new Main\Error(
					$this->prepareCouponsResult($couponsResult)
				);
				$result->addError($error);
				$markerResult = new Result();
				$markerResult->addWarning($error);
				$markerResult->addWarning(new Main\Error(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_BAD_PRICES')
				));
				$entityMarkerClassName::addMarker(
					$this->getOrder(),
					$this->getOrder(),
					$markerResult
				);
				unset($markerResult, $error);
			}
		}
		if ($process)
		{
			$couponsResult = $this->saveCoupons();
			if (!$couponsResult->isSuccess())
			{
				$process = false;
				$result->addErrors($couponsResult->getErrors());
			}
		}

		if ($process)
		{
			$storageClassName::deleteByOrder($orderId);

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
			$config = $this->getOrderConfig();
			$dataResult = $storageClassName::saveOrderStoredData(
				$orderId,
				$storageClassName::STORAGE_TYPE_ORDER_CONFIG,
				$config
			);
			if (!$dataResult->isSuccess())
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
					self::ERROR_ID
				));
			}
			unset($dataResult, $config);

			$config = array(
				'MODE' => $this->roundApplyMode,
				'CONFIG' => $this->roundApplyConfig
			);
			$dataResult = $storageClassName::saveOrderStoredData(
				$orderId,
				$storageClassName::STORAGE_TYPE_ROUND_CONFIG,
				$config,
				array('ALLOW_UPDATE' => 'Y')
			);
			if (!$dataResult->isSuccess())
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
					self::ERROR_ID
				));
			}
			unset($dataResult, $config);

			if (!empty($this->discountStoredActionData))
			{
				$dataResult = $storageClassName::saveOrderStoredData(
					$orderId,
					$storageClassName::STORAGE_TYPE_DISCOUNT_ACTION_DATA,
					$this->discountStoredActionData,
					array('ALLOW_UPDATE' => 'Y')
				);
				if (!$dataResult->isSuccess())
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
				}
				unset($dataResult);
			}

			$dataResult = $this->saveBasketStoredData($this->getBasketCodes(true));
			if (!$dataResult->isSuccess())
			{
				$result->addErrors($dataResult->getErrors());
			}
			unset($dataResult);
		}

		if ($process)
		{
			if ($couponClassName::usedByManager())
				$couponClassName::clear(true);
		}

		return $result;
	}

	protected function prepareCouponsResult(Main\Result $couponsResult): string
	{
		$commonList = [];
		$errorList = $couponsResult->getErrors();
		$error = reset($errorList);

		/** @var array $list */
		$list = $error->getCustomData();
		foreach (array_keys($list) as $coupon)
		{
			$commonList[] = $coupon.' - '.$list[$coupon];
		}
		return $error->getMessage().': '.implode(', ', $commonList);

	}

	/**
	 * Returns order configuration for save to database.
	 *
	 * @return array
	 */
	protected function getOrderConfig()
	{
		return array(
			'OPTIONS' => $this->saleOptions,
		);
	}

	/**
	 * Save basket items stored data.
	 *
	 * @param array $basketCodeList		Code list.
	 * @return Result
	 */
	protected function saveBasketStoredData(array $basketCodeList)
	{
		$result = new Result();
		if (empty($basketCodeList))
			return $result;
		$useMode = $this->getUseMode();
		if ($useMode != self::USE_MODE_FULL && $useMode != self::USE_MODE_MIXED)
			return $result;

		$itemsData = [];
		foreach ($basketCodeList as $basketCode)
		{
			if (!isset($this->basketItemsData[$basketCode]))
				continue;
			$data = $this->prepareBasketItemStoredData($basketCode);
			if ($data === null)
				continue;
			$basketId = $this->forwardBasketTable[$basketCode];
			$itemsData[$basketId] = [
				'ENTITY_ID' => $basketId,
				'ENTITY_VALUE' => $basketId,
				'ENTITY_DATA' => $data
			];
		}
		unset($data, $basketCode);
		if (!empty($itemsData))
		{
			$orderId = $this->getOrder()->getId();
			/** @var OrderDiscount $storageClassName */
			$storageClassName = $this->getOrderDiscountClassName();
			$dataResult = $storageClassName::saveStoredDataBlock(
				$orderId,
				$storageClassName::STORAGE_TYPE_BASKET_ITEM,
				$itemsData,
				['ALLOW_UPDATE' => 'Y', 'DELETE_MISSING' => 'Y']
			);
			if (!$dataResult->isSuccess())
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
					self::ERROR_ID
				));
			}
			unset($dataResult, $storageClassName);
		}
		unset($itemsData);

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

		if (!$this->isValidState())
			return $result;

		$basketResult = $this->getBasketTables();
		if (!$basketResult->isSuccess())
		{
			$process = false;
			$result->addErrors($basketResult->getErrors());
		}

		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();
		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		$rulesList = array();
		$roundList = array();
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
						$rulesList[] = array(
							'RULE_ID' => $discount['RULE_ID'],
							'APPLY' => $discount['RESULT']['APPLY'],
							'DESCR_ID' => (isset($discount['RULE_DESCR_ID']) ? (int)$discount['RULE_DESCR_ID'] : 0),
							'DESCR' => $discount['RESULT']['DESCR_DATA']['BASKET'],
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
					$roundList[] = array(
						'RULE_ID' => $row['RULE_ID'],
						'APPLY' => 'Y'
					);
				}
				unset($row);
			}
		}

		if ($process)
		{
			$ruleResult = $storageClassName::updateResultBlock($orderId, $rulesList);
			if (!$ruleResult->isSuccess())
			{

			}
			unset($ruleResult);
			$roundResult = $storageClassName::updateRoundBlock($orderId, $roundList);
			if (!$roundResult->isSuccess())
			{

			}
			unset($roundResult);
		}

		if ($process)
		{
			if (!empty($this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]))
			{
				$couponClassName::finalApply();
				$couponClassName::saveApplied();
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
			if (!empty($this->discountStoredActionData))
			{
				$dataResult = $storageClassName::saveOrderStoredData(
					$orderId,
					$storageClassName::STORAGE_TYPE_DISCOUNT_ACTION_DATA,
					$this->discountStoredActionData,
					array('ALLOW_UPDATE' => 'Y')
				);
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
			if ($couponClassName::usedByManager())
				$couponClassName::clear(true);
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
		$result = $this->saveApply();

		if ($result->isSuccess())
		{
			$basketCodeList = array_merge(
				$this->getBasketCodes(false),
				$this->getBasketCodes(true)
			);
			$dataResult = $this->saveBasketStoredData($basketCodeList);
			if (!$dataResult->isSuccess())
			{
				$result->addErrors($dataResult->getErrors());
			}
			unset($dataResult, $basketCodeList);
		}

		return $result;
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
			/** @var OrderDiscount $storageClassName */
			$storageClassName = $this->getOrderDiscountClassName();

			$orderId = $this->getOrder()->getId();
			foreach ($this->couponsCache as $orderCouponId => $couponData)
			{
				if ($couponData['ID'] > 0)
					continue;
				$fields = $couponData;
				$fields['ORDER_ID'] = $orderId;
				$couponResult = $storageClassName::saveCoupon($fields);
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
			unset($storageClassName);
		}
		return $result;
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

		$orderId = $this->getOrder()->getId();

		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter];
		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();

		$rulesList = array();
		if (!empty($applyBlock['BASKET']))
		{
			foreach ($applyBlock['BASKET'] as $basketCode => $discountList)
			{
				$commonFields = $this->getEntitySaveIdentifier([
					'ENTITY_TYPE' => self::ENTITY_BASKET_ITEM,
					'ENTITY_CODE' => $basketCode
				]);
				if ($commonFields === null)
					continue;
				foreach ($discountList as $discount)
				{
					$rulesList[] = $commonFields + [
						'ORDER_DISCOUNT_ID' => $discount['DISCOUNT_ID'],
						'COUPON_ID' => $discount['COUPON_ID'],
						'APPLY' => $discount['RESULT']['APPLY'],
						'DESCR' => $discount['RESULT']['DESCR_DATA']
					];
				}
				unset($discount);
			}
			unset($discount, $commonFields, $discountList, $basketCode);
		}
		if (!empty($applyBlock['ORDER']))
		{
			foreach ($applyBlock['ORDER'] as $discount)
			{
				if (!empty($discount['RESULT']['BASKET']))
				{
					foreach ($discount['RESULT']['BASKET'] as $basketCode => $applyData)
					{
						$commonFields = $this->getEntitySaveIdentifier([
							'ENTITY_TYPE' => self::ENTITY_BASKET_ITEM,
							'ENTITY_CODE' => $basketCode
						]);
						if ($commonFields === null)
							continue;
						$rulesList[] = $commonFields + [
							'ORDER_DISCOUNT_ID' => $discount['DISCOUNT_ID'],
							'COUPON_ID' => $discount['COUPON_ID'],
							'APPLY' => $applyData['APPLY'],
							'ACTION_BLOCK_LIST' => $applyData['ACTION_BLOCK_LIST'],
							'DESCR' => $applyData['DESCR_DATA']
						];
					}
					unset($commonFields, $basketCode, $applyData);
				}
				if (!empty($discount['RESULT']['DELIVERY']))
				{
					$commonFields = $this->getEntitySaveIdentifier([
						'ENTITY_TYPE' => self::ENTITY_DELIVERY,
						'ENTITY_CODE' => $discount['RESULT']['DELIVERY']['DELIVERY_ID']
					]);
					if ($commonFields === null)
						continue;
					$rulesList[] = $commonFields + [
						'ORDER_DISCOUNT_ID' => $discount['DISCOUNT_ID'],
						'COUPON_ID' => $discount['COUPON_ID'],
						'APPLY' => $discount['RESULT']['DELIVERY']['APPLY'],
						'DESCR' => $discount['RESULT']['DELIVERY']['DESCR_DATA']
					];
					unset($commonFields);
				}
			}
			unset($discount);
		}

		if (!empty($rulesList))
		{
			$this->normalizeNewResultRows($rulesList);
			$blockResult = $storageClassName::addResultBlock($orderId, $rulesList);
			if (!$blockResult->isSuccess())
			{
				$result->addError(new Main\Entity\EntityError(
					Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
					self::ERROR_ID
				));
			}
			unset($blockResult);
		}
		unset($rulesList);

		if (!empty($applyBlock['BASKET_ROUND']))
		{
			$roundList = array();
			foreach ($applyBlock['BASKET_ROUND'] as $basketCode => $roundData)
			{
				$commonFields = $this->getEntitySaveIdentifier([
					'ENTITY_TYPE' => self::ENTITY_BASKET_ITEM,
					'ENTITY_CODE' => $basketCode
				]);
				if ($commonFields === null)
					continue;
				$roundList[] = $commonFields + [
					'APPLY' => $roundData['APPLY'],
					'ROUND_RULE' => $roundData['ROUND_RULE']
				];
			}
			unset($commonFields, $roundData, $basketCode);
			if (!empty($roundList))
			{
				$this->normalizeNewResultRows($roundList);
				$blockResult = $storageClassName::addRoundBlock($orderId, $roundList);
				if (!$blockResult->isSuccess())
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_SAVE_APPLY_RULES'),
						self::ERROR_ID
					));
				}
				unset($blockResult);
			}
			unset($roundList);
		}

		unset($applyBlock);

		return $result;
	}

	/**
	 * Fill common system fields for new discount results.
	 *
	 * @param array $rows	Prepared new discount results.
	 * @return void
	 */
	protected function normalizeNewResultRows(array &$rows)
	{
		if (empty($rows))
			return;

		foreach (array_keys($rows) as $index)
		{
			$rows[$index]['APPLY_BLOCK_COUNTER'] = $this->discountResultCounter;
			if (isset($rows[$index]['ORDER_DISCOUNT_ID']))
				$rows[$index]['MODULE_ID'] = $this->discountsCache[$rows[$index]['ORDER_DISCOUNT_ID']]['MODULE_ID'];
			if (isset($rows[$index]['COUPON_ID']))
				$rows[$index]['COUPON_ID'] = ($rows[$index]['COUPON_ID'] != '' ? $this->couponsCache[$rows[$index]['COUPON_ID']]['ID'] : 0);
		}
		unset($index);
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

		$key = $this->getConditionField();
		$executeKey = self::getExecuteFieldName($key);

		if (empty($this->saleDiscountCache[$this->saleDiscountCacheKey][$this->currentStep['cacheIndex']][$key]))
			return false;

		$discountLink = &$this->saleDiscountCache[$this->saleDiscountCacheKey][$this->currentStep['cacheIndex']];

		if (!array_key_exists($executeKey, $discountLink))
		{
			$checkOrder = null;

			$evalCode = '$checkOrder='.$discountLink[$key].';';
			if (PHP_MAJOR_VERSION >= 7)
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
			if (!empty($discount['APPLICATION']) && !$this->loadDiscountModules($this->discountsCache[$this->currentStep['discountId']]['MODULES']))
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
			$executeKey = self::getExecuteFieldName('APPLICATION');
			if (!array_key_exists($executeKey, $discount))
			{
				$discount[$executeKey] = null;

				$evalCode = '$discount["'.$executeKey.'"] = '.$discount['APPLICATION'].';';
				if (PHP_MAJOR_VERSION >= 7)
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
			if (is_callable($discount[$executeKey]))
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
				$discount[$executeKey]($this->orderData);
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
	 * Check product discount list for basket items.
	 * @internal
	 *
	 * @return void
	 */
	protected function checkBasketDiscounts()
	{
		$useMode = $this->getUseMode();
		if (
			$useMode === self::USE_MODE_FULL
			|| $useMode == self::USE_MODE_MIXED
		)
		{
			$basketCodeList = $this->getBasketCodes(true);
			if (!empty($basketCodeList))
			{
				$basket = $this->getBasket();
				foreach ($basketCodeList as $code)
				{
					$basketItem = $basket->getItemByBasketCode($code);
					if ($basketItem instanceof BasketItemBase)
					{
						if (!isset($this->basketDiscountList[$code]))
						{
							$this->basketDiscountList[$code] = $basketItem->getField('DISCOUNT_LIST');
							if ($this->basketDiscountList[$code] === null)
								unset($this->basketDiscountList[$code]);
						}
					}
				}
				unset($basketItem, $code, $basket);
			}
			unset($basketCodeList);
		}
		unset($useMode);
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

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

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

					$couponClassName::setApplyByProduct($itemData, array($orderCouponId));
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
						$applyBlock[$basketCode][$index]['RESULT']['DESCR'] = $this->formatDescription($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
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
				if ($applyBlock[$basketCode][$index]['RESULT']['APPLY'] == 'Y')
					$this->orderData['BASKET_ITEMS'][$basketCode]['ACTION_APPLIED'] = 'Y';
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

				if (!isset($this->discountsCache[$orderDiscountId]))
				{
					$result->addError(new Main\Entity\EntityError(
						Loc::getMessage('BX_SALE_DISCOUNT_ERR_APPLY_WITHOUT_EXT_DISCOUNT'),
						self::ERROR_ID
					));
					return $result;
				}

				$orderApplication = (
					!empty($this->discountsCache[$orderDiscountId]['APPLICATION'])
					? $this->discountsCache[$orderDiscountId]['APPLICATION']
					: null
				);
				if (!empty($orderApplication) && !$this->loadDiscountModules($this->discountsCache[$orderDiscountId]['MODULES']))
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
						$applyBlock[$basketCode][$index]['RESULT']['DESCR'] = $this->formatDescription($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
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
					$this->orderData['BASKET_ITEMS'][$basketCode]['ACTION_APPLIED'] = 'Y';
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
					/** @var OrderDiscount $storageClassName */
					$storageClassName = $this->getOrderDiscountClassName();
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
						$couponsApply = $storageClassName::calculateApplyCoupons(
							$moduleId,
							$moduleCoupons,
							$currentBasket,
							$params
						);
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

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		$this->orderData['DISCOUNT_RESULT'] = Discount\Actions::getActionResult();
		$this->orderData['DISCOUNT_DESCR'] = Discount\Actions::getActionDescription();
		if (!empty($this->orderData['DISCOUNT_RESULT']) && is_array($this->orderData['DISCOUNT_RESULT']))
		{
			$stepResult = $this->getStepResult($this->orderData);
		}
		else
		{
			$stepResult = Discount\Result\CompatibleFormat::getStepResult(
				$this->orderData,
				$this->currentStep['oldData']
			);
			if (!empty($stepResult))
			{
				if (empty($this->orderData['DISCOUNT_DESCR']) || !is_array($this->orderData['DISCOUNT_DESCR']))
					$this->orderData['DISCOUNT_DESCR'] = Discount\Result\CompatibleFormat::getDiscountDescription($stepResult);
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
				$couponClassName::setApply($orderCouponId, $stepResult);
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
			$couponApply = $couponClassName::setApply($this->couponsCache[$orderCouponId]['COUPON'], $stepResult);
			unset($couponApply);
		}

		if ($applied)
		{
			$this->tryToRevertApplyStatusInBlocks($stepResult);

			if (!empty($stepResult['BASKET']))
			{
				foreach ($stepResult['BASKET'] as $basketCode => $itemResult)
				{
					if ($itemResult['APPLY'] == 'Y')
						$this->orderData['BASKET_ITEMS'][$basketCode]['ACTION_APPLIED'] = 'Y';
				}
				unset($basketCode, $itemResult);
			}

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
				//TODO: remove this hack
				if (
					!empty($rowDescription['REVERT_APPLY']) &&
					$rowDescription['VALUE_ACTION'] === Discount\Formatter::VALUE_ACTION_CUMULATIVE
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

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		$this->orderData['DISCOUNT_RESULT'] = Discount\Actions::getActionResult();
		if (!empty($this->orderData['DISCOUNT_RESULT']) && is_array($this->orderData['DISCOUNT_RESULT']))
			$stepResult = $this->getStepResult($this->orderData);
		else
			$stepResult = Discount\Result\CompatibleFormat::getStepResult(
				$this->orderData, $this->currentStep['oldData']
			);
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
										$this->orderData['BASKET_ITEMS'][$basketCode]['ACTION_APPLIED'] = 'Y';
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
			$couponApply = $couponClassName::setApply($this->couponsCache[$orderCouponId]['COUPON'], $stepResult);
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
			/** @var OrderDiscount $storageClassName */
			$storageClassName = $this->getOrderDiscountClassName();
			$entityData = $storageClassName::loadOrderStoredDataFromDb(
				$orderId,
				$storageClassName::STORAGE_TYPE_ROUND_CONFIG
			);
			unset($orderId);

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

			/** @var OrderDiscount $storageClassName */
			$storageClassName = $this->getOrderDiscountClassName();

			$result = $storageClassName::roundBasket(
				$basket,
				array(),
				$orderData
			);
			foreach ($result as $basketCode => $roundResult)
			{
				if (empty($roundResult) || !is_array($roundResult))
					continue;
				if (!$this->isExistBasketItem($basketCode))
					continue;
				$this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'] = $roundResult['PRICE'];
				$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_PRICE'] = $roundResult['DISCOUNT_PRICE'];

				$roundBlock[$basketCode] = array(
					'APPLY' => 'Y',
					'ROUND_RULE' => $roundResult['ROUND_RULE']
				);
			}
			unset($basketCode, $roundResult, $result);
			unset($storageClassName);
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

				/** @var OrderDiscount $storageClassName */
				$storageClassName = $this->getOrderDiscountClassName();

				$result = $storageClassName::roundBasket(
					$basket,
					$roundData,
					$orderData
				);
				foreach ($result as $basketCode => $roundResult)
				{
					if (empty($roundResult) || !is_array($roundResult))
						continue;
					if (!$this->isExistBasketItem($basketCode))
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
	 * Round only changed prices.
	 *
	 * @return void
	 */
	protected function roundChangedBasketPrices()
	{
		$basketCodeList = array();
		$applyBlock = $this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter];
		switch ($this->getUseMode())
		{
			case self::USE_MODE_APPLY:
				if (!empty($applyBlock['BASKET']))
				{
					foreach (array_keys($applyBlock['BASKET']) as $basketCode)
					{
						$basketCodeList[$basketCode] = $basketCode;
					}
					unset($basketCode);
				}
				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if (empty($discount['RESULT']['BASKET']))
							continue;
						foreach (array_keys($discount['RESULT']['BASKET']) as $basketCode)
						{
							$basketCodeList[$basketCode] = $basketCode;
						}
					}
					unset($basketCode, $discount);
				}
				break;
			case self::USE_MODE_MIXED:
				if (!empty($applyBlock['BASKET']))
				{
					foreach (array_keys($applyBlock['BASKET']) as $basketCode)
					{
						$basketCodeList[$basketCode] = $basketCode;
					}
					unset($basketCode);
				}
				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if (empty($discount['RESULT']['BASKET']))
							continue;
						foreach (array_keys($discount['RESULT']['BASKET']) as $basketCode)
						{
							$basketCodeList[$basketCode] = $basketCode;
						}
					}
					unset($basketCode, $discount);
				}
				foreach ($this->getBasketCodes(true) as $basketCode)
					$basketCodeList[$basketCode] = $basketCode;
				break;
		}

		if (!empty($basketCodeList))
		{
			$roundBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter]['BASKET_ROUND'];
			$orderData = $this->orderData;
			unset($orderData['BASKET_ITEMS']);
			$basket = array_intersect_key(
				$this->orderData['BASKET_ITEMS'],
				array_fill_keys($basketCodeList, true)
			);

			/** @var OrderDiscount $storageClassName */
			$storageClassName = $this->getOrderDiscountClassName();

			$result = $storageClassName::roundBasket(
				$basket,
				array(),
				$orderData
			);

			foreach ($result as $basketCode => $roundResult)
			{
				if (empty($roundResult) || !is_array($roundResult))
					continue;
				if (!$this->isExistBasketItem($basketCode))
					continue;
				$this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'] = $roundResult['PRICE'];
				$this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_PRICE'] = $roundResult['DISCOUNT_PRICE'];

				$roundBlock[$basketCode] = array(
					'APPLY' => 'Y',
					'ROUND_RULE' => $roundResult['ROUND_RULE']
				);
			}
			unset($basketCode, $roundResult, $result);
			unset($storageClassName);
			unset($basket, $orderData);
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

		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();

		$discountResult = $storageClassName::saveDiscount($discount, false);
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
				$discountData['ACTIONS_DESCR'] = $this->formatDescription($discountData['ACTIONS_DESCR']);
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

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		if (!is_array($coupon))
		{
			$couponData = $couponClassName::getEnteredCoupon($coupon, true);
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
	protected static function getStepResult(array $order)
	{
		$result = array();
		$stepResult = &$order['DISCOUNT_RESULT'];
		if (!empty($stepResult['BASKET']) && is_array($stepResult['BASKET']))
		{
			if (!isset($result['BASKET']))
				$result['BASKET'] = array();
			foreach ($stepResult['BASKET'] as $basketCode => $basketResult)
			{
				$result['BASKET'][$basketCode] = array(
					'APPLY' => 'Y',
					'DESCR' => Discount\Formatter::formatList($basketResult),
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

		$basket = [];
		if (!empty($this->orderData['BASKET_ITEMS']))
		{
			foreach ($this->orderData['BASKET_ITEMS'] as $basketCode => $basketItem)
			{
				$basket[$basketCode] = [
					'BASE_PRICE' => $basketItem['BASE_PRICE'],
					'PRICE' => $basketItem['PRICE'],
					'DISCOUNT' => $basketItem['DISCOUNT_PRICE']
				];
			}
			unset($basketCode, $basketItem);
		}

		$this->discountResult['PRICES'] = [
			'BASKET' => $basket
		];
		unset($basket);
	}

	/**
	 * Change result format.
	 *
	 * @return void
	 */
	protected function remakingDiscountResult()
	{
		$basket = [];
		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (!empty($applyBlock['BASKET']))
				{
					foreach ($applyBlock['BASKET'] as $basketCode => $discountList)
					{
						if (!isset($basket[$basketCode]))
							$basket[$basketCode] = [];
						foreach ($discountList as $discount)
						{
							$basket[$basketCode][] = [
								'DISCOUNT_ID' => $discount['DISCOUNT_ID'],
								'COUPON_ID' => $discount['COUPON_ID'],
								'APPLY' => $discount['RESULT']['APPLY'],
								'DESCR' => $discount['RESULT']['DESCR']
							];
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
									$basket[$basketCode] = [];
								$basket[$basketCode][] = [
									'DISCOUNT_ID' => $discount['DISCOUNT_ID'],
									'COUPON_ID' => $discount['COUPON_ID'],
									'APPLY' => $applyList['APPLY'],
									'DESCR' => $applyList['DESCR']
								];
							}
							unset($basketCode, $applyList);
						}
					}
					unset($discount);
				}
			}
			unset($counter, $applyBlock);
		}

		$this->discountResult['RESULT'] = [
			'BASKET' => $basket
		];
		unset($basket);
	}

	/* entities id tools */

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
	 * Returns data for save to database.
	 *
	 * @param array $entity
	 * @return array|null
	 */
	protected function getEntitySaveIdentifier(array $entity)
	{
		$result = null;

		switch ($entity['ENTITY_TYPE'])
		{
			case self::ENTITY_BASKET_ITEM:
				$basketCode = $entity['ENTITY_CODE'];
				if (isset($this->forwardBasketTable[$basketCode]))
				{
					$result = [
						'ENTITY_TYPE' => self::ENTITY_BASKET_ITEM,
						'ENTITY_ID' => (int)$this->forwardBasketTable[$basketCode],
						'ENTITY_VALUE' => (string)$this->forwardBasketTable[$basketCode]
					];
				}
				unset($basketCode);
				break;
			case self::ENTITY_ORDER:
				$result = [
					'ENTITY_TYPE' => self::ENTITY_ORDER,
					'ENTITY_ID' => (int)$entity['ENTITY_CODE'],
					'ENTITY_VALUE' => (string)$entity['ENTITY_CODE']
				];
				break;
		}

		return $result;
	}

	/* entities id tools finish */

	/**
	 * Returns exist custom price for basket item code.
	 *
	 * @param int $code			Basket code.
	 * @return bool
	 */
	protected function isCustomPriceByCode($code)
	{
		if (!$this->isExistBasketItem($code))
			return false;
		return $this->isCustomPrice($this->orderData['BASKET_ITEMS'][$code]);
	}

	/**
	 * Returns exist custom price for basket item.
	 *
	 * @param array $item			Basket item.
	 * @return bool
	 */
	protected static function isCustomPrice(array $item)
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
		if (!$this->isExistBasketItem($code))
			return false;
		return $this->isInSet($this->orderData['BASKET_ITEMS'][$code]);
	}

	/**
	 * Returns check item in set for basket item.
	 *
	 * @param array $item			Basket item.
	 * @return bool
	 */
	protected static function isInSet(array $item)
	{
		if (!empty($item['IN_SET']) && $item['IN_SET'] == 'Y')
			return true;
		return false;
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
	protected static function isNewBasketItem(array $item)
	{
		return (
			!isset($item['ID'])
			|| $item['ID'] <= 0
		);
	}

	/**
	 * Returns true, if allowed apply discounts to basket item.
	 *
	 * @param int|string $code			Basket code.
	 * @return bool
	 */
	protected function isFreezedBasketItemByCode($code)
	{
		if (!$this->isExistBasketItem($code))
			return false;
		return $this->isFreezedBasketItem($this->orderData['BASKET_ITEMS'][$code]);
	}

	/**
	 * Returns true, if allowed apply discounts to basket item.
	 *
	 * @param array $item			Basket item.
	 * @return bool
	 */
	protected static function isFreezedBasketItem(array $item)
	{
		return (static::isCustomPrice($item) || static::isInSet($item));
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
	 * Return true, if basket item exists.
	 *
	 * @param string|int $code	Basket item code.
	 * @return bool
	 */
	protected function isExistBasketItem($code)
	{
		if (!$this->isLoaded())
			return false;
		return isset($this->orderData['BASKET_ITEMS'][$code]);
	}

	/**
	 * Returns true, if changed children order entities.
	 *
	 * @return bool
	 */
	protected function isOrderChanged()
	{
		return $this->isMixedBasket();
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
	 * Returns basket codes for calculate.
	 *
	 * @param bool $full				Full or apply mode.
	 * @return array
	 */
	protected function getBasketCodes($full = true)
	{
		$result = [];
		if (empty($this->orderData['BASKET_ITEMS']))
			return $result;
		switch ($this->getUseMode())
		{
			case self::USE_MODE_FULL:
			case self::USE_MODE_COUPONS:
				foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
				{
					if ($this->isFreezedBasketItem($item))
						continue;
					$result[] = $code;
				}
				unset($code, $item);
				break;
			case self::USE_MODE_APPLY:
				foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
				{
					if (
						$this->isFreezedBasketItem($item)
						|| $this->isNewBasketItem($item)
						|| $this->isBasketItemChanged($code)
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
							!$this->isFreezedBasketItem($item)
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
							$this->isFreezedBasketItem($item)
							|| $this->isNewBasketItem($item)
							|| $this->isBasketItemChanged($code)
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

	protected function getAllowedBasketCodeList()
	{
		$result = [];
		if (empty($this->orderData['BASKET_ITEMS']))
			return $result;

		foreach ($this->orderData['BASKET_ITEMS'] as $code => $item)
		{
			if ($this->isFreezedBasketItem($item))
				continue;
			$result[] = $code;
		}
		unset($code, $item);

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
		$this->discountResult = [
			'APPLY_BLOCKS' => [],
			'DISCOUNT_LIST' => [],
			'COUPON_LIST' => []
		];
		$this->clearCurrentApplyBlock();
		$this->discountStoredActionData = [];
		$this->basketItemsData = [];
		$this->entityResultCache = [];
	}

	/**
	 * Fill result order data.
	 *
	 * @return array
	 */
	protected function fillDiscountResult()
	{
		$this->normalizeDiscountResult();
		$basketKeys = ['PRICE', 'DISCOUNT_PRICE', 'VAT_RATE', 'VAT_VALUE', 'CURRENCY'];
		$result = [
			'BASKET_ITEMS' => [],
			'CURRENCY' => $this->orderData['CURRENCY']
		];
		foreach ($this->orderData['BASKET_ITEMS'] as $index => $basketItem)
		{
			$result['BASKET_ITEMS'][$index] = [];
			foreach ($basketKeys as $key)
			{
				if (isset($basketItem[$key]))
					$result['BASKET_ITEMS'][$index][$key] = $basketItem[$key];
			}
			unset($key);
		}
		unset($index, $basketItem);

		return $result;
	}

	/**
	 * Internal. Fill current apply block empty data.
	 *
	 * @return void
	 */
	protected function clearCurrentApplyBlock()
	{
		$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter] = $this->getEmptyApplyBlock();
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
				if (!is_array($value))
					continue;
				$result['DELIVERY'][$index] = Discount\Formatter::formatRow($value);
				if ($result['DELIVERY'][$index] === null)
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
				if (!is_array($value))
					continue;
				$result['BASKET'][$index] = Discount\Formatter::formatRow($value);
				if ($result['BASKET'][$index] === null)
					unset($result['BASKET'][$index]);
			}
			unset($value, $index);
			if (!empty($result['BASKET']))
				$result['BASKET'] = implode(', ', $result['BASKET']);
		}
		return $result;
	}

	/**
	 * Set order parameters to their original state before the start of calculations.
	 *
	 * @return void
	 */
	protected function resetOrderState()
	{
		$this->resetPrices();
		$this->resetOrderPrice();
		$this->resetDiscountAppliedFlag();
	}

	/**
	 * Fill prices from base prices.
	 *
	 * @return void
	 */
	protected function resetPrices()
	{
		$this->resetBasketPrices();
	}

	/**
	 * Fill base entity price.
	 *
	 * @return void
	 */
	protected function resetOrderPrice(): void
	{
		if ($this->isOrderExists())
		{
			$order = $this->getOrder();
			$this->orderData['ORDER_PRICE'] = $order->getBasePrice();
			unset($order);
		}
		else
		{
			$basket = $this->getBasket();
			$this->orderData['ORDER_PRICE'] = $basket->getBasePrice();
			unset($basket);
		}
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
			if ($this->isFreezedBasketItem($basketItem))
				continue;
			$basketItem = self::resetBasketItemPrice($basketItem);
		}
		unset($basketItem);
	}

	/**
	 * Reset flag of applying discounts for basket items.
	 *
	 * @return void
	 */
	protected function resetDiscountAppliedFlag()
	{
		foreach ($this->orderData['BASKET_ITEMS'] as &$basketItem)
		{
			if ($this->isFreezedBasketItem($basketItem))
				continue;
			$basketItem['ACTION_APPLIED'] = 'N';
		}
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
			self::getExecuteFieldName('UNPACK') => true,
			self::getExecuteFieldName('APPLICATION') => true,
			self::getExecuteFieldName('PREDICTIONS_APP') => true
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

			if (isset($this->currentStep['stopLevel']) && $this->currentStep['stopLevel'])
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

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

		$useOrderCoupons = $couponClassName::isUsedOrderCouponsForApply();
		$couponClassName::useSavedCouponsForApply(false);
		$coupons = $couponClassName::getForApply($filter, array(), true);
		$couponClassName::useSavedCouponsForApply($useOrderCoupons);
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

		/** @var DiscountCouponsManager $couponClassName */
		$couponClassName = $this->getDiscountCouponClassName();

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

				$couponClassName::setApplyByProduct($itemData, array($orderCouponId));
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
						$applyBlock[$basketCode][$index]['RESULT']['DESCR'] = $this->formatDescription($this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_RESULT']);
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
				if ($applyBlock[$basketCode][$index]['RESULT']['APPLY'] == 'Y')
					$this->orderData['BASKET_ITEMS'][$basketCode]['ACTION_APPLIED'] = 'Y';
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

	/**
	 * Round and correct discount calculation results.
	 * @internal
	 *
	 * @return void
	 */
	protected function normalizeDiscountResult()
	{
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

	/* Instances methods */

	/**
	 * Returns true, if instance exist.
	 *
	 * @param string $index		Entity instance identifier.
	 * @return bool
	 */
	protected static function instanceExists($index)
	{
		$className = get_called_class();
		if (!isset(self::$instances[$className]))
			return false;
		return isset(self::$instances[$className][$index]);
	}

	/**
	 * Returns discount instance.
	 *
	 * @param string $index		Entity instance identifier.
	 * @return DiscountBase
	 */
	protected static function getInstance($index)
	{
		$className = get_called_class();
		if (!isset(self::$instances[$className]))
			self::$instances[$className] = array();
		if (!isset(self::$instances[$className][$index]))
			self::$instances[$className][$index] = self::createObject();

		return self::$instances[$className][$index];
	}

	protected static function migrateInstance($oldIndex, $newIndex)
	{
		$className = get_called_class();
		if (!isset(self::$instances[$className]))
			return;
		if (isset(self::$instances[$className][$oldIndex]) && !isset(self::$instances[$className][$newIndex]))
		{
			self::$instances[$className][$newIndex] = self::$instances[$className][$oldIndex];
			unset(self::$instances[$className][$oldIndex]);
		}
	}

	protected static function removeInstance($index)
	{
		$className = get_called_class();
		if (!isset(self::$instances[$className]))
			return;
		if (isset(self::$instances[$className][$index]))
			unset(self::$instances[$className][$index]);
	}

	/**
	 * Return parent entity type. The method must be overridden in the derived class.
	 * @internal
	 *
	 * @return string
	 * @throws Main\NotImplementedException
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * Return instance index for order.
	 *
	 * @internal
	 * @param OrderBase $order			Order.
	 * @return string
	 */
	protected static function getInstanceIndexByOrder(OrderBase $order)
	{
		return $order->getInternalId().'|0'.'|'.$order->getSiteId();
	}

	/**
	 * Return instance index for basket.
	 *
	 * @internal
	 *
	 * @param BasketBase $basket			Basket.
	 * @param Context\BaseContext|null	$context
	 *
	 * @return string
	 */
	protected static function getInstanceIndexByBasket(BasketBase $basket, Context\BaseContext $context = null)
	{
		if (!$context)
			return '0|'.$basket->getFUserId(false).'|'.$basket->getSiteId();
		return '0|-1|'.$basket->getSiteId().'|'.$context->getUserGroupsHash();
	}

	/* Instances methods end */

	/**
	 * Return order.
	 *
	 * @return OrderBase|null
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
		return ($this->order instanceof OrderBase);
	}

	/**
	 * Return site id for calculate.
	 * @internal
	 *
	 * @return null|string
	 */
	protected function getSiteId()
	{
		if ($this->isOrderExists())
			return $this->order->getSiteId();
		if ($this->isBasketExist())
			return $this->basket->getSiteId();
		return null;
	}

	/**
	 * Return order currency for calculate.
	 * @internal
	 *
	 * @return null|string
	 */
	protected function getCurrency()
	{
		if ($this->isOrderExists())
			return $this->order->getCurrency();
		$result = null;
		if ($this->isBasketExist())
		{
			if ($this->isBasketNotEmpty())
			{
				/** @var BasketItemBase $basketItem */
				$basketItem = $this->basket->rewind();
				$result = $basketItem->getCurrency();
				unset($basketItem);
			}
			else
			{
				$result = $this->getSiteCurrency();
			}
		}
		return $result;
	}

	/**
	 * Return site currency.
	 * @internal
	 *
	 * @return string|null
	 */
	protected function getSiteCurrency()
	{
		$result = Internals\SiteCurrencyTable::getCurrency($this->getSiteId());;
		if (is_array($result))
			return $result['CURRENCY'];
		$result = (string)Main\Config\Option::get('sale', 'default_currency');
		if ($result !== '')
			return $result;
		$result = Currency\CurrencyManager::getBaseCurrency();
		if ($result !== '')
			return $result;
		return null;
	}

	/* Sale disocunt hit cache methods */
	/**
	 * Return field name for save eval result. Only for core.
	 *
	 * @internal
	 *
	 * @param string $fieldName         Discount field name for eval.
	 * @return string
	 */
	final protected static function getExecuteFieldName($fieldName)
	{
		return self::EXECUTE_FIELD_PREFIX.$fieldName;
	}

	/**
	 * Return field list for eval.
	 *
	 * @internal
	 *
	 * @return array
	 */
	protected function getExecuteFieldList()
	{
		return ['UNPACK', 'APPLICATION'];
	}

	/**
	 * Return field with discount condition code.
	 *
	 * @internal
	 *
	 * @return string
	 */
	protected function getConditionField()
	{
		return 'UNPACK';
	}

	/**
	 * Load from database discount id for user groups.
	 *
	 * @internal
	 *
	 * @param array $filter			Additional filter.
	 * @return void
	 * @throws Main\ArgumentException
	 */
	protected function loadDiscountByUserGroups(array $filter = array())
	{
		$this->discountIds = array();
		$userGroups = $this->context->getUserGroups();
		if (empty($userGroups))
		{
			return;
		}
		$customFilter = array_diff_key(
			$filter,
			[
				'@GROUP_ID' => true,
				'=ACTIVE' => true,
			]
		);

		$filter['@GROUP_ID'] = $userGroups;
		$filter['=ACTIVE'] = 'Y';

		//RuntimeCache works only with basic filter.
		if (empty($customFilter))
		{
			$this->discountIds = Discount\RuntimeCache\DiscountCache::getInstance()->getDiscountIds($userGroups);
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
			$this->discountIds = $discountCache;
			unset($discountCache);
		}
		unset($userGroups);
	}

	/**
	 * Load discount modules.
	 *
	 * @internal
	 *
	 * @param array $modules				Discount modules.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	protected function loadDiscountModules(array $modules)
	{
		$result = true;
		if (empty($modules))
			return $result;

		foreach ($modules as $moduleId)
		{
			if (!isset($this->loadedModules[$moduleId]))
				$this->loadedModules[$moduleId] = Main\Loader::includeModule($moduleId);
			if (!$this->loadedModules[$moduleId])
			{
				$result = false;
				break;
			}
		}
		unset($moduleId);

		return $result;
	}

	/**
	 * Load sale discount from database
	 *
	 * @internal
	 *
	 * @return void
	 */
	protected function loadDiscountList()
	{
		if (empty($this->discountIds))
			return;

		$couponList = DiscountCouponsManager::getForApply(
			array('MODULE_ID' => 'sale', 'DISCOUNT_ID' => $this->discountIds),
			array(),
			true
		);

		$this->saleDiscountCacheKey = md5('D'.implode('_', $this->discountIds));
		if (!empty($couponList))
			$this->saleDiscountCacheKey .= '-C'.implode('_', array_keys($couponList));

		$this->saleDiscountCacheKey .= '-MF'.implode('_', $this->executeModuleFilter);

		if (!isset($this->saleDiscountCache[$this->saleDiscountCacheKey]))
		{
			$currentList = Discount\RuntimeCache\DiscountCache::getInstance()->getDiscounts(
				$this->discountIds,
				$this->executeModuleFilter,
				$this->getSiteId(),
				$couponList?: array()
			);

			if (!empty($currentList))
			{
				$evalCode = '';
				$executeFields = $this->getExecuteFieldList();
				foreach (array_keys($currentList) as $index)
				{
					$discount = $currentList[$index];
					if (!$this->loadDiscountModules($discount['MODULES']))
					{
						unset($currentList[$index]);
						continue;
					}

					foreach ($executeFields as $field)
					{
						if (!empty($discount[$field]))
							$evalCode .= '$currentList['.$index.'][\''.self::getExecuteFieldName($field).'\'] = '.$discount[$field].";\n";
					}
				}
				unset($field, $code, $discount, $index, $executeFields);

				if ($evalCode !== '')
				{
					if (PHP_MAJOR_VERSION >= 7)
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
	/* Sale disocunt hit cache methods end */

	/**
	 * @return DiscountBase
	 */
	private static function createObject()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$discountClassName = $registry->getDiscountClassName();

		return new $discountClassName();
	}

	/**
	 * Returns current order discount class name.
	 *
	 * @return string
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function getOrderDiscountClassName()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getOrderDiscountClassName();
	}

	/**
 	 * Returns current discount coupons manager class name.
	 *
	 * @return string
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function getDiscountCouponClassName()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getDiscountCouponClassName();
	}

	/**
	 * Return current shipment class name.
	 *
	 * @return string
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function getShipmentClassName()
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getShipmentClassName();
	}

	/**
	 * @return string
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 */
	protected function getEntityMarkerClassName(): string
	{
		$registry = Registry::getInstance(static::getRegistryType());
		return $registry->getEntityMarkerClassName();
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

	/**
	 * Return order property codes for translate to order fields.
	 *
	 * @return array
	 */
	protected static function getOrderPropertyCodes()
	{
		return [
			'DELIVERY_LOCATION' => 'IS_LOCATION',
			'USER_EMAIL' => 'IS_EMAIL',
			'PAYER_NAME' => 'IS_PAYER',
			'PROFILE_NAME' => 'IS_PROFILE_NAME',
			'DELIVERY_LOCATION_ZIP' => 'IS_ZIP'
		];
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
	 * Return empty apply block.
	 *
	 * @return array
	 */
	public static function getEmptyApplyBlock()
	{
		return array(
			'BASKET' => array(),
			'BASKET_ROUND' => array(),
			'ORDER' => array()
		);
	}

	/**
	 * Calculate discount percent for public components.
	 *
	 * @param int|float $basePrice		Base price.
	 * @param int|float $discount		Discount value (for an extra can be negative).
	 * @return float|int|null
	 */
	public static function calculateDiscountPercent($basePrice, $discount)
	{
		$basePrice = (float)$basePrice;
		if ($basePrice <= 0)
			return null;
		$discount = (float)$discount;
		if ($discount > $basePrice)
			return null;

		$result = round(100*$discount/$basePrice, 0);
		if ($result < 0)
			$result = 0;
		return $result;
	}

	/**
	 * Returns show prices for public components.
	 *
	 * @return array
	 */
	public function getShowPrices()
	{
		if (!$this->isOrderNew() && !$this->isLoaded())
		{
			$this->initUseMode();
			$this->loadOrderData();
		}

		$result = [
			'BASKET' => []
		];

		$checkRound = true;
		$useMode = $this->getUseMode();
		switch ($useMode)
		{
			case self::USE_MODE_APPLY:
			case self::USE_MODE_MIXED:
				if (!$this->isValidState())
					$checkRound = false;
				break;
		}

		if (!empty($this->orderData['BASKET_ITEMS']))
		{
			/** @var OrderDiscount $storageClassName */
			$storageClassName = $this->getOrderDiscountClassName();

			$basket = $this->orderData['BASKET_ITEMS'];
			$order = $this->orderData;
			unset($order['BASKET_ITEMS']);

			switch ($useMode)
			{
				case self::USE_MODE_FULL:
				case self::USE_MODE_APPLY:
					if ($checkRound)
					{
						$basketCodeList = $this->getBasketCodes(true);
						$existRound = array();
						$existRoundRules = array();
						foreach ($basketCodeList as $basketCode)
						{
							if (!empty($this->basketItemsData[$basketCode]['BASE_PRICE_ROUND_RULE']))
							{
								$existRound[$basketCode] = self::resetBasketItemPrice($basket[$basketCode]);
								$existRoundRules[$basketCode] = $this->basketItemsData[$basketCode]['BASE_PRICE_ROUND_RULE'];
							}
						}
						if (!empty($existRound))
						{
							$roundResult = $storageClassName::roundBasket(
								$existRound,
								$existRoundRules,
								$order
							);
							foreach ($roundResult as $basketCode => $row)
							{
								if (empty($row) || !is_array($row))
									continue;
								if (!isset($existRound[$basketCode]))
									continue;
								$basket[$basketCode]['BASE_PRICE'] = $row['PRICE'];
								$basket[$basketCode]['DISCOUNT_PRICE'] = $basket[$basketCode]['BASE_PRICE'] - $basket[$basketCode]['PRICE'];
							}
						}
						unset($existRoundRules, $existRound);
					}
					break;
				case self::USE_MODE_MIXED:
					if ($checkRound)
					{
						$existRound = array();
						$existRoundRules = array();
						foreach ($basket as $basketCode => $item)
						{
							if ($this->isFreezedBasketItem($item))
								continue;
							if (!empty($this->basketItemsData[$basketCode]['BASE_PRICE_ROUND_RULE']))
							{
								$existRound[$basketCode] = self::resetBasketItemPrice($basket[$basketCode]);
								$existRoundRules[$basketCode] = $this->basketItemsData[$basketCode]['BASE_PRICE_ROUND_RULE'];
							}
						}
						unset($code, $item);
						if (!empty($existRound))
						{
							$roundResult = $storageClassName::roundBasket(
								$existRound,
								$existRoundRules,
								$order
							);
							foreach ($roundResult as $basketCode => $row)
							{
								if (empty($row) || !is_array($row))
									continue;
								if (!isset($existRound[$basketCode]))
									continue;
								$basket[$basketCode]['BASE_PRICE'] = $row['PRICE'];
								$basket[$basketCode]['DISCOUNT_PRICE'] = $basket[$basketCode]['BASE_PRICE'] - $basket[$basketCode]['PRICE'];
							}
						}
						unset($existRoundRules, $existRound);
					}
					break;
			}

			foreach ($basket as $basketCode => $basketItem)
			{
				$result['BASKET'][$basketCode] = $this->getShowBasketItemPrice($basketCode, $basketItem);
				$result['BASKET'][$basketCode]['REAL_BASE_PRICE'] = $this->orderData['BASKET_ITEMS'][$basketCode]['BASE_PRICE'];
				$result['BASKET'][$basketCode]['REAL_PRICE'] = $this->orderData['BASKET_ITEMS'][$basketCode]['PRICE'];
				$result['BASKET'][$basketCode]['REAL_DISCOUNT'] = $this->orderData['BASKET_ITEMS'][$basketCode]['DISCOUNT_PRICE'];
			}
			unset($basketCode, $basketItem);
		}

		return $result;
	}

	/**
	 * Search round rule for base price.
	 * @internal
	 *
	 * return void
	 */
	private function getRoundForBasePrices()
	{
		$mode = $this->getUseMode();
		if ($mode != self::USE_MODE_FULL && $mode != self::USE_MODE_MIXED)
			return;

		$basketCodeList = $this->getBasketCodes(true);
		if (empty($basketCodeList))
			return;

		$basket = array_intersect_key(
			$this->orderData['BASKET_ITEMS'],
			array_fill_keys($basketCodeList, true)
		);

		if (empty($basket))
			return;

		foreach ($basketCodeList as $basketCode)
		{
			$this->addBasketItemValues(
				$basketCode,
				['BASE_PRICE_ROUND' => $basket[$basketCode]['BASE_PRICE'], 'BASE_PRICE_ROUND_RULE' => []]
			);
		}
		unset($basketCode);

		foreach ($basket as &$basketItem)
			$basketItem = self::resetBasketItemPrice($basketItem);
		unset($basketItem);

		$orderData = $this->orderData;
		unset($orderData['BASKET_ITEMS']);

		/** @var OrderDiscount $storageClassName */
		$storageClassName = $this->getOrderDiscountClassName();

		$result = $storageClassName::roundBasket(
			$basket,
			array(),
			$orderData
		);
		foreach ($basketCodeList as $basketCode)
		{
			if (empty($result[$basketCode]) || !is_array($result[$basketCode]))
				continue;
			$this->addBasketItemValues(
				$basketCode,
				[
					'BASE_PRICE_ROUND' => $result[$basketCode]['PRICE'],
					'BASE_PRICE_ROUND_RULE' => $result[$basketCode]['ROUND_RULE']
				]
			);
		}
		unset($basketCode, $result);
		unset($storageClassName);
		unset($basket, $orderData);
	}

	/**
	 * Returns basket item price for show in public components (basket, order, etc).
	 *
	 * @param string|int $basketCode	Basket item code.
	 * @param array $item				Basket item.
	 * @return array
	 */
	private function getShowBasketItemPrice($basketCode, array $item)
	{
		if ($this->isFreezedBasketItem($item))
		{
			if ($item['BASE_PRICE'] <= $item['PRICE'])
				$result = $this->getShowPriceWithZeroDiscountPercent($item);
			else
				$result = $this->getShowPriceWithDiscountPercent($item);
			return $result;
		}

		if ($item['BASE_PRICE'] <= $item['PRICE'])
			return $this->getShowPriceWithZeroDiscountPercent($item);

		if ($this->isExistBasketItemDiscount($basketCode))
			return $this->getShowPriceWithDiscountPercent($item);

		return $this->getShowPriceWithZeroDiscountPercent($item);
	}

	/**
	 * Returns basket item price with rounded discount percent. Only for show.
	 *
	 * @param array $item	Basket item (price fields).
	 * @return array
	 */
	private function getShowPriceWithDiscountPercent(array $item)
	{
		return [
			'SHOW_BASE_PRICE' => $item['BASE_PRICE'],
			'SHOW_PRICE' => $item['PRICE'],
			'SHOW_DISCOUNT' => $item['DISCOUNT_PRICE'],
			'SHOW_DISCOUNT_PERCENT' => $this->calculateDiscountPercent(
				$item['BASE_PRICE'],
				$item['DISCOUNT_PRICE']
			)
		];
	}

	/**
	 * Returns basket item price without discount. Only for show.
	 *
	 * @param array $item	Basket item (price fields).
	 * @return array
	 */
	private function getShowPriceWithZeroDiscountPercent(array $item)
	{
		return [
			'SHOW_BASE_PRICE' => $item['PRICE'],
			'SHOW_PRICE' => $item['PRICE'],
			'SHOW_DISCOUNT' => 0,
			'SHOW_DISCOUNT_PERCENT' => 0
		];
	}

	/**
	 * Checking the existence of the applied discount on the basket item.
	 *
	 * @param string|int $basketCode	Basket item code.
	 * @return bool
	 */
	private function isExistBasketItemDiscount($basketCode)
	{
		$result = false;

		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (isset($applyBlock['BASKET'][$basketCode]))
				{
					foreach ($applyBlock['BASKET'][$basketCode] as $discount)
					{
						if ($discount['RESULT']['APPLY'] == 'Y')
							$result = true;
					}
					unset($discount);
				}

				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if (!empty($discount['RESULT']['BASKET']))
						{
							if (!isset($discount['RESULT']['BASKET'][$basketCode]))
								continue;
							if ($discount['RESULT']['BASKET'][$basketCode]['APPLY'] == 'Y')
								$result = true;
						}
					}
					unset($discount);
				}
			}
			unset($counter, $applyBlock);
		}

		return $result;
	}

	/**
	 * Returns basket item stored data for save.
	 *
	 * @param string|int $basketCode	Basket item code.
	 * @return array|null
	 */
	private function prepareBasketItemStoredData($basketCode)
	{
		$result = [];
		if (isset($this->basketItemsData[$basketCode]))
		{
			if (isset($this->basketItemsData[$basketCode]['BASE_PRICE_ROUND_RULE']))
				$result['BASE_PRICE_ROUND_RULE'] = $this->basketItemsData[$basketCode]['BASE_PRICE_ROUND_RULE'];
		}

		return (!empty($result) ? $result : null);
	}

	/**
	 * Reset product price for discount calculation.
	 *
	 * @param array $item		Basket item data.
	 * @return array
	 */
	private static function resetBasketItemPrice(array $item)
	{
		$item['PRICE'] = $item['BASE_PRICE'];
		$item['DISCOUNT_PRICE'] = 0;

		return $item;
	}

	/**
	 * Update or insert internal data for basket item.
	 *
	 * @param string|int $basketCode	Basket item code.
	 * @param array $values				Update data.
	 * @return void
	 */
	private function addBasketItemValues($basketCode, array $values)
	{
		if (empty($values))
			return;

		if (!isset($this->basketItemsData[$basketCode]))
		{
			$this->basketItemsData[$basketCode] = $values;
		}
		else
		{
			foreach ($values as $index => $value)
				$this->basketItemsData[$basketCode][$index] = $value;
			unset($index, $value);
		}
	}
}