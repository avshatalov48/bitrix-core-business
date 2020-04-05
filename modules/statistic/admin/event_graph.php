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

$arrX=Array(); // plot data X
$arrY=Array(); // plot data Y
$arrayX=Array(); // axis X
$arrayY=Array(); // axis Y

/******************************************************
			Get plot data
*******************************************************/

$str = (is_array($find_events)) ? implode(" | ",$find_events) : "";
$arF = array(
	"EVENT_ID"	=> $str,
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SUMMA"		=> $find_summa
	);
$arrDays = CStatEventType::GetGraphArray($arF, $arrLegend);
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
		if ($arrL["COUNTER_TYPE"]=="DETAIL")
		{
			$value = ($find_show_money!="Y") ? $arD[$keyL]["COUNTER"] : $arD[$keyL]["MONEY"];
		}
		elseif ($arrL["COUNTER_TYPE"]=="TOTAL")
		{
			$value = ($find_show_money!="Y") ? $arD["COUNTER"] : $arD["MONEY"];
		}
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
			Grid
*******************************************************/
DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			Plot
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
