<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class ShippingItemSize
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class ShippingItemSize extends RequestEntity
{
	/** @var float */
	protected $length;

	/** @var float */
	protected $width;

	/** @var float */
	protected $height;

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
