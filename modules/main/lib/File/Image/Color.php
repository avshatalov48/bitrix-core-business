<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

class Color
{
	protected
		$red,
		$green,
		$blue,
		$alpha;

	/**
	 * Color constructor.
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @param float $alpha
	 */
	public function __construct($red = 0, $green = 0, $blue = 0, $alpha = 1.0)
	{
		$this->setRed($red)
			->setGreen($green)
			->setBlue($blue)
			->setAlpha($alpha);
	}

	/**
	 * Creates the color from a string like #ffaabb or ffaabb.
	 * @param string $color
	 * @return Color
	 */
	public static function createFromHex($color)
	{
		$color = preg_replace("/[^a-f0-9]/is", "", $color);
		if(strlen($color) != 6)
		{
			$color = "FF0000";
		}

		return new static(
			hexdec(substr($color, 0, 2)),
			hexdec(substr($color, 2, 2)),
			hexdec(substr($color, 4, 2))
		);
	}

	/**
	 * Returns hex representation e.g. #aabbcc.
	 * @return string
	 */
	public function toHex()
	{
		return sprintf("#%02x%02x%02x", $this->getRed(), $this->getGreen(), $this->getBlue());
	}

	/**
	 * Returns rgba representation, e.g. rgba(255, 0, 0, 0.5)
	 * @return string
	 */
	public function toRgba()
	{
		return sprintf("rgba(%d, %d, %d, %0.4f)", $this->getRed(), $this->getGreen(), $this->getBlue(), $this->getAlpha());
	}

	/**
	 * @return int
	 */
	public function getRed()
	{
		return $this->red;
	}

	/**
	 * @param int $red
	 * @return Color
	 */
	public function setRed($red)
	{
		if($red < 0 || $red > 255)
		{
			$red = 0;
		}
		$this->red = (int)$red;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getGreen()
	{
		return $this->green;
	}

	/**
	 * @param int $green
	 * @return Color
	 */
	public function setGreen($green)
	{
		if($green < 0 || $green > 255)
		{
			$green = 0;
		}
		$this->green = (int)$green;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getBlue()
	{
		return $this->blue;
	}

	/**
	 * @param int $blue
	 * @return Color
	 */
	public function setBlue($blue)
	{
		if($blue < 0 || $blue > 255)
		{
			$blue = 0;
		}
		$this->blue = (int)$blue;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getAlpha()
	{
		return $this->alpha;
	}

	/**
	 * @param float $alpha
	 * @return Color
	 */
	public function setAlpha($alpha)
	{
		$this->alpha = (float)$alpha;
		return $this;
	}
}
