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

// create image canvas
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array();
$arrY_hits=Array();
$arrY_enter_points=Array();
$arrY_exit_points=Array();

$arrayX=Array();
$arrayY=Array();

/******************************************************
			plot data
*******************************************************/
if (is_array($find_adv) && count($find_adv)>0) $str = implode(" | ",$find_adv);
$arFilter = Array(
	"DATE1"			=> $date1,
	"DATE2"			=> $date2,
	"ADV"			=> $str,
	"ADV_DATA_TYPE"	=> $adv_data_type,
	"IS_DIR"		=> ($is_dir=="Y") ? "Y" : "N",
	);

$dynamic = CPage::GetDynamicList($section, $by, $order, $arFilter);

while($arData = $dynamic->Fetch())
{

	$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);
	$date_tmp = 0;
	// check if dates has gaps
	$next_date = AddTime($prev_date, 1, "D");
	if(($date > $next_date) && (intval($prev_date) > 0))
	{
		// fill it
		$date_tmp = $next_date;
		while($date_tmp < $date)
		{
			$arrX[] = $date_tmp;
			if ($find_hits=="Y") $arrY_hits[] = 0;
			if ($find_enter_points=="Y") $arrY_enter_points[] = 0;
			if ($find_exit_points=="Y") $arrY_exit_points[] = 0;
			$arrY[] = 0;
			$date_tmp = AddTime($date_tmp, 1, "D");
		}
	}
	$arrX[] = $date;
	if ($find_hits=="Y") $arrY_hits[] = intval($arData["COUNTER"]);
	if ($find_enter_points=="Y") $arrY_enter_points[] = intval($arData["ENTER_COUNTER"]);
	if ($find_exit_points=="Y") $arrY_exit_points[] = intval($arData["EXIT_COUNTER"]);
	$prev_date = $date;
}

/******************************************************
			axis X
*******************************************************/
$arrayX = GetArrayX($arrX, $MinX, $MaxX);

/******************************************************
			axis Y
*******************************************************/
$arrY = array();
if ($find_hits=="Y")			$arrY = array_merge($arrY,$arrY_hits);
if ($find_enter_points=="Y")	$arrY = array_merge($arrY,$arrY_enter_points);
if ($find_exit_points=="Y")		$arrY = array_merge($arrY,$arrY_exit_points);
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

//EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY);

/******************************************************
			grid
*******************************************************/
DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			plot
*******************************************************/
if ($find_hits=="Y")
	Graf($arrX, $arrY_hits, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["RED"]);

if ($find_enter_points=="Y")
	Graf($arrX, $arrY_enter_points, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["GREEN"]);

if ($find_exit_points=="Y")
	Graf($arrX, $arrY_exit_points, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["BLUE"]);

/******************************************************
			send to client
*******************************************************/

ShowImageHeader($ImageHandle);
