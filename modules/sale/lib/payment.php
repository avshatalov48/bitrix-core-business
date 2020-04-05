<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Sale;
use Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

/**
 * Class Payment
 * @package Bitrix\Sale
 */
class Payment extends Internals\CollectableEntity implements IBusinessValueProvider, \IEntityMarker
{
	const RETURN_NONE = 'N';
	const RETURN_INNER = 'Y';
	const RETURN_PS = 'P';

	/** @var  Sale\PaySystem\Service */
	protected $paySystem;

	private static $eventClassName = null;

	/**
	 * Payment constructor.
	 * @param array $fields
	 * @throws Main\ArgumentNullException
	 */
	protected function __construct(array $fields = array())
	{
		$priceRoundedFields = ['SUM', 'PRICE_COD'];

		foreach ($priceRoundedFields as $code)
		{
			if (isset($fields[$code]))
			{
				$fields[$code] = PriceMaths::roundPrecision($fields[$code]);
			}
		}

		parent::__construct($fields);
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return array(
			'PAID',
			'DATE_PAID',
			'EMP_PAID_ID',
			'PAY_SYSTEM_ID',
			'PS_STATUS',
			'PS_STATUS_CODE',
			'PS_STATUS_DESCRIPTION',
			'PS_STATUS_MESSAGE',
			'PS_SUM',
			'PS_CURRENCY',
			'PS_RESPONSE_DATE',
			'PAY_VOUCHER_NUM',
			'PAY_VOUCHER_DATE',
			'DATE_PAY_BEFORE',
			'DATE_BILL',
			'XML_ID',
			'SUM',
			'CURRENCY',
			'PAY_SYSTEM_NAME',
			'COMPANY_ID',
			'PAY_RETURN_NUM',
			'PRICE_COD',
			'PAY_RETURN_DATE',
			'EMP_RETURN_ID',
			'PAY_RETURN_COMMENT',
			'RESPONSIBLE_ID',
			'EMP_RESPONSIBLE_ID',
			'DATE_RESPONSIBLE_ID',
			'IS_RETURN',
			'COMMENTS',
			'ACCOUNT_NUMBER',
			'UPDATED_1C',
			'ID_1C',
			'VERSION_1C',
			'EXTERNAL_PAYMENT',
			'PS_INVOICE_ID',
			'MARKED',
			'REASON_MARKED',
			'DATE_MARKED',
			'EMP_MARKED_ID',
		);
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array('PAY_SYSTEM_ID');
	}

	/**
	 * @param array $fields
	 * @return Payment
	 */
	private static function createPaymentObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$paymentClassName = $registry->getPaymentClassName();

		return new $paymentClassName($fields);
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param PaymentCollection $collection
	 * @param PaySystem\Service|null $paySystem
	 * @return Payment
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectException
	 */
	public static function create(PaymentCollection $collection, Sale\PaySystem\Service $paySystem = null)
	{
		$fields = array(
			'DATE_BILL' => new Main\Type\DateTime(),
			'PAID' => 'N',
			'XML_ID' => static::generateXmlId(),
			'IS_RETURN' => 'N'
		);

		$order = $collection->getOrder();
		if ($order)
		{
			$fields['CURRENCY'] = $order->getCurrency();
		}

		$payment = static::createPaymentObject();
		$payment->setFieldsNoDemand($fields);
		$payment->setCollection($collection);

		if ($paySystem != null)
		{
			$payment->setPaySystemService($paySystem);
		}

		return $payment;
	}

	/**
	 * @param PaySystem\Service $service
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function setPaySystemService(Sale\PaySystem\Service $service)
	{
		$this->paySystem = $service;
		$result = $this->setField("PAY_SYSTEM_ID", $service->getField('ID'));
		if ($result->isSuccess())
		{
			$this->setField("PAY_SYSTEM_NAME", $service->getField('NAME'));
		}
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @param $id
	 * @return Internals\CollectableEntity[]
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 */
	public static function loadForOrder($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException("id");
		}

		$payments = array();

		$paymentDataList = static::getList(['filter' => ['=ORDER_ID' => $id]]);
		while ($paymentData = $paymentDataList->fetch())
		{
			$payments[] = static::createPaymentObject($paymentData);
		}

		return $payments;
	}

	/**
	 * @internal
	 * 
	 * @param $idOrder
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function deleteNoDemand($idOrder)
	{
		$result = new Result();
		
		$dbRes = static::getList([
				"select" => ["ID"],
				"filter" => ["=ORDER_ID" => $idOrder]
		]);

		while ($payment = $dbRes->fetch())
		{
			$r = static::deleteInternal($payment['ID']);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function delete()
	{
		$result = new Result();

		if ($this->isPaid())
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_PAYMENT_DELETE_EXIST_PAID'), 'SALE_PAYMENT_DELETE_EXIST_PAID'));
			return $result;
		}

		$r = $this->callEventOnBeforeEntityDeleted();
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		$r = parent::delete();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		$r = $this->callEventOnEntityDeleted();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	private function callEventOnBeforeEntityDeleted()
	{
		$result = new Result();

		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnBefore".self::$eventClassName."EntityDeleted", array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(Loc::getMessage('SALE_EVENT_ON_BEFORE_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR'), 'SALE_EVENT_ON_BEFORE_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR');
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

		return $result;
	}

	/**
	 * @return Result
	 */
	private function callEventOnEntityDeleted()
	{
		$result = new Result();

		/** @var array $oldEntityValues */
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "On".self::$eventClassName."EntityDeleted", array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
		));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(Loc::getMessage('SALE_EVENT_ON_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR'), 'SALE_EVENT_ON_'.ToUpper(self::$eventClassName).'_ENTITY_DELETED_ERROR');
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

		return $result;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\SystemException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		$result = new Result();

		if ($name == "PAID")
		{
			if ($value == "Y")
			{
				$this->setField('DATE_PAID', new Main\Type\DateTime());
				$this->setField('EMP_PAID_ID', $USER->GetID());

				if ($this->getField('IS_RETURN') == self::RETURN_INNER)
				{
					$innerPsId = Sale\PaySystem\Manager::getInnerPaySystemId();

					$service = Sale\PaySystem\Manager::getObjectById($innerPsId);
					if ($service)
					{
						$operationResult = $service->creditNoDemand($this);
						if (!$operationResult->isSuccess())
						{
							$result->addErrors($operationResult->getErrors());
							return $result;
						}
						else
						{
							$this->setFieldNoDemand('IS_RETURN', self::RETURN_NONE);
						}
					}
				}
				else
				{
					$service = Sale\PaySystem\Manager::getObjectById($this->getPaymentSystemId());
					if ($service)
					{
						$operationResult = $service->creditNoDemand($this);
						if (!$operationResult->isSuccess())
						{
							$result->addErrors($operationResult->getErrors());
							return $result;
						}
					}
				}


				$this->setField('IS_RETURN', 'N');
			}
		}
		elseif ($name == "IS_RETURN")
		{
			if ($value === 'N')
			{
				return $result;
			}

			if ($oldValue === "N")
			{
				$this->setField('EMP_RETURN_ID', $USER->GetID());
			}

			/** @var PaymentCollection $collection */
			$collection = $this->getCollection();

			$creditSum = 0;
			$overPaid = $collection->getPaidSum() - $collection->getOrder()->getPrice();

			if ($overPaid <= 0)
			{
				$creditSum = $this->getSum();
				$overPaid = 0;
			}
			elseif ($this->getSum() - $overPaid > 0)
			{
				$creditSum = $this->getSum() - $overPaid;
			}

			if ($value == static::RETURN_PS)
			{
				$psId = $this->getPaymentSystemId();
			}
			else
			{
				$psId = Sale\PaySystem\Manager::getInnerPaySystemId();
			}

			$service = Sale\PaySystem\Manager::getObjectById($psId);

			if ($service && $service->isRefundable())
			{
				if ($creditSum)
				{
					if ($value == static::RETURN_PS)
					{
						if ($overPaid > 0)
						{
							$userBudget = Internals\UserBudgetPool::getUserBudgetByOrder($collection->getOrder());
							if (PriceMaths::roundPrecision($overPaid) > PriceMaths::roundPrecision($userBudget))
							{
								$result->addError(
									new Entity\EntityError(
										Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_PAID'),
										'SALE_ORDER_PAYMENT_RETURN_PAID'
									)
								);

								return $result;
							}
						}
					}

					$refResult = $service->refund($this);
					if (!$refResult->isSuccess())
					{
						return $result->addErrors($refResult->getErrors());
					}
				}
			}
			else
			{
				$result->addError(
					new Entity\EntityError(
						Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_NO_SUPPORTED'),
						'SALE_ORDER_PAYMENT_RETURN_NO_SUPPORTED'
					)
				);
			}

			$r = $this->setField('PAID', 'N');
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}
		}
		elseif($name == "SUM")
		{
			if($this->isPaid())
			{
				$result = new Result();

				return $result->addError(
					new ResultError(
						Loc::getMessage('SALE_PAYMENT_NOT_ALLOWED_CHANGE_SUM'),
						'SALE_PAYMENT_NOT_ALLOWED_CHANGE_SUM'
					)
				);
			}
		}
		elseif ($name == "MARKED")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_MARKED', new Main\Type\DateTime());
				$this->setField('EMP_MARKED_ID', $USER->GetID());
			}
			elseif ($value == "N")
			{
				$r = $this->setField('REASON_MARKED', '');
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					return $result;
				}
			}
		}

		return parent::onFieldModify($name, $oldValue, $value);
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		$id = $this->getId();
		$isNew = (int)$id <= 0;

		/** @var PaymentCollection $paymentCollection */
		if (!$paymentCollection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $paymentCollection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		if ($this->isChanged())
		{
			$this->callEventOnBeforeEntitySaved();
		}

		if ($id > 0)
		{
			$r = $this->update();
		}
		else
		{
			$r = $this->add();
			if ($r->getId() > 0)
			{
				$id = $r->getId();
			}
		}

		if (!$r->isSuccess())
		{
			OrderHistory::addAction(
				'PAYMENT',
				$order->getId(),
				'PAYMENT_UPDATE_ERROR',
				($id > 0) ? $id : null,
				$this,
				array("ERROR" => $r->getErrorMessages())
			);

			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		if ($this->fields->isChanged('PAID'))
		{
			if ($this->isPaid())
			{
				$this->callEventOnPaid();

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var Notify $notifyClassName */
				$notifyClassName = $registry->getNotifyClassName();
				$notifyClassName::callNotify($this, EventActions::EVENT_ON_PAYMENT_PAID);
			}

			$this->addCashboxChecks();

			$this->calculateStatistic();
		}

		if ($this->isChanged())
		{
			$this->callEventOnEntitySaved();
		}

		$this->callDelayedEvents();

		$this->onAfterSave($isNew);

		return $result;
	}

	/**
	 * @return void;
	 */
	protected function addCashboxChecks()
	{
		/** @var PaymentCollection $paymentCollection */
		$paymentCollection = $this->getCollection();

		/** @var Order $order */
		$order = $paymentCollection->getOrder();

		/** @var Sale\PaySystem\Service $ps */
		$ps = $this->getPaySystem();
		if (isset($ps) && $ps->getField("CAN_PRINT_CHECK") == "Y")
		{
			Cashbox\Internals\Pool::addDoc($order->getInternalId(), $this);
		}
	}

	/**
	 * @return void;
	 */
	protected function calculateStatistic()
	{
		/** @var PaymentCollection $paymentCollection */
		$paymentCollection = $this->getCollection();

		/** @var Order $order */
		$order = $paymentCollection->getOrder();

		BuyerStatistic::calculate($order->getUserId(), $order->getCurrency(), $order->getSiteId());
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	private function add()
	{
		$result = new Result();

		/** @var PaymentCollection $collection */
		$collection = $this->getCollection();

		$order = $collection->getOrder();

		$this->setFieldNoDemand('ORDER_ID', $order->getId());

		$fields = $this->fields->getValues();

		$r = $this->addInternal($fields);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$result->setId($id);

		if ($order->getId() > 0)
		{
			OrderHistory::addAction(
				'PAYMENT',
				$order->getId(),
				'PAYMENT_ADDED',
				$id,
				$this
			);
		}

		$resultData = $r->getData();
		if ($resultData)
		{
			$result->setData($resultData);
		}

		$this->setAccountNumber($id);

		return $result;
	}

	/**
	 * @return Result
	 */
	private function update()
	{
		$result = new Result();

		$fields = $this->fields->getChangedValues();
		if ($fields)
		{
			$r = static::updateInternal($this->getId(), $fields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
			else if ($resultData = $r->getData())
			{
				$result->setData($resultData);
			}
		}

		return $result;
	}

	/**
	 * @return void;
	 */
	private function callEventOnPaid()
	{
		$oldEntityValues = $this->fields->getOriginalValues();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', EventActions::EVENT_ON_PAYMENT_PAID, array(
			'ENTITY' => $this,
			'VALUES' => $oldEntityValues,
		));
		$event->send();
	}

	/**
	 * @return void;
	 */
	private function callEventOnBeforeEntitySaved()
	{
		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBefore'.self::$eventClassName.'EntitySaved', array(
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues()
		));

		$event->send();
	}

	/**
	 * @return void;
	 */
	private function callEventOnEntitySaved()
	{
		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'On'.self::$eventClassName.'EntitySaved', array(
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		));

		$event->send();
	}

	/**
	 * @return void;
	 */
	private function callDelayedEvents()
	{
		$eventList = Internals\EventsPool::getEvents('p'.$this->getInternalIndex());
		if (!empty($eventList) && is_array($eventList))
		{
			foreach ($eventList as $eventName => $eventData)
			{
				$event = new Main\Event('sale', $eventName, $eventData);
				$event->send();

				$registry = Registry::getInstance(static::getRegistryType());

				/** @var Notify $notifyClassName */
				$notifyClassName = $registry->getNotifyClassName();
				$notifyClassName::callNotify($this, $eventName);
			}

			Internals\EventsPool::resetEvents('p'.$this->getInternalIndex());
		}
	}

	/**
	 * @param $isNew
	 */
	protected function onAfterSave($isNew)
	{
		return;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->getField('ID');
	}


	/**
	 * @return float
	 */
	public function getSum()
	{
		return floatval($this->getField('SUM'));
	}


	/**
	 * @return float
	 */
	public function getSumPaid()
	{
		return $this->getField('PS_SUM');
	}

	/**
	 * @return bool
	 */
	public function isPaid()
	{
		return ($this->getField('PAID') == "Y");
	}

	/**
	 * @return bool
	 */
	public function isReturn()
	{
		return ($this->getField('IS_RETURN') == "Y" || $this->getField('IS_RETURN') == "P");
	}

	/**
	 * @return int
	 */
	public function getOrderId()
	{
		return $this->getField('ORDER_ID');
	}

	/**
	 * @return PaySystem\Service
	 */
	public function getPaySystem()
	{
		if ($this->paySystem === null)
		{
			$this->paySystem = $this->loadPaySystem();
		}

		return $this->paySystem;
	}

	/**
	 * @return PaySystem\Service
	 */
	protected function loadPaySystem()
	{
		if ($paySystemId = $this->getPaymentSystemId())
		{
			return Sale\PaySystem\Manager::getObjectById($paySystemId);
		}

		return null;
	}

	/**
	 * @return int
	 */
	public function getPaymentSystemId()
	{
		return $this->getField('PAY_SYSTEM_ID');
	}

	/**
	 * @return string
	 */
	public function getPaymentSystemName()
	{
		return $this->getField('PAY_SYSTEM_NAME');
	}

	/**
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function setPaid($value)
	{
		$result = new Result();

		/** @var Result $r */
		$r = $this->setField('PAID', $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}
		elseif($r->hasWarnings())
		{
			$result->addWarnings($r->getWarnings());
		}

		return $result;
	}

	/**
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function setReturn($value)
	{
		$result = new Result();

		if ($value == "Y" || $value == "P")
		{
			if ($this->isReturn())
			{
				return new Result();
			}
		}
		elseif($value == "N")
		{
			if (!$this->isReturn())
			{
				return new Result();
			}
		}
		else
		{
			throw new Main\ArgumentOutOfRangeException('value');
		}

		/** @var Result $r */
		$r = $this->setField('IS_RETURN', $value);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function isInner()
	{
		return (int)$this->getPaymentSystemId() === (int)Sale\PaySystem\Manager::getInnerPaySystemId();
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws \Exception
	 */
	public function setField($name, $value)
	{
		$priceRoundedFields = array(
			'SUM' => 'SUM',
			'PRICE_COD' => 'PRICE_COD',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		if ($name == "PAY_SYSTEM_ID")
		{
			if (intval($value) > 0 && !Sale\PaySystem\Manager::isExist($value))
			{
				$result = new Result();
				$result->addError( new ResultError(Loc::getMessage('SALE_PAYMENT_WRONG_PAYMENT_SERVICE'), 'SALE_PAYMENT_WRONG_PAYMENT_SERVICE') );
				return $result;
			}

			/** @var PaymentCollection $paymentCollection */
			if (!$paymentCollection = $this->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
			}

			if (count($paymentCollection) == 1)
			{
				/** @var Order $order */
				if ($order = $paymentCollection->getOrder())
				{
					if (strval($order->getField('PAY_SYSTEM_ID')) == '')
					{
						$order->setFieldNoDemand('PAY_SYSTEM_ID', intval($value));
					}
				}
			}
		}
		elseif ($name === 'REASON_MARKED' && strlen($value) > 255)
		{
			$value = substr($value, 0, 255);
		}

		return parent::setField($name, $value);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		$priceRoundedFields = array(
			'SUM' => 'SUM',
			'PRICE_COD' => 'PRICE_COD',
		);
		if (isset($priceRoundedFields[$name]))
		{
			$value = PriceMaths::roundPrecision($value);
		}

		if ($name === 'REASON_MARKED'
			&& strlen($value) > 255)
		{
			$value = substr($value, 0, 255);
		}

		parent::setFieldNoDemand($name, $value);
	}

	/**
	 * @param string $name
	 * @param null $oldValue
	 * @param null $value
	 * @throws Main\ObjectNotFoundException
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0)
		{
			/** @var PaymentCollection $paymentCollection */
			if (!$paymentCollection = $this->getCollection())
			{
				throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
			}

			/** @var Order $order */
			if (($order = $paymentCollection->getOrder()) && $order->getId() > 0)
			{
				OrderHistory::addField(
					'PAYMENT',
					$order->getId(),
					$name,
					$oldValue,
					$value,
					$this->getId(),
					$this
				);
			}
		}
	}

	/**
	 * @return Result
	 */
	public function verify()
	{
		$result = new Result();
		if ($this->getPaymentSystemId() <= 0)
		{
			$result->addError(new ResultError(Loc::getMessage("SALE_PAYMENT_PAYMENT_SERVICE_EMPTY"), 'SALE_PAYMENT_PAYMENT_SERVICE_EMPTY'));
		}
		return $result;
	}

	/**
	 * @param $id
	 *
	 * @return Result
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function setAccountNumber($id)
	{
		$result = new Sale\Result();
		$id = intval($id);
		if ($id <= 0)
		{
			$result->addError(new Sale\ResultError(Loc::getMessage('SALE_PAYMENT_GENERATE_ACCOUNT_NUMBER_ORDER_NUMBER_WRONG_ID'), 'SALE_PAYMENT_GENERATE_ACCOUNT_NUMBER_ORDER_NUMBER_WRONG_ID'));
			return $result;
		}

		$value = Internals\AccountNumberGenerator::generateForPayment($this);

		try
		{
			/** @var \Bitrix\Sale\Result $r */
			$r = static::updateInternal($id, array("ACCOUNT_NUMBER" => $value));
			$res = $r->isSuccess(true);
		}
		catch (Main\DB\SqlQueryException $exception)
		{
			$res = false;
		}

		if ($res)
		{
			$r = $this->setField('ACCOUNT_NUMBER', $value);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $mapping
	 * @return Payment|null|string
	 */
	public function getBusinessValueProviderInstance($mapping)
	{
		$providerInstance = null;

		if (is_array($mapping))
		{
			switch ($mapping['PROVIDER_KEY'])
			{
				case 'PAYMENT':
					$providerInstance = $this;
					break;
				case 'COMPANY':
					$providerInstance = $this->getField('COMPANY_ID');
					break;
				default:
					/** @var PaymentCollection $collection */
					$collection = $this->getCollection();

					/** @var Order $order */
					$order = $collection->getOrder();
					if ($order)
						$providerInstance = $order->getBusinessValueProviderInstance($mapping);
			}
		}

		return $providerInstance;
	}

	/**
	 * @return int|null
	 */
	public function getPersonTypeId()
	{
		/** @var PaymentCollection $collection */
		return ($collection = $this->getCollection()) && ($order = $collection->getOrder())
			? $order->getPersonTypeId()
			: null;
	}

	/**
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\PaymentTable::getList($parameters);
	}

	/**
	 * @internal
	 *
	 * @param \SplObjectStorage $cloneEntity
	 * @return Internals\CollectableEntity|object
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		/** @var Payment $paymentClone */
		$paymentClone = parent::createClone($cloneEntity);

		/** @var Sale\PaySystem\Service $paySystem */
		if ($paySystem = $this->getPaySystem())
		{
			if (!$cloneEntity->contains($paySystem))
			{
				$cloneEntity[$paySystem] = $paySystem->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($paySystem))
			{
				$paymentClone->paySystem = $cloneEntity[$paySystem];
			}
		}

		return $paymentClone;
	}

	/**
	 * @return mixed
	 */
	public function getHash()
	{
		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $this->getCollection();

		/** @var \Bitrix\Sale\Order $order */
		$order = $paymentCollection->getOrder();

		return md5(
			$this->getId().
			PriceMaths::roundPrecision($this->getSum()).
			$order->getId()
		);
	}

	/**
	 * @return bool
	 */
	public function isAllowPay()
	{
		/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $this->getCollection();

		/** @var \Bitrix\Sale\Order $order */
		$order = $paymentCollection->getOrder();

		return $order->isAllowPay();
	}

	/**
	 * @return bool
	 */
	public function isMarked()
	{
		return $this->getField('MARKED') == "Y";
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value)
	{
		static $className = null;
		$errorsList = static::getAutoFixErrorsList();
		if (is_array($errorsList) && in_array($value, $errorsList))
		{
			if ($className === null)
				$className = static::getClassName();
		}

		return $className;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function canAutoFixError($value)
	{
		$autoFix = false;
		$errorsList = static::getAutoFixErrorsList();
		if (is_array($errorsList) && in_array($value, $errorsList))
		{
			$autoFix = true;
		}
		return $autoFix;
	}

	/**
	 * @return array
	 */
	public function getAutoFixErrorsList()
	{
		return array();
	}

	/**
	 * @param $code
	 *
	 * @return Result
	 */
	public function tryFixError($code)
	{
		return new Result();
	}

	/**
	 * @return bool
	 */
	public function canMarked()
	{
		return true;
	}

	/**
	 * @return string
	 */
	public function getMarkField()
	{
		return 'MARKED';
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 */
	protected function addInternal(array $data)
	{
		return Internals\PaymentTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\ORM\Data\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\PaymentTable::update($primary, $data);
	}

	/**
	 * @param $primary
	 * @return Main\Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		return Internals\PaymentTable::delete($primary);
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\PaymentTable::getMap();
	}

	/**
	 * @return null
	 */
	public static function getUfId()
	{
		return Internals\PaymentTable::getUfId();
	}

}