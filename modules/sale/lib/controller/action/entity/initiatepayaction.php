<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main\Error;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem\Internals\InitiatePayException;

final class InitiatePayAction extends BaseAction
{
	/**
	 * Input arguments
	 * @var array
	 */
	private $params = [];

	/**
	 * @var Sale\PaySystem\ServiceResult
	 */
	private $serviceResult;

	/**
	 * @var Sale\PaySystem\Service
	 */
	private $service;

	/**
	 * @var Sale\Registry
	 */
	private $registry;

	/**
	 * @var Sale\Order
	 */
	private $order;

	/**
	 * @var Sale\Payment
	 */
	private $payment;

	/**
	 * @param array $fields
	 *   paymentId - required
	 *   paySystemId - required
	 *   accessCode - required
	 *   returnUrl - optional
	 *   template - optional
	 * @return array|null
	 * @example BX.ajax.runAction('sale.entity.initiatepay', { data: { fields: {...} } });
	 */
	public function run(array $fields): ?array
	{
		$this->params = $fields;
		$this->serviceResult = new Sale\PaySystem\ServiceResult();

		try
		{
			$this->validateInputParams();
			$this->initPaymentService();
			$this->initRegistry();
			$this->initPaymentEntities();
			$this->checkPaymentAllowed();
			$this->initiatePay();
		}
		catch (InitiatePayException $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
		}

		return $this->formatResponse();
	}

	/**
	 * @throws InitiatePayException
	 */
	private function validateInputParams(): void
	{
		$paymentId = (int)$this->params['PAYMENT_ID'];
		$paySystemId = (int)$this->params['PAY_SYSTEM_ID'];

		if ($paymentId <= 0)
		{
			throw new InitiatePayException(
				'paymentId must be specified',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_PAYMENT_ID_NOT_FOUND
			);
		}

		if ($paySystemId <= 0)
		{
			throw new InitiatePayException(
				'paySystemId must be specified',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_PAY_SYSTEM_ID_NOT_FOUND
			);
		}

		if (empty($this->params['ACCESS_CODE']))
		{
			throw new InitiatePayException(
				'accessCode must be specified',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_ACCESS_CODE_NOT_FOUND
			);
		}
	}

	/**
	 * @throws InitiatePayException
	 */
	private function initPaymentService(): void
	{
		$this->service = Sale\PaySystem\Manager::getObjectById((int)$this->params['PAY_SYSTEM_ID']);

		if (!$this->service)
		{
			throw new InitiatePayException(
				'payment service not found',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_PAYMENT_SERVICE_NOT_FOUND
			);
		}

		if (!empty($this->params['RETURN_URL']))
		{
			$this->service->getContext()->setUrl($this->params['RETURN_URL']);
		}
	}

	/**
	 * @throws InitiatePayException
	 */
	private function initRegistry(): void
	{
		$this->registry = Sale\Registry::getInstance($this->service->getField('ENTITY_REGISTRY_TYPE'));
		if (!$this->registry)
		{
			throw new InitiatePayException(
				'internal error',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_COMMON_ERROR
			);
		}
	}

	/**
	 * @throws InitiatePayException
	 */
	private function initPaymentEntities(): void
	{
		$paymentRow = Sale\Payment::getList([
			'filter' => ['ID' => (int)$this->params['PAYMENT_ID']],
			'select' => ['ORDER_ID', 'ID'],
			'limit' => 1
		]);
		if (!$paymentData = $paymentRow->fetch())
		{
			throw new InitiatePayException(
				'payment not found',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_PAYMENT_NOT_FOUND
			);
		}

		$paymentId = (int)$paymentData['ID'];
		$orderId = (int)$paymentData['ORDER_ID'];

		if (!$this->order = $this->findOrder($orderId))
		{
			throw new InitiatePayException(
				'order not found',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_ORDER_NOT_FOUND
			);
		}

		if (!$this->payment = $this->order->getPaymentCollection()->getItemById($paymentId))
		{
			throw new InitiatePayException(
				'payment not found',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_PAYMENT_NOT_FOUND
			);
		}
	}

	private function findOrder(int $orderId): ?Sale\Order
	{
		if ($orderId <= 0)
		{
			return null;
		}

		$orderClassName = $this->registry->getOrderClassName();
		/** @var Sale\Order $order */
		$order = $orderClassName::load($orderId);
		return $order;
	}

	/**
	 * @throws InitiatePayException
	 */
	private function checkPaymentAllowed(): void
	{
		if (!Sale\OrderStatus::isAllowPay($this->order->getField('STATUS_ID')))
		{
			throw new InitiatePayException(
				'order in unpayable status',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_ORDER_STATUS_ERROR
			);
		}

		if ($this->order->getHash() !== $this->params['ACCESS_CODE'])
		{
			throw new InitiatePayException(
				'access error',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_ORDER_ACCESS_ERROR
			);
		}
	}

	/**
	 * @throws InitiatePayException
	 */
	private function initiatePay(): void
	{
		$this->updatePaymentMetadata();

		$request = null;
		if (!empty($this->params['template']))
		{
			$request = \Bitrix\Main\Context::getCurrent()->getRequest();
			$request->set(['template' => $this->params['template']]);
		}

		$this->serviceResult = $this->service->initiatePay(
			$this->payment,
			$request,
			Sale\PaySystem\BaseServiceHandler::STRING
		);

		if (!$this->serviceResult->isSuccess())
		{
			$this->addError(new Error(
				'payment service error',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_PAYMENT_SERVICE_INTERNAL_ERROR
			));

			$this->addErrors($this->serviceResult->getErrors());

			foreach ($this->serviceResult->getBuyerErrors() as $buyerError)
			{
				$customData = $buyerError->getCustomData();
				$errorContext = is_array($customData) ? $customData : [];
				$errorContext['is_buyer'] = true;

				$this->addError(new Error(
					$buyerError->getMessage(),
					$buyerError->getCode(),
					$errorContext
				));
			}
		}
	}

	private function updatePaymentMetadata(): void
	{
		Sale\DiscountCouponsManagerBase::freezeCouponStorage();

		$result = $this->payment->setFields([
			'PAY_SYSTEM_ID' => $this->service->getField('ID'),
			'PAY_SYSTEM_NAME' => $this->service->getField('NAME')
		]);

		$result = $result->isSuccess() ? $this->order->save() : $result;

		Sale\DiscountCouponsManagerBase::unFreezeCouponStorage();

		if (!$result->isSuccess())
		{
			throw new InitiatePayException(
				'cannot update payment',
				Sale\Controller\ErrorEnumeration::INITIATE_PAY_ACTION_UNABLE_TO_UPDATE_PAYMENT
			);
		}
	}

	public function getPayment(): ?Sale\Payment
	{
		return $this->payment;
	}

	private function formatResponse(): ?array
	{
		if ($this->errorCollection->isEmpty())
		{
			return [
				'html' => $this->serviceResult->getTemplate(),
				'url' => $this->serviceResult->getPaymentUrl(),
			];
		}

		return null;
	}
}
