<?php
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################

//Some marketplace modules define the same class
if (!class_exists("CCaptcha")):

class CCaptcha
{
	var $imageWidth = 180;
	var $imageHeight = 40;

	var $codeLength = 5;

	var $ttfFilesPath = "/bitrix/modules/main/fonts";
	var $arTTFFiles = array("font.ttf");

	var $textAngleFrom = -20;
	var $textAngleTo = 20;
	var $textStartX = 7;
	var $textDistanceFrom = 27;
	var $textDistanceTo = 32;
	var $textFontSize = 20;

	var $bTransparentText = true;
	var $transparentTextPercent = 10;

	var $arTextColor = array(array(0, 100), array(0, 100), array(0, 100));

	var $arBGColor = array(array(255, 255), array(255, 255), array(255, 255));
	var $arRealBGColor = false;

	var $numEllipses = 100;
	var $arEllipseColor = array(array(127, 255), array(127, 255), array(127, 255));

	var $numLines = 20;
	var $arLineColor = array(array(110, 250), array(110, 250), array(110, 250));
	var $bLinesOverText = false;

	var $arBorderColor = array(0, 0, 0);

	var $bWaveTransformation = false;
	var $bEmptyText = false;

	var $arChars = array(
			'A','B','C','D','E','F','G','H','J','K','L','M',
			'N','P','Q','R','S','T'/*,'U'*//*,'V'*/,'W','X','Y','Z',
			'2','3','4','5','6','7','8','9'
		);//'1','I','O','0'

	var $image;
	var $code;
	var $codeCrypt;
	var $sid;

	public function __construct()
	{
		$this->transparentTextPercent = COption::GetOptionInt("main", "CAPTCHA_transparentTextPercent", 10);
		$this->bTransparentText = $this->transparentTextPercent > 0;
		$this->SetBGColorRGB(
			COption::GetOptionString("main", "CAPTCHA_arBGColor_1", "FFFFFF"),
			COption::GetOptionString("main", "CAPTCHA_arBGColor_2", "FFFFFF")
		);
		$this->SetEllipsesNumber(
			COption::GetOptionInt("main", "CAPTCHA_numEllipses", 100)
		);
		$this->SetEllipseColorRGB(
			COption::GetOptionString("main", "CAPTCHA_arEllipseColor_1", "7F7F7F"),
			COption::GetOptionString("main", "CAPTCHA_arEllipseColor_2", "FFFFFF")
		);
		$this->SetLinesOverText(
			COption::GetOptionString("main", "CAPTCHA_bLinesOverText", "N") === "Y"
		);
		$this->SetLinesNumber(
			COption::GetOptionInt("main", "CAPTCHA_numLines", 20)
		);
		$this->SetLineColorRGB(
			COption::GetOptionString("main", "CAPTCHA_arLineColor_1", "6E6E6E"),
			COption::GetOptionString("main", "CAPTCHA_arLineColor_2", "FAFAFA")
		);
		$this->SetTextWriting(
			COption::GetOptionInt("main", "CAPTCHA_textAngel_1", -20),
			COption::GetOptionInt("main", "CAPTCHA_textAngel_2", 20),
			COption::GetOptionInt("main", "CAPTCHA_textStartX", 7),
			COption::GetOptionInt("main", "CAPTCHA_textDistance_1", 27),
			COption::GetOptionInt("main", "CAPTCHA_textDistance_2", 32),
			COption::GetOptionInt("main", "CAPTCHA_textFontSize", 20)
		);
		$this->SetTextColorRGB(
			COption::GetOptionString("main", "CAPTCHA_arTextColor_1", "000000"),
			COption::GetOptionString("main", "CAPTCHA_arTextColor_2", "646464")
		);
		$this->SetWaveTransformation(
			COption::GetOptionString("main", "CAPTCHA_bWaveTransformation", "N") === "Y"
		);
		$this->SetEmptyText(
			COption::GetOptionString("main", "CAPTCHA_bEmptyText", "N") === "Y"
		);
		$this->SetBorderColorRGB(
			COption::GetOptionString("main", "CAPTCHA_arBorderColor", "000000")
		);
		$this->SetTTFFonts(
			explode(",", COption::GetOptionString("main", "CAPTCHA_arTTFFiles", "font.ttf"))
		);

		$strChars = COption::GetOptionString("main", "CAPTCHA_letters", "ABCDEFGHJKLMNPQRSTWXYZ23456789");
		$arChars = array();
		for($i = 0, $n = mb_strlen($strChars); $i < $n; $i++)
		{
			$arChars[] = mb_substr($strChars, $i, 1);
		}
		$this->SetCodeChars($arChars);
	}

	/* SET */
	function SetImageSize($width, $height)
	{
		$width = intval($width);
		$height = intval($height);

		if ($width > 0)
			$this->imageWidth = $width;

		if ($height > 0)
			$this->imageHeight = $height;
	}

	function SetCodeLength($length)
	{
		$length = intval($length);

		if ($length > 0)
			$this->codeLength = $length;
	}

	function SetTTFFontsPath($ttfFilesPath)
	{
		if ($ttfFilesPath <> '')
		{
			$filename = trim(str_replace("\\", "/", trim($ttfFilesPath)), "/");
			$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$filename);
			if(mb_strlen($FILE_NAME) > 1 && is_dir($_SERVER["DOCUMENT_ROOT"].$FILE_NAME))
				$this->ttfFilesPath = $FILE_NAME;
		}
	}

	function GetTTFFontsPath()
	{
		return $this->ttfFilesPath;
	}

	function SetTTFFonts($arFonts)
	{
		if (!is_array($arFonts) || empty($arFonts))
			$arFonts = array();

		$this->arTTFFiles = $arFonts;
	}

	function SetTextWriting($angleFrom, $angleTo, $startX, $distanceFrom, $distanceTo, $fontSize)
	{
		$angleFrom = intval($angleFrom);
		$angleTo = intval($angleTo);
		$startX = intval($startX);
		$distanceFrom = intval($distanceFrom);
		$distanceTo = intval($distanceTo);
		$fontSize = intval($fontSize);

		$this->textAngleFrom = $angleFrom;
		$this->textAngleTo = $angleTo;

		if ($startX > 0)
			$this->textStartX = $startX;

		if ($distanceFrom <> 0)
			$this->textDistanceFrom = $distanceFrom;

		if ($distanceTo <> 0)
			$this->textDistanceTo = $distanceTo;

		if ($fontSize > 0)
			$this->textFontSize = $fontSize;
	}

	function SetTextTransparent($bTransparentText, $transparentTextPercent = 10)
	{
		$this->bTransparentText = ($bTransparentText ? true : false);
		$this->transparentTextPercent = intval($transparentTextPercent);
	}

	function SetColor($arColor)
	{
		if (!is_array($arColor) || count($arColor) != 3)
			return false;

		$arNewColor = array();
		$bCorrectColor = true;

		for ($i = 0; $i < 3; $i++)
		{
			if (!is_array($arColor[$i]))
				$arColor[$i] = array($arColor[$i]);

			for ($j = 0; $j < 2; $j++)
			{
				if ($j > 0)
				{
					if (!array_key_exists($j, $arColor[$i]))
						$arColor[$i][$j] = $arColor[$i][$j - 1];
				}

				$arColor[$i][$j] = intval($arColor[$i][$j]);
				if ($arColor[$i][$j] < 0 || $arColor[$i][$j] > 255)
				{
					$bCorrectColor = false;
					break;
				}

				if ($j > 0)
				{
					if ($arColor[$i][$j] < $arColor[$i][$j - 1])
					{
						$bCorrectColor = false;
						break;
					}
				}

				$arNewColor[$i][$j] = $arColor[$i][$j];

				if ($j > 0)
					break;
			}
		}

		if ($bCorrectColor)
			return $arNewColor;

		return false;
	}

	function SetBGColor($arColor)
	{
		if ($arNewColor = $this->SetColor($arColor))
		{
			$this->arBGColor = $arNewColor;
			$this->arRealBGColor = false;
		}
	}

	function SetBGColorRGB($color_1, $color_2)
	{
		if(preg_match("/^[0-9A-Fa-f]{6}$/", $color_1) && preg_match("/^[0-9A-Fa-f]{6}$/", $color_1))
		{
			$arColor = array(
				array(hexdec(mb_substr($color_1, 0, 2)), hexdec(mb_substr($color_2, 0, 2))),
				array(hexdec(mb_substr($color_1, 2, 2)), hexdec(mb_substr($color_2, 2, 2))),
				array(hexdec(mb_substr($color_1, 4, 2)), hexdec(mb_substr($color_2, 4, 2))),
			);
			$this->SetBGColor($arColor);
		}
	}

	function SetTextColor($arColor)
	{
		if ($arNewColor = $this->SetColor($arColor))
			$this->arTextColor = $arNewColor;
	}

	function SetTextColorRGB($color_1, $color_2)
	{
		if(preg_match("/^[0-9A-Fa-f]{6}$/", $color_1) && preg_match("/^[0-9A-Fa-f]{6}$/", $color_1))
		{
			$arColor = array(
				array(hexdec(mb_substr($color_1, 0, 2)), hexdec(mb_substr($color_2, 0, 2))),
				array(hexdec(mb_substr($color_1, 2, 2)), hexdec(mb_substr($color_2, 2, 2))),
				array(hexdec(mb_substr($color_1, 4, 2)), hexdec(mb_substr($color_2, 4, 2))),
			);
			$this->SetTextColor($arColor);
		}
	}

	function SetEllipseColor($arColor)
	{
		if ($arNewColor = $this->SetColor($arColor))
			$this->arEllipseColor = $arNewColor;
	}

	function SetEllipseColorRGB($color_1, $color_2)
	{
		if(preg_match("/^[0-9A-Fa-f]{6}$/", $color_1) && preg_match("/^[0-9A-Fa-f]{6}$/", $color_1))
		{
			$arColor = array(
				array(hexdec(mb_substr($color_1, 0, 2)), hexdec(mb_substr($color_2, 0, 2))),
				array(hexdec(mb_substr($color_1, 2, 2)), hexdec(mb_substr($color_2, 2, 2))),
				array(hexdec(mb_substr($color_1, 4, 2)), hexdec(mb_substr($color_2, 4, 2))),
			);
			$this->SetEllipseColor($arColor);
		}
	}

	function SetLineColor($arColor)
	{
		if ($arNewColor = $this->SetColor($arColor))
			$this->arLineColor = $arNewColor;
	}

	function SetLineColorRGB($color_1, $color_2)
	{
		if(preg_match("/^[0-9A-Fa-f]{6}$/", $color_1) && preg_match("/^[0-9A-Fa-f]{6}$/", $color_1))
		{
			$arColor = array(
				array(hexdec(mb_substr($color_1, 0, 2)), hexdec(mb_substr($color_2, 0, 2))),
				array(hexdec(mb_substr($color_1, 2, 2)), hexdec(mb_substr($color_2, 2, 2))),
				array(hexdec(mb_substr($color_1, 4, 2)), hexdec(mb_substr($color_2, 4, 2))),
			);
			$this->SetLineColor($arColor);
		}
	}

	function SetBorderColor($arColor)
	{
		if ($arNewColor = $this->SetColor($arColor))
			$this->arBorderColor = $arNewColor;
	}

	function SetBorderColorRGB($color)
	{
		if(preg_match("/^[0-9A-Fa-f]{6}$/", $color))
		{
			$arColor = array(
				hexdec(mb_substr($color, 0, 2)),
				hexdec(mb_substr($color, 2, 2)),
				hexdec(mb_substr($color, 4, 2)),
			);
			$this->SetBorderColor($arColor);
		}
	}

	function SetEllipsesNumber($num)
	{
		$this->numEllipses = intval($num);
	}

	function SetLinesNumber($num)
	{
		$this->numLines = intval($num);
	}

	function SetLinesOverText($bLinesOverText)
	{
		$this->bLinesOverText = ($bLinesOverText ? true : false);
	}

	function SetCodeChars($arChars)
	{
		if (is_array($arChars) && !empty($arChars))
			$this->arChars = $arChars;
	}

	function SetWaveTransformation($bWaveTransformation)
	{
		$this->bWaveTransformation = ($bWaveTransformation ? true : false);
	}

	function SetEmptyText($bEmptyText)
	{
		$this->bEmptyText = ($bEmptyText? true: false);
	}

	/* UTIL */
	function GetColor($arColor)
	{
		$arResult = array();
		for ($i = 0, $n = count($arColor); $i < $n; $i++)
		{
			$arResult[$i] = round(rand($arColor[$i][0], $arColor[$i][1]));
		}
		return $arResult;
	}

	function InitImage($width = false, $height = false)
	{
		if(!$width)
			$width = $this->imageWidth;
		if(!$height)
			$height = $this->imageHeight;
		$image = imagecreatetruecolor($width, $height);
		if(!$this->arRealBGColor)
		{
			$this->arRealBGColor = $this->GetColor($this->arBGColor);
		}
		$bgColor = imagecolorallocate($image, $this->arRealBGColor[0], $this->arRealBGColor[1], $this->arRealBGColor[2]);
		imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $bgColor);
		return $image;
	}

	function CreateImage()
	{
		$this->image = $this->InitImage();

		$this->DrawEllipses();

		if (!$this->bLinesOverText)
			$this->DrawLines();

		$right_border = $this->DrawText();
//			if($right_border < ($this->imageWidth - $this->textStartX))
//			{
//				$img2 = $this->InitImage();
//				imagecopy($img2, $this->image,
//					$this->textStartX + rand(0, $this->imageWidth - $right_border - $this->textStartX), 0,
//					$this->textStartX, 0,
//					$right_border - $this->textStartX, $this->imageHeight
//				);
//				$this->image = $img2;
//			}

		if ($this->bLinesOverText)
			$this->DrawLines();

		if($this->bWaveTransformation)
		{
			$this->Wave();
		}

		$arBorderColor = $this->GetColor($this->arBorderColor);
		$borderColor = imagecolorallocate($this->image, $arBorderColor[0], $arBorderColor[1], $arBorderColor[2]);
		imageline($this->image, 0, 0, $this->imageWidth-1, 0, $borderColor);
		imageline($this->image, 0, 0, 0, $this->imageHeight-1, $borderColor);
		imageline($this->image, $this->imageWidth-1, 0, $this->imageWidth-1, $this->imageHeight-1, $borderColor);
		imageline($this->image, 0, $this->imageHeight-1, $this->imageWidth-1, $this->imageHeight-1, $borderColor);
	}

	function CreateImageError($arMsg)
	{
		$this->image = imagecreate($this->imageWidth, $this->imageHeight);
		$bgColor = imagecolorallocate($this->image, 0, 0, 0);
		$textColor = imagecolorallocate($this->image, 255, 255, 255);

		if (!is_array($arMsg))
			$arMsg = array($arMsg);

		$bTextOut = false;
		$y = 5;
		for ($i = 0, $n = count($arMsg); $i < $n; $i++)
		{
			if (trim($arMsg[$i]) <> '')
			{
				$bTextOut = true;
				imagestring($this->image, 3, 5, $y, $arMsg[$i], $textColor);
				$y += 15;
			}
		}

		if (!$bTextOut)
		{
			imagestring($this->image, 3, 5, 5, "Error!", $textColor);
			imagestring($this->image, 3, 5, 20, "Reload the page!", $textColor);
		}
	}

	function Wave()
	{
		$img = $this->image;
		$img2 = $this->InitImage();

		// случайные параметры (можно поэкспериментировать с коэффициентами):
		// частоты
		$rand1 = mt_rand(700000, 1000000) / 15000000;
		$rand2 = mt_rand(700000, 1000000) / 15000000;
		$rand3 = mt_rand(700000, 1000000) / 15000000;
		$rand4 = mt_rand(700000, 1000000) / 15000000;
		// фазы
		$rand5 = mt_rand(0, 3141592) / 1000000;
		$rand6 = mt_rand(0, 3141592) / 1000000;
		$rand7 = mt_rand(0, 3141592) / 1000000;
		$rand8 = mt_rand(0, 3141592) / 1000000;
		// амплитуды
		$rand9 = mt_rand(400, 600) / 500;
		$rand10 = mt_rand(400, 600) / 200;

		$height = $this->imageHeight;
		$height_1 = $height - 1;
		$width = $this->imageWidth;
		$width_1 = $width - 1;

		for($x = 0; $x < $width; $x++)
		{
			for($y = 0; $y < $height; $y++)
			{
				// координаты пикселя-первообраза.
				$sx = $x + ( sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6) ) * $rand9;
				$sy = $y + ( sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8) ) * $rand10;

				// первообраз за пределами изображения
				if($sx < 0 || $sy < 0 || $sx >= $width_1 || $sy >= $height_1)
				{
					$color_xy = $color_y = $color_x = $color = $this->arRealBGColor;
				}
				else
				{ // цвета основного пикселя и его 3-х соседей для лучшего антиалиасинга
					$rgb = imagecolorat($img, $sx, $sy);
					$color_r = ($rgb >> 16) & 0xFF;
					$color_g = ($rgb >> 8) & 0xFF;
					$color_b = $rgb & 0xFF;

					$rgb = imagecolorat($img, $sx+1, $sy);
					$color_x_r = ($rgb >> 16) & 0xFF;
					$color_x_g = ($rgb >> 8) & 0xFF;
					$color_x_b = $rgb & 0xFF;

					$rgb = imagecolorat($img, $sx, $sy+1);
					$color_y_r = ($rgb >> 16) & 0xFF;
					$color_y_g = ($rgb >> 8) & 0xFF;
					$color_y_b = $rgb & 0xFF;

					$rgb = imagecolorat($img, $sx+1, $sy+1);
					$color_xy_r = ($rgb >> 16) & 0xFF;
					$color_xy_g = ($rgb >> 8) & 0xFF;
					$color_xy_b = $rgb & 0xFF;
				}
				// сглаживаем
				$frsx = $sx - floor($sx); //отклонение координат первообраза от целого
				$frsy = $sy - floor($sy);
				$frsx1 = 1 - $frsx;
				$frsy1 = 1 - $frsy;
				// вычисление цвета нового пикселя как пропорции от цвета основного пикселя и его соседей
				$i11 = $frsx1 * $frsy1;
				$i01 = $frsx  * $frsy1;
				$i10 = $frsx1 * $frsy ;
				$i00 = $frsx  * $frsy ;
				$red = floor(	$color_r    * $i11 +
						$color_x_r  * $i01 +
						$color_y_r  * $i10 +
						$color_xy_r * $i00
				);
				$green = floor(	$color_g    * $i11 +
						$color_x_g  * $i01 +
						$color_y_g  * $i10 +
						$color_xy_g * $i00
				);
				$blue = floor(	$color_b    * $i11 +
						$color_x_b  * $i01 +
						$color_y_b  * $i10 +
						$color_xy_b * $i00
				);
				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $red, $green, $blue));
			}
		}
		$this->image = $img2;
	}

	function EmptyText()
	{
		$sx = imagesx($this->image)-1;
		$sy = imagesy($this->image)-1;

		$backup = imagecreatetruecolor($sx, $sy);
		imagealphablending($backup, false);
		imagecopy($backup, $this->image, 0, 0, 0, 0, $sx, $sy);

		$white = imagecolorallocate($this->image, 255, 255, 255);
		$bgColor = imagecolorallocate($this->image, $this->arRealBGColor[0], $this->arRealBGColor[1], $this->arRealBGColor[2]);

		for($x = 1; $x < $sx; $x++)
		for($y = 1; $y < $sy; $y++)
		{
			$c1 = imagecolorat($backup, $x-1, $y);
			if($c1 != $white && $c1 != $bgColor)
			{
				$c2 = imagecolorat($backup, $x+1, $y);
				if($c1 == $c2)
				{
					$c3 = imagecolorat($backup, $x, $y-1);
					if($c2 == $c3)
					{
						$c4 = imagecolorat($backup, $x, $y+1);
						if($c3 == $c4)
							imagesetpixel($this->image, $x, $y, $bgColor);
					}
				}
			}
		}

		if(function_exists('imageconvolution'))
		{
			$gaussian = array(array(1.0, 1.0, 1.0), array(1.0, 7.0, 1.0), array(1.0, 1.0, 1.0));
			imageconvolution($this->image, $gaussian, 15, 0);

			$mask = array(
				array( -0.1, -0.1, -0.1),
				array( -0.1,  1.8, -0.1),
				array( -0.1, -0.1, -0.1)
			);
			imageconvolution($this->image, $mask, 1, 0);
		}
	}

	function DestroyImage()
	{
		imagedestroy($this->image);
	}

	function ShowImage()
	{
		imagejpeg($this->image);
	}

	function DrawText()
	{
		if ($this->bTransparentText)
			$alpha = floor($this->transparentTextPercent / 100 * 127);

		$yMin = ($this->imageHeight / 2) + ($this->textFontSize / 2) - 2;
		$yMax = ($this->imageHeight / 2) + ($this->textFontSize / 2) + 2;

		$bPrecise = $this->textDistanceFrom < 0 && $this->textDistanceTo < 0;

		if($bPrecise)
		{
			//We'll need inversed color to draw on background
			$bg_color_hex = $this->arRealBGColor[0] << 16 | $this->arRealBGColor[1] << 8 | $this->arRealBGColor[2];
			$not_bg_color = array(
				(!$bg_color_hex >> 16) & 0xFF,
				(!$bg_color_hex >> 8) & 0xFF,
				(!$bg_color_hex) & 0xFF
			);
		}

		$arPos = array();
		$x = 0;

		for ($i = 0; $i < $this->codeLength; $i++)
		{
			$char = mb_substr($this->code, $i, 1);
			$utf = $char;

			$ttfFile = $_SERVER["DOCUMENT_ROOT"].$this->ttfFilesPath."/".$this->arTTFFiles[rand(1, count($this->arTTFFiles)) - 1];
			$angle = rand($this->textAngleFrom, $this->textAngleTo);

			$bounds = imagettfbbox($this->textFontSize, $angle, $ttfFile, $utf);

			$height = max($bounds[1], $bounds[3], $bounds[5], $bounds[7]) - min($bounds[1], $bounds[3], $bounds[5], $bounds[7]);
			$width = max($bounds[0], $bounds[2], $bounds[4], $bounds[6]) - min($bounds[0], $bounds[2], $bounds[4], $bounds[6]);

			$y = $height + rand(0, ($this->imageHeight-$height)*0.9);

			if($bPrecise)
			{
				//Now for precise positioning we need to draw characred and define it's borders
				$img = $this->InitImage($width, $this->imageHeight);
				$tmp = imagecolorallocate($img, $not_bg_color[0], $not_bg_color[1], $not_bg_color[2]);
				$dx = -min($bounds[0], $bounds[2], $bounds[4], $bounds[6]);
				imagettftext($img, $this->textFontSize, $angle, $dx, $y, $tmp, $ttfFile, $utf);

				$arLeftBounds = array();
				for($yy=0; $yy < $this->imageHeight; $yy++)
				{
					$arLeftBounds[$yy] = 0;
					for($xx=0; $xx < $width; $xx++)
					{
						$rgb = imagecolorat($img, $xx, $yy);
						if($rgb !== $bg_color_hex)
						{
							$arLeftBounds[$yy] = $xx;
							break;
						}
					}

					$arRightBounds[$yy] = 0;
					if($arLeftBounds[$yy] > 0)
					{
						for($xx=$width; $xx > 0; $xx--)
						{
							$rgb = imagecolorat($img, $xx-1, $yy);
							if($rgb !== $bg_color_hex)
							{
								$arRightBounds[$yy] = $xx-1;
								break;
							}
						}
					}
				}

				imagedestroy($img);
			}
			else
			{
				$arLeftBounds = array();
				$arRightBounds = array();
				$dx = 0;
			}

			if($i > 0)
			{
				if($bPrecise)
				{
					$arDX = array();
					for($yy=0; $yy < $this->imageHeight; $yy++)
					{
						if($arPos[$i-1][6][$yy] > 0 && $arLeftBounds[$yy] > 0)
							$arDX[$yy] = ($arPos[$i-1][6][$yy] - $arPos[$i-1][7])-($arLeftBounds[$yy]-$dx);
						else
							$arDX[$yy] = $arPos[$i-1][5][$yy] - $arPos[$i-1][7];
					}
					$x += max($arDX)+(rand($this->textDistanceFrom, $this->textDistanceTo));
				}
				else
				{
					$x += rand($this->textDistanceFrom, $this->textDistanceTo);
				}
			}
			else
			{
				$x = rand($this->textStartX/2, $this->textStartX*2);
			}

			$arPos[$i] = array(
				$angle,		//0
				$x,		//1
				$y,		//2
				$ttfFile,	//3
				$char,		//4
				$arLeftBounds,	//5
				$arRightBounds,	//6
				$dx,		//7
				$utf,		//8
			);

		}

		foreach($arPos as $pos)
		{
			$arTextColor = $this->GetColor($this->arTextColor);

			if ($this->bTransparentText)
				$color = imagecolorallocatealpha($this->image, $arTextColor[0], $arTextColor[1], $arTextColor[2], $alpha);
			else
				$color = imagecolorallocate($this->image, $arTextColor[0], $arTextColor[1], $arTextColor[2]);

			$bounds = imagettftext($this->image, $this->textFontSize, $pos[0], $pos[1], $pos[2], $color, $pos[3], $pos[8]);

			$x2 = $pos[1] + ($bounds[2] - $bounds[0]);
		}

		if($this->bEmptyText)
			$this->EmptyText();

		return $x2;
	}

	function DrawEllipses()
	{
		if ($this->numEllipses > 0)
		{
			for ($i = 0; $i < $this->numEllipses; $i++)
			{
				$arEllipseColor = $this->GetColor($this->arEllipseColor);
				$color = imagecolorallocate($this->image, $arEllipseColor[0], $arEllipseColor[1], $arEllipseColor[2]);
				imagefilledellipse($this->image, round(rand(0, $this->imageWidth)), round(rand(0, $this->imageHeight)), round(rand(0, $this->imageWidth / 8)), round(rand(0, $this->imageHeight / 2)), $color);
			}
		}
	}

	function DrawLines()
	{
		if ($this->numLines > 0)
		{
			for ($i = 0; $i < $this->numLines; $i++)
			{
				$arLineColor = $this->GetColor($this->arLineColor);
				$color = imagecolorallocate($this->image, $arLineColor[0], $arLineColor[1], $arLineColor[2]);
				imageline($this->image, rand(1, $this->imageWidth), rand(1, $this->imageHeight / 2), rand(1, $this->imageWidth), rand($this->imageHeight / 2, $this->imageHeight), $color);
			}
		}
	}

	/* OUTPUT */
	function Output()
	{
		header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-Type: image/jpeg");
		$this->CreateImage();
		$this->ShowImage();
		$this->DestroyImage();
	}

	function OutputError()
	{
		header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-Type: image/jpeg");

		$numArgs = func_num_args();
		if ($numArgs > 0)
			$arMsg = func_get_arg(0);
		else
			$arMsg = array();

		$this->CreateImageError($arMsg);
		$this->ShowImage();
		$this->DestroyImage();
	}


	/* CODE */
	function SetCode()
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
		{
			CCaptcha::SetCaptchaCode();
			return;
		}

		$max = count($this->arChars);

		$this->code = "";
		for ($i = 0; $i < $this->codeLength; $i++)
			$this->code .= $this->arChars[rand(1, $max) - 1];

		$this->sid = time();

		if (!is_array(\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]))
			\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"] = array();

		\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"][$this->sid] = $this->code;
	}

	function SetCodeCrypt($password = "")
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
		{
			CCaptcha::SetCaptchaCode();
			return;
		}

		$max = count($this->arChars);

		$this->code = "";
		for ($i = 0; $i < $this->codeLength; $i++)
			$this->code .= $this->arChars[rand(1, $max) - 1];

		if (!\Bitrix\Main\Application::getInstance()->getSession()->get("CAPTCHA_PASSWORD"))
			\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_PASSWORD"] = randString(10);

		$this->codeCrypt = $this->CryptData($this->code, "E", \Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_PASSWORD"]);
	}

	function SetCaptchaCode($sid = false)
	{
		$max = count($this->arChars);

		$this->code = "";
		for ($i = 0; $i < $this->codeLength; $i++)
			$this->code .= $this->arChars[rand(1, $max) - 1];

		$this->sid = $sid===false? $this->Generate32RandomString(): $sid;

		CCaptcha::Add(
			Array(
				"CODE" => $this->code,
				"ID" => $this->sid
			)
		);

	}

	function Generate32RandomString()
	{
		$prefix = (defined("BX_CLUSTER_GROUP")? BX_CLUSTER_GROUP: "0");
		return mb_substr($prefix.md5(uniqid()), 0, 32);
	}

	function InitCaptchaCode($sid)
	{
		global $DB;

		$res = $DB->Query("SELECT CODE FROM b_captcha WHERE ID = '".$DB->ForSQL($sid,32)."' ");
		if (!$ar = $res->Fetch())
		{
			return false;
		}

		$this->code = $ar["CODE"];
		$this->sid = $sid;
		$this->codeLength = mb_strlen($this->code);

		return true;

	}

	function InitCode($sid)
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
			return CCaptcha::InitCaptchaCode($sid);

		if (!is_array(\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]) || empty(\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]))
			return false;

		if (!array_key_exists($sid, \Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]))
			return false;

		$this->code = \Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"][$sid];
		$this->sid = $sid;
		$this->codeLength = mb_strlen($this->code);

		return true;
	}

	function InitCodeCrypt($codeCrypt, $password = "")
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
			return CCaptcha::InitCaptchaCode($codeCrypt);

		if ($codeCrypt == '')
			return false;

		if (!\Bitrix\Main\Application::getInstance()->getSession()->get("CAPTCHA_PASSWORD"))
			return false;

		$this->codeCrypt = $codeCrypt;
		$this->code = $this->CryptData($codeCrypt, "D", \Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_PASSWORD"]);
		$this->codeLength = mb_strlen($this->code);

		return true;
	}

	function GetSID()
	{
		return $this->sid;
	}

	function GetCodeCrypt()
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
			return $this->sid;

		return $this->codeCrypt;
	}

	function CheckCaptchaCode($userCode, $sid, $bUpperCode = true)
	{
		global $DB;

		if ($userCode == '' || $sid == '')
			return false;

		if ($bUpperCode)
			$userCode = mb_strtoupper($userCode);

		$res = $DB->Query("SELECT CODE FROM b_captcha WHERE ID = '".$DB->ForSQL($sid,32)."' ");
		if (!$ar = $res->Fetch())
			return false;

		if ($ar["CODE"] != $userCode)
			return false;

		CCaptcha::Delete($sid);

		return true;
	}

	function CheckCode($userCode, $sid, $bUpperCode = true)
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
			return CCaptcha::CheckCaptchaCode($userCode, $sid, $bUpperCode);

		if (!is_array(\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]) || empty(\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]))
			return false;

		if (!array_key_exists($sid, \Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"]))
			return false;

		if ($bUpperCode)
			$userCode = mb_strtoupper($userCode);

		if (\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"][$sid] != $userCode)
			return false;

		unset(\Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_CODE"][$sid]);

		return true;
	}

	function CheckCodeCrypt($userCode, $codeCrypt, $password = "", $bUpperCode = true)
	{
		if (!defined("CAPTCHA_COMPATIBILITY"))
			return CCaptcha::CheckCaptchaCode($userCode, $codeCrypt, $bUpperCode);

		if ($codeCrypt == '')
			return false;

		if (!\Bitrix\Main\Application::getInstance()->getSession()->get("CAPTCHA_PASSWORD"))
			return false;

		if ($bUpperCode)
			$userCode = mb_strtoupper($userCode);

		$code = $this->CryptData($codeCrypt, "D", \Bitrix\Main\Application::getInstance()->getSession()["CAPTCHA_PASSWORD"]);

		if ($code != $userCode)
			return false;

		return true;
	}

	function CryptData($data, $type, $pwdString)
	{
		$type = strtoupper($type);
		if ($type != "D")
			$type = "E";

		$res_data = "";

		if ($type == 'D')
			$data = base64_decode(urldecode($data));

		$key[] = "";
		$box[] = "";
		$temp_swap = "";
		$pwdLength = mb_strlen($pwdString);

		for ($i = 0; $i <= 255; $i++)
		{
			$key[$i] = ord(mb_substr($pwdString, ($i % $pwdLength), 1));
			$box[$i] = $i;
		}
		$x = 0;

		for ($i = 0; $i <= 255; $i++)
		{
			$x = ($x + $box[$i] + $key[$i]) % 256;
			$temp_swap = $box[$i];
			$box[$i] = $box[$x];
			$box[$x] = $temp_swap;
		}
		$cipher = "";
		$a = 0;
		$j = 0;
		for ($i = 0, $n = mb_strlen($data); $i < $n; $i++)
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$temp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $temp;
			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipherby = ord(mb_substr($data, $i, 1)) ^ $k;
			$cipher .= chr($cipherby);
		}

		if ($type == 'D')
			$res_data = urldecode(urlencode($cipher));
		else
			$res_data = urlencode(base64_encode($cipher));

		return $res_data;
	}


	public function Add($arFields)
	{
		global $DB;

		if (!is_set($arFields, "CODE") || $arFields["CODE"] == '')
			return false;

		if (!is_set($arFields, "ID") || $arFields["ID"] == '')
			$arFields["ID"] = $this->Generate32RandomString();

		if (!is_set($arFields, "IP") || $arFields["IP"] == '')
			$arFields["IP"] = $_SERVER["REMOTE_ADDR"];

		if (!is_set($arFields, "DATE_CREATE") || $arFields["DATE_CREATE"] == '' || !$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL"))
		{
			unset($arFields["DATE_CREATE"]);
			$arFields["~DATE_CREATE"] = CDatabase::CurrentTimeFunction();
		}

		$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
		$pool->useMasterOnly(true);

		$arInsert = $DB->PrepareInsert("b_captcha", $arFields);

		$result = $DB->Query("INSERT INTO b_captcha (".$arInsert[0].") VALUES (".$arInsert[1].")", true);

		$pool->useMasterOnly(false);

		if($result)
		{
			return $arFields["ID"];
		}
		return false;
	}

	function Delete($sid)
	{
		global $DB;

		if (!$DB->Query("DELETE FROM b_captcha WHERE ID='".$DB->ForSQL($sid, 32)."' "))
			return false;

		return true;

	}
}

endif;
