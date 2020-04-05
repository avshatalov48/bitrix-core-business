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
	Bitrix\Sale\Discount\Context;

Loc::loadMessages(__FILE__);

class Discount extends DiscountBase
{
	/* Sale objects */
	/** @var null|Shipment $shipment */
	protected $shipment = null;
	/** @var array */
	protected $shipmentIds = array();

	/** @var bool  */
	protected $enableCheckingPrediction = false;

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
	 * @return null|Discount|DiscountBase
	 */
	public static function loadByFuser($fuser, $site)
	{
		$instanceIndex = static::getInstanceIndexByFuser((int)$fuser, (string)$site);
		if (!static::instanceExists($instanceIndex))
			return null;
		return static::getInstance($instanceIndex);
	}

	/**
	 * Return parent entity type.
	 * @internal
	 *
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

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
	 * Clone entity.
	 *
	 * @internal
	 * @param \SplObjectStorage $cloneEntity	Clone repository.
	 *
	 * @return Discount
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		/** @var Discount $discountClone */
		$discountClone = parent::createClone($cloneEntity);

		if ($this->isShipmentExists())
		{
			if ($cloneEntity->contains($this->shipment))
				$discountClone->shipment = $cloneEntity[$this->shipment];
		}

		return $discountClone;
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
		parent::setApplyResult($applyResult);

		if (!empty($this->applyResult['DISCOUNT_LIST']))
		{
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

		$result = parent::getApplyResult($extMode);
		$result = $this->getApplyDeliveryList() + $result;

		$result['CONVERTED_ORDER'] = (!$this->isValidState() ? 'Y' : 'N');

		if (!$extMode)
		{
			/* for compatibility only */
			if (isset($this->discountResult['APPLY_BLOCKS'][0]['BASKET']))
				$result['BASKET'] = $this->discountResult['APPLY_BLOCKS'][0]['BASKET'];
			if (isset($this->discountResult['APPLY_BLOCKS'][0]['ORDER']))
				$result['ORDER'] = $this->discountResult['APPLY_BLOCKS'][0]['ORDER'];
		}
		return $result;
	}

	/**
	 * Returns show prices for public components.
	 *
	 * @return array
	 */
	public function getShowPrices()
	{
		$result = parent::getShowPrices();
		$result['DELIVERY'] = $this->getApplyDeliveryPrice();

		return $result;
	}

	/**
	 * Save discount result.
	 *
	 * @return Result
	 */
	public function save()
	{
		$result = new Result;
		if (!$this->isOrderExists() || !$this->isBasketNotEmpty())
			return $result;

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
					$result->addErrors($shipmentResult->getErrors());
					unset($shipmentResult);
					return $result;
				}
				unset($shipmentResult);
				$this->fillShipmentData();
			}
			unset($compatibleResult);
		}

		$result = parent::save();

		return $result;
	}

	/**
	 * Returns order configuration for save to database.
	 *
	 * @return array
	 */
	protected function getOrderConfig()
	{
		$shipmentClassName = $this->getShipmentClassName();
		$config = parent::getOrderConfig();
		$config['DELIVERY'] = [
			'DELIVERY_ID' => $this->orderData['DELIVERY_ID'],
			'CUSTOM_PRICE_DELIVERY' => $this->orderData['CUSTOM_PRICE_DELIVERY'],
			'SHIPMENT_ID' => 0
		];
		if ($this->shipment instanceof $shipmentClassName)
			$config['DELIVERY']['SHIPMENT_ID'] = $this->shipment->getId();
		unset($shipmentClassName);
		return $config;
	}

	/* instance tools */

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
	 * Return field list for eval.
	 *
	 * @internal
	 *
	 * @return array
	 */
	protected function getExecuteFieldList()
	{
		if (!$this->enableCheckingPrediction)
			return parent::getExecuteFieldList();
		return ['PREDICTIONS_APP', 'APPLICATION'];
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
		if (!$this->enableCheckingPrediction)
			return parent::getConditionField();
		return 'PREDICTIONS_APP';
	}

	/**
	 * Returns result after one discount.
	 *
	 * @param array $order			Order current data.
	 * @return array
	 */
	protected static function getStepResult(array $order)
	{
		$result = parent::getStepResult($order);;
		$stepResult = &$order['DISCOUNT_RESULT'];
		if (!empty($stepResult['DELIVERY']) && is_array($stepResult['DELIVERY']))
		{
			$result['DELIVERY'] = array(
				'APPLY' => 'Y',
				'DELIVERY_ID' => (isset($order['DELIVERY_ID']) ? $order['DELIVERY_ID'] : false),
				'SHIPMENT_CODE' => (isset($order['SHIPMENT_CODE']) ? $order['SHIPMENT_CODE'] : false),
				'DESCR' => Discount\Formatter::formatList($stepResult['DELIVERY']),
				'DESCR_DATA' => $stepResult['DELIVERY'],
				'ACTION_BLOCK_LIST' => array_keys($stepResult['DELIVERY'])
			);
			if (is_array($result['DELIVERY']['DESCR']))
				$result['DELIVERY']['DESCR'] = implode(', ', $result['DELIVERY']['DESCR']);
		}
		unset($stepResult);

		return $result;
	}

	/**
	 * Round and correct discount calculation results.
	 * @internal
	 *
	 * @return void
	 */
	protected function normalizeDiscountResult()
	{
		parent::normalizeDiscountResult();

		$customPrice = isset($this->orderData['CUSTOM_PRICE_DELIVERY']) && $this->orderData['CUSTOM_PRICE_DELIVERY'] == 'Y';
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
	}

	/**
	 * Fill prices in apply results.
	 *
	 * @return void
	 */
	protected function getApplyPrices()
	{
		parent::getApplyPrices();
		$this->discountResult['PRICES']['DELIVERY'] = $this->getApplyDeliveryPrice();
	}

	/**
	 * Change result format.
	 *
	 * @return void
	 */
	protected function remakingDiscountResult()
	{
		parent::remakingDiscountResult();

		$delivery = [];
		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if (!empty($discount['RESULT']['DELIVERY']))
						{
							$delivery[] = [
								'DISCOUNT_ID' => $discount['DISCOUNT_ID'],
								'COUPON_ID' => $discount['COUPON_ID'],
								'DELIVERY_ID' => $discount['RESULT']['DELIVERY']['DELIVERY_ID'],
								'APPLY' => $discount['RESULT']['DELIVERY']['APPLY'],
								'DESCR' => $discount['RESULT']['DELIVERY']['DESCR']
							];
						}
					}
					unset($discount);
				}
			}
			unset($counter, $applyBlock);
		}
		$this->discountResult['RESULT']['DELIVERY'] = $delivery;
		unset($delivery);
	}

	/**
	 * Returns delivery price data.
	 *
	 * @return array
	 */
	protected function getApplyDeliveryPrice()
	{
		return array(
			'BASE_PRICE' => $this->orderData['BASE_PRICE_DELIVERY'],
			'PRICE' => $this->orderData['PRICE_DELIVERY'],
			'DISCOUNT' => $this->orderData['PRICE_DELIVERY_DIFF']
		);
	}

	/**
	 * Get discount delivery list and delivery list.
	 *
	 * @return array
	 */
	protected function getApplyDeliveryList()
	{
		$result = array();

		$delivery = array();
		if (!empty($this->discountResult['APPLY_BLOCKS']))
		{
			foreach ($this->discountResult['APPLY_BLOCKS'] as $counter => $applyBlock)
			{
				if (!empty($applyBlock['ORDER']))
				{
					foreach ($applyBlock['ORDER'] as $discount)
					{
						if (empty($discount['RESULT']['DELIVERY']))
							continue;
						$id = $discount['RESULT']['DELIVERY']['DELIVERY_ID'];
						$delivery[$id] = $id;
					}
					unset($id, $discount);
				}
			}
			unset($counter, $applyBlock);
		}
		$result['DELIVERY_LIST'] = (empty($delivery) ? array() : array_values($delivery));
		unset($delivery);

		$shipmentClassName = $this->getShipmentClassName();
		$shipment = [];
		if ($this->shipment instanceof $shipmentClassName)
			$shipment[] = $this->shipment->getShipmentCode();
		$result['SHIPMENT_LIST'] = $shipment;
		unset($shipment);

		return $result;
	}

	/**
	 * Fill empty discount result list.
	 *
	 * @return void
	 */
	protected function fillEmptyDiscountResult()
	{
		parent::fillEmptyDiscountResult();
		$this->discountResult['DELIVERY_LIST'] = [];
		$this->discountResult['SHIPMENT_LIST'] = [];
	}

	/**
	 * Fill result order data.
	 *
	 * @return array
	 */
	protected function fillDiscountResult()
	{
		$result = parent::fillDiscountResult();

		$orderKeys = ['PRICE_DELIVERY', 'PRICE_DELIVERY_DIFF'];
		foreach ($orderKeys as $key)
		{
			if (isset($this->orderData[$key]))
				$result[$key] = $this->orderData[$key];
		}
		unset($key, $orderKeys);
		if (isset($result['PRICE_DELIVERY_DIFF']))
		{
			$result['DISCOUNT_PRICE'] = $result['PRICE_DELIVERY_DIFF'];
			unset($result['PRICE_DELIVERY_DIFF']);
		}

		$shipmentClassName = $this->getShipmentClassName();
		$result['SHIPMENT'] = null;
		if ($this->shipment instanceof $shipmentClassName)
			$result['SHIPMENT'] = $this->shipment->getShipmentCode();
		unset($shipmentClassName);

		return $result;
	}

	/**
	 * Fill prices from base prices.
	 *
	 * @return void
	 */
	protected function resetPrices()
	{
		parent::resetPrices();
		$this->resetDeliveryPrices();
	}

	/**
	 * Fill delivery price from base price.
	 *
	 * @return void
	 */
	protected function resetDeliveryPrices()
	{
		if ($this->orderData['CUSTOM_PRICE_DELIVERY'] !== 'N')
			return;
		$this->orderData['PRICE_DELIVERY'] = $this->orderData['BASE_PRICE_DELIVERY'];
		$this->orderData['PRICE_DELIVERY_DIFF'] = 0;
	}

	/**
	 * Load order information.
	 *
	 * @return Result
	 */
	protected function loadOrderData()
	{
		$result = parent::loadOrderData();
		if (!$result->isSuccess())
			return $result;

		if (!$this->isShipmentExists())
		{
			$this->shipmentIds = [];
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
		parent::fillEmptyOrderData();
		$this->orderData += [
			'BASE_PRICE_DELIVERY' => 0,
			'PRICE_DELIVERY' => 0,
			'PRICE_DELIVERY_DIFF' => 0,
			'DELIVERY_ID' => 0,
			'CUSTOM_PRICE_DELIVERY' => 'N',
			'SHIPMENT_CODE' => 0,
			'SHIPMENT_ID' => 0,
			'PAY_SYSTEM_ID' => 0
		];

		if ($this->isOrderExists())
		{
			/** @var Order $order */
			$order = $this->getOrder();

			$this->orderData['PAY_SYSTEM_ID'] = null;
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

			unset($order);
		}
	}

	/**
	 * Get basket data from owner entity.
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function loadBasket()
	{
		$result = parent::loadBasket();
		if (!$result->isSuccess())
			return $result;

		if ($this->isBasketNotEmpty())
		{
			/** @var Basket $basket */
			$basket = $this->getBasket();
			/** @var BasketItem $basketItem */
			foreach ($basket as $basketItem)
			{
				if (
					!$basketItem->canBuy()
					|| !$basketItem->isBundleParent()
				)
					continue;

				$bundle = $basketItem->getBundleCollection();
				if ($bundle->count() == 0)
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
					$item = $this->getBasketItemFields($bundleItem);
					$item['IN_SET'] = 'Y';
					$this->orderData['BASKET_ITEMS'][$bundleItem->getBasketCode()] = $item;
				}
				unset($item, $bundle, $bundleItem);
			}
			unset($basketItem);
		}

		return $result;
	}

	/**
	 * Load default order config for order.
	 *
	 * @return void
	 */
	protected function loadDefaultOrderConfig()
	{
		parent::loadDefaultOrderConfig();

		$this->shipment = null;

		$this->fillCompatibleOrderFields();
	}

	/**
	 * @param array $data		Order settings from database.
	 * @return void
	 */
	protected function applyLoadedOrderConfig(array $data)
	{
		parent::applyLoadedOrderConfig($data);

		if (isset($data['OLD_ORDER']))
			$this->setValidState(false);

		/** @var Order $order */
		$order = $this->getOrder();
		if (!empty($data['DELIVERY']))
		{
			$delivery = $data['DELIVERY'];
			$this->orderData['DELIVERY_ID'] = $delivery['DELIVERY_ID'];
			if (isset($delivery['CUSTOM_PRICE_DELIVERY']))
				$this->orderData['CUSTOM_PRICE_DELIVERY'] = $delivery['CUSTOM_PRICE_DELIVERY'];
			if (isset($delivery['SHIPMENT_ID']))
			{
				$delivery['SHIPMENT_ID'] = (int)$delivery['SHIPMENT_ID'];
				if ($delivery['SHIPMENT_ID'] > 0)
				{
					$this->shipmentIds[] = $delivery['SHIPMENT_ID'];
					/** @var ShipmentCollection $orderShipmentList */
					$orderShipmentList = $order->getShipmentCollection();
					$this->shipment = $orderShipmentList->getItemById($delivery['SHIPMENT_ID']);
					if (empty($this->shipment))
					{
						$this->shipment = null;
						$this->shipmentIds = array();
					}
				}
			}
			unset($delivery);
		}

		$this->fillCompatibleOrderFields();
	}

	/**
	 * Return is exists discount shipment.
	 *
	 * @return bool
	 */
	protected function isShipmentExists()
	{
		$shipmentClassName = $this->getShipmentClassName();
		return ($this->shipment instanceof $shipmentClassName);
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
		$this->orderData['PRICE_DELIVERY'] = $this->shipment->getPrice();
		$this->orderData['PRICE_DELIVERY_DIFF'] = $this->shipment->getField('DISCOUNT_PRICE');
		$this->orderData['SHIPMENT_CODE'] = $this->shipment->getShipmentCode();
		$this->orderData['SHIPMENT_ID'] = (int)$this->shipment->getId();
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
			/** @var Order $order */
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
						break;
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
	 * Initial instance data after set order.
	 *
	 * @return void
	 */
	protected function initInstanceFromOrder()
	{
		parent::initInstanceFromOrder();
		$this->loadShipment();
		$this->fillShipmentData();
	}

	/* entities id tools */

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
			case self::ENTITY_DELIVERY:
				$result = [
					'ENTITY_TYPE' => self::ENTITY_DELIVERY,
					'ENTITY_ID' => (int)$entity['ENTITY_CODE'],
					'ENTITY_VALUE' => (string)$entity['ENTITY_CODE']
				];
				break;
			default:
				$result = parent::getEntitySaveIdentifier($entity);
				break;
		}

		return $result;
	}

	/* entities id tools finish */

	/* compatibility tools */
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
	 * Fill order fields for deprecated discount classes.
	 *
	 * @return void
	 */
	protected function fillCompatibleOrderFields()
	{
		$this->orderData['USE_BASE_PRICE'] = $this->saleOptions['USE_BASE_PRICE'];
	}

	/* compatibility tools finish */

	/* deprecated methods */

	/**
	 * Set base price for basket item.
	 * @deprecated
	 *
	 * @param int|string $code				Basket code.
	 * @param float $price			Price.
	 * @param string $currency		Currency.
	 * @return void
	 */
	public function setBasketItemBasePrice($code, $price, $currency) {}

	/**
	 * Set base price for all basket items.
	 * @deprecated
	 *
	 * @param array $basket					Basket.
	 * @return void
	 */
	public function setBasketBasePrice($basket) {}

	/**
	 * Get base price for basket item.
	 * @deprecated
	 *
	 * @param int|string $code				Basket code.
	 * @return float|null
	 */
	public function getBasketItemBasePrice($code)
	{
		return (isset($this->orderData[$code]) ? $this->orderData[$code]['BASE_PRICE'] : null);
	}

	/**
	 * Set product discounts for basket item.
	 * @deprecated
	 *
	 * @param int|string $code				Basket code.
	 * @param array $discountList			Discount list.
	 * @return void
	 */
	public function setBasketItemDiscounts($code, $discountList) {}

	/**
	 * Set various basket item data.
	 * @deprecated
	 *
	 * @param int|string $code				Basket code.
	 * @param array $providerData			Product data from provider.
	 * @return void
	 */
	public function setBasketItemData($code, $providerData) {}

	/**
	 * Clear basket item data.
	 * @deprecated
	 *
	 * @param int|string $code				Basket code.
	 * @return void
	 */
	public function clearBasketItemData($code) {}

	/**
	 * Get discount by basket.
	 *
	 * @deprecated deprecated sinse sale 17.0.11
	 * @see Discount::buildFromBasket
	 *
	 * @param Basket $basket		Basket object.
	 * @return Discount|DiscountBase|null
	 */
	public static function loadByBasket(Basket $basket)
	{
		$order = $basket->getOrder();
		if ($order instanceof Order)
			return static::buildFromOrder($order);

		return self::buildFromBasket($basket, new Context\Fuser($basket->getFUserId(true)));
	}

	/**
	 * Get discount by order.
	 *
	 * @deprecated deprecated sinse sale 17.0.11
	 * @see Discount::buildFromOrder
	 *
	 * @param Order $order		Order object.
	 * @return Discount|DiscountBase
	 */
	public static function load(Order $order)
	{
		return static::buildFromOrder($order);
	}

	/**
	 * @deprecated
	 *
	 * @param BasketItem $basketItem
	 * @param int $orderDiscountId
	 * @return void
	 * @throws \Exception
	 */
	public function saveExternalLastApplyblock(BasketItem $basketItem, $orderDiscountId)
	{
		/** @var Basket $basket */
		$basket = $basketItem->getCollection();
		$this->order = $basket->getOrder();

		$this->loadOrderData();

		$listItems[$basketItem->getBasketCode()] = array('APPLY'=>'Y', 'ACTION_BLOCK_LIST'=>array(), 'DESCR_DATA'=>array(), 'DESCR'=>array());

		$this->discountsCache[$orderDiscountId]['MODULE_ID'] = 'sale';
		$applyBlock = &$this->discountResult['APPLY_BLOCKS'][$this->discountResultCounter];
		$applyBlock['ORDER'][] = array(
			'DISCOUNT_ID' => $orderDiscountId,
			'COUPON_ID' => 0,
			'RESULT' => array('BASKET' => $listItems)
		);

		$this->saveLastApplyBlock();
	}

	/**
	 * Return basket item currency.
	 * @deprecated
	 *
	 * @param string|int $basketCode	Basket item code.
	 * @return string|null
	 */
	protected function getBasketCurrency($basketCode)
	{
		return $this->getCurrency();
	}

	/* deprecated methods finish */
}