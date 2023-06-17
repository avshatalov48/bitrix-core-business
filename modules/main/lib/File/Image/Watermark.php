<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\File\Image;

class Watermark
{
	const
		ALIGN_LEFT = "left",
		ALIGN_CENTER = "center",
		ALIGN_RIGHT = "right",
		ALIGN_TOP = "top",
		ALIGN_BOTTOM = "bottom";

	const
		SIZE_SMALL = "small",
		SIZE_MEDIUM = "medium",
		SIZE_BIG = "big";

	protected
		$hAlign = self::ALIGN_RIGHT,
		$vAlign = self::ALIGN_BOTTOM,
		$size = self::SIZE_MEDIUM,
		$padding = 0,
		$ratio;

	public function __construct()
	{
	}

	/**
	 * @param string $hAlign ALIGN_* constants
	 * @param string $vAlign ALIGN_* constants
	 * @return $this
	 */
	public function setAlignment($hAlign, $vAlign)
	{
		$this->hAlign = $hAlign;
		$this->vAlign = $vAlign;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getRatio()
	{
		return $this->ratio;
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @param Rectangle $position
	 */
	public function alignPosition($width, $height, Rectangle $position)
	{
		$padding = $this->padding;

		if($this->vAlign == static::ALIGN_CENTER)
		{
			$position->setY(($height - $position->getHeight()) / 2);
		}
		elseif($this->vAlign == static::ALIGN_BOTTOM)
		{
			$position->setY($height - $position->getHeight() - $padding);
		}
		else //static::ALIGN_TOP
		{
			$position->setY($padding);
		}

		if($this->hAlign == static::ALIGN_CENTER)
		{
			$position->setX(($width - $position->getWidth()) / 2);
		}
		elseif($this->hAlign == static::ALIGN_RIGHT)
		{
			$position->setX(($width - $position->getWidth()) - $padding);
		}
		else //static::ALIGN_LEFT
		{
			$position->setX($padding);
		}

		if($position->getY() < $padding)
		{
			$position->setY($padding);
		}
		if($position->getX() < $padding)
		{
			$position->setX($padding);
		}
	}

	/**
	 * @param float $ratio
	 * @return Watermark
	 */
	public function setRatio($ratio)
	{
		$this->ratio = (float)$ratio;
		return $this;
	}

	/**
	 * @param string $size SIZE_* constants
	 * @return Watermark
	 */
	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getVerticalAlignment()
	{
		return $this->vAlign;
	}

	/**
	 * @return string
	 */
	public function getHorizontalAlignment()
	{
		return $this->hAlign;
	}

	/**
	 * @return int
	 */
	public function getPadding()
	{
		return $this->padding;
	}

	/**
	 * @param int $padding
	 * @return Watermark
	 */
	public function setPadding($padding)
	{
		$this->padding = (int)$padding;
		return $this;
	}

	/**
	 * @internal For compatibility only. Usage is discouraged.
	 * @param array $params
	 * @return ImageWatermark|TextWatermark
	 */
	public static function createFromArray($params)
	{
		$params["position"] = strtolower(trim($params["position"] ?? ''));
		$positions = ["topleft", "topcenter", "topright", "centerleft", "center", "centerright", "bottomleft", "bottomcenter", "bottomright"];
		$shortPositions = ["tl", "tc", "tr", "ml", "mc", "mr", "bl", "bc", "br"];
		$position = ['x' => 'right','y' => 'bottom']; // Default position

		if(in_array($params["position"], $shortPositions))
		{
			$params["position"] = str_replace($shortPositions, $positions, $params["position"]);
		}

		if(in_array($params["position"], $positions))
		{
			foreach(['top', 'center', 'bottom'] as $k)
			{
				$l = strlen($k);
				if(substr($params["position"], 0, $l) == $k)
				{
					$position['y'] = $k;
					$position['x'] = substr($params["position"], $l);
					if($position['x'] == '')
					{
						$position['x'] = ($k == 'center'? 'center' : 'right');
					}
				}
			}
		}

		if(isset($params["type"]) && $params["type"] == "text")
		{
			$watermark = new TextWatermark(
				$params['text'],
				$params['font'],
				Color::createFromHex($params['color'])
			);

			if($params["text_width"] > 0)
			{
				$watermark->setWidth($params["text_width"]);
			}
			if($params["use_copyright"] == "Y")
			{
				$watermark->setCopyright(true);
			}
		}
		else
		{
			$watermark = new ImageWatermark($params["file"] ?? null);

			if(!isset($params["fill"]) || $params["fill"] <> 'repeat')
			{
				if(!isset($params["fill"]) || $params["fill"] <> 'exact')
				{
					if(isset($params["size"]) && $params["size"] == "real")
					{
						$params["fill"] = 'exact';
					}
					else
					{
						$params["fill"] = 'resize';
					}
				}
			}
			else
			{
				$position["x"] = "left";
				$position["y"] = "top";
			}

			$watermark->setMode($params["fill"]);

			if(isset($params["alpha_level"]))
			{
				$watermark->setAlpha(intval($params["alpha_level"]) / 100);
			}
		}

		$watermark->setAlignment($position["x"], $position["y"]);

		if(isset($params["padding"]))
		{
			$watermark->setPadding($params["padding"]);
		}

		if(isset($params["size"]))
		{
			$watermark->setSize($params["size"]);
		}

		if(isset($params["coefficient"]))
		{
			$watermark->setRatio($params["coefficient"]);
		}

		return $watermark;
	}
}
