<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File;

use Bitrix\Main\DI;

class Image
{
	const
		FORMAT_PNG = \IMAGETYPE_PNG,
		FORMAT_JPEG = \IMAGETYPE_JPEG,
		FORMAT_GIF = \IMAGETYPE_GIF,
		FORMAT_BMP = \IMAGETYPE_BMP,
		FORMAT_WEBP = \IMAGETYPE_WEBP;

	const
		RESIZE_PROPORTIONAL_ALT = 0,
		RESIZE_PROPORTIONAL = 1,
		RESIZE_EXACT = 2;

	protected $file;

	/** @var Image\Engine */
	protected $engine;

	/**
	 * Image constructor.
	 * @param string|null $file Physical file name.
	 */
	public function __construct($file = null)
	{
		$this->file = $file;

		$serviceLocator = DI\ServiceLocator::getInstance();
		if($serviceLocator->has("main.imageEngine"))
		{
			$this->setEngine($serviceLocator->get("main.imageEngine"));
		}
		else
		{
			$this->setEngine(new Image\Gd());
		}
	}

	/**
	 * Sets image processing engine.
	 * @param Image\Engine $engine
	 */
	public function setEngine(Image\Engine $engine)
	{
		$this->engine = $engine;

		if($this->file !== null)
		{
			$this->engine->setFile($this->file);
		}
	}

	/**
	 * Returns EXIF data from the image.
	 * @return array
	 */
	public function getExifData()
	{
		return $this->engine->getExifData();
	}

	/**
	 * Returns the image file info, including the image size.
	 * @param bool $flashEnabled
	 * @return Image\Info|null
	 */
	public function getInfo($flashEnabled = false)
	{
		return $this->engine->getInfo($flashEnabled);
	}

	/**
	 * Reads the image data from the file.
	 * @return bool
	 */
	public function load()
	{
		return $this->engine->load();
	}

	/**
	 * Rotates the image clockwise.
	 * @param float $angle
	 * @param Image\Color|null $bgColor
	 * @return bool
	 */
	public function rotate($angle, Image\Color $bgColor = null)
	{
		if($bgColor === null)
		{
			$bgColor = new Image\Color();
		}
		return $this->engine->rotate($angle, $bgColor);
	}

	/**
	 * Flips the image vertically.
	 * @return bool
	 */
	public function flipVertical()
	{
		return $this->engine->flipVertical();
	}

	/**
	 * Flips the image horizontally.
	 * @return bool
	 */
	public function flipHorizontal()
	{
		return $this->engine->flipHorizontal();
	}

	/**
	 * Fixes wierd orientation (from EXIF).
	 * @param int $orientation
	 * @return bool True if it was fixed.
	 */
	public function autoRotate($orientation)
	{
		if($orientation > 1)
		{
			if($orientation == 7 || $orientation == 8)
			{
				$this->rotate(270);
			}
			elseif($orientation == 3 || $orientation == 4)
			{
				$this->rotate(180);
			}
			elseif($orientation == 5 || $orientation == 6)
			{
				$this->rotate(90);
			}

			if ($orientation == 2 || $orientation == 7 || $orientation == 4 || $orientation == 5)
			{
				$this->flipHorizontal();
			}

			$this->setOrientation(0);

			return true;
		}
		return false;
	}

	/**
	 * Sets the image orientation.
	 * @param $orientation
	 * @return bool
	 */
	public function setOrientation($orientation)
	{
		return $this->engine->setOrientation($orientation);
	}

	/**
	 * Resizes the image.
	 * @param Image\Rectangle $source
	 * @param Image\Rectangle $destination
	 * @return bool
	 */
	public function resize(Image\Rectangle $source, Image\Rectangle $destination)
	{
		return $this->engine->resize($source, $destination);
	}

	/**
	 * Applies a mask to the image (convolution).
	 * @param Image\Mask $mask
	 * @return bool
	 */
	public function filter(Image\Mask $mask)
	{
		return $this->engine->filter($mask);
	}

	/**
	 * Draws a text or image watermark on the image (depending on type).
	 * @param Image\Watermark $watermark
	 * @return bool
	 */
	public function drawWatermark(Image\Watermark $watermark)
	{
		if($watermark instanceof Image\TextWatermark)
		{
			return $this->engine->drawTextWatermark($watermark);
		}
		if($watermark instanceof Image\ImageWatermark)
		{
			return $this->engine->drawImageWatermark($watermark);
		}
		return false;
	}

	/**
	 * Saves the image to the current file.
	 * @param int $quality Percents, normalized to 95 on incorrect values.
	 * @return bool
	 */
	public function save($quality = 95)
	{
		if($quality <= 0 || $quality > 100)
		{
			$quality = 95;
		}

		if($this->engine->save($this->file, $quality))
		{
			$this->setFileAttributes($this->file);

			return true;
		}
		return false;
	}

	/**
	 * Saves the image to the specified file.
	 * @param string $file Physical file.
	 * @param int $quality Percents, normalized to 95 on incorrect values.
	 * @param int|null $format One of the Image::FORMAT_* constants.
	 * @return bool
	 */
	public function saveAs($file, $quality = 95, $format = null)
	{
		if($quality <= 0 || $quality > 100)
		{
			$quality = 95;
		}

		if($this->engine->save($file, $quality, $format))
		{
			$this->setFileAttributes($file);

			return true;
		}
		return false;
	}

	protected function setFileAttributes($file)
	{
		@chmod($file, BX_FILE_PERMISSIONS);
		clearstatcache(true, $file);
	}

	/**
	 * Returns actual width of the image.
	 * @return int
	 */
	public function getWidth()
	{
		return $this->engine->getWidth();
	}

	/**
	 * Returns actual height of the image.
	 * @return int
	 */
	public function getHeight()
	{
		return $this->engine->getHeight();
	}

	/**
	 * Returns actual width and height in the Rectangle object.
	 * @return Image\Rectangle
	 */
	public function getDimensions()
	{
		return $this->engine->getDimensions();
	}

	/**
	 * Clears all resources associated to the image.
	 */
	public function clear()
	{
		$this->engine->clear();
	}
}
