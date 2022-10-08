<?php

namespace Bitrix\Sale\Tax;

use Bitrix\Sale\PriceMaths;

/**
 * Calculating vat value.
 *
 * Example:
 * ```php
 * $calculator = new VatCalculator($rate, $isVatInPrice, $quantity);
 * $vatAmout = $calculator->calc($price, $isVatInPrice, $quantity);
 * ```
 */
class VatCalculator
{
	private float $rate;

	/**
	 * @param float $rate number from 0 to 1 (20% its 0.2).
	 * @param bool $isVatInPrice
	 */
	public function __construct(float $rate)
	{
		$this->rate = $rate;
	}

	/**
	 * Calculate vat value.
	 *
	 * @param float $price
	 * @param bool $withRound
	 *
	 * @return float
	 */
	public function calc(float $price, bool $isVatInPrice, bool $withRound = true): float
	{
		if ($this->rate === 0.0)
		{
			return 0.0;
		}
		elseif ($isVatInPrice)
		{
			$vat = $price * $this->rate / ($this->rate + 1);
		}
		else
		{
			$vat = $price * $this->rate;
		}

		return $withRound ? PriceMaths::roundPrecision($vat) : $vat;
	}

	/**
	 * Allocate price without vat from price with vat.
	 *
	 * @param float $price
	 * @param bool $withRound
	 *
	 * @return float price without vat.
	 */
	public function allocate(float $price, bool $withRound = true): float
	{
		if ($this->rate === 0.0)
		{
			return $price;
		}

		$rate = $this->rate * 100;
		$vat = $price * $rate / ($rate + 100);
		$priceWithoutVat = $price - $vat;

		return $withRound ? PriceMaths::roundPrecision($priceWithoutVat) : $priceWithoutVat;
	}

	/**
	 * Accrue vat value to price.
	 *
	 * @param float $price
	 * @param bool $withRound
	 *
	 * @return float price with vat.
	 */
	public function accrue(float $price, bool $withRound = true): float
	{
		$priceWithVat = $price * (1 + $this->rate);

		return $withRound ? PriceMaths::roundPrecision($priceWithVat) : $priceWithVat;
	}
}
