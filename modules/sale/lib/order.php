<?php

namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Reservation\Configuration\ReserveCondition;

Loc::loadMessages(__FILE__);

/**
 * Class Order
 * @package Bitrix\Sale
 */
class Order extends OrderBase implements \IShipmentOrder, \IPaymentOrder, IBusinessValueProvider
{
	/** @var ShipmentCollection */
	protected $shipmentCollection;

	/** @var PaymentCollection */
	protected $paymentCollection;

	/** @var TradeBindingCollection */
	protected $tradeBindingCollection;

	/** @var array $printedChecks */
	protected $printedChecks = array();


	const SALE_ORDER_LOCK_STATUS_RED = 'red';
	const SALE_ORDER_LOCK_STATUS_GREEN = 'green';
	const SALE_ORDER_LOCK_STATUS_YELLOW = 'yellow';

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}


	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\OrderTable::getMap();
	}

	/**
	 * @return null|string
	 */
	public static function getUfId()
	{
		return Internals\OrderTable::getUfId();
	}

	/**
	 * Return printed check list
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 */
	public function getPrintedChecks()
	{
		if (!$this->printedChecks
			&& !$this->isNew()
		)
		{
			$this->printedChecks = $this->loadPrintedChecks();
		}

		return $this->printedChecks;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 */
	protected function loadPrintedChecks()
	{
		$result = [];

		$dbRes = Cashbox\CheckManager::getList([
			'filter' => [
				'=ORDER_ID' => $this->getId()
			]
		]);

		while ($data = $dbRes->fetch())
		{
			$result[] = Cashbox\CheckManager::create($data);
		}

		return $result;
	}


	/**
	 * Add printed check to order
	 *
	 * @param $check
	 */
	public function addPrintedCheck($check)
	{
		$this->printedChecks[] = $check;
	}

	/**
	 * Modify shipment collection.
	 *
	 * @param $action
	 * @param Shipment $shipment
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 */
	public function onShipmentCollectionModify($action, Shipment $shipment, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		$registry = Registry::getInstance(static::getRegistryType());

		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		/** @var EntityMarker $entityMarker */
		$entityMarker = $registry->getEntityMarkerClassName();

		if ($action === EventActions::DELETE)
		{
			if ((int)$this->getField('DELIVERY_ID') === $shipment->getDeliveryId())
			{
				$foundShipment = false;

				/** @var Shipment $entityShipment */
				foreach ($this->getShipmentCollection()->getNotSystemItems() as $entityShipment)
				{
					if ((int)$entityShipment->getField('DELIVERY_ID') > 0)
					{
						$foundShipment = true;
						$this->setFieldNoDemand('DELIVERY_ID', $entityShipment->getField('DELIVERY_ID'));
						break;
					}
				}

				if (!$foundShipment && !$shipment->isSystem())
				{
					$systemShipment = $this->getShipmentCollection()->getSystemShipment();

					if ((int)$systemShipment->getField('DELIVERY_ID') > 0)
					{
						$this->setFieldNoDemand('DELIVERY_ID', $systemShipment->getField('DELIVERY_ID'));
					}
				}
			}
		}
		elseif ($action === EventActions::ADD)
		{
			if ($shipment->getId() === 0)
			{
				$this->getPropertyCollection()->refreshRelated();
			}

			if (!$shipment->isSystem())
			{
				$this->setFieldNoDemand('DELIVERY_ID', $shipment->getDeliveryId());
			}
		}

		if ($action !== EventActions::UPDATE)
		{
			return $result;
		}

		if ($name === "ALLOW_DELIVERY")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ALLOW_DELIVERY_ORDER_CANCELED'), 'SALE_ORDER_ALLOW_DELIVERY_ORDER_CANCELED'));
				return $result;
			}

			$r = $shipment->deliver();
			if ($r->isSuccess())
			{
				$eventManager = Main\EventManager::getInstance();
				if ($eventManager->findEventHandlers('sale', EventActions::EVENT_ON_SHIPMENT_DELIVER))
				{
					$event = new Main\Event('sale', EventActions::EVENT_ON_SHIPMENT_DELIVER, array(
						'ENTITY' =>$shipment
					));
					$event->send();
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if (
				Configuration::isEnableAutomaticReservation()
				&& Configuration::getProductReservationCondition() === ReserveCondition::ON_ALLOW_DELIVERY
			)
			{
				if ($value === "Y")
				{
					$r = $shipment->tryReserve();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
				elseif (!$shipment->isShipped())
				{
					$r = $shipment->tryUnreserve();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}

				if (!$result->isSuccess())
				{
					return $result;
				}
			}

			$orderStatus = null;

			if ($oldValue === "N")
			{
				if ($this->getShipmentCollection()->isAllowDelivery())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_allow_delivery', '');
				}
				elseif ($this->getShipmentCollection()->hasAllowDelivery())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_allow_delivery_one_of', '');
				}
			}

			if ($orderStatus !== null && $this->getField('STATUS_ID') != static::getFinalStatus())
			{
				if ((string)$orderStatus != '')
				{
					$r = $this->setField('STATUS_ID', $orderStatus);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $this, $r);
						$this->setField('MARKED', 'Y');
					}
				}
			}

			if (Configuration::needShipOnAllowDelivery() && $value === "Y")
			{
				if (!$shipment->isEmpty())
				{
					$r = $shipment->setField("DEDUCTED", "Y");
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
			}

			$isAllowDelivery = $this->getShipmentCollection()->isAllowDelivery();
			if (
				$isAllowDelivery
				&& $this->getField('ALLOW_DELIVERY') === 'N'
			)
			{
				$this->setFieldNoDemand('DATE_ALLOW_DELIVERY', new Type\DateTime());
			}

			$this->setFieldNoDemand('ALLOW_DELIVERY', $isAllowDelivery ? "Y" : "N");
		}
		elseif ($name === "DEDUCTED")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_SHIPMENT_ORDER_CANCELED'), 'SALE_ORDER_SHIPMENT_ORDER_CANCELED'));
				return $result;
			}

			if (
				Configuration::isEnableAutomaticReservation()
				&& Configuration::getProductReservationCondition() == ReserveCondition::ON_SHIP
			)
			{
				if ($value === "Y")
				{
					/** @var Result $r */
					$r = $shipment->tryReserve();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
				else
				{
					$r = $shipment->tryUnreserve();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
			}

			if ($value === "Y")
			{
				/** @var Result $r */
				$r = $shipment->tryShip();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

				if ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
					$entityMarker::addMarker($this, $shipment, $r);
					if (!$shipment->isSystem())
					{
						$shipment->setField('MARKED', 'Y');
					}
				}

			}
			elseif ($oldValue === 'Y')
			{
				$r = $shipment->tryUnship();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

				if ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
					$entityMarker::addMarker($this, $shipment, $r);
					if (!$shipment->isSystem())
					{
						$shipment->setField('MARKED', 'Y');
					}
				}

				if (
					Configuration::isEnableAutomaticReservation()
					&& $shipment->needReservation()
				)
				{
					$r = $shipment->tryReserve();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						$entityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
			}

			if (!$result->isSuccess())
			{
				return $result;
			}

			$orderStatus = null;

			$allowSetStatus = false;

			if ($oldValue === "N")
			{
				if ($this->getShipmentCollection()->isShipped())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_shipped_shipment', '');
				}
				elseif ($this->getShipmentCollection()->hasShipped())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_shipped_shipment_one_of', '');
				}
				$allowSetStatus = ($this->getField('STATUS_ID') != static::getFinalStatus());
			}
			else
			{
				$fields = $this->getFields();
				$originalValues = $fields->getOriginalValues();
				if (!empty($originalValues['STATUS_ID']))
				{
					$orderStatus = $originalValues['STATUS_ID'];
					$allowSetStatus = true;
				}
			}

			if (
				(string)$orderStatus !== ''
				&& $allowSetStatus
			)
			{
				$r = $this->setField('STATUS_ID', $orderStatus);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
					$entityMarker::addMarker($this, $this, $r);
					$this->setField('MARKED', 'Y');
				}
			}

			$isShipped = $this->getShipmentCollection()->isShipped();
			$this->setFieldNoDemand('DEDUCTED', $isShipped ? "Y" : "N");

			if ($isShipped)
			{
				if (strval($shipment->getField('DATE_DEDUCTED')) != '')
				{
					$this->setFieldNoDemand('DATE_DEDUCTED', $shipment->getField('DATE_DEDUCTED'));
				}
				if (strval($shipment->getField('EMP_DEDUCTED_ID')) != '')
				{
					$this->setFieldNoDemand('EMP_DEDUCTED_ID', $shipment->getField('EMP_DEDUCTED_ID'));
				}
			}
		}
		elseif ($name == "MARKED")
		{
			if ($value == "Y")
			{
				/** @var Result $r */
				$r = $this->setField('MARKED', 'Y');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}
		elseif ($name == "REASON_MARKED")
		{
			$r = $this->setReasonMarked($value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		elseif ($name == "BASE_PRICE_DELIVERY")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_PRICE_DELIVERY_ORDER_CANCELED'), 'SALE_ORDER_PRICE_DELIVERY_ORDER_CANCELED'));
				return $result;
			}

			$discount = $this->getDiscount();
			$discount->setCalculateShipments($shipment);
		}
		elseif ($name == "PRICE_DELIVERY")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_PRICE_DELIVERY_ORDER_CANCELED'), 'SALE_ORDER_PRICE_DELIVERY_ORDER_CANCELED'));
				return $result;
			}

			$deliveryPrice = ($this->isNew()) ? $value : $this->getField("PRICE_DELIVERY") - $oldValue + $value;
			$this->setFieldNoDemand(
				"PRICE_DELIVERY",
				$deliveryPrice
			);

			/** @var Result $r */
			$r = $this->setField(
				"PRICE",
				$this->getField("PRICE") - $oldValue + $value
			);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

		}
		elseif ($name == "DELIVERY_ID")
		{
			if ($shipment->isSystem() || intval($shipment->getField('DELIVERY_ID')) <= 0 )
			{
				return $result;
			}

			$this->setFieldNoDemand('DELIVERY_ID', $shipment->getField('DELIVERY_ID'));

			$this->getPropertyCollection()->refreshRelated();

		}
		elseif ($name == "TRACKING_NUMBER")
		{
			if ($shipment->isSystem())
			{
				return $result;
			}

			$this->setFieldNoDemand('TRACKING_NUMBER', $shipment->getField('TRACKING_NUMBER'));
		}

		if ($value != $oldValue)
		{
			$fields = $this->fields->getChangedValues();
			if (!empty($fields) && !array_key_exists("UPDATED_1C", $fields))
			{
				parent::setField("UPDATED_1C", "N");
			}
		}

		return $result;
	}

	public function onTradeBindingCollectionModify($action, TradeBindingEntity $entity, $name = null, $oldValue = null, $value = null)
	{
		if ($action === EventActions::ADD)
		{
			if (
				$entity->getId() === 0
				&& $entity->getField('TRADING_PLATFORM_ID') > 0
			)
			{
				$this->propertyCollection = $this->loadPropertyCollection();
			}
		}
		elseif ($action === EventActions::UPDATE)
		{
			if ($name === 'TRADING_PLATFORM_ID')
			{
				$this->propertyCollection = $this->loadPropertyCollection();
			}
		}

		return new Result();
	}

	/**
	 * @param array $select
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function resetData($select = array('PRICE'))
	{
		$this->getShipmentCollection()->resetData();

		parent::resetData($select);
	}

	/**
	 * Returns order price without discounts.
	 *
	 * @return float
	 */
	public function getBasePrice(): float
	{
		$price = parent::getBasePrice();

		return $price + $this->getShipmentCollection()->getBasePriceDelivery();
	}

	/**
	 * @param BasketBase $basket
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function setBasket(BasketBase $basket)
	{
		$result = new Result();

		$isStartField = $this->isStartField();

		$r = parent::setBasket($basket);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		$result = $this->getShipmentCollection()->resetCollection();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if (!$this->isMathActionOnly())
		{
			$r = $this->refreshData();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $this->hasMeaningfulField();

			$r = $this->doFinalAction($hasMeaningfulFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param BasketBase $basket
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function appendBasket(BasketBase $basket)
	{
		$result = new Result();

		$isStartField = $this->isStartField();

		$r = parent::appendBasket($basket);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		/** @var Result $r */
		$result = $this->getShipmentCollection()->resetCollection();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if (!$this->isMathActionOnly())
		{
			/** @var Result $r */
			$r = $this->refreshData();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $this->hasMeaningfulField();

			/** @var Result $r */
			$r = $this->doFinalAction($hasMeaningfulFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Return shipment collection
	 *
	 * @return ShipmentCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public function getShipmentCollection()
	{
		if (empty($this->shipmentCollection))
		{
			$this->shipmentCollection = $this->loadShipmentCollection();
		}

		return $this->shipmentCollection;
	}

	/**
	 * Return trade binding collection
	 *
	 * @return TradeBindingCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function getTradeBindingCollection()
	{
		if (empty($this->tradeBindingCollection))
		{
			$this->tradeBindingCollection = $this->loadTradeBindingCollection();
		}

		return $this->tradeBindingCollection;
	}

	/**
	 * Return payment collection
	 *
	 * @return PaymentCollection
	 */
	public function getPaymentCollection()
	{
		if (empty($this->paymentCollection))
		{
			$this->paymentCollection = $this->loadPaymentCollection();
		}

		return $this->paymentCollection;
	}

	/**
	 * Load shipment collection
	 *
	 * @return ShipmentCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public function loadShipmentCollection()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var ShipmentCollection $shipmentCollectionClassName */
		$shipmentCollectionClassName = $registry->getShipmentCollectionClassName();
		return $shipmentCollectionClassName::load($this);
	}

	/**
	 * Load payment collection
	 *
	 * @return PaymentCollection
	 * @throws Main\ArgumentException
	 */
	public function loadPaymentCollection()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var PaymentCollection $paymentCollectionClassName */
		$paymentCollectionClassName = $registry->getPaymentCollectionClassName();
		return $paymentCollectionClassName::load($this);
	}

	/**
	 * @return TradeBindingCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	protected function loadTradeBindingCollection()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var TradeBindingCollection $tradeBindingCollection */
		$tradeBindingCollection = $registry->get(Registry::ENTITY_TRADE_BINDING_COLLECTION);

		return $tradeBindingCollection::load($this);
	}

	/**
	 * @param $orderId
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function deleteEntitiesNoDemand($orderId)
	{
		$r = parent::deleteEntitiesNoDemand($orderId);
		if (!$r->isSuccess())
			return $r;

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var Shipment $shipmentClassName */
		$shipmentClassName = $registry->getShipmentClassName();
		$shipmentClassName::deleteNoDemand($orderId);
		if (!$r->isSuccess())
			return $r;

		/** @var Payment $paymentClassName */
		$paymentClassName = $registry->getPaymentClassName();
		$paymentClassName::deleteNoDemand($orderId);
		if (!$r->isSuccess())
			return $r;

		return new Result();
	}

	/**
	 * @param OrderBase $order
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function deleteEntities(OrderBase $order)
	{
		parent::deleteEntities($order);

		/** @var Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			$shipment->delete();
		}

		/** @var Payment $payment */
		foreach ($order->getPaymentCollection() as $payment)
		{
			$payment->delete();
		}
	}

	/**
	 * @return bool
	 */
	public function isShipped()
	{
		return $this->getShipmentCollection()->isShipped();
	}

	/**
	 * @param $action
	 * @param Payment $payment
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onPaymentCollectionModify($action, Payment $payment, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		if ($action == EventActions::DELETE)
		{
			if ($this->getField('PAY_SYSTEM_ID') == $payment->getPaymentSystemId())
			{
				/** @var Payment $entityPayment */
				foreach ($payment->getCollection() as $entityPayment)
				{
					if (intval($entityPayment->getField('PAY_SYSTEM_ID')) > 0
						&& intval($entityPayment->getField('PAY_SYSTEM_ID')) != $payment->getPaymentSystemId())
					{
						$this->setFieldNoDemand('PAY_SYSTEM_ID', $entityPayment->getField('PAY_SYSTEM_ID'));
						break;
					}
				}
			}
		}
		elseif ($action === EventActions::ADD)
		{
			if ($payment->getId() === 0)
			{
				$this->getPropertyCollection()->refreshRelated();
			}
		}

		if ($action != EventActions::UPDATE)
		{
			return $result;
		}

		if (($name == "CURRENCY") && ($value != $this->getField("CURRENCY")))
		{
			throw new Main\NotImplementedException();
		}

		if ($name == "SUM" || $name == "PAID")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_PAID_ORDER_CANCELED'), 'SALE_ORDER_PAID_ORDER_CANCELED'));
				return $result;
			}

			if ($name == "SUM"
				&& !$payment->isPaid()
			)
			{
				return $result;
			}

			$r = $this->syncOrderAndPayments($payment);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}
		}
		elseif ($name == "PAY_SYSTEM_ID")
		{
			$this->setFieldNoDemand('PAY_SYSTEM_ID', $payment->getField('PAY_SYSTEM_ID'));

			$this->getPropertyCollection()->refreshRelated();
		}
		elseif ($name == "DATE_PAID")
		{
			$this->setFieldNoDemand('DATE_PAYED', $payment->getField('DATE_PAID'));
		}
		elseif ($name == "PAY_VOUCHER_NUM")
		{
			$this->setFieldNoDemand('PAY_VOUCHER_NUM', $payment->getField('PAY_VOUCHER_NUM'));
		}
		elseif ($name == "PAY_VOUCHER_DATE")
		{
			$this->setFieldNoDemand('PAY_VOUCHER_DATE', $payment->getField('PAY_VOUCHER_DATE'));
		}
		elseif ($name == "EMP_PAID_ID")
		{
			$this->setFieldNoDemand('EMP_PAYED_ID', $payment->getField('EMP_PAID_ID'));
		}
		elseif ($name == "MARKED")
		{
			if ($value == "Y")
			{
				/** @var Result $r */
				$r = $this->setField('MARKED', 'Y');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}
		elseif ($name == "REASON_MARKED")
		{
			$r = $this->setReasonMarked($value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		if ($value != $oldValue)
		{
			$fields = $this->fields->getChangedValues();
			if (!empty($fields) && !array_key_exists("UPDATED_1C", $fields) && $name != 'UPDATED_1C')
			{
				parent::setField("UPDATED_1C", "N");
			}
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param float|int|mixed|string $oldValue
	 * @param float|int|mixed|string $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		$result = parent::onFieldModify($name, $oldValue, $value);

		if ($name == "PRICE")
		{
			$r = $this->getShipmentCollection()->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}

			$r = $this->getPaymentCollection()->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}

			$r = $this->syncOrderAndPayments();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		elseif($name == "MARKED")
		{
			global $USER;

			if (is_object($USER))
			{
				$this->setField('EMP_MARKED_ID', $USER->GetID());
			}

			$this->setField('DATE_MARKED', new Main\Type\DateTime());

			$r = $this->getShipmentCollection()->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $name
	 * @param $oldValue
	 * @param $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function onOrderModify($name, $oldValue, $value)
	{
		$result = new Result();

		$r = $this->getPaymentCollection()->onOrderModify($name, $oldValue, $value);
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		$r = $this->getShipmentCollection()->onOrderModify($name, $oldValue, $value);
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param BasketItem $basketItem
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onBeforeBasketItemDelete(BasketItem $basketItem)
	{
		$result = new Result();

		$r = $this->getShipmentCollection()->onBeforeBasketItemDelete($basketItem);
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		$r = $this->getPaymentCollection()->onBeforeBasketItemDelete($basketItem);
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}


		return $result;
	}

	/**
	 * Modify basket.
	 *
	 * @param string $action
	 * @param BasketItemBase $basketItem
	 * @param null $name
	 * @param null $oldValue
	 * @param null $value
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	public function onBasketModify($action, BasketItemBase $basketItem, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		if ($action === EventActions::DELETE)
		{
			$r = parent::onBasketModify($action, $basketItem, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			return $result;
		}
		elseif ($action === EventActions::ADD)
		{
			if ($basketItem->getField('ORDER_ID'))
			{
				return $result;
			}

			return $this->getShipmentCollection()->onBasketModify($action, $basketItem, $name, $oldValue, $value);
		}
		elseif ($action !== EventActions::UPDATE)
		{
			return $result;
		}

		if ($name === "QUANTITY")
		{
			$r = parent::onBasketModify($action, $basketItem, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$r = $this->getShipmentCollection()->onBasketModify($action, $basketItem, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
			elseif ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			return $result;
		}
		elseif ($name === "PRICE")
		{
			$r = parent::onBasketModify($action, $basketItem, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($this->getId() === 0 && !$this->isMathActionOnly())
			{
				return $this->getShipmentCollection()->onBasketModify($action, $basketItem, $name, $oldValue, $value);
			}
		}
		elseif ($name === 'WEIGHT')
		{
			return $this->getShipmentCollection()->onBasketModify($action, $basketItem, $name, $oldValue, $value);
		}
		else
		{
			$r = parent::onBasketModify($action, $basketItem, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function onBeforeBasketRefresh()
	{
		$result = new Result();

		$r = $this->getShipmentCollection()->tryUnreserve();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function onAfterBasketRefresh()
	{
		$result = new Result();

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($this->getShipmentCollection() as $shipment)
		{
			if (
				$shipment->isShipped()
				|| !$shipment->needReservation()
				|| !Configuration::isEnableAutomaticReservation()
			)
			{
				continue;
			}

			$r = $shipment->tryReserve();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Sync.
	 *
	 * @param Payment|null $payment
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	private function syncOrderAndPayments(Payment $payment = null)
	{
		$result = new Result();

		$oldPaid = $this->getField('PAYED');
		$paymentCollection = $this->getPaymentCollection();
		$sumPaid = $paymentCollection->getPaidSum();

		if ($payment)
		{
			$finalSumPaid = $sumPaid;

			if ($payment->isPaid())
			{
				if ($sumPaid > $this->getPrice())
				{
					$finalSumPaid = $this->getSumPaid() + $payment->getSum();
				}
			}
			else
			{
				$r = $this->syncOrderPaymentPaid($payment);
				if ($r->isSuccess())
				{
					$paidResult = $r->getData();
					if (isset($paidResult['SUM_PAID']))
					{
						$finalSumPaid = $paidResult['SUM_PAID'];
					}
				}
				else
				{
					return $result->addErrors($r->getErrors());
				}
			}
		}
		else
		{
			$finalSumPaid = $this->getSumPaid();

			$r = $this->syncOrderPaid();
			if (!$r->isSuccess())
			{
				return $result->addErrors($r->getErrors());
			}

			$paidResult = $r->getData();
			if (isset($paidResult['SUM_PAID']))
			{
				$finalSumPaid = $paidResult['SUM_PAID'];
			}
		}

		$paid = false;

		if ($finalSumPaid >= 0 && $paymentCollection->hasPaidPayment()
			&& PriceMaths::roundPrecision($this->getPrice()) <= PriceMaths::roundPrecision($finalSumPaid))
		{
			$paid = true;
		}

		$this->setFieldNoDemand('PAYED', $paid ? "Y" : "N");

		if ($this->getFields()->isChanged('PAYED'))
		{
			Internals\EventsPool::addEvent(
				$this->getInternalId(),
				EventActions::EVENT_ON_ORDER_PAID,
				[
					'ENTITY' => $this,
				]
			);

			Internals\EventsPool::addEvent(
				$this->getInternalId(),
				EventActions::EVENT_ON_ORDER_PAID_SEND_MAIL,
				[
					'ENTITY' => $this,
				]
			);
		}

		if ($finalSumPaid > 0 && $finalSumPaid > $this->getPrice())
		{
			if (!$payment || $payment->isPaid())
			{
				Internals\UserBudgetPool::addPoolItem($this, $finalSumPaid - $this->getPrice(), Internals\UserBudgetPool::BUDGET_TYPE_EXCESS_SUM_PAID, $payment);
			}

			$finalSumPaid = $this->getPrice();
		}

		$this->setFieldNoDemand('SUM_PAID', $finalSumPaid);

		$r = $this->onAfterSyncPaid($oldPaid);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return Result
	 */
	private function syncOrderPaymentPaid(Payment $payment)
	{
		$result = new Result();

		if ($payment->isPaid())
		{
			return $result;
		}

		$sumPaid = $this->getPaymentCollection()->getPaidSum();

		$userBudget = Internals\UserBudgetPool::getUserBudgetByOrder($this);

		$debitSum = $payment->getSum();

		$maxPaid = $payment->getSum() + $sumPaid - $this->getSumPaid();

		if ($maxPaid >= $payment->getSum())
		{
			$finalSumPaid = $this->getSumPaid();
		}
		else
		{
			$debitSum = $maxPaid;
			$finalSumPaid = $sumPaid;
		}

		if ($debitSum > 0 && $payment->isInner())
		{
			if (PriceMaths::roundPrecision($debitSum) > PriceMaths::roundPrecision($userBudget))
			{
				$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_PAYMENT_CANCELLED_PAID'), 'SALE_ORDER_PAYMENT_NOT_ENOUGH_USER_BUDGET_SYNCPAID') );
				return $result;
			}

			Internals\UserBudgetPool::addPoolItem($this, ($debitSum * -1), Internals\UserBudgetPool::BUDGET_TYPE_ORDER_CANCEL_PART, $payment);
		}

		$result->setData(array('SUM_PAID' => $finalSumPaid));

		return $result;
	}

	/**
	 * @return Result
	 */
	private function syncOrderPaid()
	{
		$result = new Result();

		if ($this->getSumPaid() == $this->getPrice())
			return $result;

		$debitSum = $this->getPrice() - $this->getSumPaid();

		$sumPaid = $this->getPaymentCollection()->getPaidSum();
		$userBudget = Internals\UserBudgetPool::getUserBudgetByOrder($this);

		$bePaid = $sumPaid - $this->getSumPaid();

		if ($bePaid > 0)
		{
			if ($debitSum > $bePaid)
			{
				$debitSum = $bePaid;
			}

			if ($debitSum >= $userBudget)
			{
				$debitSum = $userBudget;
			}

			if ($userBudget >= $debitSum && $debitSum > 0)
			{
				Internals\UserBudgetPool::addPoolItem($this, ($debitSum * -1), Internals\UserBudgetPool::BUDGET_TYPE_ORDER_PAY);

				$finalSumPaid = $this->getSumPaid() + $debitSum;
				$result->setData(['SUM_PAID' => $finalSumPaid]);
			}
		}

		return $result;
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function getStatusOnPaid()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);
		return $optionClassName::get('sale', 'status_on_paid', '');
	}

	/**
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function getStatusOnPartialPaid()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);
		return $optionClassName::get('sale', 'status_on_half_paid', '');
	}

	/**
	 * @param null $oldPaid
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 */
	private function onAfterSyncPaid($oldPaid = null)
	{
		$result = new Result();

		$paymentCollection = $this->getPaymentCollection();

		$shipmentCollection = $this->getShipmentCollection();

		$orderStatus = null;

		$allowSetStatus = false;

		if ($oldPaid == "N")
		{
			if ($this->isPaid())
			{
				$orderStatus = $this->getStatusOnPaid();
			}
			elseif ($paymentCollection->hasPaidPayment())
			{
				$orderStatus = $this->getStatusOnPartialPaid();
			}

			$allowSetStatus = ($this->getField('STATUS_ID') != static::getFinalStatus());
		}

		if ($orderStatus !== null && $allowSetStatus)
		{
			if (strval($orderStatus) != '')
			{
				$r = $this->setField('STATUS_ID', $orderStatus);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());

					$registry = Registry::getInstance(static::getRegistryType());

					/** @var EntityMarker $entityMarker */
					$entityMarker = $registry->getEntityMarkerClassName();
					$entityMarker::addMarker($this, $this, $r);
					$this->setField('MARKED', 'Y');
				}
			}
		}

		if (Configuration::isEnableAutomaticReservation())
		{
			$r = null;

			if (Configuration::getProductReservationCondition() == ReserveCondition::ON_PAY)
			{
				if ($paymentCollection->hasPaidPayment())
				{
					$r = $shipmentCollection->tryReserve();
				}
				else
				{
					$r = $shipmentCollection->tryUnreserve();
				}
			}
			elseif (Configuration::getProductReservationCondition() == ReserveCondition::ON_FULL_PAY)
			{
				if ($oldPaid == "N" && $this->isPaid())
				{
					$r = $shipmentCollection->tryReserve();
				}
				elseif ($oldPaid == "Y" && !$this->isPaid())
				{
					$r = $shipmentCollection->tryUnreserve();
				}
			}

			if ($r !== null)
			{
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
				}
			}
		}

		$allowDelivery = null;

		if (Configuration::getAllowDeliveryOnPayCondition() === Configuration::ALLOW_DELIVERY_ON_PAY)
		{
			if ($oldPaid == "N" && $paymentCollection->hasPaidPayment())
			{
				$allowDelivery = true;
			}
			elseif ($oldPaid == "Y" && !$paymentCollection->hasPaidPayment())
			{
				$allowDelivery = false;
			}
		}
		elseif(Configuration::getAllowDeliveryOnPayCondition() === Configuration::ALLOW_DELIVERY_ON_FULL_PAY)
		{
			if ($oldPaid == "N" && $this->isPaid())
			{
				$allowDelivery = true;
			}
			elseif ($oldPaid == "Y" && !$this->isPaid())
			{
				$allowDelivery = false;
			}
		}

		if ($allowDelivery !== null)
		{
			if ($allowDelivery)
			{
				$r = $shipmentCollection->allowDelivery();
			}
			else
			{
				$r = $shipmentCollection->disallowDelivery();
			}

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 */
	protected function refreshInternal()
	{
		$result = parent::refreshInternal();
		if (!$result->isSuccess())
		{
			return $result;
		}

		return $this->getShipmentCollection()->refreshData();
	}

	/**
	 * @internal
	 *
	 * @param array $data
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 */
	public function applyDiscount(array $data)
	{
		$r = parent::applyDiscount($data);
		if (!$r->isSuccess())
		{
			return $r;
		}

		if (isset($data['SHIPMENT']) && intval($data['SHIPMENT']) > 0
			&& (isset($data['PRICE_DELIVERY']) && floatval($data['PRICE_DELIVERY']) >= 0
				|| isset($data['DISCOUNT_PRICE']) && floatval($data['DISCOUNT_PRICE']) >= 0))
		{
			if ($shipment = $this->getShipmentCollection()->getItemByShipmentCode($data['SHIPMENT']))
			{
				if (!$shipment->isCustomPrice())
				{
					$data['PRICE_DELIVERY'] = (float)$data['PRICE_DELIVERY'];
					$data['DISCOUNT_PRICE'] = (float)$data['DISCOUNT_PRICE'];
					if ($data['PRICE_DELIVERY'] >= 0 && $data['PRICE_DELIVERY'] != $shipment->getPrice())
					{
						$data['PRICE_DELIVERY'] = PriceMaths::roundPrecision(floatval($data['PRICE_DELIVERY']));
						$shipment->setField('PRICE_DELIVERY', $data['PRICE_DELIVERY']);
					}

					if ($data['DISCOUNT_PRICE'] != $shipment->getField('DISCOUNT_PRICE'))
					{
						$data['DISCOUNT_PRICE'] = PriceMaths::roundPrecision(floatval($data['DISCOUNT_PRICE']));
						$shipment->setField('DISCOUNT_PRICE', $data['DISCOUNT_PRICE']);
					}
				}

			}
		}

		return new Result();
	}

	/**
	 * Lock order.
	 *
	 * @param int $id			Order id.
	 * @return Entity\UpdateResult|Result
	 * @throws \Exception
	 */
	public static function lock($id)
	{
		global $USER;

		$result = new Result();
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_WRONG_ID'), 'SALE_ORDER_WRONG_ID') );
			return $result;
		}

		return static::updateInternal($id, array(
			'DATE_LOCK' => new Main\Type\DateTime(),
			'LOCKED_BY' => (is_object($USER) ? $USER->GetID(): false)
		));
	}

	/**
	 * Unlock order.
	 *
	 * @param int $id			Order id.
	 * @return Entity\UpdateResult|Result
	 * @throws Main\ArgumentNullException
	 * @throws \Exception
	 */
	public static function unlock($id)
	{
		global $USER;

		$result = new Result();
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_WRONG_ID'), 'SALE_ORDER_WRONG_ID') );
			return $result;
		}

		if(!$order = static::load($id))
		{
			$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND') );
			return $result;
		}

		$userRights = \CMain::getUserRight("sale", $USER->getUserGroupArray(), "Y", "Y");

		if (($userRights >= "W") || ($order->getField("LOCKED_BY") == $USER->getID()))
		{
			return static::updateInternal($id, array(
				'DATE_LOCK' => null,
				'LOCKED_BY' => null
			));
		}

		return $result;
	}

	/**
	 * Return TRUE if order is locked.
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function isLocked($id)
	{
		/** @var Result $r */
		$r = static::getLockedStatus($id);
		if ($r->isSuccess())
		{
			$lockResultData = $r->getData();

			if (array_key_exists('LOCK_STATUS', $lockResultData)
				&& $lockResultData['LOCK_STATUS'] == static::SALE_ORDER_LOCK_STATUS_RED)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Return order locked status.
	 *
	 * @param int $id		Order id.
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	public static function getLockedStatus($id)
	{
		$result = new Result();

		$res = static::getList(array(
				'filter' => array('=ID' => $id),
				'select' => array(
					'LOCKED_BY',
					'LOCK_STATUS',
					'DATE_LOCK'
				)
		));

		if ($data = $res->fetch())
		{
			$result->addData(array(
				'LOCKED_BY' => $data['LOCKED_BY'],
				'LOCK_STATUS' => $data['LOCK_STATUS'],
				'DATE_LOCK' => $data['DATE_LOCK'],
			));
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function verify()
	{
		$result = parent::verify();

		$r = $this->getPaymentCollection()->verify();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$r = $this->getShipmentCollection()->verify();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @param $mapping
	 * @return Order|null|string
	 */
	public function getBusinessValueProviderInstance($mapping)
	{
		$providerInstance = null;

		if (is_array($mapping) && isset($mapping['PROVIDER_KEY']))
		{
			switch ($mapping['PROVIDER_KEY'])
			{
				case 'ORDER':
				case 'PROPERTY':
					$providerInstance = $this;
					break;
				case 'USER':
					$providerInstance = $this->getField('USER_ID');
					break;
				case 'COMPANY':
					$providerInstance = $this->getField('COMPANY_ID');
					break;
			}
		}

		return $providerInstance;
	}

	/**
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\OrderTable::getList($parameters);
	}

	/**
	 * @param \SplObjectStorage $cloneEntity
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function cloneEntities(\SplObjectStorage $cloneEntity)
	{
		/** @var Order $orderClone */
		parent::cloneEntities($cloneEntity);

		$orderClone = $cloneEntity[$this];

		$orderClone->shipmentCollection = $this->getShipmentCollection()->createClone($cloneEntity);

		$orderClone->paymentCollection = $this->getPaymentCollection()->createClone($cloneEntity);

		$orderClone->tradeBindingCollection = $this->getTradeBindingCollection()->createClone($cloneEntity);
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		if (parent::isChanged())
		{
			return true;
		}

		if ($this->getPaymentCollection()->isChanged())
		{
			return true;
		}

		if ($this->getShipmentCollection()->isChanged())
		{
			return true;
		}

		return false;
	}

	/**
	 * @internal
	 * @return void
	 */
	public function clearChanged()
	{
		parent::clearChanged();

		$this->getPaymentCollection()->clearChanged();

		$this->getShipmentCollection()->clearChanged();

		$this->getTradeBindingCollection()->clearChanged();
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public function getDeliveryIdList() : array
	{
		$result = [];

		/** @var Shipment $shipment */
		foreach ($this->getShipmentCollection()->getNotSystemItems() as $shipment)
		{
			if ($shipment->getDeliveryId() > 0)
			{
				$result[] = $shipment->getDeliveryId();
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getPaySystemIdList() : array
	{
		$result = [];

		/** @var Payment $payment */
		foreach ($this->getPaymentCollection() as $payment)
		{
			if ($payment->getPaymentSystemId() > 0)
			{
				$result[] = $payment->getPaymentSystemId();
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function calculateVat()
	{
		$vatInfo = parent::calculateVat();

		/** @var Shipment $shipment */
		foreach ($this->getShipmentCollection() as $shipment)
		{
			$rate = $shipment->getVatRate();
			if ($rate)
			{
				$vatInfo['VAT_SUM'] += $shipment->getVatSum();
				$vatInfo['VAT_RATE'] = max($vatInfo['VAT_RATE'], $rate);
			}
		}

		return $vatInfo;
	}

	/**
	 * @return Result
	 */
	protected function saveEntities()
	{
		$result = parent::saveEntities();

		$r = $this->getShipmentCollection()->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$r = $this->getPaymentCollection()->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		// user budget
		Internals\UserBudgetPool::onUserBudgetSave($this->getUserId());

		$r = $this->getTradeBindingCollection()->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		$res = Cashbox\Internals\Pool::generateChecks($this->getInternalId());
		if (!$res->isSuccess())
		{
			$result->addWarnings($res->getErrors());

			$warningResult = new Result();
			$warningResult->addWarnings($res->getErrors());

			$registry = Registry::getInstance(static::getRegistryType());
			/** @var EntityMarker $entityMarker */
			$entityMarker = $registry->getEntityMarkerClassName();
			$entityMarker::addMarker($this, $this, $warningResult);
			static::updateInternal($this->getId(), array('MARKED' => 'Y'));
		}

		return $result;
	}

	/**
	 * @return float
	 */
	protected function calculatePrice()
	{
		$price = parent::calculatePrice();
		$shipmentCollection = $this->getShipmentCollection();

		return $price + $shipmentCollection->getPriceDelivery();
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	protected function onBeforeSave()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var EntityMarker $entityMarker */
		$entityMarker = $registry->getEntityMarkerClassName();

		$result = Internals\Catalog\Provider::save($this);
		if ($result->hasWarnings())
		{
			$entityMarker::addMarker($this, $this, $result);
			if ($this->getId() > 0)
			{
				static::updateInternal($this->getId(), ['MARKED' => 'Y']);
			}
		}

		$entityMarker::refreshMarkers($this);

		return $result;
	}

	/**
	 * @return Result
	 */
	protected function onAfterSave()
	{
		$result = parent::onAfterSave();
		if (!$result->isSuccess())
		{
			return $result;
		}

		global $CACHE_MANAGER;

		if (defined("CACHED_b_sale_order")
			&& (
				$this->isNew
				|| (
					$this->isChanged()
					&& $this->getField("UPDATED_1C") != "Y"
				)
			)
		)
		{
			$CACHE_MANAGER->Read(CACHED_b_sale_order, "sale_orders");
			$CACHE_MANAGER->SetImmediate("sale_orders", true);
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = parent::save();

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::collectEntityFields('ORDER', $this->getId(), $this->getId());

		/** @var EntityMarker $entityMarker */
		$entityMarker = $registry->getEntityMarkerClassName();
		if ($entityMarker::hasErrors($this))
		{
			$entityMarker::saveMarkers($this);
			static::updateInternal(
				$this->getId(),
				[
					'MARKED' => 'Y',
					'REASON_MARKED' => $this->getField('REASON_MARKED')
				]
			);
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function add()
	{
		$result = parent::add();

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::addAction('ORDER', $result->getId(), 'ORDER_ADDED', $result->getId(), $this);

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	protected function update()
	{
		$result = parent::update();

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();

		if (!$result->isSuccess())
		{
			$orderHistory::addAction(
				'ORDER',
				$this->getId(),
				'ORDER_UPDATE_ERROR',
				$this->getId(),
				$this,
				array("ERROR" => $result->getErrorMessages())
			);
		}
		else
		{
			$orderHistory::addAction(
				'ORDER',
				$this->getId(),
				'ORDER_UPDATED',
				$this->getId(),
				$this,
				array(),
				OrderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
			);
		}

		return $result;
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void
	 */
	protected function callEventOnSaleOrderEntitySaved()
	{
		parent::callEventOnSaleOrderEntitySaved();

		$changeMeaningfulFields = array(
			"PERSON_TYPE_ID",
			"CANCELED",
			"STATUS_ID",
			"MARKED",
			"PRICE",
			"SUM_PAID",
			"USER_ID",
			"EXTERNAL_ORDER",
		);

		if ($this->isChanged())
		{
			$logFields = array();

			if (!$this->isNew)
			{
				$fields = $this->getFields();
				$originalValues = $fields->getOriginalValues();

				foreach($originalValues as $originalFieldName => $originalFieldValue)
				{
					if (in_array($originalFieldName, $changeMeaningfulFields) && $this->getField($originalFieldName) != $originalFieldValue)
					{
						$logFields[$originalFieldName] = $this->getField($originalFieldName);
						$logFields['OLD_'.$originalFieldName] = $originalFieldValue;
					}
				}

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var OrderHistory $orderHistory */
				$orderHistory = $registry->getOrderHistoryClassName();
				$orderHistory::addLog(
					'ORDER',
					$this->getId(),
					"ORDER_UPDATE",
					$this->getId(),
					$this,
					$logFields,
					$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
				);
			}
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @return void
	 */
	protected function callEventOnSaleOrderSaved()
	{
		$registry = Registry::getInstance(static::getRegistryType());

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::addLog(
			'ORDER',
			$this->getId(),
			'ORDER_EVENT_ON_ORDER_SAVED',
			null,
			null,
			array(),
			$orderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1
		);

		parent::callEventOnSaleOrderSaved();
	}

	/**
	 * @param array $data
	 * @return Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $data)
	{
		return Internals\OrderTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Entity\UpdateResult
	 * @throws \Exception
	 */
	protected static function updateInternal($primary, array $data)
	{
		return Internals\OrderTable::update($primary, $data);
	}

	/**
	 * @param $primary
	 * @return Entity\DeleteResult
	 * @throws \Exception
	 */
	protected static function deleteInternal($primary)
	{
		return Internals\OrderTable::delete($primary);
	}

	/**
	 * @param $orderId
	 * @throws Main\ArgumentException
	 */
	protected static function deleteExternalEntities($orderId)
	{
		parent::deleteExternalEntities($orderId);

		$registry = Registry::getInstance(static::getRegistryType());

		TradingPlatform\OrderTable::deleteByOrderId($orderId);
		Internals\OrderProcessingTable::deleteByOrderId($orderId);

		/** @var EntityMarker $entityMarker */
		$entityMarker = $registry->getEntityMarkerClassName();
		$entityMarker::deleteByOrderId($orderId);

		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();
		$orderHistory::deleteByOrderId($orderId);
	}

	/**
	 * Save field modify to history.
	 *
	 * @param string $name				Field name.
	 * @param null|string $oldValue		Old value.
	 * @param null|string $value		New value.
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0)
		{
			$historyFields = array();
			if ($name == "PRICE")
			{
				$historyFields['CURRENCY'] = $this->getCurrency();
			}
			if ($name == "RESPONSIBLE_ID")
			{
				$historyFields[$name] = $value;
			}

			$historyFields['OLD_'.$name] = $oldValue;

			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::addField(
				'ORDER',
				$this->getId(),
				$name,
				$oldValue,
				$value,
				$this->getId(),
				$this,
				$historyFields
			);
		}
	}

	/**
	 * @deprecated
	 *
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	public function getDeliverySystemId()
	{
		return $this->getDeliveryIdList();
	}

	/**
	 * @deprecated
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	public function getPaymentSystemId()
	{
		return $this->getPaySystemIdList();
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function toArray() : array
	{
		$result = parent::toArray();

		$result['PAYMENTS'] = $this->getPaymentCollection()->toArray();
		$result['SHIPMENTS'] = $this->getShipmentCollection()->toArray();
		$result['TRADE_BINDINGS'] = $this->getTradeBindingCollection()->toArray();

		return $result;
	}
}
