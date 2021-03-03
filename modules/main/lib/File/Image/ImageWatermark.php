<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

class ImageWatermark extends Watermark
{
	const SIZE_REAL = "real";

	const
		MODE_REPEAT = 'repeat',
		MODE_EXACT = 'exact',
		MODE_RESIZE = 'resize';

	protected $imageFile;
	protected $mode = self::MODE_RESIZE;
	protected $alpha = 1.0;

	/**
	 * ImageWatermark constructor.
	 * @param string|null $imageFile Path to a watermark image.
	 */
	public function __construct($imageFile = null)
	{
		parent::__construct();

		$this->imageFile = $imageFile;
	}

	public function getRatio()
	{
		if($this->ratio === null)
		{
			if($this->size == static::SIZE_BIG)
			{
				return 0.75;
			}
			if($this->size == static::SIZE_SMALL)
			{
				return 0.20;
			}
			if($this->size == static::SIZE_REAL)
			{
				return 1.00;
			}
			//static::SIZE_MEDIUM
			return 0.50;
		}
		return $this->ratio;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @param string $mode
	 * @return ImageWatermark
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
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
	 * @param float $alpha 1.0 is opaque, 0.0 is transparent.
	 * @return ImageWatermark
	 */
	public function setAlpha($alpha)
	{
		$this->alpha = (float)$alpha;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getImageFile()
	{
		return $this->imageFile;
	}

	/**
	 * @param string $imageFile
	 * @return ImageWatermark
	 */
	public function setImageFile($imageFile)
	{
		$this->imageFile = $imageFile;
		return $this;
	}
}
