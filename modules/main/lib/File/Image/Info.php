<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

use \Bitrix\Main\File\Image;

class Info
{
	protected
		$width,
		$height,
		$format,
		$attributes,
		$mime;

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param int $width
	 * @return Info
	 */
	public function setWidth($width)
	{
		$this->width = $width;
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
	 * @return Info
	 */
	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @param int $format
	 * @return Info
	 */
	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAttributes()
	{
		return "width=\"{$this->getWidth()}\" height=\"{$this->getHeight()}\"";
	}

	/**
	 * @return string
	 */
	public function getMime()
	{
		return $this->mime;
	}

	/**
	 * @param string $mime
	 * @return Info
	 */
	public function setMime($mime)
	{
		$this->mime = $mime;
		return $this;
	}

	/**
	 * Swaps width and height.
	 * @return Info
	 */
	public function swapSides()
	{
		$tmp = $this->getHeight();
		$this->setHeight($this->getWidth())
			->setWidth($tmp);
		return $this;
	}

	/**
	 * Returns true for known image formats.
	 * @return bool
	 */
	public function isSupported()
	{
		static $knownTypes = null;

		if($knownTypes === null)
		{
			$knownTypes = [
				Image::FORMAT_PNG => 1,
				Image::FORMAT_JPEG => 1,
				Image::FORMAT_GIF => 1,
				Image::FORMAT_BMP => 1,
			];
			if(function_exists("imagecreatefromwebp"))
			{
				$knownTypes[Image::FORMAT_WEBP] = 1;
			}
		}

		return isset($knownTypes[$this->getFormat()]);
	}

	/**
	 * Returns width and height in the Rectangle object.
	 * @return Rectangle
	 */
	public function toRectangle()
	{
		return new Rectangle($this->getWidth(), $this->getHeight());
	}
}
