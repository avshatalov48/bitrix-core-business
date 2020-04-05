<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$width = intval($_GET["width"]);
$max_width = COption::GetOptionInt("statistic", "GRAPH_WEIGHT");
if($width <= 0 || $width > $max_width)
	$width = $max_width;

$height = intval($_GET["height"]);
$max_height = COption::GetOptionInt("statistic", "GRAPH_HEIGHT");
if($height <= 0 || $height > $max_height)
	$height = $max_height;

// create image
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array(); // data points X
$arrY=Array(); // data points Y
$arrayX=Array(); // axis X
$arrayY=Array(); // axis Y

/******************************************************
			Get data
*******************************************************/

$arF = array(
	"COUNTRY_ID" => $find_country_id,
	"DATE1" => $find_date1,
	"DATE2" => $find_date2
);
$arrDays = CCity::GetGraphArray($arF, $arrLegend, $find_data_type, 20);
reset($arrDays);
while (list($keyD,$arD) = each($arrDays))
{
	$date = mktime(0,0,0,$arD["M"],$arD["D"],$arD["Y"]);
	$date_tmp = 0;
	$next_date = AddTime($prev_date,1,"D");
	if ($date>$next_date && intval($prev_date)>0)
	{
		$date_tmp = $next_date;
		while ($date_tmp<$date)
		{
			$arrX[] = $date_tmp;
			reset($arrLegend);
			while(list($keyL, $arrL) = each($arrLegend))
			{
				$arrY_data[$keyL][] = 0;
				$arrY[] = 0;
			}
			$date_tmp = AddTime($date_tmp,1,"D");
		}
	}
	$arrX[] = $date;
	reset($arrLegend);
	while(list($keyL, $arrL) = each($arrLegend))
	{
		$value = $arD[$keyL][$find_data_type];
		$arrY_data[$keyL][] = $value;
		$arrY[] = $value;
	}
	$prev_date = $date;
}

/******************************************************
			Axes X
*******************************************************/
$arrayX = GetArrayX($arrX, $MinX, $MaxX);

/******************************************************
			Axes Y
*******************************************************/
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

/******************************************************
			Draw grid
*******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			Plot data
*******************************************************/

reset($arrLegend);
while(list($keyL, $arrL) = each($arrLegend))
{
	if (strlen($keyL)>0)
	{
		Graf($arrX, $arrY_data[$keyL], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrL["COLOR"]);
	}
}

/******************************************************
			Send image
*******************************************************/

ShowImageHeader($ImageHandle);
