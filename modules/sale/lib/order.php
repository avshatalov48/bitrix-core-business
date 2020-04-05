<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Main;
use Bitrix\Main\Type;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem\Manager;

Loc::loadMessages(__FILE__);

class Order
	extends OrderBase implements \IShipmentOrder, \IPaymentOrder, IBusinessValueProvider
{
	private $isNew = true;
	private $isSaving = false;

	/** @var Discount $discount */
	protected $discount = null;

	const SALE_ORDER_LOCK_STATUS_RED = 'red';
	const SALE_ORDER_LOCK_STATUS_GREEN = 'green';
	const SALE_ORDER_LOCK_STATUS_YELLOW = 'yellow';


	protected $isStartField = null;
	protected $isMeaningfulField = false;
	protected $isOnlyMathAction = null;

	protected $isClone = false;
	protected $printedChecks = array();

	/**
	 * @param array $fields				Data.
	 */
	protected function __construct(array $fields = array())
	{
		parent::__construct($fields);
		$this->isNew = (empty($fields['ID']));
	}

	public function getPrintedChecks()
	{
		return $this->printedChecks;
	}

	public function addPrintedCheck($check)
	{
		$this->printedChecks[] = $check;
	}

	private static $eventClassName = null;

	/**
	 * @param array $fields
	 * @throws Main\NotImplementedException
	 * @return Order
	 */
	protected static function createOrderObject(array $fields = array())
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$orderClassName = $registry->getOrderClassName();

		return new $orderClassName($fields);
	}

	/**
	 * Modify shipment collection.
	 *
	 * @param string $action				Action code.
	 * @param Shipment $shipment			Shipment.
	 * @param null|string $name					Field name.
	 * @param null|string|int|float $oldValue				Old value.
	 * @param null|string|int|float $value					New value.
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function onShipmentCollectionModify($action, Shipment $shipment, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

		if ($action == EventActions::DELETE)
		{
			if ($this->getField('DELIVERY_ID') == $shipment->getDeliveryId())
			{
				/** @var ShipmentCollection $shipmentCollection */
				if (!$shipmentCollection = $shipment->getCollection())
				{
					throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
				}

				$foundShipment = false;

				/** @var Shipment $entityShipment */
				foreach ($shipmentCollection as $entityShipment)
				{
					if ($entityShipment->isSystem())
						continue;

					if (intval($entityShipment->getField('DELIVERY_ID')) > 0)
					{
						$foundShipment = true;
						$this->setFieldNoDemand('DELIVERY_ID', $entityShipment->getField('DELIVERY_ID'));
						break;
					}
				}

				if (!$foundShipment && !$shipment->isSystem())
				{
					/** @var Shipment $systemShipment */
					if (($systemShipment = $shipmentCollection->getSystemShipment()) && intval($systemShipment->getField('DELIVERY_ID')) > 0)
					{
						$this->setFieldNoDemand('DELIVERY_ID', $systemShipment->getField('DELIVERY_ID'));
					}
				}
			}
		}

		if ($action != EventActions::UPDATE)
			return $result;


		if ($name == "ALLOW_DELIVERY")
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
				if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_SHIPMENT_DELIVER))
				{
					$event = new Main\Event('sale', EventActions::EVENT_ON_SHIPMENT_DELIVER, array(
						'ENTITY' =>$shipment
					));
					$event->send();
				}

				Notify::callNotify($shipment, EventActions::EVENT_ON_SHIPMENT_DELIVER);
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if (Configuration::getProductReservationCondition() == Configuration::RESERVE_ON_ALLOW_DELIVERY)
			{
				if ($value == "Y")
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
						EntityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
				else
				{
					if (!$shipment->isShipped())
					{
						/** @var Result $r */
						$r = $shipment->tryUnreserve();
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}

						if ($r->hasWarnings())
						{
							$result->addWarnings($r->getWarnings());
							EntityMarker::addMarker($this, $shipment, $r);
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
			}

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}
			
			$orderStatus = null;

			if ($oldValue == "N")
			{
				if ($shipmentCollection->isAllowDelivery())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_allow_delivery', '');
				}
				elseif ($shipmentCollection->hasAllowDelivery())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_allow_delivery_one_of', '');
				}
			}

			if ($orderStatus !== null && $this->getField('STATUS_ID') != OrderStatus::getFinalStatus())
			{
				if (strval($orderStatus) != '')
				{
					$r = $this->setField('STATUS_ID', $orderStatus);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						EntityMarker::addMarker($this, $this, $r);
						$this->setField('MARKED', 'Y');
					}
				}
			}

			if (Configuration::needShipOnAllowDelivery() && $value == "Y")
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
						EntityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
			}

			if ($shipmentCollection->isAllowDelivery() && $this->getField('ALLOW_DELIVERY') == 'N')
				$this->setFieldNoDemand('DATE_ALLOW_DELIVERY', new Type\DateTime());

			$this->setFieldNoDemand('ALLOW_DELIVERY', $shipmentCollection->isAllowDelivery() ? "Y" : "N");
		}
		elseif ($name == "DEDUCTED")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_SHIPMENT_ORDER_CANCELED'), 'SALE_ORDER_SHIPMENT_ORDER_CANCELED'));
				return $result;
			}

			if (Configuration::getProductReservationCondition() == Configuration::RESERVE_ON_SHIP)
			{
				if ($value == "Y")
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
						EntityMarker::addMarker($this, $shipment, $r);
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
						EntityMarker::addMarker($this, $shipment, $r);
						if (!$shipment->isSystem())
						{
							$shipment->setField('MARKED', 'Y');
						}
					}
				}
			}

			if ($value == "Y")
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
					EntityMarker::addMarker($this, $shipment, $r);
					if (!$shipment->isSystem())
					{
						$shipment->setField('MARKED', 'Y');
					}
				}

			}
			elseif ($oldValue == 'Y')
			{
				/** @var Result $r */
				$r = $shipment->tryUnship();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}

				if ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
					EntityMarker::addMarker($this, $shipment, $r);
					if (!$shipment->isSystem())
					{
						$shipment->setField('MARKED', 'Y');
					}
				}
				if ($shipment->needReservation())
				{
					$r = $shipment->tryReserve();
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
					}

					if ($r->hasWarnings())
					{
						$result->addWarnings($r->getWarnings());
						EntityMarker::addMarker($this, $shipment, $r);
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
			
			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $shipment->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$orderStatus = null;

			$allowSetStatus = false;

			if ($oldValue == "N")
			{
				if ($shipmentCollection->isShipped())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_shipped_shipment', '');
				}
				elseif ($shipmentCollection->hasShipped())
				{
					$orderStatus = $optionClassName::get('sale', 'status_on_shipped_shipment_one_of', '');
				}
				$allowSetStatus = ($this->getField('STATUS_ID') != OrderStatus::getFinalStatus());
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

			if (strval($orderStatus) != '' && $allowSetStatus)
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
						EntityMarker::addMarker($this, $this, $r);
						$this->setField('MARKED', 'Y');
					}
				}
			}

			$this->setFieldNoDemand('DEDUCTED', $shipmentCollection->isShipped() ? "Y" : "N");

			if ($shipmentCollection->isShipped())
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

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $shipment->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$discount = $this->getDiscount();
			$discount->setCalculateShipments($shipment);

			$r = $shipment->setField('PRICE_DELIVERY', $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		elseif ($name == "PRICE_DELIVERY")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_PRICE_DELIVERY_ORDER_CANCELED'), 'SALE_ORDER_PRICE_DELIVERY_ORDER_CANCELED'));
				return $result;
			}

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $shipment->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
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
		}
		elseif ($name == "TRACKING_NUMBER")
		{
			if ($shipment->isSystem() || ($shipment->getField('TRACKING_NUMBER') == $this->getField('TRACKING_NUMBER')))
			{
				return $result;
			}

			$this->setFieldNoDemand('TRACKING_NUMBER', $shipment->getField('TRACKING_NUMBER'));
		}


		if ($value != $oldValue)
		{
			$fields = $this->fields->getChangedValues();
			if (!array_key_exists("UPDATED_1C", $fields))
				parent::setField("UPDATED_1C", "N");
		}

		return $result;
	}

	/**
	 * Fill basket.
	 *
	 * @param Basket $basket			Basket.
	 * @return Result
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function setBasket(Basket $basket)
	{
		$result = new Result();

		$isStartField = $this->isStartField();

		$r = parent::setBasket($basket);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $this->getShipmentCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Result $r */
		$result = $shipmentCollection->resetCollection();
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
	 * @param BasketBase $basket
	 *
	 * @return Result
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

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $this->getShipmentCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Result $r */
		$result = $shipmentCollection->resetCollection();
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
	 * @param string $value
	 * @return null|static
	 * @throws Main\ArgumentNullException
	 * @throws Main\NotImplementedException
	 */
	public static function loadByAccountNumber($value)
	{
		if (strval(trim($value)) == '')
			throw new Main\ArgumentNullException("value");

		$filter = array(
			'filter' => array('=ACCOUNT_NUMBER' => $value),
			'select' => array('*'),
		);

		$list = static::loadByFilter($filter);
		if (!empty($list) && is_array($list))
		{
			return reset($list);
		}

		return null;
	}


	/**
	 * @param array $filter
	 *
	 * @return Main\DB\Result
	 */
	static protected function loadFromDb(array $filter)
	{
		return Internals\OrderTable::getList($filter);
	}


	/**
	 * @return bool|Basket
	 */
	protected function loadBasket()
	{
		if (intval($this->getId()) > 0)
			return Basket::loadItemsForOrder($this);

		return null;
	}

	/**
	 * @return ShipmentCollection
	 */
	public function getShipmentCollection()
	{
		if(empty($this->shipmentCollection))
		{
			$this->shipmentCollection = $this->loadShipmentCollection();
		}

		return $this->shipmentCollection;
	}


	/**
	 * @return PaymentCollection
	 */
	public function getPaymentCollection()
	{
		if(empty($this->paymentCollection))
		{
			$this->paymentCollection = $this->loadPaymentCollection();
		}
		return $this->paymentCollection;
	}

	/**
	 * @return ShipmentCollection|static
	 */
	public function loadShipmentCollection()
	{
		return ShipmentCollection::load($this);
	}

	/**
	 * @return PaymentCollection
	 */
	public function loadPaymentCollection()
	{
		return PaymentCollection::load($this);
	}

	/**
	 * @return PropertyValueCollection
	 */
	public function loadPropertyCollection()
	{
		return PropertyValueCollection::load($this);
	}

	/**
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	public function getDeliverySystemId()
	{
		$result = array();
		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $this->getShipmentCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		/** @var Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			$result[] = $shipment->getDeliveryId();
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws Main\ObjectNotFoundException
	 */
	public function getPaymentSystemId()
	{
		$result = array();
		/** @var PaymentCollection $paymentCollection */
		if (!$paymentCollection = $this->getPaymentCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
		}

		/** @var Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			$result[] = $payment->getPaymentSystemId();
		}

		return $result;
	}

	/**
	 * @return Entity\AddResult|Entity\UpdateResult|Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\SystemException
	 * @throws \Exception
	 */
	public function save()
	{
		global $USER, $CACHE_MANAGER;

		if ($this->isSaving())
		{
			trigger_error("Order saving in recursion", E_USER_WARNING);
		}

		$this->setSaving(true);

		$result = new Result();

		$id = $this->getId();
		$this->isNew = ($id == 0);

		if ($this->isNew)
		{
			$fields = $this->fields->getChangedValues();
			if (empty($fields['STATUS_ID']))
			{
				/** @var Result $r */
				$r = $this->setField("STATUS_ID", OrderStatus::getInitialStatus());
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_ORDER_BEFORE_SAVED))
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_BEFORE_SAVED, array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR'), 'SALE_EVENT_ON_BEFORE_ORDER_SAVED_ERROR');
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
		}

		if (!$result->isSuccess())
		{
			$this->setSaving(false);
			return $result;
		}

		$r = $this->verify();
		if (!$r->isSuccess())
		{
			/** @var ResultError $error */
			foreach ($r->getErrors() as $error)
			{
				if ($error instanceof ResultNotice)
				{
					continue;
				}
				elseif (!($error instanceof ResultWarning))
				{
					$result->addError($error);
				}
			}

			if (!$result->isSuccess())
			{
				if ($id > 0)
				{
					EntityMarker::saveMarkers($this);
				}
				$this->setSaving(false);
				return $result;
			}
		}

		/** @var Result $r */
		$r = Internals\Catalog\Provider::save($this);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
			EntityMarker::addMarker($this, $this, $r);
			if ($this->getId() > 0)
			{
				Internals\OrderTable::update($this->getId(), ['MARKED' => 'Y']);
			}
		}

		EntityMarker::refreshMarkers($this);

		if (!$result->isSuccess())
		{
			$resultPool = EntityMarker::getPoolAsResult($this);
			if (!$resultPool->isSuccess())
			{
				foreach ($resultPool->getErrors() as $errorPool)
				{
					foreach ($result->getErrors() as $error)
					{
						if ($errorPool->getCode() == $error->getCode() && $errorPool->getMessage() == $error->getMessage())
						{
							continue 2;
						}
					}

					$result->addError($errorPool);
				}
			}
			$this->setSaving(false);
			EntityMarker::saveMarkers($this);
			return $result;
		}

		$orderDateList = array();

		if ($id > 0)
		{
			$fields = $this->fields->getChangedValues();
			$isChanged = (bool)(!empty($fields));

			if ($this->isChanged())
			{
				if (!array_key_exists('DATE_UPDATE', $fields))
				{
					$fields['DATE_UPDATE'] = new Type\DateTime();
					$orderDateList['DATE_UPDATE'] = $fields['DATE_UPDATE'];
					$this->setField('DATE_UPDATE', $fields['DATE_UPDATE']);
				}
				elseif ($fields['DATE_UPDATE'] === null)
				{
					unset($fields['DATE_UPDATE']);
				}

				$fields['VERSION'] = intval($this->getField('VERSION')) + 1;
				$this->setFieldNoDemand('VERSION', $fields['VERSION']);

				if (array_key_exists('REASON_MARKED', $fields) && strlen($fields['REASON_MARKED']) > 255)
				{
					$fields['REASON_MARKED'] = substr($fields['REASON_MARKED'], 0, 255);
				}
			}

			if (!empty($fields) && is_array($fields))
			{
				$r = Internals\OrderTable::update($id, $fields);
				if (!$r->isSuccess())
				{
					OrderHistory::addAction(
						'ORDER',
						$id,
						'ORDER_UPDATE_ERROR',
						$id,
						$this,
						array("ERROR" => $r->getErrorMessages())
					);

					$result->addWarnings($r->getErrors());
					$this->setSaving(false);
					return $result;
				}

				if ($resultData = $r->getData())
					$result->setData($resultData);

				OrderHistory::addAction(
					'ORDER',
					$id,
					'ORDER_UPDATED',
					$id,
					$this,
					array(),
					OrderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
				);
			}
		}
		else
		{
			$currentDateTime = new Type\DateTime();
			if (!$this->getField('DATE_INSERT'))
				$this->setField('DATE_INSERT', $currentDateTime);

			if (!$this->getField('DATE_UPDATE'))
				$this->setField('DATE_UPDATE', $currentDateTime);

			$fields = $this->fields->getValues();

			$isChanged = true;

			$orderDateList['DATE_INSERT'] = $fields['DATE_INSERT'];
			$orderDateList['DATE_UPDATE'] = $fields['DATE_UPDATE'];

			if ($USER->isAuthorized())
			{
				$fields['CREATED_BY'] = $USER->getID();
				$this->setFieldNoDemand('CREATED_BY', $fields['CREATED_BY']);
			}

			if (!isset($fields['STATUS_ID']) || strval($fields['STATUS_ID']) == '')
			{
				$orderStatus = OrderStatus::getInitialStatus();
				if (!empty($orderStatus) && !is_array($orderStatus))
				{
					$fields['STATUS_ID'] = $orderStatus;
					$this->setFieldNoDemand('STATUS_ID', $fields['STATUS_ID']);
				}
			}

			if (isset($fields['STATUS_ID']) && strval($fields['STATUS_ID']) != '')
			{
				if (!isset($fields['DATE_STATUS']) || strval($fields['DATE_STATUS']) == '')
				{
					$fields['DATE_STATUS'] = new Type\DateTime();
					$this->setFieldNoDemand('DATE_STATUS', $fields['DATE_STATUS']);
				}


				if ((!isset($fields['EMP_STATUS_ID']) || (int)$fields['EMP_STATUS_ID'] <= 0) && $USER->isAuthorized())
				{
					$fields['EMP_STATUS_ID'] = $USER->getID();
					$this->setFieldNoDemand('EMP_STATUS_ID', $fields['EMP_STATUS_ID']);
				}
			}

			if (array_key_exists('REASON_MARKED', $fields) && strlen($fields['REASON_MARKED']) > 255)
			{
				$fields['REASON_MARKED'] = substr($fields['REASON_MARKED'], 0, 255);
			}

			$fields['RUNNING'] = 'Y';

			$r = Internals\OrderTable::add($fields);
			if (!$r->isSuccess())
			{
				$result->addWarnings($r->getErrors());
				$this->setSaving(false);
				return $result;
			}

			if ($resultData = $r->getData())
				$result->setData($resultData);

			$id = $r->getId();
			$this->setFieldNoDemand('ID', $id);

			/** @var Result $r */
			$r = static::setAccountNumber($id);
			if ($r->isSuccess())
			{
				if ($accountData = $r->getData())
				{
					if (array_key_exists('ACCOUNT_NUMBER', $accountData))
					{
						$this->setField('ACCOUNT_NUMBER', $accountData['ACCOUNT_NUMBER']);
					}
				}
			}
			OrderHistory::addAction('ORDER', $id, 'ORDER_ADDED', $id, $this);
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		if (self::$eventClassName)
		{
			$oldEntityValues = $this->fields->getOriginalValues();

			if (!empty($oldEntityValues))
			{
				$eventManager = Main\EventManager::getInstance();
				if ($eventsList = $eventManager->findEventHandlers('sale', 'On'.self::$eventClassName.'EntitySaved'))
				{
					/** @var Main\Event $event */
					$event = new Main\Event('sale', 'On'.self::$eventClassName.'EntitySaved', array(
						'ENTITY' => $this,
						'VALUES' => $oldEntityValues,
					));
					$event->send();
				}
			}
		}

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

		if ($isChanged)
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

				OrderHistory::addLog('ORDER', $id, "ORDER_UPDATE", $id, $this, $logFields, OrderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1);
			}

		}

		OrderHistory::collectEntityFields('ORDER', $id, $id);


		/** @var Basket $basket */
		$basket = $this->getBasket();

		/** @var Result $r */
		$r = $basket->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		/** @var PaymentCollection $paymentCollection */
		$paymentCollection = $this->getPaymentCollection();

		/** @var Result $r */
		$r = $paymentCollection->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}


		// user budget
		Internals\UserBudgetPool::onUserBudgetSave($this->getUserId());

		/** @var ShipmentCollection $shipmentCollection */
		$shipmentCollection = $this->getShipmentCollection();

		/** @var Result $r */
		$r = $shipmentCollection->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		/** @var Tax $tax */
		$tax = $this->getTax();

		/** @var Result $r */
		$r = $tax->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}


		/** @var PropertyValueCollection $propertyCollection */
		$propertyCollection = $this->getPropertyCollection();

		/** @var Result $r */
		$r = $propertyCollection->save();
		if (!$r->isSuccess())
		{
			$result->addWarnings($r->getErrors());
		}

		/** @var Discount $discount */
		$discount = $this->getDiscount();

		/** @var Result $r */
		$r = $discount->save();
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
			EntityMarker::addMarker($this, $this, $warningResult);
			Internals\OrderTable::update($id, array('MARKED' => 'Y'));
		}

		$currentDateTime = new Type\DateTime();
		$updateFields = array('RUNNING' => 'N');

		if (array_key_exists('DATE_UPDATE', $orderDateList))
		{
			if ($orderDateList['DATE_UPDATE'] !== null)
				$updateFields['DATE_UPDATE'] = $currentDateTime;
		}

		if ($this->isNew)
		{
			$updateFields['DATE_INSERT'] = $currentDateTime;
		}

		$this->setFieldsNoDemand($updateFields);
		Internals\OrderTable::update($id, $updateFields);

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		OrderHistory::addLog('ORDER', $this->getId(), 'ORDER_EVENT_ON_ORDER_SAVED', null, null, array(), OrderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1);

		$isChanged = $this->isChanged();
		static::clearChanged();

		$eventManager = Main\EventManager::getInstance();
		if ($eventsList = $eventManager->findEventHandlers('sale', EventActions::EVENT_ON_ORDER_SAVED))
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_SAVED, array(
				'ENTITY' => $this,
				'IS_NEW' => $this->isNew,
				'IS_CHANGED' => $isChanged,
				'VALUES' => $oldEntityValues,
			));
			$event->send();
		}

		if (($eventList = Internals\EventsPool::getEvents($this->getInternalId())) && !empty($eventList) && is_array($eventList))
		{
			foreach ($eventList as $eventName => $eventData)
			{
				$event = new Main\Event('sale', $eventName, $eventData);
				$event->send();

				Notify::callNotify($this, $eventName);
			}

			Internals\EventsPool::resetEvents($this->getInternalId());
		}

		Notify::callNotify($this, EventActions::EVENT_ON_ORDER_SAVED);

		if (!$result->isSuccess())
		{
			$errorMsg = $this->getField('REASON_MARKED');
			$errorMsg .= (strval($errorMsg) != ""? "\n" : "").join("\n", $result->getErrors());
			$updateFields = array(
				'MARKED' => 'Y',
				'DATE_MARKED' => new Type\DateTime(),
				'EMP_MARKED_ID' => $USER->getId(),
				'REASON_MARKED' => $errorMsg
			);

			Internals\OrderTable::update($id, $updateFields);

			OrderHistory::addLog('ORDER', $this->getId(), 'ORDER_EVENT_ON_ORDER_SAVED_ERROR', null, null, array("ERROR" => $errorMsg), OrderHistory::SALE_ORDER_HISTORY_LOG_LEVEL_1);
		}
		else
		{
			if(defined("CACHED_b_sale_order") && ($this->isNew || ($isChanged && $fields["UPDATED_1C"] != "Y")))
			{
				$CACHE_MANAGER->Read(CACHED_b_sale_order, "sale_orders");
				$CACHE_MANAGER->SetImmediate("sale_orders", true);
			}
		}

		EntityMarker::saveMarkers($this);

		OrderHistory::collectEntityFields('ORDER', $id, $id);

		$this->isNew = false;
		$this->setSaving(false);

		return $result;
	}

	/**
	 * @internal
	 * 
	 * Delete order without demands.
	 *
	 * @param int $id				Order id.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public static function deleteNoDemand($id)
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		$result = new Result();

		/** @var Main\Result $result */
		$order = Internals\OrderTable::getById($id)->fetch();
		if (!$order)
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND'));
			return $result;
		}

		$basketClassName = $registry->getBasketClassName();
		$basketClassName::deleteNoDemand($id);

		$shipmentClassName = $registry->getShipmentClassName();
		$shipmentClassName::deleteNoDemand($id);

		$paymentClassName = $registry->getPaymentClassName();
		$paymentClassName::deleteNoDemand($id);

		$propertyValueClassName = $registry->getPropertyValueClassName();
		$propertyValueClassName::deleteNoDemand($id);

		OrderHistory::deleteByOrderId($id);
		EntityMarker::deleteByOrderId($id);
		TradingPlatform\OrderTable::deleteByOrderId($id);
		OrderProcessingTable::deleteByOrderId($id);
		Internals\OrderTable::delete($id);

		return $result;
	}

	/**
	 * Delete order.
	 *
	 * @param int $id				Order id.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 */
	public static function delete($id)
	{
		$result = new Result();

		if (!$order = Order::load($id))
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND'));
			return $result;
		}

		Notify::setNotifyDisable(true);

		/** @var Result $r */
		$r = $order->setField('CANCELED', 'Y');
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		/** @var Basket $basketCollection */
		if ($basketCollection = $order->getBasket())
		{
			/** @var BasketItem $basketItem */
			foreach ($basketCollection as $basketItem)
			{
				$basketItem->delete();
			}
		}

		/** @var ShipmentCollection $shipmentCollection */
		if ($shipmentCollection = $order->getShipmentCollection())
		{
			/** @var Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				$shipment->delete();
			}
		}


		/** @var PaymentCollection $paymentCollection */
		if ($paymentCollection = $order->getPaymentCollection())
		{
			/** @var Payment $payment */
			foreach ($paymentCollection as $payment)
			{
				$payment->delete();
			}
		}

		/** @var PropertyValueCollection $propertyCollection */
		if ($propertyCollection = $order->getPropertyCollection())
		{
			/** @var PropertyValue $property */
			foreach ($propertyCollection as $property)
			{
				$property->delete();
			}
		}

		$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_DELETE, array(
			'ENTITY' => $order
		));
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			$return = null;
			if ($eventResult->getType() == Main\EventResult::ERROR)
			{
				if ($eventResultData = $eventResult->getParameters())
				{
					if (isset($eventResultData) && $eventResultData instanceof ResultError)
					{
						/** @var ResultError $errorMsg */
						$errorMsg = $eventResultData;
					}
				}

				if (!isset($errorMsg))
					$errorMsg = new ResultError('EVENT_ORDER_DELETE_ERROR');

				$result->addError($errorMsg);
				return $result;
			}
		}

		/** @var Result $r */
		$r = $order->save();
		if ($r->isSuccess())
		{
			/** @var Entity\DeleteResult $r */
			$r = Internals\OrderTable::delete($id);
			if ($r->isSuccess())
			{
				OrderHistory::deleteByOrderId($id);
				EntityMarker::deleteByOrderId($id);
				\Bitrix\Sale\TradingPlatform\OrderTable::deleteByOrderId($id);
				OrderProcessingTable::deleteByOrderId($id);
			}
		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		Notify::setNotifyDisable(false);

		$event = new Main\Event('sale', EventActions::EVENT_ON_ORDER_DELETED, array(
			'ENTITY' => $order,
			'VALUE' => ((bool) $r->isSuccess())
		));
		$event->send();

		return $result;
	}

	/**
	 * @return bool
	 */
	public function isPaid()
	{
		return $this->getField('PAYED') == "Y" ? true : false;
	}

	/**
	 * @return bool
	 */
	public function isShipped()
	{
		$shipmentCollection = $this->getShipmentCollection();
		return $shipmentCollection->isShipped();
	}

	/**
	 * @return bool
	 */
	public function isAllowDelivery()
	{
		return $this->getField('ALLOW_DELIVERY') == "Y"? true: false;
	}

	/**
	 * @return bool
	 */
	public function isCanceled()
	{
		return $this->getField('CANCELED') == "Y"? true: false;
	}

	/**
	 * Modify payment collection.
	 *
	 * @param string $action			Action.
	 * @param Payment $payment			Payment.
	 * @param null|string $name				Field name.
	 * @param null|string|int|float $oldValue		Old value.
	 * @param null|string|int|float $value			New value.
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function onPaymentCollectionModify($action, Payment $payment, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();

		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$paymentClassName = $registry->getPaymentClassName();

		if ($action == EventActions::DELETE)
		{
			if ($this->getField('PAY_SYSTEM_ID') == $payment->getPaymentSystemId())
			{
				/** @var PaymentCollection $paymentCollection */
				if (!$paymentCollection = $payment->getCollection())
				{
					throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
				}

				/** @var Payment $entityPayment */
				foreach ($paymentCollection as $entityPayment)
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

		if ($action != EventActions::UPDATE)
			return $result;

		if (($name == "CURRENCY") && ($value != $this->getField("CURRENCY")))
			throw new Main\NotImplementedException();

		if ($name == "SUM" || $name == "PAID")
		{

			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_PAID_ORDER_CANCELED'), 'SALE_ORDER_PAID_ORDER_CANCELED'));
				return $result;
			}

			if (($name == "SUM") && !$payment->isPaid())
			{
				if ($value == 0 && $payment->isInner())
					$payment->delete();

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
		elseif ($name == "IS_RETURN")
		{
			if ($this->isCanceled())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_ORDER_RETURN_ORDER_CANCELED'), 'SALE_ORDER_RETURN_ORDER_CANCELED'));
				return $result;
			}

			if ($value != $paymentClassName::RETURN_NONE)
			{
				if (!$payment->isPaid())
				{
					$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_NOT_PAID'), 'SALE_ORDER_PAYMENT_RETURN_NOT_PAID'));
					return $result;
				}

				$oldPaid = $this->isPaid()? "Y" : "N";

				/** @var PaymentCollection $paymentCollection */
				if (!$paymentCollection = $this->getPaymentCollection())
				{
					throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
				}

				$creditSum = 0;
				$overPaid = $paymentCollection->getPaidSum() - $this->getPrice();

				if ($overPaid <= 0)
				{
					$creditSum = $payment->getSum();
					$overPaid = 0;
				}
				elseif ($payment->getSum() - $overPaid > 0)
				{
					$creditSum = $payment->getSum() - $overPaid;
				}

				if ($value == $paymentClassName::RETURN_PS)
				{
					$psId = $payment->getPaymentSystemId();
				}
				elseif ($value == $paymentClassName::RETURN_INNER)
				{
					$psId = Manager::getInnerPaySystemId();
				}
				else
				{
					$result->addError(new Entity\EntityError('unsupported operation'));
					return $result;
				}

				$service = Manager::getObjectById($psId);

				if ($service && $service->isRefundable())
				{
					if ($creditSum)
					{
						if ($value == $paymentClassName::RETURN_PS)
						{
							if ($overPaid > 0)
							{
								$userBudget = Internals\UserBudgetPool::getUserBudgetByOrder($this);
								if (PriceMaths::roundPrecision($overPaid) > PriceMaths::roundPrecision($userBudget))
								{
									$result->addError(new Entity\EntityError(Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_PAID'), 'SALE_ORDER_PAYMENT_RETURN_PAID'));
									return $result;
								}
							}
						}

						$refResult = $service->refund($payment);
						if ($refResult->isSuccess())
						{
							if ($overPaid > 0)
								Internals\UserBudgetPool::addPoolItem($this, -$overPaid, Internals\UserBudgetPool::BUDGET_TYPE_ORDER_PAY, $payment);
						}
						else
						{
							$result->addErrors($refResult->getErrors());
							return $result;
						}
					}
				}
				else
				{
					$result->addError(new Entity\EntityError(Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_NO_SUPPORTED'), 'SALE_ORDER_PAYMENT_RETURN_NO_SUPPORTED'));
					return $result;
				}

				$payment->setFieldNoDemand('PAID', 'N');

				$finalSumPaid = $this->getSumPaid() - $creditSum;
				if ($finalSumPaid != $this->getSumPaid())
				{
					$this->setFieldNoDemand('SUM_PAID', $finalSumPaid);
					$this->setFieldNoDemand('PAYED', ($this->getPrice() <= $finalSumPaid) ? "Y" : "N");
				}

				/** @var Result $r */
				$r = $this->onAfterSyncPaid($oldPaid);
				if (!$r->isSuccess())
				{

//					$result->addErrors($r->getErrors());
				}
			}
			else
			{

				if ($payment->isPaid())
				{
					$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_PAID'), 'SALE_ORDER_PAYMENT_RETURN_PAID'));
					return $result;
				}

				$userBudget = Internals\UserBudgetPool::getUserBudgetByOrder($this);
				if (PriceMaths::roundPrecision($userBudget) < PriceMaths::roundPrecision($payment->getSum()))
				{
					$result->addError( new ResultError( Loc::getMessage('SALE_ORDER_PAYMENT_NOT_ENOUGH_USER_BUDGET'), "SALE_ORDER_PAYMENT_NOT_ENOUGH_USER_BUDGET") );
					return $result;
				}

				Internals\UserBudgetPool::addPoolItem($this, ($payment->getSum() * -1), Internals\UserBudgetPool::BUDGET_TYPE_ORDER_PAY, $payment);

				$r = $payment->setField('PAID', "Y");
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}

			}

		}
		elseif ($name == "PAY_SYSTEM_ID")
		{
			if ($payment->getField('PAY_SYSTEM_ID') != $this->getField('PAY_SYSTEM_ID'))
			{
				$this->setFieldNoDemand('PAY_SYSTEM_ID', $payment->getField('PAY_SYSTEM_ID'));
			}

		}
		elseif ($name == "DATE_PAID")
		{
			if ($payment->getField('DATE_PAID') != $this->getField('DATE_PAID'))
			{
				$this->setFieldNoDemand('DATE_PAYED', $payment->getField('DATE_PAID'));
			}
		}
		elseif ($name == "PAY_VOUCHER_NUM")
		{
			if ($payment->getField('PAY_VOUCHER_NUM') != $this->getField('PAY_VOUCHER_NUM'))
			{
				$this->setFieldNoDemand('PAY_VOUCHER_NUM', $payment->getField('PAY_VOUCHER_NUM'));
			}
		}
		elseif ($name == "PAY_VOUCHER_DATE")
		{
			if ($payment->getField('PAY_VOUCHER_DATE') != $this->getField('PAY_VOUCHER_DATE'))
			{
				$this->setFieldNoDemand('PAY_VOUCHER_DATE', $payment->getField('PAY_VOUCHER_DATE'));
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

		if ($value != $oldValue)
		{
			$fields = $this->fields->getChangedValues();
			if (!array_key_exists("UPDATED_1C", $fields))
				parent::setField("UPDATED_1C", "N");
		}

		return $result;
	}

	/**
	 * @param $value
	 *
	 * @return Result
	 */
	private function setReasonMarked($value)
	{
		$result = new Result();

		if (!empty($value))
		{
			$orderReasonMarked = $this->getField('REASON_MARKED');
			if (is_array($value))
			{
				$newOrderReasonMarked = '';

				foreach ($value as $err)
				{
					$newOrderReasonMarked .= (strval($newOrderReasonMarked) != '' ? "\n" : "") . $err;
				}
			}
			else
			{
				$newOrderReasonMarked = $value;
			}

			/** @var Result $r */
			$r = $this->setField('REASON_MARKED', $orderReasonMarked. (strval($orderReasonMarked) != '' ? "\n" : ""). $newOrderReasonMarked);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * Modify order field.
	 *
	 * @param string $name				Field name.
	 * @param mixed|string|int|float $oldValue			Old value.
	 * @param mixed|string|int|float $value				New value.
	 * @return Entity\Result|Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectNotFoundException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		$result = new Result();

		if ($name == "PRICE")
		{
			/** @var Result $r */
			$r = $this->refreshVat();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}


			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$r = $shipmentCollection->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			/** @var PaymentCollection $paymentCollection */
			if (!$paymentCollection = $this->getPaymentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
			}

			$r = $paymentCollection->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			/** @var Result $r */
			$r = $this->syncOrderAndPayments();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			return $result;
		}
		elseif ($name == "CURRENCY")
		{
			throw new Main\NotImplementedException('field CURRENCY');
		}
		elseif ($name == "PERSON_TYPE_ID")
		{
			// may be need activate properties
			//throw new Main\NotImplementedException();
		}
		elseif ($name == "CANCELED")
		{
			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_CANCELED, array(
				'ENTITY' => $this
			));
			$event->send();

			/** @var PaymentCollection $paymentCollection */
			if (!$paymentCollection = $this->getPaymentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
			}

			$r = $paymentCollection->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$r = $shipmentCollection->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$this->setField('DATE_CANCELED', new Type\DateTime());

			if ($USER->isAuthorized())
				$this->setField('EMP_CANCELED_ID', $USER->getID());

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_CANCELED, array(
				'ENTITY' => $this,
			));

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_CANCELED_SEND_MAIL, array(
				'ENTITY' => $this,
			));
		}
		elseif ($name == "USER_ID")
		{
			throw new Main\NotImplementedException('field USER_ID');
		}
		elseif($name == "MARKED")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_MARKED', new Type\DateTime());

				if ($USER->isAuthorized())
					$this->setField('EMP_MARKED_ID', $USER->getID());
			}
			elseif ($value == "N")
			{
				$this->setField('REASON_MARKED', '');
			}

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$r = $shipmentCollection->onOrderModify($name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}
		elseif ($name == "STATUS_ID")
		{

			$event = new Main\Event('sale', EventActions::EVENT_ON_BEFORE_ORDER_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));
			$event->send();

			if ($event->getResults())
			{
				/** @var Main\EventResult $eventResult */
				foreach($event->getResults() as $eventResult)
				{
					if($eventResult->getType() == Main\EventResult::ERROR)
					{
						$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_ORDER_STATUS_CHANGE_ERROR'), 'SALE_EVENT_ON_BEFORE_ORDER_STATUS_CHANGE_ERROR');
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

			if (!$result->isSuccess())
			{
				return $result;
			}

			$this->setField('DATE_STATUS', new Type\DateTime());

			if ($USER && $USER->isAuthorized())
				$this->setField('EMP_STATUS_ID', $USER->GetID());

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_CHANGE, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_CHANGE_SEND_MAIL, array(
				'ENTITY' => $this,
				'VALUE' => $value,
				'OLD_VALUE' => $oldValue,
			));

			$allowPayStatus = OrderStatus::getAllowPayStatusList();
			$disallowPayStatus = OrderStatus::getDisallowPayStatusList();

			if ((!empty($disallowPayStatus) && in_array($oldValue, $disallowPayStatus))
				&& (!empty($allowPayStatus) && in_array($value, $allowPayStatus)))
			{
				Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_STATUS_ALLOW_PAY_CHANGE, array(
					'ENTITY' => $this,
					'VALUE' => $value,
					'OLD_VALUE' => $oldValue,
				));
			}

		}

		if ($value != $oldValue)
		{
			$fields = $this->fields->getChangedValues();
			if (!array_key_exists("UPDATED_1C", $fields))
			{
				parent::setField("UPDATED_1C", "N");
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getSettableFields()
	{
		$result = array(
			"LID", "PERSON_TYPE_ID", "CANCELED", "DATE_CANCELED",
			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID",  "DEDUCTED",
			"MARKED", "DATE_MARKED", "EMP_MARKED_ID", "REASON_MARKED",
			"PRICE", "DISCOUNT_VALUE",
			"DATE_INSERT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "COMMENTS", "TAX_VALUE",
			"STAT_GID", "RECURRING_ID", "LOCKED_BY",
			"DATE_LOCK", "RECOUNT_FLAG", "AFFILIATE_ID", "DELIVERY_DOC_NUM", "DELIVERY_DOC_DATE", "UPDATED_1C",
			"STORE_ID", "ORDER_TOPIC", "RESPONSIBLE_ID", "DATE_BILL", "DATE_PAY_BEFORE", "ACCOUNT_NUMBER",
			"XML_ID", "ID_1C", "VERSION_1C", "VERSION", "EXTERNAL_ORDER", "COMPANY_ID"
		);

		return array_merge($result, static::getCalculatedFields());
	}

	/**
	 * Modify basket.
	 *
	 * @param string $action				Action.
	 * @param BasketItemBase $basketItem		Basket item.
	 * @param null|string $name				Field name.
	 * @param null|string|int|float $oldValue		Old value.
	 * @param null|string|int|float $value			New value.
	 * @return Result
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public function onBasketModify($action, BasketItemBase $basketItem, $name = null, $oldValue = null, $value = null)
	{
		$result = new Result();
		if ($action != EventActions::UPDATE)
			return $result;

		if ($name == "QUANTITY")
		{
			if ($value < 0)
			{
				$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_BASKET_WRONG_QUANTITY',
									array(
										'#PRODUCT_NAME#' => $basketItem->getField('NAME')
									)
				), 'SALE_ORDER_BASKET_WRONG_QUANTITY') );

				return $result;
			}

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}
			$r = $shipmentCollection->onBasketModify($action, $basketItem, $name, $oldValue, $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($value == 0)
			{

				/** @var Result $r */
				$r = $this->refreshVat();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}

				if ($tax = $this->getTax())
				{
					$tax->resetTaxList();
				}
			}

			if ($basketItem->isBundleChild())
				return $result;

			/** @var Result $result */
			$r = $this->setField(
				"PRICE",
				$this->getBasket()->getPrice() + $this->getShipmentCollection()->getPriceDelivery()
			);

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if ($this->getId() == 0 && !$this->isMathActionOnly())
			{
				$shipmentCollection->refreshData();
			}

			return $result;
		}
		elseif ($name == "PRICE")
		{
			/** @var Result $result */
			$r = $this->refreshOrderPrice();

			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			if ($this->getId() == 0 && !$this->isMathActionOnly())
			{
				$shipmentCollection->refreshData();
			}
			return $result;
		}
		elseif ($name == "CURRENCY")
		{
			if ($value != $this->getField("CURRENCY"))
				throw new Main\NotSupportedException("CURRENCY");
		}
		elseif ($name == "DIMENSIONS")
		{
			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}
			return $shipmentCollection->onBasketModify($action, $basketItem, $name, $oldValue, $value);
		}

		return $result;
	}


	/**
	 * @return Result
	 */
	public function onBeforeBasketRefresh()
	{
		$result = new Result();

		$shipmentCollection = $this->getShipmentCollection();
		if ($shipmentCollection)
		{
			$r = $shipmentCollection->tryUnreserve();
			if(!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	public function onAfterBasketRefresh()
	{
		$result = new Result();

		$shipmentCollection = $this->getShipmentCollection();
		if ($shipmentCollection)
		{
			/** @var \Bitrix\Sale\Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				if ($shipment->isShipped() || !$shipment->needReservation())
					continue;

				$r = $shipment->tryReserve();
				if(!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}


	/**
	 * Modify property value collection.
	 *
	 * @param string $action				Action.
	 * @param PropertyValue $property		Property.
	 * @param null|string $name				Field name.
	 * @param null|string|int|float $oldValue		Old value.
	 * @param null|string|int|float $value			New value.
	 * @return Result
	 */
	public function onPropertyValueCollectionModify($action, PropertyValue $property, $name = null, $oldValue = null, $value = null)
	{
		return new Result();
	}

	/**
	 * Sync.
	 *
	 * @param Payment $payment			Payment.
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function syncOrderAndPayments(Payment $payment = null)
	{
		global $USER;

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
					$result->addErrors($r->getErrors());
					return $result;
				}

			}
		}
		else
		{
			$finalSumPaid = $this->getSumPaid();

			$r = $this->syncOrderPaid();
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
				$result->addErrors($r->getErrors());
				return $result;
			}
		}

		$paid = false;

		if ($finalSumPaid >= 0 && $paymentCollection->hasPaidPayment()
			&& PriceMaths::roundPrecision($this->getPrice()) <= PriceMaths::roundPrecision($finalSumPaid))
		{
			$paid = true;
		}

		$this->setFieldNoDemand('PAYED', $paid ? "Y" : "N");

		if ($paid && $oldPaid == "N")
		{
			if ($payment !== null)
				$payment->setFieldNoDemand('IS_RETURN', 'N');

			if ($USER->isAuthorized())
				$this->setFieldNoDemand('EMP_PAYED_ID', $USER->getID());

			if ($paymentCollection->isPaid() && $payment !== null)
			{
				if (strval($payment->getField('PAY_VOUCHER_NUM')) != '')
				{
					$this->setFieldNoDemand('PAY_VOUCHER_NUM', $payment->getField('PAY_VOUCHER_NUM'));
				}

				if (strval($payment->getField('PAY_VOUCHER_DATE')) != '')
				{
					$this->setFieldNoDemand('PAY_VOUCHER_DATE', $payment->getField('PAY_VOUCHER_DATE'));
				}
			}
		}

		if ($finalSumPaid > 0 && $finalSumPaid > $this->getPrice())
		{
			if (($payment && $payment->isPaid()) || !$payment)
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
	protected function syncOrderPaymentPaid(Payment $payment)
	{
		$result = new Result();

		if ($payment->isPaid())
			return $result;

		$paymentCollection = $this->getPaymentCollection();
		$sumPaid = $paymentCollection->getPaidSum();

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
	protected function syncOrderPaid()
	{
		$result = new Result();

		if ($this->getSumPaid() == $this->getPrice())
			return $result;

		$debitSum = $this->getPrice() - $this->getSumPaid();

		$paymentCollection = $this->getPaymentCollection();
		$sumPaid = $paymentCollection->getPaidSum();
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
				$result->setData(array(
									 'SUM_PAID' => $finalSumPaid
								 ));
			}
		}


		return $result;
	}


	/**
	 * @param null $oldPaid
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function onAfterSyncPaid($oldPaid = null)
	{

		$result = new Result();
		/** @var PaymentCollection $paymentCollection */
		if (!$paymentCollection = $this->getPaymentCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
		}

		/** @var ShipmentCollection $shipmentCollection */
		if (!$shipmentCollection = $this->getShipmentCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
		}

		$oldPaidBool = null;

		if ($oldPaid !== null)
			$oldPaidBool = ($oldPaid == "Y");

		if ($oldPaid !== null && $this->isPaid() != $oldPaidBool)
		{
			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_PAID, array(
				'ENTITY' => $this,
			));

			Internals\EventsPool::addEvent($this->getInternalId(), EventActions::EVENT_ON_ORDER_PAID_SEND_MAIL, array(
				'ENTITY' => $this,
			));
		}

		$orderStatus = null;

		$allowSetStatus = false;

		if ($oldPaid == "N")
		{
			$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

			$optionClassName = $registry->get(Registry::ENTITY_OPTIONS);

			if ($this->isPaid())
			{
				$orderStatus = $optionClassName::get('sale', 'status_on_paid', '');
			}
			elseif ($paymentCollection->hasPaidPayment())
			{
				$orderStatus = $optionClassName::get('sale', 'status_on_half_paid', '');
			}

			$allowSetStatus = ($this->getField('STATUS_ID') != OrderStatus::getFinalStatus());
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
					EntityMarker::addMarker($this, $this, $r);
					$this->setField('MARKED', 'Y');
				}
			}

		}

		if (Configuration::getProductReservationCondition() == Configuration::RESERVE_ON_PAY)
		{
			if ($paymentCollection->hasPaidPayment())
			{
				$r = $shipmentCollection->tryReserve();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
				}
			}
			else
			{
				$r = $shipmentCollection->tryUnreserve();
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
		elseif (Configuration::getProductReservationCondition() == Configuration::RESERVE_ON_FULL_PAY)
		{
			if ($oldPaid == "N" && $this->isPaid())
			{
				$r = $shipmentCollection->tryReserve();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
				elseif ($r->hasWarnings())
				{
					$result->addWarnings($r->getWarnings());
				}
			}
			elseif ($oldPaid == "Y" && !$this->isPaid())
			{
				$r = $shipmentCollection->tryUnreserve();
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
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
			elseif (!$allowDelivery)
			{
				$r = $shipmentCollection->disallowDelivery();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * Reset the value of the order and delivery
	 * @internal
	 * @param array $select - the list of fields which need to be reset
	 */
	public function resetData($select = array('PRICE'))
	{
		if (in_array('PRICE', $select))
		{
			$this->setFieldNoDemand('PRICE', 0);
		}

		if (in_array('PRICE_DELIVERY', $select))
		{
			$this->setFieldNoDemand('PRICE_DELIVERY', 0);
		}
	}

	/**
	 * Reset the value of taxes
	 *
	 * @internal
	 */
	public function resetTax()
	{
		$this->setFieldNoDemand('TAX_PRICE', 0);
		$this->setFieldNoDemand('TAX_VALUE', 0);
	}

	/**
	 * Full refresh order data.
	 *
	 * @param array $select				Fields list.
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function refreshData($select = array())
	{
		$result = new Result();

		$isStartField = $this->isStartField();

		$this->calculateType = ($this->getId() > 0 ? static::SALE_ORDER_CALC_TYPE_REFRESH : static::SALE_ORDER_CALC_TYPE_NEW);
		$this->resetData($select);

		/** @var Basket $basket */
		$basket = $this->getBasket();
		if (!$basket)
		{
			return $result;
		}

		/** @var Result $r */
		$r = $this->setField('PRICE', $basket->getPrice());
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($this instanceof \IShipmentOrder)
		{
			/** @var ShipmentCollection $shipmentCollection */
			if (!$shipmentCollection = $this->getShipmentCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
			}

			$r = $shipmentCollection->refreshData();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
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
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	protected function syncOrderTax()
	{
		$result = new Result();

		/** @var Tax $tax */
		if (!$tax = $this->getTax())
		{
			throw new Main\ObjectNotFoundException('Entity "Tax" not found');
		}

		$this->resetTax();
		/** @var Result $r */
		$r = $tax->calculate();
		if ($r->isSuccess())
		{
			$taxResult = $r->getData();
			if (isset($taxResult['TAX_PRICE']) && floatval($taxResult['TAX_PRICE']) > 0)
			{
				/** @var Result $r */
				$r = $this->setField('TAX_PRICE', $taxResult['TAX_PRICE']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			if (isset($taxResult['VAT_SUM']) && floatval($taxResult['VAT_SUM']) > 0)
			{
				/** @var Result $r */
				$r = $this->setField('VAT_SUM', $taxResult['VAT_SUM']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			if (isset($taxResult['VAT_DELIVERY']) && floatval($taxResult['VAT_DELIVERY']) > 0)
			{
				/** @var Result $r */
				$r = $this->setField('VAT_DELIVERY', $taxResult['VAT_DELIVERY']);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			/** @var Result $r */
			$r = $this->setField('TAX_VALUE', $this->isUsedVat()? $this->getVatSum() : $this->getField('TAX_PRICE'));
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

		}
		else
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Tax
	 */
	protected function loadTax()
	{
		return Tax::load($this);
	}


	/**
	 * @return Discount
	 */
	public function getDiscount()
	{
		if ($this->discount === null)
		{
			$this->discount = $this->loadDiscount();
		}

		return $this->discount;
	}

	/**
	 * @return mixed
	 */
	protected function loadDiscount()
	{
		return Discount::buildFromOrder($this);
	}

	/**
	 * Set account number.
	 *
	 * @param int $id			Account id.
	 * @return bool
	 */
	public static function setAccountNumber($id)
	{
		return \CSaleOrder::setAccountNumberById($id);
	}

	/**
	 * apply discount.
	 * @internal
	 * @param array $data			Order data.
	 * @return Result
	 */
	public function applyDiscount(array $data)
	{
		if (!empty($data['BASKET_ITEMS']) && is_array($data['BASKET_ITEMS']))
		{
			/** @var Basket $basket */
			$basket = $this->getBasket();

			foreach ($data['BASKET_ITEMS'] as $basketCode => $basketItemData)
			{
				/** @var BasketItem $basketItem */
				if ($basketItem = $basket->getItemByBasketCode($basketCode))
				{
					if (!$basketItem->isCustomPrice())
					{
						if (isset($basketItemData['PRICE']) && isset($basketItemData['DISCOUNT_PRICE']))
						{
							$basketItemData['PRICE'] = (float)$basketItemData['PRICE'];

							if ($basketItemData['PRICE'] >= 0
								&& $basketItem->getPrice() != $basketItemData['PRICE'])
							{
								$basketItemData['PRICE'] = PriceMaths::roundPrecision($basketItemData['PRICE']);
								$basketItem->setFieldNoDemand('PRICE', $basketItemData['PRICE']);
							}

							if ($basketItemData['DISCOUNT_PRICE'] >= 0
								&& $basketItem->getDiscountPrice() != $basketItemData['DISCOUNT_PRICE'])
							{
								$basketItemData['DISCOUNT_PRICE'] = PriceMaths::roundPrecision($basketItemData['DISCOUNT_PRICE']);
								$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $basketItemData['DISCOUNT_PRICE']);
							}
						}
					}
				}
				unset($basketItem);
			}
			unset($basketCode, $basketItemData);

			$this->refreshOrderPrice();
		}

		if (isset($data['SHIPMENT']) && intval($data['SHIPMENT']) > 0
			&& (isset($data['PRICE_DELIVERY']) && floatval($data['PRICE_DELIVERY']) >= 0
				|| isset($data['DISCOUNT_PRICE']) && floatval($data['DISCOUNT_PRICE']) >= 0))
		{
			/** @var ShipmentCollection $shipmentCollection */
			if ($shipmentCollection = $this->getShipmentCollection())
			{
				/** @var Shipment $shipment */
				if ($shipment = $shipmentCollection->getItemByShipmentCode($data['SHIPMENT']))
				{
					if (!$shipment->isCustomPrice())
					{
						if (floatval($data['PRICE_DELIVERY']) >= 0)
						{
							$data['PRICE_DELIVERY'] = PriceMaths::roundPrecision(floatval($data['PRICE_DELIVERY']));
							$shipment->setField('PRICE_DELIVERY', $data['PRICE_DELIVERY']);
						}

						if (floatval($data['DISCOUNT_PRICE']) != 0)
						{
							$data['DISCOUNT_PRICE'] = PriceMaths::roundPrecision(floatval($data['DISCOUNT_PRICE']));
							$shipment->setField('DISCOUNT_PRICE', $data['DISCOUNT_PRICE']);
						}
					}

				}
			}
		}


		if (isset($data['DISCOUNT_PRICE']) && floatval($data['DISCOUNT_PRICE']) >= 0)
		{
			$data['DISCOUNT_PRICE'] = PriceMaths::roundPrecision(floatval($data['DISCOUNT_PRICE']));
			$this->setField('DISCOUNT_PRICE', $data['DISCOUNT_PRICE']);
		}

		return new Result();
	}

	private function refreshOrderPrice()
	{
		$taxPrice = !$this->isUsedVat() ? $this->getField('TAX_PRICE') : 0;

		return $this->setField(
			"PRICE",
			$this->getBasket()->getPrice() + $this->getShipmentCollection()->getPriceDelivery() + $taxPrice
		);
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

			$historyFields['OLD_'.$name] = $oldValue;

			OrderHistory::addField(
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

		return Internals\OrderTable::update($id, array(
			'DATE_LOCK' => new Main\Type\DateTime(),
			'LOCKED_BY' => $USER->GetID()
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

		if(!$order = Order::load($id))
		{
			$result->addError( new ResultError(Loc::getMessage('SALE_ORDER_ENTITY_NOT_FOUND'), 'SALE_ORDER_ENTITY_NOT_FOUND') );
			return $result;
		}

		$userRights = \CMain::getUserRight("sale", $USER->getUserGroupArray(), "Y", "Y");

		if (($userRights >= "W") || ($order->getField("LOCKED_BY") == $USER->getID()))
		{
			return Internals\OrderTable::update($id, array(
				'DATE_LOCK' => null,
				'LOCKED_BY' => null
			));
		}

		return $result;
	}

	/**
	 * Return is order locked.
	 *
	 * @param int $id			Order id.
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

		$res = Internals\OrderTable::getList(array(
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
	 * @return null|string
	 */
	public function getTaxLocation()
	{
		if (strval(($this->getField('TAX_LOCATION')) == ""))
		{
			/** @var PropertyValueCollection $propertyCollection */
			$propertyCollection = $this->getPropertyCollection();

			if ($property = $propertyCollection->getTaxLocation())
			{
				$this->setField('TAX_LOCATION', $property->getValue());
			}

		}

		return $this->getField('TAX_LOCATION');
	}

	/**
	 * @param bool $isMeaningfulField
	 * @return bool
	 */
	public function isStartField($isMeaningfulField = false)
	{
		if ($this->isStartField === null)
		{
			$this->isStartField = true;
		}
		else
			$this->isStartField = false;

		if ($isMeaningfulField === true)
		{
			$this->isMeaningfulField = true;
		}

		return $this->isStartField;
	}

	/**
	 *
	 */
	public function clearStartField()
	{
		$this->isStartField = null;
		$this->isMeaningfulField = false;
	}

	/**
	 * @return bool
	 */
	public function hasMeaningfulField()
	{
		return $this->isMeaningfulField;
	}

	/**
	 * @param bool $hasMeaningfulField
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectNotFoundException
	 */
	public function doFinalAction($hasMeaningfulField = false)
	{
		$result = new Result();

		$orderInternalId = $this->getInternalId();

		$r = Internals\ActionEntity::runActions($orderInternalId);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		if (!$hasMeaningfulField)
		{
			$this->clearStartField();
			return $result;
		}
		

		if ($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		$currentIsMathActionOnly = $this->isMathActionOnly();

		$basket = $this->getBasket();
		if ($basket)
		{
			$this->setMathActionOnly(true);

			if (self::$eventClassName === null)
			{
				self::$eventClassName = static::getEntityEventName();
			}

			if (self::$eventClassName)
			{
				$eventManager = Main\EventManager::getInstance();
				$eventsList = $eventManager->findEventHandlers('sale', 'OnBefore'.self::$eventClassName.'FinalAction');
				if (!empty($eventsList))
				{
					$event = new Main\Event('sale', 'OnBefore'.self::$eventClassName.'FinalAction', array(
						'ENTITY' => $this,
						'HAS_MEANINGFUL_FIELD' => $hasMeaningfulField,
						'BASKET' => $basket,
					));
					$event->send();

					if ($event->getResults())
					{
						/** @var Main\EventResult $eventResult */
						foreach($event->getResults() as $eventResult)
						{
							if($eventResult->getType() == Main\EventResult::ERROR)
							{
								$errorMsg = new ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_ON_BEFORE_'.strtoupper(self::$eventClassName).'_FINAL_ACTION_ERROR'), 'SALE_EVENT_ON_BEFORE_'.strtoupper(self::$eventClassName).'_FINAL_ACTION_ERROR');
								$eventResultData = $eventResult->getParameters();
								if ($eventResultData)
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
				}

				if (!$result->isSuccess())
				{
					return $result;
				}
			}



			// discount
			$discount = $this->getDiscount();
			$r = $discount->calculate();
			if (!$r->isSuccess())
			{
//				$this->clearStartField();
//				$result->addErrors($r->getErrors());
//				return $result;
			}

			if ($r->isSuccess() && ($discountData = $r->getData()) && !empty($discountData) && is_array($discountData))
			{
				/** @var Result $r */
				$r = $this->applyDiscount($discountData);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}


			/** @var Tax $tax */
			$tax = $this->getTax();
			/** @var Result $r */
			$r = $tax->refreshData();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$taxResult = $r->getData();

			$taxChanged = false;
			if (isset($taxResult['TAX_PRICE']) && floatval($taxResult['TAX_PRICE']) >= 0)
			{
				if (!$this->isUsedVat())
				{
					$taxChanged = $this->getField('TAX_PRICE') !== $taxResult['TAX_PRICE'];
					if ($taxChanged)
					{
						$this->setField('TAX_PRICE', $taxResult['TAX_PRICE']);
						$this->refreshOrderPrice();
					}
				}

			}

			if (array_key_exists('VAT_SUM', $taxResult))
			{
				if ($this->isUsedVat())
				{
					$this->setField('VAT_SUM', $taxResult['VAT_SUM']);
				}
			}

			if ($taxChanged || $this->isUsedVat())
			{
				$taxValue = $this->isUsedVat()? $this->getVatSum() : $this->getField('TAX_PRICE');
				if (floatval($taxValue) != floatval($this->getField('TAX_VALUE')))
					$this->setField('TAX_VALUE', floatval($taxValue));
			}

		}

		//
		if (!$currentIsMathActionOnly)
			$this->setMathActionOnly(false);

		//


		$this->clearStartField();

		if (self::$eventClassName)
		{
			$eventManager = Main\EventManager::getInstance();
			if ($eventsList = $eventManager->findEventHandlers('sale', 'OnAfter'.self::$eventClassName.'FinalAction'))
			{
				$event = new Main\Event('sale', 'OnAfter'.self::$eventClassName.'FinalAction', array(
					'ENTITY' => $this,
				));
				$event->send();
			}
		}

		return $result;
	}

	/**
	 * @internal
	 * @param bool $value
	 */
	public function setMathActionOnly($value = false)
	{
		$this->isOnlyMathAction = $value;
	}

	/**
	 * @return bool
	 */
	public function isMathActionOnly()
	{
		return $this->isOnlyMathAction;
	}

	/**
	 * @param string $event
	 * @return array
	 */
	public static function getEventListUsed($event)
	{
		return GetModuleEvents("sale", $event, true);
	}

	/**
	 * @internal
	 * @return null|bool
	 */
	public function isNew()
	{
		return $this->isNew;
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		if (parent::isChanged())
			return true;

		/** @var PropertyValueCollection $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			if ($propertyCollection->isChanged())
			{
				return true;
			}
		}

		/** @var Basket $basket */
		if ($basket = $this->getBasket())
		{
			if ($basket->isChanged())
			{
				return true;
			}

			/** @var PaymentCollection $paymentCollection */
			if ($paymentCollection = $this->getPaymentCollection())
			{
				if ($paymentCollection->isChanged())
				{
					return true;
				}
			}

			/** @var ShipmentCollection $shipmentCollection */
			if ($shipmentCollection = $this->getShipmentCollection())
			{
				if ($shipmentCollection->isChanged())
				{
					return true;
				}
			}

		}

		return false;
	}

	/**
	 * @return Result
	 */
	public function verify()
	{
		$result = new Result();
		/** @var Basket $basket */
		if ($basket = $this->getBasket())
		{
			$r = $basket->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		/** @var PropertyValueCollection $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			$r = $propertyCollection->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		/** @var PaymentCollection $paymentCollection */
		if ($paymentCollection = $this->getPaymentCollection())
		{
			$r = $paymentCollection->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		/** @var ShipmentCollection $shipmentCollection */
		if ($shipmentCollection = $this->getShipmentCollection())
		{
			$r = $shipmentCollection->verify();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	public function getBusinessValueProviderInstance($mapping)
	{
		$providerInstance = null;

		if (is_array($mapping))
		{
			switch ($mapping['PROVIDER_KEY'])
			{
				case 'ORDER'   :
				case 'PROPERTY': $providerInstance = $this; break;
				case 'USER'    : $providerInstance = $this->getField('USER_ID'); break;
				case 'COMPANY' : $providerInstance = $this->getField('COMPANY_ID'); break;
				// TODO case 'PAYMENT' & 'SHIPMENT': aggregate fields maybe?? What about COMPANY??
			}
		}

		return $providerInstance;
	}

	/**
	 * @param array $filter
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $filter = array())
	{
		return Internals\OrderTable::getList($filter);
	}

	/**
	 * @return Order
	 */
	public function createClone()
	{
		$cloneEntity = new \SplObjectStorage();

		/** @var Order $orderClone */
		$orderClone = clone $this;
		$orderClone->isClone = true;

		/** @var Internals\Fields $fields */
		if ($fields = $this->fields)
		{
			$orderClone->fields = $fields->createClone($cloneEntity);
		}

		/** @var Internals\Fields $calculatedFields */
		if ($calculatedFields = $this->calculatedFields)
		{
			$orderClone->calculatedFields = $calculatedFields->createClone($cloneEntity);
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $orderClone;
		}

		/** @var Basket $basket */
		if ($basket = $this->getBasket())
		{
			$orderClone->basketCollection = $basket->createClone($cloneEntity);
		}

		/** @var ShipmentCollection $shipmentCollection */
		if ($shipmentCollection = $this->getShipmentCollection())
		{
			$orderClone->shipmentCollection = $shipmentCollection->createClone($cloneEntity);
		}

		/** @var PaymentCollection $paymentCollection */
		if ($paymentCollection = $this->getPaymentCollection())
		{
			$orderClone->paymentCollection = $paymentCollection->createClone($cloneEntity);
		}

		/** @var PropertyValueCollection $propertyCollection */
		if ($propertyCollection = $this->getPropertyCollection())
		{
			$orderClone->propertyCollection = $propertyCollection->createClone($cloneEntity);
		}

		if ($tax = $this->getTax())
		{
			$orderClone->tax = $tax->createClone($cloneEntity);
		}

		if ($discount = $this->getDiscount())
		{
			$orderClone->discount = $discount->createClone($cloneEntity);
		}

		return $orderClone;
	}


	public function isClone()
	{
		return $this->isClone;
	}

	public function isAllowPay()
	{
		$statusId = $this->getField('STATUS_ID');

		$allowPayStatusList = OrderStatus::getAllowPayStatusList();

		if (!empty($allowPayStatusList))
		{
			foreach ($allowPayStatusList as $allowStatusId)
			{
				if ($allowStatusId == $statusId)
				{
					return true;
				}

			}
		}

		return false;
	}

	/**
	 * @internal
	 */
	public function clearChanged()
	{
		if ($basket = $this->getBasket())
		{
			$basket->clearChanged();
		}

		if ($paymentCollection = $this->getPaymentCollection())
		{
			$paymentCollection->clearChanged();
		}

		if ($shipmentCollection = $this->getShipmentCollection())
		{
			$shipmentCollection->clearChanged();
		}

		parent::clearChanged();
	}


	/**
	 * @return mixed
	 */
	public function getHash()
	{
		$dateInsert = $this->getDateInsert()->setTimeZone(new \DateTimeZone("Europe/Moscow"));
		$timestamp = $dateInsert->getTimestamp();
		return md5(
			$this->getId().
			$timestamp.
			$this->getUserId().
			$this->getField('ACCOUNT_NUMBER')
		);
	}

	/**
	 * @param string $reasonMarked
	 *
	 * @return Result
	 * @throws \Exception
	 */
	public function addMarker($reasonMarked)
	{
		$result = new Result();

		$orderId = $this->getId();
		if ($orderId > 0)
		{
			$updateResult = Internals\OrderTable::update($orderId, array(
				"MARKED" => "Y",
				"REASON_MARKED" => $reasonMarked
			));
			if (!$updateResult->isSuccess())
				$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	protected function isSaving()
	{
		return $this->isSaving;
	}

	/**
	 * @param bool $value
	 */
	protected function setSaving($value)
	{
		$this->isSaving = ($value === true);
	}
}