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
$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2
	);
$dynamic = CAdv::GetDynamicList($ADV_ID, "s_date", "asc", $arMaxMin, $arFilter);
while($arData = $dynamic->GetNext())
{
	$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);
	$date_tmp = 0;
	// arrage dates in order
	$next_date = AddTime($prev_date, 1, "D");
	if(($date > $next_date) && (intval($prev_date) > 0))
	{
		// fill missing dates
		$date_tmp = $next_date;
		while($date_tmp < $date)
		{
			$arrX[] = $date_tmp;
			if ($find_sessions=="Y") $arrY_sessions[] = 0;
			if ($find_sessions_back=="Y") $arrY_sessions_back[] = 0;
			if ($find_guests=="Y") $arrY_guests[] = 0;
			if ($find_new_guests=="Y") $arrY_new_guests[] = 0;
			if ($find_guests_back=="Y") $arrY_guests_back[] = 0;
			if ($find_hosts=="Y") $arrY_hosts[] = 0;
			if ($find_hosts_back=="Y") $arrY_hosts_back[] = 0;
			if ($find_hits=="Y") $arrY_hits[] = 0;
			if ($find_hits_back=="Y") $arrY_hits_back[] = 0;
			$date_tmp = AddTime($date_tmp, 1, "D");
		}
	}
	$arrX[] = $date;
	if ($find_sessions=="Y") $arrY_sessions[] = intval($arData["SESSIONS"]);
	if ($find_sessions_back=="Y") $arrY_sessions_back[] = intval($arData["SESSIONS_BACK"]);
	if ($find_guests=="Y") $arrY_guests[] = intval($arData["GUESTS"]);
	if ($find_new_guests=="Y") $arrY_new_guests[] = intval($arData["NEW_GUESTS"]);
	if ($find_guests_back=="Y") $arrY_guests_back[] = intval($arData["GUESTS_BACK"]);
	if ($find_hosts=="Y") $arrY_hosts[] = intval($arData["C_HOSTS"]);
	if ($find_hosts_back=="Y") $arrY_hosts_back[] = intval($arData["HOSTS_BACK"]);
	if ($find_hits=="Y") $arrY_hits[] = intval($arData["HITS"]);
	if ($find_hits_back=="Y") $arrY_hits_back[] = intval($arData["HITS_BACK"]);
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
if ($find_sessions=="Y") $arrY = array_merge($arrY, $arrY_sessions);
if ($find_sessions_back=="Y") $arrY = array_merge($arrY, $arrY_sessions_back);
if ($find_guests=="Y") $arrY = array_merge($arrY, $arrY_guests);
if ($find_guests_back=="Y") $arrY = array_merge($arrY, $arrY_guests_back);
if ($find_new_guests=="Y") $arrY = array_merge($arrY, $arrY_new_guests);
if ($find_hosts=="Y") $arrY = array_merge($arrY, $arrY_hosts);
if ($find_hosts_back=="Y") $arrY = array_merge($arrY, $arrY_hosts_back);
if ($find_hits=="Y") $arrY = array_merge($arrY, $arrY_hits);
if ($find_hits_back=="Y") $arrY = array_merge($arrY, $arrY_hits_back);
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

//EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arrX, $arrY);

/******************************************************
			draw grid
*******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
			plot
*******************************************************/

if ($find_sessions=="Y")
	Graf($arrX, $arrY_sessions, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["SESSIONS"]);
if ($find_sessions_back=="Y")
	Graf($arrX, $arrY_sessions_back, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["SESSIONS"], "Y");

if ($find_guests=="Y")
	Graf($arrX, $arrY_guests, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["GUESTS"]);
if ($find_guests_back=="Y")
	Graf($arrX, $arrY_guests_back, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["GUESTS"], "Y");
if ($find_new_guests=="Y")
	Graf($arrX, $arrY_new_guests, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["NEW_GUESTS"]);

if ($find_hosts=="Y")
	Graf($arrX, $arrY_hosts, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HOSTS"]);
if ($find_hosts_back=="Y")
	Graf($arrX, $arrY_hosts_back, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HOSTS"], "Y");

if ($find_hits=="Y")
	Graf($arrX, $arrY_hits, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HITS"]);
if ($find_hits_back=="Y")
	Graf($arrX, $arrY_hits_back, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HITS"], "Y");

/******************************************************
			send image
*******************************************************/

ShowImageHeader($ImageHandle);
