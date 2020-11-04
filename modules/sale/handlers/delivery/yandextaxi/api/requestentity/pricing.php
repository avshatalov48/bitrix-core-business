<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class Pricing
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class Pricing extends RequestEntity
{
	/** @var string */
	protected $currency;

	/** @var string */
	protected $finalPrice;

	/** @var Offer */
	protected $offer;

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 * @return Pricing
	 */
	public function setCurrency(string $currency): Pricing
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFinalPrice()
	{
		return $this->finalPrice;
	}

	/**
	 * @param string $finalPrice
	 * @return Pricing
	 */
	public function setFinalPrice(string $finalPrice): Pricing
	{
		$this->finalPrice = $finalPrice;

		return $this;
	}

	/**
	 * @return Offer
	 */
	public function getOffer()
	{
		return $this->offer;
	}

	/**
	 * @param Offer $offer
	 * @return Pricing
	 */
	public function setOffer(Offer $offer): Pricing
	{
		$this->offer = $offer;

		return $this;
	}
}
