<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Result;

/**
 * Class CalculateRateResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class CalculateRateResult extends Result
{
	/** @var float */
	private $rate;

	/**
	 * @return float
	 */
	public function getRate()
	{
		return $this->rate;
	}

	/**
	 * @param float $rate
	 * @return CalculateRateResult
	 */
	public function setRate(float $rate): CalculateRateResult
	{
		$this->rate = $rate;

		return $this;
	}
}
