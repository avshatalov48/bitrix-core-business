<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

use Bitrix\Main\File;

class Gd extends Engine
{
	protected $resource;
	protected $format;

	/**
	 * @inheritDoc
	 */
	public function load()
	{
		$this->clear();

		$info = $this->getInfo();

		if ($info && $info->isSupported())
		{
			$this->format = $info->getFormat();
			$resource = null;

			switch($this->format)
			{
				case File\Image::FORMAT_GIF:
					$resource = imagecreatefromgif($this->file);
					break;
				case File\Image::FORMAT_PNG:
					$resource = imagecreatefrompng($this->file);
					break;
				case File\Image::FORMAT_WEBP:
					$resource = imagecreatefromwebp($this->file);
					break;
				case File\Image::FORMAT_BMP:
					$resource = imagecreatefrombmp($this->file);
					break;
				case File\Image::FORMAT_JPEG:
					ini_set('gd.jpeg_ignore_warning', 1);
					$resource = imagecreatefromjpeg($this->file);
					break;
			}

			if ($resource)
			{
				$this->resource = $resource;
				return true;
			}
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function rotate($angle, Color $bgColor)
	{
		if($this->resource === null)
		{
			return false;
		}

		$angle = 360 - $angle;
		$alpha = (1.0 - $bgColor->getAlpha())*127;
		$color = imagecolorallocatealpha($this->resource, $bgColor->getRed(), $bgColor->getGreen(), $bgColor->getBlue(), $alpha);

		$resource = imagerotate($this->resource, $angle, $color);

		if($resource === false)
		{
			return false;
		}

		$this->clear();
		$this->resource = $resource;

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function flipVertical()
	{
		if($this->resource === null)
		{
			return false;
		}

		return imageflip($this->resource, IMG_FLIP_VERTICAL);
	}

	/**
	 * @inheritDoc
	 */
	public function flipHorizontal()
	{
		if($this->resource === null)
		{
			return false;
		}

		return imageflip($this->resource, IMG_FLIP_HORIZONTAL);
	}

	/**
	 * @inheritDoc
	 */
	public function setOrientation($orientation)
	{
		//not implemented
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function resize(Rectangle $source, Rectangle $destination)
	{
		if($this->resource === null)
		{
			return false;
		}

		$transparentColor = -1;

		$destinationWidth = $destination->getWidth();
		$destinationHeight = $destination->getHeight();

		if(($picture = imagecreatetruecolor($destinationWidth, $destinationHeight)))
		{
			imagealphablending($picture, false);

			if($this->format == File\Image::FORMAT_PNG || $this->format == File\Image::FORMAT_WEBP)
			{
				$transparentColor = imagecolorallocatealpha($picture, 0, 0, 0, 127);
				imagefilledrectangle($picture, 0, 0, $destinationWidth, $destinationHeight, $transparentColor);
			}
			elseif($this->format == File\Image::FORMAT_GIF)
			{
				//save transparency
				$transparentColor = imagecolortransparent($this->resource);
				if($transparentColor >= 0)
				{
					$rgb = imagecolorsforindex($this->resource, $transparentColor);
					$transparentColor = imagecolorallocatealpha($picture, $rgb["red"], $rgb["green"], $rgb["blue"], 127);
					imagefilledrectangle($picture, 0, 0, $destinationWidth, $destinationHeight, $transparentColor);
				}
			}

			if(imagecopyresampled($picture, $this->resource, 0, 0, $source->getX(), $source->getY(), $destinationWidth, $destinationHeight, $source->getWidth(), $source->getHeight()))
			{
				$this->clear();
				$this->resource = $picture;

				if($this->format == File\Image::FORMAT_GIF)
				{
					//restore transparency
					if($transparentColor >= 0)
					{
						$this->restoreTransparency($transparentColor);
					}
				}

				return true;
			}
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function filter(Mask $mask)
	{
		if($this->resource === null)
		{
			return false;
		}

		$transparentColor = -1;
		if($this->format == File\Image::FORMAT_GIF)
		{
			//Process transparency for GIFs
			$transparentColor = imagecolortransparent($this->resource);
		}

		//Fix left top corner
		$newPixel = $this->calculatePixel($mask, 0, 0);

		$result = imageconvolution($this->resource, $mask->getValue(), 1, 0);

		if($result)
		{
			//Fix left top corner
			imagealphablending($this->resource, false);
			imagesetpixel($this->resource, 0, 0, $newPixel);

			//restore transparency
			if ($transparentColor >= 0)
			{
				$this->restoreTransparency($transparentColor);
			}
		}
		return $result;
	}

	protected function restoreTransparency($transparentColor)
	{
		imagecolortransparent($this->resource, $transparentColor);

		$width = $this->getWidth();
		$height = $this->getHeight();

		for($y = 0; $y < $height; ++$y)
		{
			for($x = 0; $x < $width; ++$x)
			{
				if(((imagecolorat($this->resource, $x, $y) >> 24) & 0x7F) >= 100)
				{
					imagesetpixel($this->resource,	$x,	$y,	$transparentColor);
				}
			}
		}
	}

	/**
	 * @param Mask $mask
	 * @param int $x
	 * @param int $y
	 * @return false|int
	 */
	protected function calculatePixel($mask, $x, $y)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();

		$alpha = (imagecolorat($this->resource, $x, $y) >> 24) & 0xFF;
		$newR = $newG = $newB = 0;

		for($j = 0; $j < 3; ++$j)
		{
			$yv = $y - 1 + $j;
			if($yv < 0)
			{
				$yv = 0;
			}
			elseif($yv >= $height)
			{
				$yv = $height - 1;
			}

			for($i = 0; $i < 3; ++$i)
			{
				$xv = $x - 1 + $i;
				if($xv < 0)
				{
					$xv = 0;
				}
				elseif($xv >= $width)
				{
					$xv = $width - 1;
				}

				$m = $mask[$j][$i];
				$rgb = imagecolorat($this->resource, $xv, $yv);

				$newR += (($rgb >> 16) & 0xFF) * $m;
				$newG += (($rgb >> 8) & 0xFF) * $m;
				$newB += ($rgb & 0xFF) * $m;
			}
		}

		$newR = ($newR > 255? 255 : (($newR < 0? 0 : $newR)));
		$newG = ($newG > 255? 255 : (($newG < 0? 0 : $newG)));
		$newB = ($newB > 255? 255 : (($newB < 0? 0 : $newB)));

		return imagecolorallocatealpha($this->resource, $newR, $newG, $newB, $alpha);
	}

	/**
	 * @inheritDoc
	 */
	public function drawTextWatermark(TextWatermark $watermark)
	{
		if($this->resource === null)
		{
			return false;
		}

		$font = $watermark->getFont();

		if(!file_exists($font))
		{
			return false;
		}

		$utfText = $watermark->getUtfText();

		$width = $this->getWidth();
		$height = $this->getHeight();

		if(($textWidth = $watermark->getWidth()) > 0)
		{
			$textBox = imagettfbbox(20, 0, $font, $utfText);

			$scale = $textWidth / ($textBox[2] - $textBox[0]);
			$fontSize = 20 * $scale;

			$position = new Rectangle($textWidth, ($textBox[0] - $textBox[7]) * $scale);
		}
		else
		{
			$fontSize = $watermark->getFontSize($width);

			$textBox = imagettfbbox($fontSize, 0, $font, $utfText);

			$position = new Rectangle(($textBox[2] - $textBox[0]), ($textBox[0] - $textBox[7]));
		}

		$watermark->alignPosition($width, $height, $position);

		$color = $watermark->getColor();
		$textColor = imagecolorallocate($this->resource, $color->getRed(), $color->getGreen(), $color->getBlue());

		if($watermark->getVerticalAlignment() == Watermark::ALIGN_BOTTOM)
		{
			//Try to take into consideration font's descenders.
			//Coordinates in imagettftext are for font's *baseline*.
			//Let the descenders be 20% of the font size.
			$descender = $fontSize * 0.2;
			$y = $position->getY() + $position->getHeight() - $descender; //baseline
		}
		else
		{
			$y = $position->getY() + $fontSize; //baseline
		}

		$result = imagettftext($this->resource, $fontSize, 0, $position->getX(), $y, $textColor, $font, $utfText);

		return ($result !== false);
	}

	/**
	 * @inheritDoc
	 */
	public function drawImageWatermark(ImageWatermark $watermark)
	{
		if($this->resource === null)
		{
			return false;
		}

		if(($image = $this->loadWatermark($watermark)) === null)
		{
			return false;
		}

		$width = $this->getWidth();
		$height = $this->getHeight();

		$watermarkWidth = $image->getWidth();
		$watermarkHeight = $image->getHeight();

		$position = new Rectangle($watermarkWidth, $watermarkHeight);

		$watermark->alignPosition($width, $height, $position);

		$watermarkX = $position->getX();
		$watermarkY = $position->getY();

		$watermarkAlpha = $watermark->getAlpha();
		$repeat = ($watermark->getMode() == ImageWatermark::MODE_REPEAT);

		for($y = 0; $y < $watermarkHeight; $y++)
		{
			for($x = 0; $x < $watermarkWidth; $x++)
			{
				$posY = $watermarkY + $y;
				while(true)
				{
					$posX = $watermarkX + $x;
					while(true)
					{
						$alpha = $watermarkAlpha;

						$mainRgb = imagecolorsforindex($this->resource, imagecolorat($this->resource, $posX, $posY));
						$watermarkRgb = imagecolorsforindex($image->resource, imagecolorat($image->resource, $x, $y));

						if($watermarkRgb['alpha'] == 127)
						{
							$pixel = $mainRgb;
						}
						else
						{
							if($watermarkRgb['alpha'])
							{
								$alpha = round((( 127 - $watermarkRgb['alpha']) / 127), 2);
								$alpha = $alpha * $watermarkAlpha;
							}

							$pixel = [];
							foreach(['red', 'green', 'blue', 'alpha'] as $k)
							{
								$pixel[$k] = round(($mainRgb[$k] * (1 - $alpha)) + ($watermarkRgb[$k] * $alpha));
							}
						}

						$color = imagecolorexactalpha($this->resource, $pixel["red"], $pixel["green"], $pixel["blue"], $pixel["alpha"]);
						if($color == -1)
						{
							$color = imagecolorallocatealpha($this->resource, $pixel["red"], $pixel["green"], $pixel["blue"], $pixel["alpha"]);
							if($color === false)
							{
								$color = imagecolorclosestalpha($this->resource, $pixel["red"], $pixel["green"], $pixel["blue"], $pixel["alpha"]);
							}
						}

						imagesetpixel($this->resource, $posX, $posY, $color);

						$posX += $watermarkWidth;

						if($repeat == false || $posX > $width)
						{
							break;
						}
					}

					$posY += $watermarkHeight;

					if($repeat == false || $posY > $height)
					{
						break;
					}
				}
			}
		}

		$image->clear();

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function save($file, $quality = 95, $format = null)
	{
		if($this->resource === null)
		{
			return false;
		}

		if($format === null)
		{
			$format = $this->format;
		}

		$result = false;

		switch($format)
		{
			case File\Image::FORMAT_GIF:
				$result = imagegif($this->resource, $file);
				break;
			case File\Image::FORMAT_PNG:
				imagealphablending($this->resource, true);
				imagesavealpha($this->resource, true);
				$result = imagepng($this->resource, $file);
				break;
			case File\Image::FORMAT_WEBP:
				imagealphablending($this->resource, true);
				imagesavealpha($this->resource, true);
				$result = imagewebp($this->resource, $file, $quality);
				break;
			case File\Image::FORMAT_BMP:
				$result = imagebmp($this->resource, $file);
				break;
			case File\Image::FORMAT_JPEG:
				$result = imagejpeg($this->resource, $file, $quality);
				break;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getWidth()
	{
		return imagesx($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function getHeight()
	{
		return imagesy($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function clear()
	{
		if($this->resource !== null)
		{
			imagedestroy($this->resource);
			$this->resource = null;
		}
	}

	/**
	 * @internal
	 * @param resource $resource
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
	}

	/**
	 * @internal
	 */
	public function getResource()
	{
		return $this->resource;
	}
}
