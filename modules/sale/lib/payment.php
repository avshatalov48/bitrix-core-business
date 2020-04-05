<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Services\Company;

Loc::loadMessages(__FILE__);

class Payment
	extends Internals\CollectableEntity
	implements IBusinessValueProvider, \IEntityMarker
{
	const RETURN_NONE = 'N';
	const RETURN_INNER = 'Y';
	const RETURN_PS = 'P';

	/** @var  Sale\PaySystem\Service */
	protected $paySystem;

	protected $isInner = null;

	protected static $innerPaySystemId = null;

	protected static $mapFields = array();

	private static $eventClassName = null;

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
			'COMPANY_BY',
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
	public static function getMeaningfulFields()
	{
		return array('PAY_SYSTEM_ID');
	}

	/**
	 * @return array
	 */
	public static function getAllFields()
	{
		if (empty(static::$mapFields))
		{
			static::$mapFields = parent::getAllFieldsByMap(Internals\PaymentTable::getMap());
		}
		return static::$mapFields;
	}

	/**
	 * @param array $fields
	 * @return Payment
	 */
	protected static function createPaymentObject(array $fields = array())
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$paymentClassName = $registry->getPaymentClassName();

		return new $paymentClassName($fields);
	}

	public static function create(PaymentCollection $collection, Sale\PaySystem\Service $paySystem = null)
	{
		$fields = array(
			'PAID' => 'N',
			'IS_RETURN' => 'N'
		);

		if ($paySystem != null)
		{
			$fields["PAY_SYSTEM_ID"] = $paySystem->getField('ID');
			$fields["PAY_SYSTEM_NAME"] = $paySystem->getField('NAME');
		}

		$payment = static::createPaymentObject();
		$payment->setFieldsNoDemand($fields);
		$payment->setCollection($collection);

		$payment->paySystem = $paySystem;

		return $payment;
	}

	public static function loadForOrder($id)
	{
		if (intval($id) <= 0)
			throw new Main\ArgumentNullException("id");

		$payments = array();

		$paymentDataList = Internals\PaymentTable::getList(
			array(
				'filter' => array('ORDER_ID' => $id)
			)
		);
		while ($paymentData = $paymentDataList->fetch())
			$payments[] = static::createPaymentObject($paymentData);

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
		
		$paymentDataList = Internals\PaymentTable::getList(
			array(
				"filter" => array("=ORDER_ID" => $idOrder),
				"select" => array("ID")
			)
		);
		while ($payment = $paymentDataList->fetch())	
		{
			$r = Internals\PaymentTable::delete($payment['ID']);
			
			if (!$r->isSuccess())
				$result->addErrors($r->getErrors());
		}

		return $result;
	}
	
	public function delete()
	{
		$result = new Result();
		if ($this->isPaid())
		{
			$result->addError(new ResultError(Loc::getMessage('SALE_PAYMENT_DELETE_EXIST_PAID'), 'SALE_PAYMENT_DELETE_EXIST_PAID'));
			return $result;
		}

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
				if($eventResult->getType() == Main\EventResult::ERROR)
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

			if (!$result->isSuccess())
			{
				return $result;
			}
		}


		$r = parent::delete();
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}



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

			if (!$result->isSuccess())
			{
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param mixed $oldValue
	 * @param mixed $value
	 * @return Result
	 * @throws Main\NotSupportedException
	 */
	protected function onFieldModify($name, $oldValue, $value)
	{
		global $USER;

		if ($name == "PAID")
		{
			if ($oldValue != "Y")
			{
				$this->setField('DATE_PAID', new Main\Type\DateTime());
				$this->setField('EMP_PAID_ID', $USER->GetID());
			}
//			if ($oldValue != $value && $value == "Y")
//			{
//				/** @var PaymentCollection $col */
//				$col = $this->getCollection();
//				Cashbox\Internals\Pool::addDoc($col->getOrder()->getInternalId(), $this);
//			}
		}
		elseif ($name == "IS_RETURN")
		{
			if ($oldValue != "Y")
			{
//				$this->setField('PAY_RETURN_DATE', new Main\Type\DateTime());
				$this->setField('EMP_RETURN_ID', $USER->GetID());
			}
		}
		elseif($name == "SUM")
		{
			if($this->isPaid())
			{
				$result = new Result();

				$result->addError( new ResultError(Loc::getMessage('SALE_PAYMENT_NOT_ALLOWED_CHANGE_SUM'), 'SALE_PAYMENT_NOT_ALLOWED_CHANGE_SUM') );
				return $result;
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
				$this->setField('REASON_MARKED', '');
			}
		}

		return parent::onFieldModify($name, $oldValue, $value);
	}

	/**
	 * @return Entity\AddResult|Entity\UpdateResult
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();
		$id = $this->getId();
		$fields = $this->fields->getValues();
		$isNew = ($this->getId() == 0);
		$oldEntityValues = $this->fields->getOriginalValues();

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

		if (self::$eventClassName === null)
		{
			self::$eventClassName = static::getEntityEventName();
		}

		if ($this->isChanged() && self::$eventClassName)
		{
			/** @var Main\Entity\Event $event */
			$event = new Main\Event('sale', 'OnBefore'.self::$eventClassName.'EntitySaved', array(
					'ENTITY' => $this,
					'VALUES' => $this->fields->getOriginalValues()
			));
			$event->send();
		}

		if ($id > 0)
		{
			$fields = $this->fields->getChangedValues();

			if (!empty($fields) && is_array($fields))
			{
				if (array_key_exists('REASON_MARKED', $fields) && strlen($fields['REASON_MARKED']) > 255)
				{
					$fields['REASON_MARKED'] = substr($fields['REASON_MARKED'], 0, 255);

					$this->setFieldNoDemand('REASON_MARKED', $fields['REASON_MARKED']);
				}

				//$fields['DATE_UPDATE'] = new Main\Type\DateTime();

				$r = Internals\PaymentTable::update($id, $fields);
				if (!$r->isSuccess())
				{
					OrderHistory::addAction(
						'PAYMENT',
						$order->getId(),
						'PAYMENT_UPDATE_ERROR',
						$id,
						$this,
						array("ERROR" => $r->getErrorMessages())
					);

					$result->addErrors($r->getErrors());
					return $result;
				}

				if ($resultData = $r->getData())
					$result->setData($resultData);
			}
		}
		else
		{
			$fields['ORDER_ID'] = $this->getParentOrderId();
			$this->setFieldNoDemand('ORDER_ID', $fields['ORDER_ID']);

			if (!isset($fields['CURRENCY']) || strval($fields['CURRENCY']) == "" )
			{
				$fields['CURRENCY'] = $order->getCurrency();
				$this->setFieldNoDemand('CURRENCY', $fields['CURRENCY']);
			}

			if (!isset($fields['DATE_BILL']) || strval($fields['DATE_BILL']) == "" )
			{
				$fields['DATE_BILL'] = new Main\Type\DateTime();
				$this->setFieldNoDemand('DATE_BILL', $fields['DATE_BILL']);
			}

			if (array_key_exists('REASON_MARKED', $fields) && strlen($fields['REASON_MARKED']) > 255)
			{
				$fields['REASON_MARKED'] = substr($fields['REASON_MARKED'], 0, 255);

				$this->setFieldNoDemand('REASON_MARKED', $fields['REASON_MARKED']);
			}

			$r = Internals\PaymentTable::add($fields);
			if (!$r->isSuccess())
			{
				OrderHistory::addAction(
					'PAYMENT',
					$order->getId(),
					'PAYMENT_UPDATE_ERROR',
					null,
					$this,
					array("ERROR" => $r->getErrorMessages())
				);
				$result->addErrors($r->getErrors());
				return $result;
			}

			if ($resultData = $r->getData())
				$result->setData($resultData);

			$id = $r->getId();
			$this->setFieldNoDemand('ID', $id);

			$this->setAccountNumber($id);

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
		}

		if (!empty($fields['PAID']) && $fields['PAID'] == "Y")
		{
			/** @var Main\Event $event */
			$event = new Main\Event('sale', EventActions::EVENT_ON_PAYMENT_PAID, array(
				'ENTITY' => $this,
				'VALUES' => $oldEntityValues,
			));
			$event->send();

			Notify::callNotify($this, EventActions::EVENT_ON_PAYMENT_PAID);
		}

		if ($id > 0)
		{
			$result->setId($id);
		}


		if ($result->isSuccess())
		{
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

			$changedKeys = $this->fields->getChangedKeys();
			if (in_array("PAID", $changedKeys))
			{
				$originalValues = $this->fields->getOriginalValues();
				/** @var Sale\PaySystem\Service $ps */
				$ps = $this->getPaySystem();
				if ($originalValues["PAID"] != $this->getField("PAID"))
				{
					/** @var PaymentCollection $col */
					$col = $this->getCollection();
					$order = $col->getOrder();
					if ($ps->getField("CAN_PRINT_CHECK") == "Y")
					{
						Cashbox\Internals\Pool::addDoc($order->getInternalId(), $this);
					}

					BuyerStatistic::calculate($order->getUserId(), $order->getCurrency(), $order->getSiteId());
				}
			}

			if ($this->isChanged() && self::$eventClassName)
			{
				/** @var Main\Event $event */
				$event = new Main\Event('sale', 'On'.self::$eventClassName.'EntitySaved', array(
					'ENTITY' => $this,
					'VALUES' => $this->fields->getOriginalValues(),
				));
				$event->send();
			}

			if (($eventList = Internals\EventsPool::getEvents('p'.$this->getInternalIndex())) && !empty($eventList) && is_array($eventList))
			{
				foreach ($eventList as $eventName => $eventData)
				{
					$event = new Main\Event('sale', $eventName, $eventData);
					$event->send();

					Notify::callNotify($this, $eventName);
				}

				Internals\EventsPool::resetEvents('p'.$this->getInternalIndex());
			}
		}

		return $result;
	}

	private function getParentOrderId()
	{
		/** @var PaymentCollection $collection */
		if (!$collection = $this->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "PaymentCollection" not found');
		}

		/** @var Order $order */
		if (!$order = $collection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		return $order->getId();
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
	 * @return Payment|Sale\PaySystem\Service|bool|static
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
	 * @return Sale\PaySystem\Service|bool|static
	 */
	protected function loadPaySystem()
	{
		if ($paySystemId = $this->getPaymentSystemId())
		{
			$this->paySystem = Sale\PaySystem\Manager::getObjectById($paySystemId);
		}
		return $this->paySystem;
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

		if ($value == "Y")
		{
			if ($this->isPaid())
				return new Result();

			if ($this->getField('IS_RETURN') == self::RETURN_INNER)
			{
				$innerPsId = Sale\PaySystem\Manager::getInnerPaySystemId();

				$service = Sale\PaySystem\Manager::getObjectById($innerPsId);
				if ($service)
				{
					$operationResult = $service->creditNoDemand($this);
					if (!$operationResult->isSuccess())
						$result->addErrors($operationResult->getErrors());
					else
						$this->setFieldNoDemand('IS_RETURN', self::RETURN_NONE);
				}
			}
			else
			{
				$service = Sale\PaySystem\Manager::getObjectById($this->getPaymentSystemId());
				if ($service)
				{
					$operationResult = $service->creditNoDemand($this);
					if (!$operationResult->isSuccess())
						$result->addErrors($operationResult->getErrors());
				}
			}
		}
		elseif($value == "N")
		{
			if (!$this->isPaid())
				return new Result();
		}
		else
		{
			throw new Main\ArgumentOutOfRangeException('value');
		}

		if ($result->isSuccess())
		{
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
				return new Result();

		}
		elseif($value == "N")
		{
			if (!$this->isReturn())
				return new Result();

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
	 * @return bool|null
	 * @throws Main\ObjectNotFoundException
	 */
	public function isInner()
	{

		if ($this->isInner !== null)
			return $this->isInner;


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

		if ( ($paySystemId = static::getInnerPaySystemId()) && intval($paySystemId) > 0)
		{
			$this->isInner = ($this->getPaymentSystemId() == $paySystemId);
		}

		return $this->isInner;
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


		return parent::setField($name, $value);
	}

	/**
	 * @internal
	 *
	 * @param $name
	 * @param $value
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
	 * @return int|null
	 */
	protected static function getInnerPaySystemId()
	{
		if (static::$innerPaySystemId === null)
		{
			static::$innerPaySystemId = \Bitrix\Sale\PaySystem\Manager::getInnerPaySystemId();
		}

		return static::$innerPaySystemId;
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

		$value = Internals\AccountNumberGenerator::generate($this);

		try
		{
			/** @var \Bitrix\Sale\Result $r */
			$r = Internals\PaymentTable::update($id, array("ACCOUNT_NUMBER" => $value));
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

	public function getBusinessValueProviderInstance($mapping)
	{
		$providerInstance = null;

		if (is_array($mapping))
		{
			switch ($mapping['PROVIDER_KEY'])
			{
				case 'PAYMENT': $providerInstance = $this; break;
				case 'COMPANY': $providerInstance = $this->getField('COMPANY_ID'); break;
				default:
					/** @var PaymentCollection $collection */
					if (($collection = $this->getCollection()) && ($order = $collection->getOrder()))
						$providerInstance = $order->getBusinessValueProviderInstance($mapping);
			}
		}

		return $providerInstance;
	}

	public function getPersonTypeId()
	{
		/** @var PaymentCollection $collection */
		return ($collection = $this->getCollection()) && ($order = $collection->getOrder())
			? $order->getPersonTypeId()
			: null;
	}


	/**
	 * @param array $filter
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $filter)
	{
		return Internals\PaymentTable::getList($filter);
	}


	/**
	 * @internal
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return Payment
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$paymentClone = clone $this;
		$paymentClone->isClone = true;

		/** @var Internals\Fields $fields */
		if ($fields = $this->fields)
		{
			$paymentClone->fields = $fields->createClone($cloneEntity);
		}

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $paymentClone;
		}

		if ($collection = $this->getCollection())
		{
			if (!$cloneEntity->contains($collection))
			{
				$cloneEntity[$collection] = $collection->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($collection))
			{
				$paymentClone->collection = $cloneEntity[$collection];
			}
		}

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
		return ($this->getField('MARKED') == "Y");
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
		$result = new Result();

		$method = static::getFixMethod($code);
		$r = call_user_func_array($method, array($this));
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

	public function canMarked()
	{
		return true;
	}

	public function getMarkField()
	{
		return 'MARKED';
	}


}