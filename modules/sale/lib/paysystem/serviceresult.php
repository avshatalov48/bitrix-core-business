<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Entity\Result;
use Bitrix\Main;
use Bitrix\Sale\Internals;

/**
 * Class ServiceResult
 * @package Bitrix\Sale\PaySystem
 */
class ServiceResult extends Result
{
	const MONEY_COMING = 'money_coming';
	const MONEY_LEAVING = 'money_leaving';

	private $psData = array();
	private $resultApplied = true;
	private $operationType = null;
	private $template = '';
	private $paymentUrl = '';

	private string $qr = '';

	/**
	 * @param array $psData
	 */
	public function setPsData($psData)
	{
		$this->psData = $psData;
	}

	/**
	 * @return array
	 */
	public function getPsData()
	{
		return $this->psData;

	}

	/**
	 * @return bool
	 */
	public function isResultApplied()
	{
		return $this->resultApplied;
	}

	/**
	 * @param $operationType
	 */
	public function setOperationType($operationType)
	{
		$this->operationType = $operationType;
	}

	/**
	 * @return null|string
	 */
	public function getOperationType()
	{
		return $this->operationType;
	}

	/**
	 * @param $resultApplied
	 */
	public function setResultApplied($resultApplied)
	{
		$this->resultApplied = $resultApplied;
	}

	/**
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @return string
	 */
	public function getPaymentUrl(): string
	{
		return $this->paymentUrl;
	}

	/**
	 * @param $paymentUrl
	 */
	public function setPaymentUrl($paymentUrl): void
	{
		$this->paymentUrl = $paymentUrl;
	}

	public function getQr(): string
	{
		return $this->qr;
	}

	public function setQr(string $qr): void
	{
		$this->qr = $qr;
	}

	/**
	 * @return Error[]
	 */
	public function getBuyerErrors(): array
	{
		$errors = [];

		/** @var Main\Error $error */
		foreach ($this->getBuyerErrorIterator() as $error)
		{
			$errors[] = $error;
		}

		return $errors;
	}

	/**
	 * @return array
	 */
	public function getBuyerErrorMessages(): array
	{
		$messages = [];

		/** @var Main\Error $error */
		foreach ($this->getBuyerErrorIterator() as $error)
		{
			$messages[] = $error->getMessage();
		}

		return $messages;
	}

	/**
	 * @return Internals\CollectionFilterIterator
	 */
	public function getBuyerErrorIterator(): Internals\CollectionFilterIterator
	{
		$callback = function (Main\Error $error)
		{
			return $error instanceof Error
				&& $error->isVisibleForBuyer();
		};

		return new Internals\CollectionFilterIterator(new \ArrayIterator($this->getErrors()), $callback);
	}
}