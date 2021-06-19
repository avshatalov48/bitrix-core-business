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

// Image init
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array(); // X axis points
$arrY=Array(); // Y axis points
$arrayX=Array(); // X axis grid points
$arrayY=Array(); // Y axis grid points


$arrParams = array(
	"SESSION_SUMMA" => true,
	"SESSION" => true,
	"SESSION_BACK" => true,
	"VISITOR_SUMMA" => true,
	"VISITOR" => true,
	"VISITOR_BACK" => true,
	"NEW_VISITOR" => true,
	"HOST_SUMMA" => true,
	"HOST" => true,
	"HOST_BACK" => true,
	"HIT_SUMMA" => true,
	"HIT" => true,
	"HIT_BACK" => true,
	"EVENT_SUMMA" => true,
	"EVENT" => true,
	"EVENT_BACK" => true,
);

if(!array_key_exists($find_data_type, $arrParams))
	$find_data_type = "SESSION_SUMMA";

/******************************************************
			Get graph data
*******************************************************/

$arFilter = Array(
	"DATE1"				=> $find_date1,
	"DATE2"				=> $find_date2,
	"ADV_ID"			=> $find_adv_id,
	"ADV_ID_EXACT_MATCH"		=> $find_adv_id_exact_match,
	"REFERER1"			=> $find_referer1,
	"REFERER1_EXACT_MATCH"		=> $find_referer1_exact_match,
	"REFERER2"			=> $find_referer2,
	"REFERER2_EXACT_MATCH"		=> $find_referer2_exact_match,
	"EVENT_TYPE_ID"			=> $find_event_type_id,
	"EVENT_TYPE_ID_EXACT_MATCH"	=> $find_event_type_id_exact_match,
	"EVENT1"			=> $find_event1,
	"EVENT1_EXACT_MATCH"		=> $find_event1_exact_match,
	"EVENT2"			=> $find_event2,
	"EVENT2_EXACT_MATCH"		=> $find_event2_exact_match,
	"ADV"				=> $find_adv,
	"EVENT_TYPE"			=> $find_events
	);
$arrDays = CAdv::GetAnalysisGraphArray($arFilter, $is_filtered, $find_data_type, $arrLegend, $total, $max);

foreach ($arrDays as $keyD => $arD)
{
	$date = mktime(0,0,0,$arD["M"],$arD["D"],$arD["Y"]);
	$date_tmp = 0;
	// check if date is missing (or misordered) then
	$next_date = AddTime($prev_date,1,"D");
	if ($date>$next_date && intval($prev_date)>0)
	{
		// fill missed
		$date_tmp = $next_date;
		while ($date_tmp<$date)
		{
			$arrX[] = $date_tmp;
			foreach ($arrLegend as $adv_id => $arrS)
			{
				$arrY_adv[$adv_id][] = 0;
				$arrY[] = 0;
			}
			$date_tmp = AddTime($date_tmp,1,"D");
		}
	}
	$arrX[] = $date;
	foreach ($arrLegend as $adv_id => $arrS)
	{
		$arrY_adv[$adv_id][] = $arD[$adv_id];
		$arrY[] = $arD[$adv_id];
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

/******************************************************
			draw grid
*******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			plot graph
*******************************************************/

foreach ($arrLegend as $adv_id => $arrS)
{
	Graf($arrX, $arrY_adv[$adv_id], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrS["CLR"]);
}

/******************************************************
			send image
*******************************************************/

ShowImageHeader($ImageHandle);
