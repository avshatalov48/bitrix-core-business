<?php

namespace Bitrix\Sale\Delivery\Services\Taxi;

use Bitrix\Main\Result;

/**
 * Class CancellationRequestResult
 * @package Bitrix\Sale\Delivery\Services\Taxi
 * @internal
 */
final class CancellationRequestResult extends Result
{
	/** @var bool */
	private $isPaid = false;

	/**
	 * @return bool
	 */
	public function isPaid(): bool
	{
		return $this->isPaid;
	}

	/**
	 * @param bool $isPaid
	 * @return CancellationRequestResult
	 */
	public function setIsPaid(bool $isPaid): CancellationRequestResult
	{
		$this->isPaid = $isPaid;

		return $this;
	}
}
