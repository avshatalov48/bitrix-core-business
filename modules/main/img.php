<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/*******************************************************
Converts ISO to UNICODE
********************************************************/

function iso2uni ($isoline)
{
	$uniline = "";
	for ($i = 0, $n = mb_strlen($isoline); $i < $n; $i++)
	{
		$thischar = mb_substr($isoline, $i, 1);
		$charcode = ord($thischar);
		$uniline .= ($charcode>175) ? "&#" . (1040+($charcode-176)). ";" : $thischar;
	}
	return $uniline;
}

/*******************************************************
Creates image to draw on
********************************************************/

function CreateImageHandle($width, $height, $background="FFFFFF", $truecolor=true)
{
	if($truecolor)
	{
		$im = ImageCreateTrueColor($width,$height);
	}
	else
	{
		$im = ImageCreate($width,$height);
	}
	if (!$im)
	{
		die ("Cannot Initialize GD image stream");
	}
	else
	{
		$dec = ReColor($background);
		ImageColorAllocate ($im, $dec[0], $dec[1], $dec[2]);
	}
	return $im;
}

/******************************************************
Send proper headers for image
*******************************************************/
function ShowImageHeader($ImageHandle)
{
	if (ImageTypes() & IMG_PNG)
	{
		Header("Content-type: image/png");
		ImagePng($ImageHandle);
	}
	elseif(ImageTypes() & IMG_GIF)
	{
		Header("Content-type: image/gif");
		ImageGif($ImageHandle);
	}
	elseif (ImageTypes() & IMG_JPEG)
	{
		Header("Content-type: image/jpeg");
		ImageJpeg($ImageHandle, "", 0.5);
	}
	else
	{
		die("No images support");
	}
	ImageDestroy ($ImageHandle);
}

/******************************************************
Returns some color
*******************************************************/

function GetArrSaveDecColor($arr)
{
	$arrSaveDecColor = array();
	foreach($arr as $key => $scolor)
	{
		$arrSaveDecColor[$key] = hexdec($scolor);
	}
	asort($arrSaveDecColor);
	return $arrSaveDecColor;
}

function GetNextRGB($base_color, $total)
{
	$arrSaveColor = getSafeColors();

	$tsc = count($arrSaveColor);
	if ($total > $tsc)
	{
		return GetBNextRGB($base_color, $total);
	}
	elseif ($base_color == '')
	{
		$res = "1288A0";
	}
	else
	{
		$index = 0;
		$step = round($tsc/$total);
		$dec = hexdec($base_color);
		$arrSaveDecColor = GetArrSaveDecColor($arrSaveColor);
		foreach($arrSaveDecColor as $key => $sdcolor)
		{
			if ($dec <= $sdcolor)
			{
				$index = $key;
				break;
			}
		}
		$index = intval($index);
		$tsc = $tsc-1;
		if ($index + $step > $tsc)
		{
			$rkey = ($index + $step) - $tsc;
		}
		else
		{
			$rkey = $index + $step;
		}
		$res = $arrSaveColor[$rkey];
	}
	return $res;
}

function GetBNextRGB($base_color, $total, $start_color = "999900", $end_color = "99FFFF")
{
	$step = round((hexdec($end_color) - hexdec($start_color)) / $total);
	$dec = intval(hexdec($base_color)) + intval($step);

	if ($dec < hexdec($start_color) - $step)
	{
		$dec = $start_color;
	}
	elseif ($dec > hexdec($end_color) + $step)
	{
		$dec = $end_color;
	}
	elseif ($dec > hexdec("FFFFFF"))
	{
		$dec = "000000";
	}
	else
	{
		$dec = sprintf("%06X", $dec);
	}

	return $dec;
}

/*******************************************************
Graph data debug
*******************************************************/

function EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY, $die=true)
{
	echo "<pre>";
	echo "--------------------------------------\n";
	foreach($arrX as $key => $value)
	{
		echo date("d.m.Y",$value)." = ".$arrY[$key]."\n";
	}
	echo "--------------------------------------\n";
	echo "Signs of X axis (arrayX):\n";
	print_r($arrayX);
	echo "MinX: ".$MinX." - ".date("d.m",$MinX)."\n";
	echo "MaxX: ".$MaxX." - ".date("d.m",$MaxX)."\n\n";
	echo "Signs of Y axis (arrayY):\n";
	print_r($arrayY);
	echo "MinY: ".$MinY."\n";
	echo "MaxY: ".$MaxY."\n\n";
	echo "Values of X axis (arrX):\n";
	$i = 0;
	foreach ($arrX as $d)
	{
		echo "[".$i."] => ".GetTime($d)." (".$d.")"."\n";
		$i++;
	}
	echo "\nValues of Y axis (arrY):\n";
	print_r($arrY);
	echo "--------------------------------------\n";
	echo "</pre>";
	if ($die)
	{
		die();
	}
}

/*******************************************************
Makes proper X axis (date)
*******************************************************/
function GetArrayX($arrX, &$MinX, &$MaxX, $max_grid=15, $min_grid=10)
{
	$h = 2;

	$MinX = (count($arrX)>0) ? min($arrX) : 0;
	$MaxX = (count($arrX)>0) ? max($arrX) : 0;
	$period_days = (($MaxX-$MinX)/86400)+1;
	if ($period_days>$min_grid)
	{
		$h = $min_grid;
	}
	if ($max_grid<$h)
	{
		$max_grid = $h;
	}
	$arrOst = array();
	for ($i=$max_grid; $i>=$h; $i--)
	{
		$ost = $period_days%$i;
		$arrOst[$i] = $ost;
		if ($ost == 0)
		{
			break;
		}
	}
	$minOst = min($arrOst);
	$shiftX = ($period_days/array_search($minOst, $arrOst));
	$shiftX = $shiftX*86400;
	$unix_date = $MinX;
	if(preg_match("/(DD|MM)(.*?)(DD|MM)/",FORMAT_DATE,$arMatch))
	{
		$strFrmt = str_replace(array("DD","MM"), array("d","m"), $arMatch[0]);
	}
	else
	{
		$strFrmt = "d.m";
	}
	$prev_date = "";
	$tmp_arrX = array();
	$arrayX = array();
	while ($unix_date < $MaxX+$shiftX)
	{
		if ($prev_date == date("d.m.Y", $unix_date))
		{
			$unix_date += 3600;
		}
		$date = date($strFrmt, $unix_date);
		$arrayX[] = $date;
		$tmp_arrX[] = $unix_date;
		$unix_date += $shiftX;
		$prev_date = date("d.m.Y", $unix_date);
	}

	$MinX = MkDateTime(date("d.m.Y", min($tmp_arrX)),"d.m.Y");
	$MaxX = MkDateTime(date("d.m.Y", max($tmp_arrX)),"d.m.Y");

	return $arrayX;
}


function GetArrayY($arrY, &$MinY, &$MaxY, $max_grid=15, $first_null="Y", $integers=false)
{
	$arrayY = array();
	$arrY = array_unique($arrY);
	if ($first_null=="Y")
	{
		$arrY[] = 0;
	}
	asort($arrY);
	$MinY = min($arrY);
	$MaxY = max($arrY);
	if ($MinY==$MaxY)
	{
		if ($MinY!=0)
		{
			$arrayY[] = 0;
		}
		$arrayY[] = $MinY;
		$arrayY[] = $MaxY+1;
		asort($arrayY);
	}
	else
	{
		$shiftY = round(($MaxY-$MinY)/$max_grid);
		if($shiftY<=0)
		{
			if($integers==false)
			{
				$shiftY = round(($MaxY-$MinY)/$max_grid,3);
			}
			else
			{
				$shiftY = 1;
			}
		}
		$i = $MinY;
		if ($shiftY>0)
		{
			while ($i<$MaxY+$shiftY+$shiftY)
			{
				$arrayY[] = $i;
				$i += $shiftY;
			}
		}
		else
		{
			for ($i=$MinY; $i<=$MaxY+$shiftY+$shiftY; $i++)
			{
				$arrayY[] = $i;
			}
		}
	}
	$MinY = min($arrayY);
	$MaxY = max($arrayY);
	return $arrayY;
}

/******************************************************************************
* $colorString - Color. Example 'FFFFFF' or '#FF0000'
* ReColor - function converting HEX to DEC color
******************************************************************************/
function ReColor($colorString)
{
	if (!is_string($colorString))
		return 0;

	if (!preg_match('/^#{0,1}([0-9a-z]{2})([0-9a-z]{2})([0-9a-z]{2})$/i', $colorString, $match))
		return 0;

	return array(
		hexdec($match[1]),
		hexdec($match[2]),
		hexdec($match[3]),
	);
}

function DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle, $bgColor="FFFFFF", $gColor='B1B1B1', $Color="000000", $dD=15, $FontWidth=2, $arrTTF_FONT=false)
{
	global $xA, $yA, $xPixelLength, $yPixelLength;

	/******************************************************************************
	* $k - array performance font size to pixel format
	* array index == font size
	******************************************************************************/
	$k = array();
	$k[1]=5;
	$k[2]=2.7;
	$k[3]=2.3;
	$k[4]=2;
	$k[5]=1.7;
	$k[6]=1.5;
	$k[7]=1.3;
	$k[8]=1.1;
	$k[9]=1;
	$k[10]=0.85;
	$k[11]=0.75;
	$k[12]=0.7;
	$k[13]=0.65;
	$k[14]=0.60;
	$k[15]=0.55;
	$k[16]=0.52;

	$arResult = array();

	$max_len=0;

	$bUseTTFY = false;
	$bUseTTFX = false;

	if(is_array($arrTTF_FONT) && function_exists("ImageTTFText"))
	{
		$bUseTTFY = is_array($arrTTF_FONT["Y"] ?? null);
		$bUseTTFX = is_array($arrTTF_FONT["X"] ?? null);
	}

	$ttf_font_y = "";
	$ttf_size_y = $ttf_shift_y = $ttf_base_y = 0;

	if ($bUseTTFY)
	{
		$ttf_font_y = $_SERVER["DOCUMENT_ROOT"].$arrTTF_FONT["Y"]["FONT_PATH"];
		$ttf_size_y = $arrTTF_FONT["Y"]["FONT_SIZE"];
		$ttf_shift_y = $arrTTF_FONT["Y"]["FONT_SHIFT"];
		$ttf_base_y = 0;
		if (isset($arrTTF_FONT["Y"]["FONT_BASE"])) $ttf_base_y = $arrTTF_FONT["Y"]["FONT_BASE"];
		$dlataX = 0;
		foreach($arrayY as $value)
		{
			$bbox = imagettfbbox($ttf_size_y, 0, $ttf_font_y, $value);
			$dlataX = max($dlataX, abs($bbox[2] - $bbox[0]) + 1);
		}
	}
	else
	{
		foreach($arrayY as $value)
			$max_len=max($max_len, mb_strlen($value));
		$dlataX = $max_len*ImageFontWidth($FontWidth);
	}

	$arr_bgColor = ReColor($bgColor);
	$colorFFFFFF = ImageColorAllocate($ImageHandle,$arr_bgColor[0],$arr_bgColor[1],$arr_bgColor[2]);

	$arr_Color = ReColor($Color);
	$color000000 = ImageColorAllocate($ImageHandle,$arr_Color[0],$arr_Color[1],$arr_Color[2]);

	$arr_gColor = ReColor($gColor);
	$colorCOCOCO = ImageColorAllocate($ImageHandle,$arr_gColor[0], $arr_gColor[1], $arr_gColor[2]);

	ImageFill($ImageHandle, 0, 0, $colorFFFFFF);

	$bForBarDiagram = is_array($arrTTF_FONT) && (($arrTTF_FONT["type"] ?? '') == "bar");
	if($bForBarDiagram)
	{
		$arResult["XBUCKETS"] = array();
	}

/*
	C
	|
	|
	|
	|
	|__________________
	A                 B
*/

	$ttf_font_x = "";
	$ttf_size_x = $ttf_shift_x = $ttf_base_x = 0;

	$xA = $dD+$dlataX;
	if ($bUseTTFX)
	{
		$ttf_font_x = $_SERVER["DOCUMENT_ROOT"].$arrTTF_FONT["X"]["FONT_PATH"];
		$ttf_size_x = $arrTTF_FONT["X"]["FONT_SIZE"];
		$ttf_shift_x = $arrTTF_FONT["X"]["FONT_SHIFT"];
		if (isset($arrTTF_FONT["X"]["FONT_BASE"]))
		{
			$ttf_base_x = $arrTTF_FONT["X"]["FONT_BASE"];
		}
		$yA = $height-$dD-$ttf_shift_x;
	}
	else
	{
		$yA = $height-$dD-ImageFontHeight($FontWidth)/2;
	}

	$xC = $xA;
	$yC = $dD;

	$xB = $width-$dD;
	$yB = $yA;

	$GrafWidth = $xB - $xA;
	$GrafHeight = $yA - $yC;

	$PointsX = max(sizeof($arrayX)+$bForBarDiagram, 2);
	$PointsY = max(sizeof($arrayY), 2);

	$dX = $GrafWidth/($PointsX-1);
	$dY = $GrafHeight/($PointsY-1);

/*
	C	P1
	|	|
	|	|
	|	|
	|	|
	|___|______________
	A	P0				B
*/

	$i=0;
	$xP0 = $xA;
	$yP0 = $yA;
	$yP1 = $yC;
	while ($i < $PointsX)
	{
		if ($i==$PointsX-1)
		{
			$xP0 = $xB;
		}
		$style = array (
			$colorCOCOCO,
			IMG_COLOR_TRANSPARENT,
			IMG_COLOR_TRANSPARENT,
			);
		ImageSetStyle($ImageHandle, $style);
		ImageLine($ImageHandle, ceil($xP0), ceil($yP0), ceil($xP0), ceil($yP1),  IMG_COLOR_STYLED);

		if($bForBarDiagram)
			$arResult["XBUCKETS"][$i] = array(ceil($xP0)+1, ceil($xP0+$dX)-1);

		$captionX = $arrayX[$i];
		$xCaption = $xP0 - mb_strlen($captionX) * $k[$FontWidth] + ($dX*$bForBarDiagram/2);
		$yCaption = $yP0;

		if ($bUseTTFX)
		{
			$bbox = imagettfbbox($ttf_size_x, 0, $ttf_font_x, $captionX);
			$ttf_width_x = abs($bbox[2] - $bbox[0]) + 1;
			$xCaption = $xP0 - $ttf_width_x/2 + ($dX*$bForBarDiagram/2);
			$yCaption = $yP0 + $dD + $ttf_shift_x - $ttf_base_x;
			$captionX = \Bitrix\Main\Text\Encoding::convertEncoding($captionX, LANG_CHARSET, "UTF-8");
			ImageTTFText($ImageHandle, $ttf_size_x, 0, $xCaption, $yCaption, $color000000, $ttf_font_x, $captionX);
		}
		else ImageString($ImageHandle, $FontWidth, $xCaption, $yCaption+ImageFontHeight($FontWidth)/2, $captionX, $color000000);

		$xP0 += $dX;
		$i++;
	}

/*
   C
   |
   |
   |
 M1|___________________	M0
   |___________________
   A					B
*/

	$i=0;
	$xM0 = $xB;
	$yM0 = $yB;
	$xM1 = $xA;
	$yM1 = $yA;
	while ($i < $PointsY)
	{
		if ($i==$PointsY-1)
		{
			$yM0 = $yC;
			$yM1 = $yC;
		}
		if ($yM1>0 && $yM0>0)
		{
			$style = array (
				$colorCOCOCO,
				IMG_COLOR_TRANSPARENT,
				IMG_COLOR_TRANSPARENT,
				);
			ImageSetStyle($ImageHandle, $style);
			ImageLine($ImageHandle, ceil($xM0), ceil($yM0), ceil($xM1), ceil($yM1), IMG_COLOR_STYLED);
			$captionY = $arrayY[$i];
			$xCaption = $dlataX;
			$yCaption = $yM1-$k[$FontWidth]*3;

			if ($bUseTTFY)
			{
				$captionY = \Bitrix\Main\Text\Encoding::convertEncoding($captionY, LANG_CHARSET, "UTF-8");
				$bbox = imagettfbbox($ttf_size_y, 0, $ttf_font_y, $captionY);
				$yCaption = $yM1+($ttf_shift_y-$ttf_base_y)/2;
				ImageTTFText($ImageHandle, $ttf_size_y, 0, $xCaption-abs($bbox[2]-$bbox[0])-1, $yCaption, $color000000, $ttf_font_y, $captionY);
			}
			else ImageString($ImageHandle, $FontWidth, $xCaption- mb_strlen($captionY) * ImageFontWidth($FontWidth), $yCaption, $captionY, $color000000);
		}
		$yM0 -= $dY;
		$yM1 -= $dY;
		$i++;
	}

	ImageLine($ImageHandle, ceil($xA), ceil($yA), ceil($xC), ceil($yC), $color000000);
	ImageLine($ImageHandle, ceil($xB), ceil($yB), ceil($xA), ceil($yA), $color000000);

	$xPixelLength = $xB - $xA;
	$yPixelLength = $yA - $yC;

	$arResult["VIEWPORT"] = array(ceil($xA), ceil($yA), ceil($xB), ceil($yC));

	return $arResult;
}

function Bar_Diagram($ImageHandle, $arData, $MinY, $MaxY, $gridInfo)
{
	$max_y = 0;
	foreach($arData as $arRecs)
	{
		$y = max($arRecs["DATA"]);
		if($y > $max_y)
			$max_y = $y;
	}
	$scale = ($gridInfo["VIEWPORT"][1] - $gridInfo["VIEWPORT"][3]) / ($MaxY - $MinY);

	$xIndex = 0;
	foreach($arData as $arRecs)
	{
		$arPair = $gridInfo["XBUCKETS"][$xIndex];
		if (is_array($arPair))
		{
			$bar_count = count($arRecs["DATA"]);
			$bar_width = ceil(($arPair[1] - $arPair[0] - 1) * 0.7 / $bar_count);
			$ws_width = round((($arPair[1] - $arPair[0] - 1) - ($bar_width * $bar_count)) / ($bar_count + 1));

			foreach($arRecs["DATA"] as $i => $Y)
			{
				$arColor = ReColor($arRecs["COLORS"][$i][0]);
				$color = ImageColorAllocate($ImageHandle, $arColor[0], $arColor[1], $arColor[2]);

				$x1 = $arPair[0] + $ws_width + ($bar_width + $ws_width)*$i;
				$y1 = round($Y*$scale);

				if($y1 > 0)
				{
					imagefilledrectangle($ImageHandle,
						$x1,
						$gridInfo["VIEWPORT"][1]-$y1,
						$x1 + $bar_width,
						$gridInfo["VIEWPORT"][1]-1,
						$color);
				}
			}
		}
		$xIndex++;
	}
}

function Graf($arrayX, $arrayY, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $Color='FF0000', $dashed="N", $thikness=2, $antialiase=true)
{
	global $xA, $yA, $xPixelLength, $yPixelLength;

	if(sizeof($arrayX) != sizeof($arrayY))
	{
		return;
	}

	$arr_Color = ReColor($Color);
	$color = ImageColorAllocate($ImageHandle, $arr_Color[0], $arr_Color[1], $arr_Color[2]);

	$xGrafLength = $MaxX - $MinX;
	$yGrafLength = $MaxY - $MinY;

	if($antialiase)
	{
		$bgcolor = imagecolorallocate($ImageHandle, 255, 255, 255);
		$fgcolors = imagecolorsforindex($ImageHandle, $color);
		$bgcolors = imagecolorsforindex($ImageHandle, $bgcolor);
		for( $i = 0; $i < 100; $i++ )
		{
			imagecolorallocate(
				$ImageHandle,
				($fgcolors['red'] + $i*$bgcolors['red'])/($i + 1),
				($fgcolors['green'] + $i*$bgcolors['green'])/($i + 1),
				($fgcolors['blue'] + $i*$bgcolors['blue'])/($i + 1)
			);
		}
	}

	$x1 = $y1 = $x2 = $y2 = 0;

	for($i = 0, $n = sizeof($arrayX)-1; $i < $n; $i++)
	{
		if ($xGrafLength>0)
		{
			$x1 = $xA + ((($arrayX[$i]-$MinX) * $xPixelLength) / $xGrafLength);
			$x2 = $xA + ((($arrayX[$i+1]-$MinX) * $xPixelLength) / $xGrafLength);
		}

		if ($yGrafLength>0)
		{
			$y1 = $yA - ((($arrayY[$i]-$MinY) * $yPixelLength) / $yGrafLength);
			$y2 = $yA - ((($arrayY[$i+1]-$MinY) * $yPixelLength) / $yGrafLength);
		}

		$x1 = ceil($x1);
		$y1 = ceil($y1);
		$x2 = ceil($x2);
		$y2 = ceil($y2);

		if($antialiase)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			_a_draw_line($ImageHandle, $x1, $y1, $x2, $y2, $fgcolors, $dashed, 10, 4);
			if($thikness>1)
			{
				if($y1<$y2)
				{
					_a_draw_line($ImageHandle, $x1-0.4, $y1+0.4, $x2-0.4, $y2+0.4, $fgcolors, $dashed, 10, 4);
					_a_draw_line($ImageHandle, $x1+0.4, $y1-0.4, $x2+0.4, $y2-0.4, $fgcolors, $dashed, 10, 4);
				}
				else
				{
					_a_draw_line($ImageHandle, $x1+0.4, $y1+0.4, $x2+0.4, $y2+0.4, $fgcolors, $dashed, 10, 4);
					_a_draw_line($ImageHandle, $x1-0.4, $y1-0.4, $x2-0.4, $y2-0.4, $fgcolors, $dashed, 10, 4);
				}
			}
		}
		elseif($dashed=="Y")
		{
			$style = array (
				$color,$color,
				IMG_COLOR_TRANSPARENT,
				IMG_COLOR_TRANSPARENT,
				IMG_COLOR_TRANSPARENT
			);
			ImageSetStyle($ImageHandle, $style);
			ImageLine($ImageHandle, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
		}
		else
		{
			ImageLine($ImageHandle, $x1, $y1, $x2, $y2, $color);
		}
	}
}

function Draw_Sector($ImageHandle, $start, $end, $color, $diameter, $centerX, $centerY)
{
	$radius = $diameter/2;
	$dec = ReColor($color);
	$color = ImageColorAllocate ($ImageHandle, $dec[0], $dec[1], $dec[2]);

	imagearc($ImageHandle, $centerX, $centerY, $diameter, $diameter, 0, 360, $color);

	$startX = $centerX + cos(deg2rad($start)) * $radius;
	$startY = $centerY + sin(deg2rad($start)) * $radius;
	imageline($ImageHandle, $centerX, $centerY, $startX, $startY, $color);

	$endX = $centerX + cos(deg2rad($end)) * $radius;
	$endY = $centerY + sin(deg2rad($end)) * $radius;
	imageline($ImageHandle, $centerX, $centerY, $endX, $endY, $color);

	$diff = intval($end - $start);
	if ($diff < 180)
	{
		$x = ($centerX+(($startX + $endX)/2))/2;
		$y = ($centerY+(($startY + $endY)/2))/2;
	}
	else
	{
		$m_end = $start + $diff/2;
		$m_X = $centerX + cos(deg2rad($m_end)) * $radius;
		$m_Y = $centerY + sin(deg2rad($m_end)) * $radius;
		$x = ($centerX+$m_X)/2;
		$y = ($centerY+$m_Y)/2;
		//ImageString($ImageHandle, 2, 30, 30, $m_end, $color);
		//imagesetpixel($ImageHandle, $m_X, $m_Y, ImageColorAllocate($ImageHandle,"FF", "00", "00"));
	}
	imagefill ($ImageHandle, $x, $y, $color);
	//imagesetpixel($ImageHandle, $x, $y, $color);
}

function Circular_Diagram($ImageHandle, $arr, $background_color, $diameter, $centerX, $centerY, $antialiase=true)
{
	if($antialiase)
	{
		$ImageHandle_Saved = $ImageHandle;
		$diameter_saved = $diameter;
		$diameter=$diameter*5;
		$centerX=$centerX*5;
		$centerY=$centerY*5;
		$ImageHandle = CreateImageHandle($diameter, $diameter, "FFFFFF", true);
		imagefill($ImageHandle, 0, 0, imagecolorallocate($ImageHandle, 255,255,255));
	}
	$arr2 = array();
	$diameterX = $diameter;
	$diameterY = intval($diameter*0.6);
	if(count($arr)>0)
	{
		$sum = 0;
		foreach($arr as $sector)
		{
			$sum += $sector["COUNTER"];
		}
		$degree1=0;
		$p=0.0;
		$i=0;
		foreach($arr as $sector)
		{
			$p += $sector["COUNTER"]/$sum*360.0;
			++$i;
			if ($i==count($arr))
			{
				$degree2 = 360;
			}
			else
			{
				$degree2 = intval($p);
			}
			if($degree2 > $degree1)
			{
				$dec = ReColor($sector["COLOR"]);
				$arr2[] = array(
					"DEGREE_1"	=> $degree1,
					"DEGREE_2"	=> $degree2,
					"COLOR"		=> $sector["COLOR"],
					"IMAGE_COLOR"	=> ImageColorAllocate ($ImageHandle, $dec[0], $dec[1], $dec[2]),
					"IMAGE_DARK"	=> ImageColorAllocate ($ImageHandle, $dec[0]/1.5, $dec[1]/1.5, $dec[2]/1.5),
					);
				$degree1 = $degree2;
			}
		}
		if(count($arr2)>0)
		{
			$h = 15;
			if($antialiase)
			{
				$h = $h * 5;
			}
			for($i = 0; $i <= $h; $i++)
			{
				foreach($arr2 as $sector)
				{
					$degree1 = $sector["DEGREE_1"];
					$degree2 = $sector["DEGREE_2"];
					$difference = $degree2 - $degree1;
					$degree1 -= 180;
					$degree1 = $degree1<0?360+$degree1:$degree1;
					$degree2 -= 180;
					$degree2 = $degree2<0?360+$degree2:$degree2;
					$color = $i==$h?$sector["IMAGE_COLOR"]:$sector["IMAGE_DARK"];
					if ($difference==360)
						imageellipse($ImageHandle, $centerX, $centerY-$i, $diameterX, $diameterY, $color);
					else
						imagearc($ImageHandle, $centerX, $centerY-$i, $diameterX, $diameterY, $degree1, $degree2, $color);
				}
			}
			$i--;
			foreach($arr2 as $sector)
			{
				$degree1 = $sector["DEGREE_1"];
				$degree2 = $sector["DEGREE_2"];
				$difference = $degree2 - $degree1;
				$degree1 -= 180;
				$degree1 = $degree1<0?360+$degree1:$degree1;
				$degree2 -= 180;
				$degree2 = $degree2<0?360+$degree2:$degree2;
				$color = $i==$h?$sector["IMAGE_COLOR"]:$sector["IMAGE_DARK"];
				if ($difference==360)
					imagefilledellipse($ImageHandle, $centerX, $centerY-$i, $diameterX, $diameterY, $color);
				else
				{
					imagefilledarc($ImageHandle, $centerX, $centerY-$i, $diameterX, $diameterY, $degree1, $degree2, $color, IMG_ARC_PIE);
				}
			}
		}
	}
	else
	{
		$dec = ReColor($background_color);
		$color= ImageColorAllocate ($ImageHandle, $dec[0], $dec[1], $dec[2]);
		imagefilledellipse($ImageHandle, $centerX, $centerY, $diameterX, $diameterY, $color);
	}
	if($antialiase)
	{
		/** @noinspection PhpUndefinedVariableInspection */
		imagecopyresampled($ImageHandle_Saved, $ImageHandle, 0, 0, 0, 0, $diameter_saved, $diameter_saved, $diameter, $diameter);
	}
}

function Clean_Circular_Diagram($ImageHandle, $background_color, $diameter, $centerX, $centerY)
{
	$dec = ReColor($background_color);
	$color = ImageColorAllocate ($ImageHandle, $dec[0], $dec[1], $dec[2]);
	for($i=0;$i<=$diameter;$i++) imagearc($ImageHandle, $centerX, $centerY, $diameter+$i, $diameter+$i, 0, 360, $color);
}

function _a_set_pixel($im, $x, $y, $filled, $fgcolors)
{
	$rgb=imagecolorat($im, $x, $y);
	$r = ($rgb >> 16) & 0xFF;
	$g = ($rgb >> 8) & 0xFF;
	$b = $rgb & 0xFF;

	$red = round($r + ( $fgcolors['red'] - $r ) * $filled);
	$green = round($g + ( $fgcolors['green'] - $g ) * $filled);
	$blue = round($b + ( $fgcolors['blue'] - $b ) * $filled);
	imagesetpixel($im, $x, $y, imagecolorclosest($im, $red, $green, $blue));
}

function _a_frac($x)
{
	$x = doubleval($x);
	return $x-floor($x);
}

function _a_draw_line($im, $x1, $y1, $x2, $y2, $fgcolors, $dashed="N", $dash=5, $white=2)
{
	$xd = $x2-$x1;
	$yd = $y2-$y1;
	if($xd==0 && $yd==0)
	{
		return;
	}
	if(abs($xd)>abs($yd))
	{
		$wasexchange = false;
	}
	else
	{
		$wasexchange = true;
		$tmpreal = $x1;
		$x1 = $y1;
		$y1 = $tmpreal;
		$tmpreal = $x2;
		$x2 = $y2;
		$y2 = $tmpreal;
		$tmpreal = $xd;
		$xd = $yd;
		$yd = $tmpreal;
	}
	if( $x1>$x2 )
	{
		$tmpreal = $x1;
		$x1 = $x2;
		$x2 = $tmpreal;
		$tmpreal = $y1;
		$y1 = $y2;
		$y2 = $tmpreal;
		$xd = $x2-$x1;
		$yd = $y2-$y1;
	}
	$grad = $yd/$xd;
	$xend = floor($x1+0.5);
	$yend = $y1+$grad*($xend-$x1);
	$xgap = 1-_a_frac($x1+0.5);
	$ix1 = floor($x1+0.5);
	$iy1 = floor($yend);
	$brightness1 = (1-_a_frac($yend))*$xgap;
	$brightness2 = _a_frac($yend)*$xgap;
	if( $wasexchange )
	{
		_a_set_pixel($im, $iy1, $ix1, $brightness1, $fgcolors);
		_a_set_pixel($im, $iy1+1, $ix1, $brightness2, $fgcolors);
	}
	else
	{
		_a_set_pixel($im, $ix1, $iy1, $brightness1, $fgcolors);
		_a_set_pixel($im, $ix1, $iy1+1, $brightness2, $fgcolors);
	}
	$yf = $yend+$grad;
	$xend = floor($x2+0.5);
	$yend = $y2+$grad*($xend-$x2);
	$xgap = 1-_a_frac($x2-0.5);
	$ix2 = floor($x2+0.5);
	$iy2 = floor($yend);
	$brightness1 = (1-_a_frac($yend))*$xgap;
	$brightness2 = _a_frac($yend)*$xgap;
	if( $wasexchange )
	{
		_a_set_pixel($im, $iy2, $ix2, $brightness1, $fgcolors);
		_a_set_pixel($im, $iy2+1, $ix2, $brightness2, $fgcolors);
	}
	else
	{
		_a_set_pixel($im, $ix2, $iy2, $brightness1, $fgcolors);
		_a_set_pixel($im, $ix2, $iy2+1, $brightness2, $fgcolors);
	}
	$kk=0;
	for($x = $ix1+1; $x <= $ix2-1; $x++)
	{
		if(($kk % $dash)<($dash-$white))
		{
		$brightness1 = 1-_a_frac($yf);
		$brightness2 = _a_frac($yf);
		if( $wasexchange )
		{
			_a_set_pixel($im, floor($yf), $x, $brightness1, $fgcolors);
			_a_set_pixel($im, floor($yf)+1, $x, $brightness2, $fgcolors);
		}
		else
		{
			_a_set_pixel($im, $x, floor($yf), $brightness1, $fgcolors);
			_a_set_pixel($im, $x, floor($yf)+1, $brightness2, $fgcolors);
		}
		}
		$yf = $yf+$grad;
		if($dashed=="Y")
			++$kk;
	}
}
function _a_draw_ellipse($im, $x1, $y1, $x2, $y2, $fgcolors, $half=false)
{
	if( $x2<$x1 )
	{
		$t = $x1;
		$x1 = $x2;
		$x2 = $t;
	}
	if( $y2<$y1 )
	{
		$t = $y1;
		$y1 = $y2;
		$y2 = $t;
	}
	if( $x2-$x1<$y2-$y1 )
	{
		$exch = false;
	}
	else
	{
		$exch = true;
		$t = $x1;
		$x1 = $y1;
		$y1 = $t;
		$t = $x2;
		$x2 = $y2;
		$y2 = $t;
	}
	$a = ($x2-$x1)/2;
	$b = ($y2-$y1)/2;
	$cx = ($x1+$x2)/2;
	$cy = ($y1+$y2)/2;
	$t = $a*$a/sqrt($a*$a+$b*$b);
	$i1 = floor($cx-$t);
	$i2 = ceil($cx+$t);
	for($ix = $i1; $ix <= $i2; $ix++)
	{
		if( 1-pow(($ix-$cx)/$a, 2)<0 )
		{
			continue;
		}
		$y = $b*sqrt(1-pow(($ix-$cx)/$a, 2));
		$iy = ceil($cy+$y);
		$f = $iy-$cy-$y;
		if( !$exch )
		{
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix, $iy, 1-$f, $fgcolors);
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix, $iy-1, $f, $fgcolors);
		}
		else
		{
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy, $ix, 1-$f, $fgcolors);
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy-1, $ix, $f, $fgcolors);
		}
		$iy = floor($cy-$y);
		$f = $cy-$y-$iy;
		if( !$exch )
		{
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix, $iy+1, $f, $fgcolors);
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix, $iy, 1-$f, $fgcolors);
		}
		else
		{
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy+1, $ix, $f, $fgcolors);
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy, $ix, 1-$f, $fgcolors);
		}
	}
	$t = $b*$b/sqrt($a*$a+$b*$b);
	$i1 = ceil($cy-$t);
	$i2 = floor($cy+$t);
	for($iy = $i1; $iy <= $i2; $iy++)
	{
		if( 1-pow(($iy-$cy)/$b, 2)<0 )
		{
			continue;
		}
		$x = $a*sqrt(1-pow(($iy-$cy)/$b, 2));
		$ix = floor($cx-$x);
		$f = $cx-$x-$ix;
		if( !$exch )
		{
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix, $iy, 1-$f, $fgcolors);
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix+1, $iy, $f, $fgcolors);
		}
		else
		{
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy, $ix, 1-$f, $fgcolors);
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy, $ix+1, $f, $fgcolors);
		}
		$ix = ceil($cx+$x);
		$f = $ix-$cx-$x;
		if( !$exch )
		{
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix, $iy, 1-$f, $fgcolors);
			if(!$half || $iy>$cx) _a_set_pixel($im, $ix-1, $iy, $f, $fgcolors);
		}
		else
		{
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy, $ix, 1-$f, $fgcolors);
			if(!$half || $ix>$cx) _a_set_pixel($im, $iy, $ix-1, $f, $fgcolors);
		}
	}
}

function getSafeColors()
{
	static $colors = array(
		"CCCCCC",
		"999999",
		"FF0000",
		"FF3333",
		"CC0000",
		"FF6666",
		"CC3333",
		"990000",
		"FF9999",
		"CC6666",
		"993333",
		"FFCCCC",
		"CC9999",
		"996666",
		"FF3300",
		"FF6633",
		"CC3300",
		"FF9966",
		"CC6633",
		"993300",
		"FF6600",
		"FF9933",
		"CC6600",
		"FFCC99",
		"CC9966",
		"996633",
		"FF9900",
		"FFCC66",
		"CC9933",
		"996600",
		"CC9900",
		"FFCC33",
		"FFCC00",
		"FFFF00",
		"FFFF33",
		"CCCC00",
		"FFFF66",
		"CCCC33",
		"999900",
		"FFFF99",
		"CCCC66",
		"999933",
		"FFFFCC",
		"CCCC99",
		"999966",
		"A2CA00",
		"CCFF33",
		"99CC00",
		"CCFF66",
		"99CC33",
		"669900",
		"99FF00",
		"99FF33",
		"66CC00",
		"73E600",
		"99CC66",
		"669933",
		"66FF00",
		"99FF66",
		"66CC33",
		"339900",
		"66FF33",
		"33CC00",
		"33FF00",
		"00FF00",
		"33FF33",
		"00CC00",
		"66FF66",
		"33CC33",
		"009900",
		"99FF99",
		"66CC66",
		"339933",
		"00AA00",
		"99CC99",
		"669966",
		"00FF33",
		"33FF66",
		"00CC33",
		"66FF99",
		"33CC66",
		"009933",
		"00FF66",
		"33FF99",
		"00CC66",
		"99FFCC",
		"66CC99",
		"339966",
		"00FF99",
		"66FFCC",
		"33CC99",
		"009966",
		"33FFCC",
		"00CC99",
		"00FFCC",
		"00FFFF",
		"33FFFF",
		"00CCCC",
		"66FFFF",
		"33CCCC",
		"009999",
		"99FFFF",
		"66CCCC",
		"339999",
		"CCFFFF",
		"99CCCC",
		"669999",
		"00CCFF",
		"33CCFF",
		"0099CC",
		"66CCFF",
		"3399CC",
		"006699",
		"0099FF",
		"3399FF",
		"0066CC",
		"99CCFF",
		"6699CC",
		"336699",
		"599BFF",
		"6699FF",
		"3366CC",
		"003399",
		"3366FF",
		"0033CC",
		"0033FF",
		"0000FF",
		"3333FF",
		"0000CC",
		"6666FF",
		"3333CC",
		"000099",
		"9999FF",
		"6666CC",
		"333399",
		"CCCCFF",
		"9999CC",
		"666699",
		"0A7BB8",
		"6633FF",
		"3300CC",
		"9966FF",
		"5997D5",
		"330099",
		"6600FF",
		"9933FF",
		"1AA9F7",
		"CC99FF",
		"9966CC",
		"663399",
		"9900FF",
		"CC66FF",
		"CE37CE",
		"660099",
		"CC33FF",
		"9900CC",
		"CC00FF",
		"FF00FF",
		"FF33FF",
		"CC00CC",
		"FF66FF",
		"CC33CC",
		"990099",
		"FF99FF",
		"CC66CC",
		"993399",
		"FFCCFF",
		"CC99CC",
		"996699",
		"FF00CC",
		"FF33CC",
		"CC0099",
		"FF66CC",
		"CC3399",
		"990066",
		"FF0099",
		"FF3399",
		"CC0066",
		"FF99CC",
		"CC6699",
		"993366",
		"FF0066",
		"FF6699",
		"CC3366",
		"990033",
		"FF3366",
		"CC0033",
		"FF0033",
	);

	return $colors;
}
