<?php

namespace Bitrix\Sale;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Sale;
use Bitrix\Sale\Internals;
use Bitrix\Sale\PaySystem\ServiceResult;

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

	/** @var Sale\PaySystem\Service */
	protected $service;

	/** @var PayableItemCollection */
	protected $payableItemCollection;

	/**
	 * @return string|void
	 */
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_PAYMENT;
	}

	/**
	 * @return PayableItemCollection
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\SystemException
	 */
	public function getPayableItemCollection() : PayableItemCollection
	{
		if ($this->payableItemCollection === null)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var PayableItemCollection $itemCollectionClassName */
			$itemCollectionClassName = $registry->getPayableItemCollectionClassName();
			$this->payableItemCollection = $itemCollectionClassName::load($this);
		}

		return $this->payableItemCollection;
	}

	public function getBasketItemQuantity(BasketItem $basketItem) : float
	{
		$quantity = 0;

		/** @var PayableBasketItem $payableBasketItem */
		foreach ($this->getPayableItemCollection()->getBasketItems() as $payableBasketItem)
		{
			if ($payableBasketItem->getEntityObject()->getBasketCode() === $basketItem->getBasketCode())
			{
				$quantity += $payableBasketItem->getQuantity();
			}
		}

		return $quantity;
	}

	/**
	 * @param array $values
	 * @return array
	 */
	protected function onBeforeSetFields(array $values)
	{
		if (isset($values['PAID']))
		{
			if ($this->getField('PAID') === 'Y')
			{
				if ($values['PAID'] === 'N')
				{
					$values = ['PAID' => $values['PAID']] + $values;
				}
			}
			else
			{
				if ($values['PAID'] === 'Y')
				{
					// move to the end of array
					unset($values['PAID']);
					$values['PAID'] = 'Y';
				}
			}
		}

		return $values;
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return [
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
			'PS_RECURRING_TOKEN',
			'PS_CARD_NUMBER',
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
		];
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return ['PAY_SYSTEM_ID'];
	}

	/**
	 * @param array $fields
	 * @return Payment
	 * @throws Main\ArgumentException
	 */
	protected static function createPaymentObject(array $fields = [])
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public static function create(PaymentCollection $collection, Sale\PaySystem\Service $paySystem = null)
	{
		$fields = [
			'DATE_BILL' => new Main\Type\DateTime(),
			'SUM' => 0,
			'PAID' => 'N',
			'XML_ID' => static::generateXmlId(),
			'IS_RETURN' => static::RETURN_NONE,
			'CURRENCY' => $collection->getOrder()->getCurrency(),
			'ORDER_ID' => $collection->getOrder()->getId()
		];

		$payment = static::createPaymentObject();
		$payment->setFieldsNoDemand($fields);
		$payment->setCollection($collection);

		if ($paySystem !== null)
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
		$this->service = $service;
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
	 * @return Payment[]
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadForOrder($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException("id");
		}

		$payments = [];

		$paymentDataList = static::getList(['filter' => ['=ORDER_ID' => $id]]);
		while ($paymentData = $paymentDataList->fetch())
		{
			$payments[] = static::createPaymentObject($paymentData);
		}

		return $payments;
	}

	/**
	 * @internal
	 * @param $orderId
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function deleteNoDemand($orderId)
	{
		$result = new Result();

		$dbRes = static::getList([
				"select" => ["ID"],
				"filter" => ["=ORDER_ID" => $orderId]
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

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnBeforeSalePaymentEntityDeleted", [
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues(),
		]);
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(
						Loc::getMessage('SALE_EVENT_ON_BEFORE_SALEPAYMENT_ENTITY_DELETED_ERROR'),
						'SALE_EVENT_ON_BEFORE_SALEPAYMENT_ENTITY_DELETED_ERROR'
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

		return $result;
	}

	/**
	 * @return Result
	 */
	private function callEventOnEntityDeleted()
	{
		$result = new Result();

		/** @var Main\Event $event */
		$event = new Main\Event('sale', "OnSalePaymentEntityDeleted", [
				'ENTITY' => $this,
				'VALUES' => $this->fields->getOriginalValues(),
		]);
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() == Main\EventResult::ERROR)
				{
					$errorMsg = new ResultError(
						Loc::getMessage('SALE_EVENT_ON_SALEPAYMENT_ENTITY_DELETED_ERROR'),
						'SALE_EVENT_ON_SALEPAYMENT_ENTITY_DELETED_ERROR'
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

		if ($name === "PAID")
		{
			if ($value === "Y")
			{
				if (!$this->getFields()->isChanged('DATE_PAID'))
				{
					$this->setField('DATE_PAID', new Main\Type\DateTime());
				}

				$this->setField('EMP_PAID_ID', $USER->GetID());

				if ($this->getField('IS_RETURN') === self::RETURN_INNER)
				{
					$paySystemId = Sale\PaySystem\Manager::getInnerPaySystemId();
				}
				else
				{
					$paySystemId = $this->getPaymentSystemId();
				}

				$service = Sale\PaySystem\Manager::getObjectById($paySystemId);
				if ($service)
				{
					$operationResult = $service->creditNoDemand($this);
					if (!$operationResult->isSuccess())
					{
						return $result->addErrors($operationResult->getErrors());
					}
				}

				$this->setField('IS_RETURN', static::RETURN_NONE);

				Internals\EventsPool::addEvent(
					'p'.$this->getInternalIndex(),
					EventActions::EVENT_ON_PAYMENT_PAID,
					[
						'ENTITY' => $this,
						'VALUES' => $this->fields->getOriginalValues(),
					]
				);
			}

			$this->addCashboxChecks();
		}
		elseif ($name === "IS_RETURN")
		{
			if ($value === static::RETURN_NONE)
			{
				return $result;
			}

			if ($oldValue === static::RETURN_NONE)
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
								return $result->addError(
									new Entity\EntityError(
										Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_PAID'),
										'SALE_ORDER_PAYMENT_RETURN_PAID'
									)
								);
							}
						}
					}

					$refResult = $service->refund($this);
					if (!$refResult->isSuccess())
					{
						return $result->addErrors($refResult->getErrors());
					}

					$refResultOperation = $refResult->getOperationType();
					if ($refResultOperation === ServiceResult::MONEY_LEAVING)
					{
						$setUnpaidResult = $this->setField('PAID', 'N');
						if (!$setUnpaidResult->isSuccess())
						{
							return $result->addErrors($setUnpaidResult->getErrors());
						}
					}
				}
			}
			else
			{
				return $result->addError(
					new Entity\EntityError(
						Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_NO_SUPPORTED'),
						'SALE_ORDER_PAYMENT_RETURN_NO_SUPPORTED'
					)
				);
			}
		}
		elseif($name === "SUM")
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
		elseif ($name === "MARKED")
		{
			if ($oldValue !== "Y")
			{
				$this->setField('DATE_MARKED', new Main\Type\DateTime());

				if (is_object($USER))
				{
					$this->setField('EMP_MARKED_ID', $USER->GetID());
				}
			}
			elseif ($value === "N")
			{
				$r = $this->setField('REASON_MARKED', '');
				if (!$r->isSuccess())
				{
					return $result->addErrors($r->getErrors());
				}
			}
		}
		elseif ($name === 'RESPONSIBLE_ID')
		{
			$this->setField('DATE_RESPONSIBLE_ID', new Main\Type\DateTime());
		}

		return parent::onFieldModify($name, $oldValue, $value);
	}

	public function onBeforeBasketItemDelete(BasketItem $basketItem)
	{
		$result = new Result();

		$r = $this->getPayableItemCollection()->onBeforeBasketItemDelete($basketItem);
		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @return Result
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$this->checkCallingContext();

		$result = new Result();

		$id = $this->getId();
		$isNew = $id <= 0;

		$this->callEventOnBeforeEntitySaved();

		if (!$this->isChanged())
		{
			return $result;
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
			$result->addErrors($r->getErrors());
			return $result;
		}

		if ($id > 0)
		{
			$result->setId($id);
		}

		if ($this->fields->isChanged('PAID'))
		{
			$this->calculateStatistic();
		}

		$this->callEventOnEntitySaved();

		$this->callDelayedEvents();

		$payableItemCollection = $this->getPayableItemCollection();
		$r = $payableItemCollection->save();
		if (!$r->isSuccess())
		{
			return $result->addErrors($r->getErrors());
		}

		$this->onAfterSave($isNew);

		return $result;
	}

	public function isChanged()
	{
		$isChanged = parent::isChanged();
		if ($isChanged)
		{
			return true;
		}

		return $this->getPayableItemCollection()->isChanged();
	}

	/**
	 * @return void
	 */
	private function checkCallingContext()
	{
		$order = $this->getOrder();

		if (!$order->isSaveRunning())
		{
			trigger_error("Incorrect call to the save process. Use method save() on \Bitrix\Sale\Order entity", E_USER_WARNING);
		}
	}

	/**
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->getCollection()->getOrder();
	}

	/**
	 * @return void;
	 */
	protected function addCashboxChecks()
	{
		$service = $this->getPaySystem();
		if ($service && $service->getField("CAN_PRINT_CHECK") === "Y")
		{
			Cashbox\Internals\Pool::addDoc($this->getOrder()->getInternalId(), $this);
		}
	}

	/**
	 * @return void;
	 */
	protected function calculateStatistic()
	{
		/** @var Order $order */
		$order = $this->getOrder();

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

		$registry = Registry::getInstance(static::getRegistryType());
		/** @var OrderHistory $orderHistory */
		$orderHistory = $registry->getOrderHistoryClassName();

		if ($this->getOrderId() === 0)
		{
			$this->setFieldNoDemand('ORDER_ID', $this->getOrder()->getId());
		}

		$r = $this->addInternal($this->getFields()->getValues());
		if (!$r->isSuccess())
		{
			$orderHistory::addAction(
				'PAYMENT',
				$this->getOrderId(),
				'PAYMENT_ADD_ERROR',
				null,
				$this,
				["ERROR" => $r->getErrorMessages()]
			);

			$result->addErrors($r->getErrors());
			return $result;
		}

		$id = $r->getId();
		$this->setFieldNoDemand('ID', $id);
		$result->setId($id);

		$this->setAccountNumber($id);

		$orderHistory::addAction(
			'PAYMENT',
			$this->getOrderId(),
			'PAYMENT_ADDED',
			$id,
			$this
		);

		return $result;
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function update()
	{
		$result = new Result();

		$r = static::updateInternal($this->getId(), $this->getFields()->getChangedValues());
		if (!$r->isSuccess())
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();

			$orderHistory::addAction(
				'PAYMENT',
				$this->getOrderId(),
				'PAYMENT_UPDATE_ERROR',
				$this->getId(),
				$this,
				["ERROR" => $r->getErrorMessages()]
			);

			$result->addErrors($r->getErrors());
		}

		return $result;
	}

	/**
	 * @return void;
	 */
	private function callEventOnBeforeEntitySaved()
	{
		/** @var Main\Entity\Event $event */
		$event = new Main\Event('sale', 'OnBeforeSalePaymentEntitySaved', [
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues()
		]);

		$event->send();
	}

	/**
	 * @return void;
	 */
	private function callEventOnEntitySaved()
	{
		/** @var Main\Event $event */
		$event = new Main\Event('sale', 'OnSalePaymentEntitySaved', [
			'ENTITY' => $this,
			'VALUES' => $this->fields->getOriginalValues(),
		]);

		$event->send();
	}

	/**
	 * @throws Main\ArgumentException
	 */
	private function callDelayedEvents()
	{
		$eventList = Internals\EventsPool::getEvents('p'.$this->getInternalIndex());
		if ($eventList)
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
		return $this->getField('PAID') === 'Y';
	}

	/**
	 * @return bool
	 */
	public function isReturn()
	{
		return
			$this->getField('IS_RETURN') === static::RETURN_INNER
			||
			$this->getField('IS_RETURN') === static::RETURN_PS
		;
	}

	/**
	 * @return int
	 */
	public function getOrderId() : int
	{
		return (int)$this->getField('ORDER_ID');
	}

	/**
	 * @return PaySystem\Service
	 */
	public function getPaySystem()
	{
		if ($this->service === null)
		{
			$this->service = $this->loadPaySystem();
		}

		return $this->service;
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
		return (int)$this->getField('PAY_SYSTEM_ID');
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

		if ($value === static::RETURN_INNER || $value === static::RETURN_PS)
		{
			if ($this->isReturn())
			{
				return new Result();
			}
		}
		elseif($value === static::RETURN_NONE)
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
		return $this->getPaymentSystemId() === Sale\PaySystem\Manager::getInnerPaySystemId();
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws \Exception
	 */
	protected function normalizeValue($name, $value)
	{
		if ($this->isPriceField($name))
		{
			$value = PriceMaths::roundPrecision($value);
		}
		elseif ($name === 'REASON_MARKED')
		{
			$value = (string)$value;
			if (mb_strlen($value) > 255)
			{
				$value = mb_substr($value, 0, 255);
			}
		}

		return parent::normalizeValue($name, $value);
	}

	/**
	 * @param $name
	 * @param $value
	 * @return Result
	 * @throws Main\ArgumentException
	 */
	protected function checkValueBeforeSet($name, $value)
	{
		$result = parent::checkValueBeforeSet($name, $value);

		if ($name == "PAY_SYSTEM_ID")
		{
			if (intval($value) > 0 && !Sale\PaySystem\Manager::isExist($value))
			{
				$result->addError(
					new ResultError(
						Loc::getMessage('SALE_PAYMENT_WRONG_PAYMENT_SERVICE'),
						'SALE_PAYMENT_WRONG_PAYMENT_SERVICE'
					)
				);
			}
		}
		elseif ($name === 'ACCOUNT_NUMBER')
		{
			$dbRes = static::getList([
				'select' => ['ID'],
				'filter' => ['=ACCOUNT_NUMBER' => $value]
			]);

			if ($dbRes->fetch())
			{
				$result->addError(
					new ResultError(
						Loc::getMessage('SALE_PAYMENT_ACCOUNT_NUMBER_EXISTS')
					)
				);
			}
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @param null $oldValue
	 * @param null $value
	 */
	protected function addChangesToHistory($name, $oldValue = null, $value = null)
	{
		if ($this->getId() > 0)
		{
			$order = $this->getOrder();

			if ($order && $order->getId() > 0)
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

		$value = Internals\AccountNumberGenerator::generateForPayment($this);

		try
		{
			$r = static::updateInternal($id, ["ACCOUNT_NUMBER" => $value]);
			$res = $r->isSuccess(true);
		}
		catch (\Exception $exception)
		{
			$res = false;
		}

		if ($res)
		{
			$this->setFieldNoDemand('ACCOUNT_NUMBER', $value);
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

		if (is_array($mapping) && isset($mapping['PROVIDER_KEY']))
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
					$order = $this->getOrder();
					if ($order)
					{
						$providerInstance = $order->getBusinessValueProviderInstance($mapping);
					}
			}
		}

		return $providerInstance;
	}

	/**
	 * @return int|null
	 */
	public function getPersonTypeId()
	{
		$order = $this->getOrder();
		if ($order)
		{
			return $order->getPersonTypeId();
		}

		return null;
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = [])
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
				$paymentClone->service = $cloneEntity[$paySystem];
			}
		}

		return $paymentClone;
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 */
	public function getHash()
	{
		$order = $this->getOrder();

		return md5(
			$this->getId().
			PriceMaths::roundPrecision($this->getSum()).
			$order->getId()
		);
	}

	/**
	 * @deprecated
	 *
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function isAllowPay()
	{
		/** @var \Bitrix\Sale\Order $order */
		$order = $this->getOrder();

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
		return [];
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

	protected function isPriceField(string $name) : bool
	{
		return
			$name === 'PRICE_COD'
			|| $name === 'SUM'
		;
	}

	/**
	 * @param array $data
	 * @return Main\ORM\Data\AddResult
	 * @throws \Exception
	 *
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
	 * @return Main\ORM\Data\DeleteResult
	 * @throws \Exception
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

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SalePayment';
	}

}
