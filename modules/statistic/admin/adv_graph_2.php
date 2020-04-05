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
$arF = array();
$arF["ID"] = implode(" | ",$find_events);
$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2
	);
$dynamic = CAdv::GetDynamicList($ADV_ID, ($by="s_date"), ($order="asc"), $arMaxMin, $arFilter, $is_filtered);
while ($arData = $dynamic->Fetch())
{
	$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);
	$date_tmp = 0;
	$next_date = AddTime($prev_date, 1, "D");
	if(($date > $next_date) && (intval($prev_date) > 0))
	{
		$date_tmp = $next_date;
		while ($date_tmp < $date)
		{
			$arrX[] = $date_tmp;
			foreach($find_events as $eid)
			{
				$arrY_events[$eid][] = 0;
				$arrY_events_back[$eid][] = 0;
			}
			$date_tmp = AddTime($date_tmp,1,"D");
		}
	}
	$arrX[] = $date;
	$arF["DATE1_PERIOD"] = GetTime($date);
	$arF["DATE2_PERIOD"] = GetTime($date);
	$e = CAdv::GetEventList($ADV_ID, ($by="s_def"), ($order="desc"), $arF, $is_filtered);
	while($er = $e->Fetch())
	{
		if ($find_show_money=="Y" && $STAT_RIGHT>"M")
		{
			$arrEvent[$er["ID"]][$date] = intval($er["MONEY_PERIOD"]);
			$arrEvent_back[$er["ID"]][$date] = intval($er["MONEY_BACK_PERIOD"]);
		}
		else
		{
			$arrEvent[$er["ID"]][$date] = intval($er["COUNTER_PERIOD"]);
			$arrEvent_back[$er["ID"]][$date] = intval($er["COUNTER_BACK_PERIOD"]);
		}
	}

	foreach ($find_events as $eid)
	{
		$arrY_events[$eid][] = intval($arrEvent[$eid][$date]);
		$arrY_events_back[$eid][] = intval($arrEvent_back[$eid][$date]);
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
$arrY = array();
reset($find_events);
foreach ($find_events as $eid)
{
	$arr = $arrY_events[$eid];
	foreach ($arr as $value) $arrY[] = intval($value);

	$arr = $arrY_events_back[$eid];
	foreach ($arr as $value) $arrY[] = intval($value);
}
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

//EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY);

/******************************************************
			draw grid
*******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);


/******************************************************
			plot data
*******************************************************/

reset($find_events);
$total = sizeof($find_events);
foreach ($find_events as $eid)
{
	$arrY_dk = $arrY_events[$eid];
	$arrY_bc = $arrY_events_back[$eid];
	$color = GetNextRGB($color, $total);

	Graf($arrX, $arrY_dk, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $color);
	Graf($arrX, $arrY_bc, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $color, "Y");
}

/******************************************************
			send image
*******************************************************/

ShowImageHeader($ImageHandle);
