<?php

namespace Bitrix\Sale\Delivery\Services\Taxi;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Result;

/**
 * Class CreationExternalRequestResult
 * @package Bitrix\Sale\Delivery\Services\Taxi
 * @internal
 */
final class CreationExternalRequestResult extends Result
{
	/** @var string */
	private $status;

	/** @var string */
	private $externalRequestId;

	/**
	 * @return string
	 */
	public function getStatus(): ?string
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 * @return CreationExternalRequestResult
	 * @throws ArgumentException
	 */
	public function setStatus(string $status): CreationExternalRequestResult
	{
		if (!StatusDictionary::isStatusValid($status))
		{
			throw new ArgumentException(sprintf('Invalid status - %s', $status));
		}

		$this->status = $status;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getExternalRequestId(): ?string
	{
		return $this->externalRequestId;
	}

	/**
	 * @param string $externalRequestId
	 * @return $this
	 */
	public function setExternalRequestId(string $externalRequestId): CreationExternalRequestResult
	{
		$this->externalRequestId = $externalRequestId;

		return $this;
	}
}
