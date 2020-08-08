<?php

namespace Sale\Handlers\Delivery\Taxi;

use Bitrix\Main\Result;
use Sale\Handlers\Delivery\Taxi\Status\StatusContract;

/**
 * Class SendTaxiRequestResult
 * @package Sale\Handlers\Delivery\Taxi
 */
class SendTaxiRequestResult extends Result
{
	/** @var StatusContract */
	private $status;

	/** @var int */
	private $requestId;

	/**
	 * @param StatusContract $status
	 * @return SendTaxiRequestResult
	 */
	public function setStatus(StatusContract $status): SendTaxiRequestResult
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @param int $requestId
	 * @return SendTaxiRequestResult
	 */
	public function setRequestId(int $requestId): SendTaxiRequestResult
	{
		$this->requestId = $requestId;

		return $this;
	}

	/**
	 * @return StatusContract
	 */
	public function getStatus(): ?StatusContract
	{
		return $this->status;
	}

	/**
	 * @return int
	 */
	public function getRequestId(): ?int
	{
		return $this->requestId;
	}
}
