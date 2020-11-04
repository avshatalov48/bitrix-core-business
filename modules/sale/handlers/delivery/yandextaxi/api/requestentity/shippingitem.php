<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class ShippingItem
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class ShippingItem extends RequestEntity
{
	/** @var string */
	protected $title;

	/** @var ShippingItemSize */
	protected $size;

	/** @var string */
	protected $costValue;

	/** @var string */
	protected $costCurrency;

	/** @var int */
	protected $weight;

	/** @var int */
	protected $quantity;

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
