<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class ShippingItemSize
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class ShippingItemSize implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var float */
	private $length;

	/** @var float */
	private $width;

	/** @var float */
	private $height;

	/**
	 * @return float Length in meters
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * @param float $length Length in meters
	 * @return ShippingItemSize
	 */
	public function setLength(float $length): ShippingItemSize
	{
		$this->length = $length;

		return $this;
	}

	/**
	 * @return float Width in meters
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param float $width Width in meters
	 * @return ShippingItemSize
	 */
	public function setWidth(float $width): ShippingItemSize
	{
		$this->width = $width;

		return $this;
	}

	/**
	 * @return float Height in meters
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param float $height Height in meters
	 * @return ShippingItemSize
	 */
	public function setHeight(float $height): ShippingItemSize
	{
		$this->height = $height;

		return $this;
	}
}
