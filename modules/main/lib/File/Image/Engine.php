<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\File\Image;

use Bitrix\Main;
use Bitrix\Main\File\Image;

/**
 * Class Engine
 * @internal
 * @package Bitrix\Main\File\Image
 */
abstract class Engine
{
	protected $file;
	protected $info;
	protected $options;
	protected $substituted = false;

	/**
	 * Engine constructor.
	 * @param string|null $file
	 * @param array $options
	 */
	public function __construct($file = null, array $options = [])
	{
		if($file !== null)
		{
			$this->setFile($file);
		}

		$this->options = $options;
	}

	/**
	 * @param string $file Physical file name.
	 * @return $this
	 */
	public function setFile($file)
	{
		$this->file = $file;
		$this->info = null;
		return $this;
	}

	/**
	 * Returns the image file info, including the image size.
	 * @param bool $flashEnabled
	 * @return Image\Info|null
	 */
	public function getInfo($flashEnabled = false)
	{
		if($this->info !== null)
		{
			return $this->info;
		}

		if($this->file === null)
		{
			return null;
		}

		if(!file_exists($this->file))
		{
			return null;
		}

		/*
		This will protect us from scan the whole file in order to find out size of the xbm image
		ext/standard/image.c php_getimagetype
		*/
		$handler = fopen($this->file, "rb");
		if(!is_resource($handler))
		{
			return null;
		}
		$signature = fread($handler, 12);
		fclose($handler);

		if($flashEnabled)
		{
			$flashPattern = "
				|FWS                   # php_sig_swf
				|CWS                   # php_sig_swc
			";
		}
		else
		{
			$flashPattern = "";
		}

		if(preg_match("/^(
			GIF                    # php_sig_gif
			|\\xff\\xd8\\xff       # php_sig_jpg
			|\\x89\\x50\\x4e       # php_sig_png
			".$flashPattern."
			|8BPS                  # php_sig_psd
			|BM                    # php_sig_bmp
			|\\xff\\x4f\\xff       # php_sig_jpc
			|II\\x2a\\x00          # php_sig_tif_ii
			|MM\\x00\\x2a          # php_sig_tif_mm
			|FORM                  # php_sig_iff
			|\\x00\\x00\\x01\\x00  # php_sig_ico
			|\\x00\\x00\\x00\\x0c
			\\x6a\\x50\\x20\\x20
			\\x0d\\x0a\\x87\\x0a   # php_sig_jp2
			|RIFF.{4}WEBP		   # php_sig_riff php_sig_webp
			)/xs",
			$signature
		))
		{
			//This function does not require the GD image library.
			$size = getimagesize($this->file);

			if($size !== false)
			{
				$this->info = ((new Image\Info())
					->setWidth($size[0])
					->setHeight($size[1])
					->setFormat($size[2])
					->setMime($size["mime"])
				);
				return $this->info;
			}
		}

		return null;
	}

	/**
	 * Reads EXIF data from the image.
	 * @return array
	 */
	public function getExifData()
	{
		$result = [];

		if($this->file !== null)
		{
			if(function_exists("exif_read_data"))
			{
				// exif_read_data() generates unnecessary warnings
				if(($exif = @exif_read_data($this->file)) !== false)
				{
					$culture = Main\Context::getCurrent()->getCulture();
					$result = Main\Text\Encoding::convertEncoding($exif, ini_get('exif.encode_unicode'), $culture->getCharset());
				}
			}
		}

		return $result;
	}

	/**
	 * Reads the image data from the file.
	 * @return bool
	 */
	abstract public function load();

	/**
	 * Rotates the image clockwise.
	 * @param float $angle
	 * @param Color $bgColor
	 * @return bool
	 */
	abstract public function rotate($angle, Color $bgColor);

	/**
	 * Flips the image vertically.
	 * @return bool
	 */
	abstract public function flipVertical();

	/**
	 * Flops the image horizontally.
	 * @return bool
	 */
	abstract public function flipHorizontal();

	/**
	 * Sets the image orientation.
	 * @param $orientation
	 * @return bool
	 */
	abstract public function setOrientation($orientation);

	/**
	 * Resizes the image.
	 * @param Rectangle $source
	 * @param Rectangle $destination
	 * @return bool
	 */
	abstract public function resize(Rectangle $source, Rectangle $destination);

	/**
	 * Applies a mask to the image (convolution).
	 * @param Mask $mask
	 * @return bool
	 */
	abstract public function filter(Mask $mask);

	/**
	 * Draws a text watermark on the image.
	 * @param Image\TextWatermark $watermark
	 * @return bool
	 */
	abstract public function drawTextWatermark(TextWatermark $watermark);

	/**
	 * Draws an image watermark on the image.
	 * @param Image\ImageWatermark $watermark
	 * @return bool
	 */
	abstract public function drawImageWatermark(ImageWatermark $watermark);

	protected function loadWatermark(ImageWatermark $watermark)
	{
		$file = $watermark->getImageFile();

		if($file === null)
		{
			return null;
		}

		$image = new static($file);

		if(!$image->load())
		{
			return null;
		}

		if($watermark->getMode() == ImageWatermark::MODE_RESIZE)
		{
			$source = $image->getDimensions();
			$destination = $this->getDimensions();

			$destination->scale($watermark->getRatio());

			if ($source->resize($destination, Image::RESIZE_PROPORTIONAL))
			{
				$image->resize($source, $destination);
			}
		}

		return $image;
	}

	/**
	 * Saves the image to the specified file.
	 * @param string $file Physical file.
	 * @param int $quality Percents.
	 * @param int|null $format One of the Image::FORMAT_* constants.
	 * @return bool
	 */
	abstract public function save($file, $quality = 95, $format = null);

	/**
	 * Returns actual width of the image.
	 * @return int
	 */
	abstract public function getWidth();

	/**
	 * Returns actual height of the image.
	 * @return int
	 */
	abstract public function getHeight();

	/**
	 * Returns actual width and height in the Rectangle object.
	 * @return Rectangle
	 */
	public function getDimensions()
	{
		return new Rectangle($this->getWidth(), $this->getHeight());
	}

	/**
	 * Clears all resources associated to the image.
	 */
	abstract public function clear();

	/**
	 * Returns true if the image is substituted with a stub.
	 *
	 * @return bool
	 */
	public function substituted(): bool
	{
		return $this->substituted;
	}

	protected function getMaxSize()
	{
		if (isset($this->options["maxSize"]) && is_array($this->options["maxSize"]))
		{
			return new Rectangle($this->options["maxSize"][0], $this->options["maxSize"][1]);
		}

		return null;
	}

	/**
	 * Returns true if the image exceeds maximum dimensions in options.
	 *
	 * @return bool
	 */
	public function exceedsMaxSize(): bool
	{
		$info = $this->getInfo();
		if (!$info)
		{
			return false;
		}

		$source = $info->toRectangle();
		$maxSize = $this->getMaxSize();

		if ($maxSize && $source->resize($maxSize, Image::RESIZE_PROPORTIONAL))
		{
			return true;
		}

		return false;
	}

	public function __destruct()
	{
		$this->clear();
	}
}
