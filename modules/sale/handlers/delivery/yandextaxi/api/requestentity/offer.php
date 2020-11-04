<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class Offer
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class Offer extends RequestEntity
{
	/** @var string */
	protected $offerId;

	/** @var string */
	protected $price;

	/**
	 * @return string
	 */
	public function getOfferId()
	{
		return $this->offerId;
	}

	/**
	 * @param string $offerId
	 * @return Offer
	 */
	public function setOfferId(string $offerId): Offer
	{
		$this->offerId = $offerId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param string $price
	 * @return Offer
	 */
	public function setPrice(string $price): Offer
	{
		$this->price = $price;

		return $this;
	}
}
