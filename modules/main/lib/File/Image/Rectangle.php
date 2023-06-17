<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

use Bitrix\Main\File;

class Rectangle
{
	protected
		$width,
		$height,
		$x,
		$y;

	/**
	 * Rectangle constructor.
	 * @param int $width
	 * @param int $height
	 * @param int $x
	 * @param int $y
	 */
	public function __construct($width = 0, $height = 0, $x = 0, $y = 0)
	{
		$this->setWidth($width)
			->setHeight($height)
			->setX($x)
			->setY($y);
	}

	/**
	 * @return int
	 */
	public function getX()
	{
		return $this->x;
	}

	/**
	 * @param int $x
	 * @return Rectangle
	 */
	public function setX($x)
	{
		$this->x = (int)$x;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getY()
	{
		return $this->y;
	}

	/**
	 * @param int $y
	 * @return Rectangle
	 */
	public function setY($y)
	{
		$this->y = (int)$y;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param int $width
	 * @return Rectangle
	 */
	public function setWidth($width)
	{
		$this->width = (int)$width;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param int $height
	 * @return Rectangle
	 */
	public function setHeight($height)
	{
		$this->height = (int)$height;
		return $this;
	}

	/**
	 * Adjusts the rectangle to fit into the destination rectangle. Destination rectangle can also change.
	 * @param Rectangle $destination
	 * @param int $mode One of Image::RESIZE_* constants.
	 * @return bool True if resized.
	 */
	public function resize(Rectangle $destination, $mode)
	{
		if($this->width == 0 || $this->height == 0)
		{
			return false;
		}

		if($destination->width == 0 || $destination->height == 0)
		{
			return false;
		}

		if($this->width == $destination->width && $this->height == $destination->height)
		{
			return false;
		}

		$result = false;

		switch($mode)
		{
			case File\Image::RESIZE_EXACT:
				//in this mode we change the source rectangle, the destination one is intact
				if(($this->width / $this->height) < ($destination->width / $destination->height))
				{
					$ratio = $destination->width / $this->width;
				}
				else
				{
					$ratio = $destination->height / $this->height;
				}

				$this->x = max(0, round(($this->width / 2) - (($destination->width / 2) / $ratio)));
				$this->y = max(0, round(($this->height / 2) - (($destination->height / 2) / $ratio)));

				$this->width = round($destination->width / $ratio);
				$this->height = round($destination->height / $ratio);

				$result = true;
				break;

			case File\Image::RESIZE_PROPORTIONAL_ALT:
			case File\Image::RESIZE_PROPORTIONAL:
				//in this mode we change the destination rectangle, the source one is intact
				if($mode == File\Image::RESIZE_PROPORTIONAL_ALT)
				{
					$width = max($this->width, $this->height);
					$height = min($this->width, $this->height);
				}
				else
				{
					$width = $this->width;
					$height = $this->height;
				}
				$ratioWidth = $destination->width / $width;
				$ratioHeight = $destination->height / $height;

				$ratio = min($ratioWidth, $ratioHeight);

				//todo: enlarge?
				if($ratio > 0 && $ratio < 1)
				{
					// scale down
					$result = true;
				}
				else
				{
					// destination is larger than source
					$ratio = 1;
				}
				$destination->width = max(1, round($ratio * $this->width));
				$destination->height = max(1, round($ratio * $this->height));

				break;
		}

		return $result;
	}

	public function scale($ratio)
	{
		$this->setWidth($this->getWidth() * $ratio)
			->setHeight($this->getHeight() * $ratio)
			->setX($this->getX() * $ratio)
			->setY($this->getY() * $ratio);
	}
}
