<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult;

use Bitrix\Main;

/**
 * Class PriceResult
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
final class PriceResult extends Main\Result
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
	 * @return PriceResult
	 */
	public function setPrice(float $price): PriceResult
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
	 * @return PriceResult
	 */
	public function setCurrency(string $currency): PriceResult
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
	 * @return PriceResult
	 */
	public function setEta(?int $eta): PriceResult
	{
		$this->eta = $eta;

		return $this;
	}
}
