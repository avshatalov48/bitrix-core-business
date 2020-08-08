<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult;

use Bitrix\Main;

/**
 * Class CheckPrice
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult
 */
class CheckPriceResult extends Main\Result
{
	/** @var float */
	private $price;

	/** @var string */
	private $currency;

	/** @var int|null */
	private $eta;

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param float $price
	 * @return CheckPriceResult
	 */
	public function setPrice(float $price): CheckPriceResult
	{
		$this->price = $price;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 * @return CheckPriceResult
	 */
	public function setCurrency(string $currency): CheckPriceResult
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getEta(): ?int
	{
		return $this->eta;
	}

	/**
	 * @param int|null $eta
	 * @return CheckPriceResult
	 */
	public function setEta(?int $eta): CheckPriceResult
	{
		$this->eta = $eta;

		return $this;
	}
}
