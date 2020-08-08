<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class ShippingItem
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class ShippingItem implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var string */
	private $title;

	/** @var ShippingItemSize */
	private $size;

	/** @var string */
	private $costValue;

	/** @var string */
	private $costCurrency;

	/** @var int */
	private $weight;

	/** @var int */
	private $quantity;

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return ShippingItem
	 */
	public function setTitle(string $title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return ShippingItemSize
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param ShippingItemSize $size
	 * @return ShippingItem
	 */
	public function setSize(ShippingItemSize $size)
	{
		$this->size = $size;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCostValue()
	{
		return $this->costValue;
	}

	/**
	 * @param string $costValue
	 * @return ShippingItem
	 */
	public function setCostValue(string $costValue)
	{
		$this->costValue = $costValue;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCostCurrency()
	{
		return $this->costCurrency;
	}

	/**
	 * @param string $costCurrency
	 * @return ShippingItem
	 */
	public function setCostCurrency(string $costCurrency)
	{
		$this->costCurrency = $costCurrency;

		return $this;
	}

	/**
	 * @return int Weight in kg
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * @param int $weight Weight in kg
	 * @return ShippingItem
	 */
	public function setWeight(int $weight)
	{
		$this->weight = $weight;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param int $quantity
	 * @return ShippingItem
	 */
	public function setQuantity(int $quantity)
	{
		$this->quantity = $quantity;

		return $this;
	}
}
