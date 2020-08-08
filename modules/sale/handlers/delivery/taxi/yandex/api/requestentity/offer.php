<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

class Offer implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var string */
	private $offerId;

	/** @var string */
	private $price;

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
