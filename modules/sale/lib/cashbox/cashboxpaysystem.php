<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxPaySystem
 * @package Bitrix\Sale\Cashbox
 */
abstract class CashboxPaySystem extends Cashbox implements IPrintImmediately, ICheckable
{
	public const CACHE_ID = '';
	private const TTL = 31536000;

	abstract protected function getPrintUrl(): string;

	abstract protected function getCheckUrl(): string;

	abstract protected function send(string $url, Sale\Payment $payment, array $fields): Sale\Result;

	abstract protected function processPrintResult(Sale\Result $result): Sale\Result;

	abstract protected function getDataForCheck(Sale\Payment $payment): array;

	abstract protected function processCheckResult(Sale\Result $result): Sale\Result;

	abstract protected function onAfterProcessCheck(Sale\Result $result, Sale\Payment $payment): Sale\Result;

	abstract public static function getPaySystemCodeForKkm(): string;

	/**
	 * @param Sale\Payment $payment
	 * @param string $code
	 * @return mixed|null
	 */
	protected function getPaySystemSetting(Sale\Payment $payment, string $code)
	{
		$params = $payment->getPaySystem()->getParamsBusValue($payment);
		return $params[$code] ?? null;
	}

	/**
	 * @param Check $check
	 * @return Sale\Result
	 * @throws Main\SystemException
	 */
	protected function checkParams(Check $check): Sale\Result
	{
		$result = new Sale\Result();

		$payment = CheckManager::getPaymentByCheck($check);
		if ($payment && $service = $payment->getPaySystem())
		{
			if (!$service->isSupportPrintCheck())
			{
				$result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage(
							'SALE_CASHBOX_PAYSYSTEM_PAYSYSTEM_NOT_SUPPORT_PRINT_CHECK',
							[
								'#PAY_SYSTEM_NAME#' => $service->getField('NAME')
							]
						)
					)
				);
			}

			if (!$service->canPrintCheckSelf($payment))
			{
				$result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage(
							'SALE_CASHBOX_PAYSYSTEM_PAYSYSTEM_CANT_PRINT_CHECK_SELF',
							[
								'#PAY_SYSTEM_NAME#' => $service->getField('NAME')
							]
						)
					)
				);
			}
		}
		else
		{
			$result->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage('SALE_CASHBOX_PAYSYSTEM_PAYMENT_NOT_FOUND')
				)
			);
		}

		return $result;
	}

	/**
	 * @param Check $check
	 * @return Sale\Result
	 * @throws Main\SystemException
	 */
	public function printImmediately(Check $check): Sale\Result
	{
		$result = new Sale\Result();

		$checkParamsResult = $this->checkParams($check);
		if (!$checkParamsResult->isSuccess())
		{
			$result->addErrors($checkParamsResult->getErrors());
			return $result;
		}

		if ($this->needPrintCheck($check))
		{
			$payment = CheckManager::getPaymentByCheck($check);
			if (!$payment)
			{
				$result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage('SALE_CASHBOX_PAYSYSTEM_PAYMENT_NOT_FOUND')
					)
				);
				return $result;
			}

			$url = $this->getPrintUrl();
			$fields = $this->buildCheckQuery($check);

			$sendResult = $this->send($url, $payment, $fields);
			if ($sendResult->isSuccess())
			{
				$processPrintResult = $this->processPrintResult($sendResult);
				if ($processPrintResult->isSuccess())
				{
					$result->setData($processPrintResult->getData());
				}
				else
				{
					$result->addErrors($processPrintResult->getErrors());
				}
			}
			else
			{
				$result->addErrors($sendResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param Check $check
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function needPrintCheck(Check $check): bool
	{
		$isShipmentEntity = (bool)array_filter($check->getEntities(), static function ($entity) {
			return $entity instanceof Sale\Shipment;
		});

		return $check::getType() === SellCheck::getType() && $isShipmentEntity;
	}

	public function buildZReportQuery($id)
	{
		return [];
	}

	/**
	 * @param Check $check
	 * @return Sale\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @throws Main\SystemException
	 */
	public function check(Check $check): Sale\Result
	{
		$result = new Sale\Result();

		$checkParamsResult = $this->checkParams($check);
		if (!$checkParamsResult->isSuccess())
		{
			$result->addErrors($checkParamsResult->getErrors());
			return $result;
		}

		$payment = CheckManager::getPaymentByCheck($check);
		if (!$payment)
		{
			$result->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage('SALE_CASHBOX_PAYSYSTEM_PAYMENT_NOT_FOUND')
				)
			);
			return $result;
		}

		$url = $this->getCheckUrl();
		$fields = $this->getDataForCheck($payment);

		$sendResult = $this->send($url, $payment, $fields);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$processCheckResult = $this->processCheckResult($sendResult);
		if ($processCheckResult->isSuccess())
		{
			$onAfterProcessCheckResult = $this->onAfterProcessCheck($processCheckResult, $payment);
			if (!$onAfterProcessCheckResult->isSuccess())
			{
				$result->addErrors($onAfterProcessCheckResult->getErrors());
			}
		}
		else
		{
			$result->addErrors($processCheckResult->getErrors());
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.05;
	}
}
