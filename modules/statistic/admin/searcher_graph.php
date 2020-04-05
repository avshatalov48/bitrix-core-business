<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

if(strlen($find_date1_DAYS_TO_BACK)>0 && $find_date1_DAYS_TO_BACK!="NOT_REF")
	$find_date1 = GetTime(time()-86400*intval($find_date1_DAYS_TO_BACK));

$width = intval($_GET["width"]);
$max_width = COption::GetOptionInt("statistic", "GRAPH_WEIGHT");
if($width <= 0 || $width > $max_width)
	$width = $max_width;

$height = intval($_GET["height"]);
$max_height = COption::GetOptionInt("statistic", "GRAPH_HEIGHT");
if($height <= 0 || $height > $max_height)
	$height = $max_height;

// create image canvas
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array();
$arrY=Array();
$arrayX=Array();
$arrayY=Array();

/******************************************************
			Plot data
*******************************************************/

$str = (is_array($find_searchers)) ? implode(" | ",$find_searchers) : "";
$arF = array(
	"SEARCHER_ID"	=> $str,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2,
	"SUMMA"			=> $find_summa
	);
$arrDays = CSearcher::GetGraphArray($arF, $arrLegend);
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
		$value = ($arrL["COUNTER_TYPE"]=="DETAIL") ? $arD[$keyL]["TOTAL_HITS"] : $arD["TOTAL_HITS"];
		$arrY_data[$keyL][] = $value;
		$arrY[] = $value;
	}
	$prev_date = $date;
}

/******************************************************
			axis X
*******************************************************/
$arrayX = GetArrayX($arrX, $MinX, $MaxX);

/******************************************************
			axis Y
*******************************************************/
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

/******************************************************
			grid
*******************************************************/
DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			plot
*******************************************************/

foreach($arrLegend as $keyL=>$arrL)
{
	if (strlen($keyL)>0)
	{
		Graf($arrX, $arrY_data[$keyL], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrL["COLOR"]);
	}
}

/******************************************************
			send to client
*******************************************************/

ShowImageHeader($ImageHandle);
