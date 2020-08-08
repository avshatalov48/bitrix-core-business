<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Result;

/**
 * Class CancellationResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class CancellationResult extends Result
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
	 * @return CancellationResult
	 */
	public function setIsPaid(bool $isPaid): CancellationResult
	{
		$this->isPaid = $isPaid;

		return $this;
	}
}
