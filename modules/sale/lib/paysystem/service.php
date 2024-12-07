<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Entity\EntityError;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Sale;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultError;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Services\Base\RestrictionInfoCollection;
use Bitrix\Sale\Services\Base\RestrictableService;

Loc::loadMessages(__FILE__);

/**
 * Class Service
 * @package Bitrix\Sale\PaySystem
 */
class Service implements RestrictableService
{
	public const EVENT_ON_BEFORE_PAYMENT_PAID = 'OnSalePsServiceProcessRequestBeforePaid';
	public const EVENT_ON_AFTER_PROCESS_REQUEST = 'OnSaleAfterPsServiceProcessRequest';

	public const EVENT_BEFORE_ON_INITIATE_PAY = 'onSalePsBeforeInitiatePay';
	public const EVENT_INITIATE_PAY_SUCCESS = 'onSalePsInitiatePaySuccess';
	public const EVENT_INITIATE_PAY_ERROR = 'onSalePsInitiatePayError';

	public const PAY_SYSTEM_PREFIX = 'PAYSYSTEM_';

	/** @var ServiceHandler|IHold|IPartialHold|IRefund|IPrePayable|ICheckable|IPayable|IPdf|IDocumentGeneratePdf|IRecurring|Sale\PaySystem\Cashbox\ISupportPrintCheck $handler */
	private $handler = null;

	/** @var array */
	private $fields = array();

	/** @var bool */
	protected $isClone = false;

	/** @var Context  */
	protected $context;

	/**
	 * Service constructor.
	 * @param $fields
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function __construct($fields)
	{
		[$className, $handlerType] = Manager::includeHandler($fields['ACTION_FILE']);

		$this->fields = $fields;
		$this->handler = new $className($handlerType, $this);

		$this->context = new Context();
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @param int $mode
	 * @return ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null, $mode = BaseServiceHandler::STREAM)
	{
		$onBeforeInitResult = $this->callEventOnBeforeInitiatePay($payment);
		if (!$onBeforeInitResult->isSuccess())
		{
			$error = implode("\n", $onBeforeInitResult->getErrorMessages());
			Logger::addError(get_class($this->handler) . '. OnBeforeInitiatePay: ' . $error);

			$this->markPayment($payment, $onBeforeInitResult);

			return $onBeforeInitResult;
		}

		$this->handler->setInitiateMode($mode);
		$initResult = $this->handler->initiatePay($payment, $request);

		$psData = $initResult->getPsData();
		if ($psData)
		{
			$setResult = $payment->setFields($psData);
			if ($setResult->isSuccess())
			{
				$order = $payment->getCollection()->getOrder();
				if ($order)
				{
					$saveResult = $order->save();
					if (!$saveResult->isSuccess())
					{
						$initResult->addErrors($saveResult->getErrors());
					}
				}
			}
			else
			{
				$initResult->addErrors($setResult->getErrors());
			}
		}

		if ($initResult->isSuccess())
		{
			$this->callEventOnInitiatePaySuccess($payment);
		}
		else
		{
			$error = implode("\n", $initResult->getErrorMessages());
			Logger::addError(get_class($this->handler) . '. InitiatePay: ' . $error);

			$this->markPayment($payment, $onBeforeInitResult);

			$this->callEventOnInitiatePayError($payment, $initResult);
		}

		return $initResult;
	}

	private function callEventOnBeforeInitiatePay(Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		$event = new Event(
			'sale',
			self::EVENT_BEFORE_ON_INITIATE_PAY,
			[
				'payment' => $payment,
				'service' => $this
			]
		);

		$event->send();

		foreach($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() === EventResult::ERROR)
			{
				$parameters = $eventResult->getParameters();
				$error = $parameters['ERROR'] ?? null;

				$result->addError(
					$error instanceof Error
						? $error
						: Sale\PaySystem\Error::create(
							Loc::getMessage('SALE_PS_SERVICE_ERROR_ON_BEFORE_INITIATE_PAY')
						)
				);
			}
		}

		return $result;
	}

	private function callEventOnInitiatePaySuccess(Payment $payment)
	{
		$event = new Event(
			'sale',
			self::EVENT_INITIATE_PAY_SUCCESS,
			[
				'payment' => $payment
			]
		);

		$event->send();
	}

	private function callEventOnInitiatePayError(Payment $payment, ServiceResult $result)
	{
		$event = new Event(
			'sale',
			self::EVENT_INITIATE_PAY_ERROR,
			[
				'payment' => $payment,
				'errors' => $result->getErrorMessages(),
			]
		);

		$event->send();
	}
	/**
	 * @return bool
	 */
	public function isRefundable()
	{
		if ($this->handler instanceof IRefundExtended)
		{
			return $this->handler->isRefundableExtended();
		}

		return $this->handler instanceof IRefund;
	}

	/**
	 * @param Payment $payment
	 * @param int $refundableSum
	 * @return ServiceResult|Result
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function refund(Payment $payment, $refundableSum = 0)
	{
		if ($this->isRefundable())
		{
			$result = new Result();

			if (!$payment->isPaid())
			{
				$result->addError(new ResultError(Loc::getMessage('SALE_PS_SERVICE_PAYMENT_NOT_PAID')));
				return $result;
			}

			if ($refundableSum == 0)
				$refundableSum = $payment->getSum();

			/** @var ServiceResult $result */
			$result = $this->handler->refund($payment, $refundableSum);
			if (!$result->isSuccess())
			{
				Logger::addError(get_class($this->handler).': refund: '.implode("\n", $result->getErrorMessages()));
			}

			return $result;
		}

		throw new SystemException();
	}

	/**
	 * @param Request $request
	 * @return Result
	 * @throws NotSupportedException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public function processRequest(Request $request)
	{
		$processResult = new Result();

		if (!($this->handler instanceof ServiceHandler))
		{
			return $processResult;
		}

		$debugInfo = http_build_query($request->toArray(), "", "\n");
		if (empty($debugInfo))
		{
			$debugInfo = file_get_contents("php://input");
		}
		Logger::addDebugInfo(
			get_class($this->handler)." ProcessRequest. paySystemId=".$this->getField("ID").", request=".($debugInfo ? $debugInfo : "empty")
		);

		$paymentId = $this->handler->getPaymentIdFromRequest($request);

		if (empty($paymentId))
		{
			$processResult->addError(new Error(Loc::getMessage('SALE_PS_SERVICE_PAYMENT_ERROR_EMPTY')));

			Logger::addError(
				get_class($this->handler).'. ProcessRequest: '.Loc::getMessage('SALE_PS_SERVICE_PAYMENT_ERROR_EMPTY')
			);

			return $processResult;
		}

		[$orderId, $paymentId] = Manager::getIdsByPayment($paymentId, $this->getField('ENTITY_REGISTRY_TYPE'));

		if (!$orderId)
		{
			$errorMessage = str_replace('#ORDER_ID#', $orderId, Loc::getMessage('SALE_PS_SERVICE_ORDER_ERROR'));
			$processResult->addError(new Error($errorMessage));

			Logger::addError(get_class($this->handler).'. ProcessRequest: '.$errorMessage);

			return $processResult;
		}

		$registry = Registry::getInstance($this->getField('ENTITY_REGISTRY_TYPE'));
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$order = $orderClassName::load($orderId);
		if (!$order)
		{
			$errorMessage = str_replace('#ORDER_ID#', $orderId, Loc::getMessage('SALE_PS_SERVICE_ORDER_ERROR'));
			$processResult->addError(new Error($errorMessage));

			Logger::addError(get_class($this->handler).'. ProcessRequest: '.$errorMessage);

			return $processResult;
		}

		if ($order->isCanceled())
		{
			$errorMessage = str_replace('#ORDER_ID#', $orderId, Loc::getMessage('SALE_PS_SERVICE_ORDER_CANCELED'));
			$processResult->addError(new Error($errorMessage));

			Logger::addError(get_class($this->handler).'. ProcessRequest: '.$errorMessage);

			return $processResult;
		}

		/** @var \Bitrix\Sale\PaymentCollection $collection */
		$collection = $order->getPaymentCollection();

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $collection->getItemById($paymentId);

		if (!$payment)
		{
			$errorMessage = str_replace('#PAYMENT_ID#', $paymentId, Loc::getMessage('SALE_PS_SERVICE_PAYMENT_ERROR'));
			$processResult->addError(new Error($errorMessage));

			Logger::addError(get_class($this->handler).'. ProcessRequest: '.$errorMessage);

			return $processResult;
		}

		/** @var \Bitrix\Sale\PaySystem\ServiceResult $serviceResult */
		$serviceResult = $this->handler->processRequest($payment, $request);
		if ($serviceResult->isSuccess())
		{
			$status = null;
			$operationType = $serviceResult->getOperationType();

			if ($operationType === ServiceResult::MONEY_COMING)
			{
				$status = 'Y';
			}
			else if ($operationType === ServiceResult::MONEY_LEAVING)
			{
				$status = 'N';
			}

			if ($status !== null)
			{
				$event = new Event('sale', self::EVENT_ON_BEFORE_PAYMENT_PAID,
					array(
						'payment' => $payment,
						'status' => $status,
						'pay_system_id' => $this->getField('ID')
					)
				);
				$event->send();

				if ($status === 'N')
				{
					$payment->setFieldsNoDemand([
						'IS_RETURN' => Payment::RETURN_PS,
						'PAY_RETURN_DATE' => new Date(),
					]);
				}

				$paidResult = $payment->setPaid($status);
				if (!$paidResult->isSuccess())
				{
					$error = 'PAYMENT SET PAID: '.join(' ', $paidResult->getErrorMessages());
					Logger::addError(get_class($this->handler).'. ProcessRequest: '.$error);

					$serviceResult->setResultApplied(false);
				}
			}

			$psData = $serviceResult->getPsData();
			if ($psData)
			{
				$res = $payment->setFields($psData);
				if (!$res->isSuccess())
				{
					$error = 'PAYMENT SET PAID: '.join(' ', $res->getErrorMessages());
					Logger::addError(get_class($this->handler).'. ProcessRequest: '.$error);

					$serviceResult->setResultApplied(false);
				}
			}

			$saveResult = $order->save();

			if (!$saveResult->isSuccess())
			{
				$error = 'ORDER SAVE: '.join(' ', $saveResult->getErrorMessages());
				Logger::addError(get_class($this->handler).'. ProcessRequest: '.$error);

				$serviceResult->setResultApplied(false);
			}

			PullManager::onSuccessfulPayment($payment);
		}
		else
		{
			$serviceResult->setResultApplied(false);
			$processResult->addErrors($serviceResult->getErrors());

			$error = implode("\n", $serviceResult->getErrorMessages());
			Logger::addError(get_class($this->handler).'. ProcessRequest Error: '.$error);

			$this->markPayment($payment, $serviceResult);

			PullManager::onFailurePayment($payment);
		}

		$event = new Event(
			'sale',
			self::EVENT_ON_AFTER_PROCESS_REQUEST,
			[
				'payment' => $payment,
				'serviceResult' => $serviceResult,
				'request' => $request,
			]
		);
		$event->send();

		$this->handler->sendResponse($serviceResult, $request);

		return $processResult;
	}

	/**
	 * @return string
	 */
	public function getConsumerName()
	{
		$id = $this->fields['ID'] ?? 0;

		return static::PAY_SYSTEM_PREFIX.$id;
	}

	/**
	 * @return array
	 */
	public function getHandlerDescription()
	{
		return $this->handler->getDescription();
	}

	/**
	 * @return bool
	 */
	public function isBlockable()
	{
		return $this->handler instanceof IHold || $this->handler instanceof IPartialHold;
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 * @throws SystemException
	 */
	public function cancel(Payment $payment)
	{
		if ($this->isBlockable())
		{
			return $this->handler->cancel($payment);
		}

		throw new SystemException(Loc::getMessage('SALE_PS_SERVICE_ERROR_HOLD_IS_NOT_SUPPORTED'));
	}

	/**
	 * @param Payment $payment
	 * @param int $sum
	 * @return ServiceResult
	 * @throws SystemException
	 */
	public function confirm(Payment $payment, $sum = 0)
	{
		if ($this->isBlockable())
		{
			if ($this->handler instanceof IPartialHold)
			{
				return $this->handler->confirm($payment, $sum);
			}
			else if ($sum > 0)
			{
				throw new SystemException(Loc::getMessage('SALE_PS_SERVICE_ERROR_PARTIAL_CONFIRM_IS_NOT_SUPPORTED'));
			}

			return $this->handler->confirm($payment);
		}

		throw new SystemException(Loc::getMessage('SALE_PS_SERVICE_ERROR_HOLD_IS_NOT_SUPPORTED'));
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getField($name)
	{
		return $this->fields[$name] ?? null;
	}

	/**
	 * @return array
	 */
	public function getCurrency()
	{
		return $this->handler->getCurrencyList();
	}

	/**
	 * The type of client that the handler can work with
	 *
	 * @return string
	 */
	public function getClientTypeFromHandler()
	{
		return $this->handler->getClientType(
			$this->fields['PS_MODE'] ?? null
		);
	}

	/**
	 * The type of client that the payment system can work with
	 *
	 * @return string
	 */
	public function getClientType()
	{
		return (string)($this->fields['PS_CLIENT_TYPE'] ?? $this->getClientTypeFromHandler());
	}

	/**
	 * @return bool
	 */
	public function isCash()
	{
		return $this->fields['IS_CASH'] === 'Y';
	}

	/**
	 * @return bool
	 */
	public function canPrintCheck(): bool
	{
		return $this->fields['CAN_PRINT_CHECK'] === 'Y';
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	public function canPrintCheckSelf(Payment $payment): bool
	{
		$service = $payment->getPaySystem();
		if (!$service || !$this->isSupportPrintCheck() || !$this->canPrintCheck())
		{
			return false;
		}

		/** @var Cashbox\CashboxPaySystem $cashboxClass */
		$cashboxClass = $this->getCashboxClass();
		$kkm = $cashboxClass::getKkmValue($service);

		return (bool)Cashbox\Manager::getList([
			'select' => ['ID'],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=HANDLER' => $cashboxClass,
				'=KKM_ID' => $kkm,
			],
		])->fetch();
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 */
	public function creditNoDemand(Payment $payment)
	{
		return $this->handler->creditNoDemand($payment);
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 */
	public function debitNoDemand(Payment $payment)
	{
		return $this->handler->debitNoDemand($payment);
	}

	/**
	 * @return bool
	 */
	public function isPayable()
	{
		if ($this->handler instanceof IPayable)
			return true;

		if (method_exists($this->handler, 'isPayableCompatibility'))
			return $this->handler->isPayableCompatibility();

		return false;
	}

	/**
	 * @return bool
	 */
	public function isAffordPdf()
	{
		return $this->handler instanceof IPdf;
	}

	/**
	 * @return bool
	 */
	public function isAffordDocumentGeneratePdf()
	{
		return $this->handler instanceof IDocumentGeneratePdf;
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function getPdfContent(Payment $payment)
	{
		if ($this->isAffordPdf())
		{
			return $this->handler->getContent($payment);
		}

		throw new NotSupportedException('Handler is not implemented interface '.IPdf::class);
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function getPdf(Payment $payment)
	{
		if ($this->isAffordPdf())
		{
			return $this->handler->getFile($payment);
		}

		throw new NotSupportedException('Handler is not implemented interface '.IPdf::class);
	}

	/**
	 * @param Payment $payment
	 * @param $params
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function registerCallbackOnGenerate(Payment $payment, $params)
	{
		if ($this->isAffordDocumentGeneratePdf())
		{
			return $this->handler->registerCallbackOnGenerate($payment, $params);
		}

		throw new NotSupportedException('Handler is not implemented interface '.IDocumentGeneratePdf::class);
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function isPdfGenerated(Payment $payment)
	{
		if ($this->isAffordPdf())
		{
			return $this->handler->isGenerated($payment);
		}

		throw new NotSupportedException('Handler is not implemented interface '.IPdf::class);
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	public function getPaymentPrice(Payment $payment)
	{
		if ($this->isPayable())
			return $this->handler->getPrice($payment);

		return 0;
	}

	/**
	 * @param array $params
	 */
	public function setTemplateParams(array $params)
	{
		$this->handler->setExtraParams($params);
	}

	/**
	 * @param Payment|null $payment
	 * @param $templateName
	 * @return ServiceResult
	 */
	public function showTemplate(Payment $payment = null, $templateName)
	{
		return $this->handler->showTemplate($payment, $templateName);
	}

	/**
	 * @return bool
	 */
	public function isPrePayable()
	{
		return $this->handler instanceof IPrePayable;
	}

	/**
	 * @param Payment|null $payment
	 * @param Request $request
	 * @throws NotSupportedException
	 */
	public function initPrePayment(Payment $payment = null, Request $request)
	{
		if ($this->isPrePayable())
			return $this->handler->initPrePayment($payment, $request);

		throw new NotSupportedException;
	}

	/**
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function getPrePaymentProps()
	{
		if ($this->isPrePayable())
			return $this->handler->getProps();

		throw new NotSupportedException;
	}

	/**
	 * @param array $orderData
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function basketButtonAction(array $orderData = array())
	{
		if ($this->isPrePayable())
			return $this->handler->basketButtonAction($orderData);

		throw new NotSupportedException;
	}

	/**
	 * @param array $orderData
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function setOrderDataForPrePayment($orderData = array())
	{
		if ($this->isPrePayable())
			return $this->handler->setOrderConfig($orderData);

		throw new NotSupportedException;
	}

	/**
	 * @param $orderData
	 * @return mixed
	 * @throws NotSupportedException
	 */
	public function payOrderByPrePayment($orderData)
	{
		if ($this->isPrePayable())
			return $this->handler->payOrder($orderData);

		throw new NotSupportedException;
	}

	/**
	 * @return array
	 */
	public function getFieldsValues()
	{
		return $this->fields;
	}

	/**
	 * @return bool
	 */
	public function isAllowEditPayment()
	{
		return $this->fields['ALLOW_EDIT_PAYMENT'] == 'Y';
	}

	/**
	 * @return bool
	 */
	public function isCheckable()
	{
		if ($this->handler instanceof ICheckable)
			return true;

		if (method_exists($this->handler, 'isCheckableCompatibility'))
			return $this->handler->isCheckableCompatibility();

		return false;
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 */
	public function check(Payment $payment)
	{
		$result = new ServiceResult();

		if ($this->isCheckable())
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $payment->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			$order = $paymentCollection->getOrder();

			if (!$order->isCanceled())
			{
				/** @var ServiceResult $result */
				$checkResult = $this->handler->check($payment);
				if ($checkResult instanceof ServiceResult && $checkResult->isSuccess())
				{
					$psData = $checkResult->getPsData();
					if ($psData)
					{
						$res = $payment->setFields($psData);
						if (!$res->isSuccess())
							$result->addErrors($res->getErrors());
					}

					if ($checkResult->getOperationType() == ServiceResult::MONEY_COMING)
					{
						$res = $payment->setPaid('Y');
						if (!$res->isSuccess())
							$result->addErrors($res->getErrors());
					}

					$res = $order->save();
					if (!$res->isSuccess())
						$result->addErrors($res->getErrors());
				}
				elseif (!$checkResult)
				{
					$result->addError(new Error(Loc::getMessage('SALE_PS_SERVICE_ERROR_CONNECT_PS')));
				}
			}
			else
			{
				$result->addError(new EntityError(Loc::getMessage('SALE_PS_SERVICE_ORDER_CANCELED', array('#ORDER_ID#' => $order->getId()))));
			}
		}

		return $result;
	}

	/**
	 * @param \SplObjectStorage $cloneEntity
	 *
	 * @return Service
	 */
	public function createClone(\SplObjectStorage $cloneEntity)
	{
		if ($this->isClone() && $cloneEntity->contains($this))
		{
			return $cloneEntity[$this];
		}

		$paySystemServiceClone = clone $this;
		$paySystemServiceClone->isClone = true;

		if (!$cloneEntity->contains($this))
		{
			$cloneEntity[$this] = $paySystemServiceClone;
		}

		if ($handler = $this->handler)
		{
			if (!$cloneEntity->contains($handler))
			{
				$cloneEntity[$handler] = $handler->createClone($cloneEntity);
			}

			if ($cloneEntity->contains($handler))
			{
				$paySystemServiceClone->handler = $cloneEntity[$handler];
			}
		}

		return $paySystemServiceClone;
	}

	/**
	 * @return bool
	 */
	public function isClone()
	{
		return $this->isClone;
	}

	/**
	 * @return bool
	 */
	public function isCustom()
	{
		return in_array($this->handler->getHandlerType(), array('CUSTOM', 'USER'));
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	public function getParamsBusValue(Payment $payment)
	{
		return $this->handler->getParamsBusValue($payment);
	}

	/**
	 * @return bool
	 */
	public function isTuned()
	{
		return $this->handler->isTuned();
	}

	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		return $this->handler->getDemoParams();
	}

	/**
	 * @param $mode
	 */
	public function setTemplateMode($mode)
	{
		$this->handler->setInitiateMode($mode);
	}

	/**
	 * @return Context
	 */
	public function getContext(): Context
	{
		return $this->context;
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	public function isRecurring(Payment $payment): bool
	{
		return $this->handler instanceof IRecurring
			&& $this->handler->isRecurring($payment);
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 */
	public function repeatRecurrent(Payment $payment, Request $request = null): ServiceResult
	{
		$result = new ServiceResult();

		if ($this->isRecurring($payment))
		{
			return $this->handler->repeatRecurrent($payment, $request);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 */
	public function cancelRecurrent(Payment $payment, Request $request = null): ServiceResult
	{
		$result = new ServiceResult();

		if ($this->isRecurring($payment))
		{
			return $this->handler->cancelRecurrent($payment, $request);
		}

		return $result;
	}

	/**
	 * Returns true if handler extends ISupportPrintCheck interface
	 *
	 * @return bool
	 */
	public function isSupportPrintCheck(): bool
	{
		return $this->handler instanceof Sale\PaySystem\Cashbox\ISupportPrintCheck;
	}

	/**
	 * Returns class name of cashbox for pay system
	 *
	 * @return string
	 * @throws NotSupportedException
	 */
	public function getCashboxClass(): string
	{
		if ($this->isSupportPrintCheck())
		{
			$cashboxClassName = $this->handler::getCashboxClass();
			if (!Cashbox\Manager::isPaySystemCashbox($cashboxClassName))
			{
				throw new NotSupportedException(
					'Cashbox is not extended class '.Cashbox\CashboxPaySystem::class
				);
			}

			return $cashboxClassName;
		}

		throw new NotSupportedException(
			'Handler is not implemented interface '.Sale\PaySystem\Cashbox\ISupportPrintCheck::class
		);
	}

	/**
	 * Returns true if handler extends IFiscalizationAware interface
	 *
	 * @return bool
	 */
	public function isFiscalizationAware(): bool
	{
		return $this->handler instanceof Sale\PaySystem\Cashbox\IFiscalizationAware;
	}

	/**
	 * Returns indicator showing if fiscalization is enabled on the payment system side
	 *
	 * @param Payment $payment
	 * @return bool
	 * @throws NotSupportedException
	 */
	public function isFiscalizationEnabled(Payment $payment): bool
	{
		if ($this->isFiscalizationAware())
		{
			return $this->handler->isFiscalizationEnabled($payment);
		}

		throw new NotSupportedException(
			'Handler does not implement interface '.Sale\PaySystem\Cashbox\IFiscalizationAware::class
		);
	}

	public function getStartupRestrictions(): RestrictionInfoCollection
	{
		if ($this->handler instanceof Sale\Services\PaySystem\Restrictions\RestrictableServiceHandler)
		{
			return $this->handler->getRestrictionList();
		}

		return (new RestrictionInfoCollection());
	}

	public function getServiceId(): int
	{
		return (int)($this->getField('ID') ?? 0);
	}

	private function markPayment(Payment $payment, ServiceResult $serviceResult): void
	{
		(new PaymentMarker($this, $payment))
			->mark($serviceResult)
			->save()
		;
	}
}
