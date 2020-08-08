<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Result;
use Sale\Handlers\Delivery\Taxi\Status\StatusContract;

/**
 * Class CreateClaimResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class CreateClaimResult extends Result
{
	/** @var StatusContract */
	private $status;

	/** @var string */
	private $requestId;

	/**
	 * @return StatusContract
	 */
	public function getStatus(): ?StatusContract
	{
		return $this->status;
	}

	/**
	 * @param StatusContract $status
	 * @return CreateClaimResult
	 */
	public function setStatus(StatusContract $status): CreateClaimResult
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestId(): ?string
	{
		return $this->requestId;
	}

	/**
	 * @param string $requestId
	 * @return CreateClaimResult
	 */
	public function setRequestId(string $requestId): CreateClaimResult
	{
		$this->requestId = $requestId;

		return $this;
	}
}
