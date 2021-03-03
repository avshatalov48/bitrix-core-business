<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

use Bitrix\Main;

class TextWatermark extends Watermark
{
	protected
		$text,
		$width,
		$font,
		$color,
		$copyright = false,
		$padding = 5;

	/**
	 * TextWatermark constructor.
	 * @param string $text Text to show on an image.
	 * @param string $font Full path to a ttf font file.
	 * @param Color|null $color Text color.
	 */
	public function __construct($text, $font, Color $color = null)
	{
		parent::__construct();

		$this->text = $text;
		$this->font = $font;

		if($color !== null)
		{
			$this->color = $color;
		}
		else
		{
			$this->color = new Color();
		}
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return ($this->copyright? chr(169)." " : "").$this->text;
	}

	/**
	 * @return string UTF-8
	 */
	public function getUtfText()
	{
		$text = $this->getText();

		$culture = Main\Context::getCurrent()->getCulture();
		if($culture)
		{
			$text = Main\Text\Encoding::convertEncoding($text, $culture->getCharset(), "UTF-8");
		}

		return $text;
	}

	/**
	 * @param string $text
	 * @return TextWatermark
	 */
	public function setText($text)
	{
		$this->text = $text;
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
	 * @return TextWatermark
	 */
	public function setWidth($width)
	{
		$this->width = (int)$width;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFont()
	{
		return $this->font;
	}

	/**
	 * @param string $font
	 * @return TextWatermark
	 */
	public function setFont($font)
	{
		$this->font = $font;
		return $this;
	}

	/**
	 * @return Color
	 */
	public function getColor(): Color
	{
		return $this->color;
	}

	/**
	 * @param Color $color
	 * @return TextWatermark
	 */
	public function setColor(Color $color)
	{
		$this->color = $color;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isCopyright()
	{
		return $this->copyright;
	}

	/**
	 * @param bool $copyright
	 * @return TextWatermark
	 */
	public function setCopyright($copyright)
	{
		$this->copyright = (bool)$copyright;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getRatio()
	{
		if($this->ratio === null)
		{
			if($this->size == static::SIZE_BIG)
			{
				return 7;
			}
			if($this->size == static::SIZE_SMALL)
			{
				return 2;
			}
			//static::SIZE_MEDIUM
			return 4;
		}
		return $this->ratio;
	}

	/**
	 * @param int $width
	 * @return float In pt.
	 */
	public function getFontSize($width)
	{
		$length = mb_strlen($this->getText());

		$fontSize = $width * ($this->getRatio() / 100.0);

		if(($fontSize * $length * 0.7) > $width)
		{
			$fontSize = $width / ($length * 0.7);
		}

		return $fontSize;
	}
}
