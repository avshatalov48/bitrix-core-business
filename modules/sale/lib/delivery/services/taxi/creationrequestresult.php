<?php

namespace Bitrix\Sale\Delivery\Services\Taxi;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Result;

/**
 * Class CreationRequestResult
 * @package Bitrix\Sale\Delivery\Services\Taxi
 * @internal
 */
final class CreationRequestResult extends Result
{
	/** @var string */
	private $status;

	/** @var int */
	private $requestId;

	/**
	 * @param string $status
	 * @return CreationRequestResult
	 * @throws ArgumentException
	 */
	public function setStatus(string $status): CreationRequestResult
	{
		if (!StatusDictionary::isStatusValid($status))
		{
			throw new ArgumentException(sprintf('Invalid status - %s', $status));
		}

		$this->status = $status;

		return $this;
	}

	/**
	 * @param int $requestId
	 * @return CreationRequestResult
	 */
	public function setRequestId(int $requestId): CreationRequestResult
	{
		$this->requestId = $requestId;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->status;
	}

	/**
	 * @return int|null
	 */
	public function getRequestId(): ?int
	{
		return $this->requestId;
	}
}
