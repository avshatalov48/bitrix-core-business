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

// image init
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array(); // X axis points
$arrY=Array(); // Y axis points
$arrayX=Array(); // X axis grid points
$arrayY=Array(); // Y axis grid points

/******************************************************
			Get plot data
*******************************************************/

$site_filtered = ($find_site_id <> '' && $find_site_id!="NOT_REF") ? true : false;
$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SITE_ID"	=> $find_site_id
	);
$rs = CTraffic::GetDailyList("s_date", "asc", $arMaxMin, $arFilter);
while($arData = $rs->Fetch())
{
	$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);
	$date_tmp = 0;
	// arrange dates
	$next_date = AddTime($prev_date, 1, "D");
	if(($date > $next_date) && (intval($prev_date) > 0))
	{
		// fill missing
		$date_tmp = $next_date;
		while ($date_tmp<$date)
		{
			$arrX[] = $date_tmp;
			$arrY[] = 0;
			$date_tmp = AddTime($date_tmp,1,"D");
		}
	}
	$arrX[] = $date;
	if ($show=="time")
	{
		$arData["AM_AVERAGE_TIME"] = (float) $arData["AM_AVERAGE_TIME"];
		$arrY[] = round($arData["AM_AVERAGE_TIME"]/60, 2);
	}
	else
	{
		$arData["AH_AVERAGE_HITS"] = (float) $arData["AH_AVERAGE_HITS"];
		$arrY[] = round($arData["AH_AVERAGE_HITS"], 2);
	}
	$prev_date = $date;
}

/******************************************************
			X axis
*******************************************************/

$arrayX = GetArrayX($arrX, $MinX, $MaxX);

/******************************************************
			Y axis
*******************************************************/

$arrayY = GetArrayY($arrY, $MinY, $MaxY);

//EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY);

/******************************************************
			draw grid
*******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			plot data
*******************************************************/

$color = ($show=="time") ? $arrColor["AM_AVERAGE_TIME"] : $arrColor["AH_AVERAGE_HITS"];
Graf($arrX, $arrY, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $color);

/******************************************************
			send image
*******************************************************/

ShowImageHeader($ImageHandle);
