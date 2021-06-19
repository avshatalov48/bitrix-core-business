<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

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

$max_grid = isset($_GET["max_grid"]) && !is_array($_GET["max_grid"]) ? intval($_GET["max_grid"]) : 15;
$min_grid = isset($_GET["min_grid"]) && !is_array($_GET["min_grid"]) ? intval($_GET["min_grid"]) : 10;

// create image canvas
$ImageHandle = CreateImageHandle($width, $height, "FFFFFF", true);

$colorFFFFFF = imagecolorallocate($ImageHandle,255,255,255);
imagefill($ImageHandle, 0, 0, $colorFFFFFF);

$arrX=Array();
$arrY=Array();
$arrayX=Array();
$arrayY=Array();

$M['WEEKDAY_0'] = "Sun";
$M['WEEKDAY_1'] = "Mon";
$M['WEEKDAY_2'] = "Tue";
$M['WEEKDAY_3'] = "Wed";
$M['WEEKDAY_4'] = "Thu";
$M['WEEKDAY_5'] = "Fri";
$M['WEEKDAY_6'] = "Sat";

$M['MONTH_1'] = "Jan";
$M['MONTH_2'] = "Feb";
$M['MONTH_3'] = "Mar";
$M['MONTH_4'] = "Apr";
$M['MONTH_5'] = "May";
$M['MONTH_6'] = "June";
$M['MONTH_7'] = "Jule";
$M['MONTH_8'] = "Aug";
$M['MONTH_9'] = "Sep";
$M['MONTH_10'] = "Oct";
$M['MONTH_11'] = "Nov";
$M['MONTH_12'] = "Dec";

/******************************************************
			Plot data
*******************************************************/
$find_site_id = array();
if(isset($_REQUEST["find_site_id"]))
{
	if(is_array($_REQUEST["find_site_id"]))
		$find_site_id = $_REQUEST["find_site_id"];
	else
		$find_site_id = array($_REQUEST["find_site_id"]);
}

foreach($find_site_id as $k=>$v)
{
	$v = trim($v);
	if($v == '')
		unset($find_site_id[$k]);
	else
		$find_site_id[$k] = $v;
}


$site_filtered = !empty($find_site_id);
$arFilter = Array(
	"DATE1" => $find_date1,
	"DATE2" => $find_date2,
	"SITE_ID" => $find_site_id
);
$arrTTF_FONT = array();

if ($find_graph_type!="date")
{
	$rs = CTraffic::GetSumList($find_graph_type, $arFilter);
	$ar = $rs->Fetch();

	switch ($find_graph_type)
	{
		case "hour":
			$start = 0; $end = 23; break;
		case "weekday":
			$start = 0; $end = 6;
			if(LANGUAGE_ID != "en")
				$arrTTF_FONT = array(
					"X" => array(
						"FONT_PATH" => "/bitrix/modules/main/install/fonts/opensans-regular.ttf",
						"FONT_SIZE" => 8,
						"FONT_SHIFT" => 12,
					),
				);
			break;
		case "month":
			$start = 1; $end = 12;
			if(LANGUAGE_ID != "en")
				$arrTTF_FONT = array(
					"X" => array(
						"FONT_PATH" => "/bitrix/modules/main/install/fonts/opensans-regular.ttf",
						"FONT_SIZE" => 8,
						"FONT_SHIFT" => 12,
					),
				);
			break;
	}

	$arColors = array();
	if($find_hit == "Y")
		$arColors[] = array($arrColor["HITS"]);
	if($find_host == "Y")
		$arColors[] = array($arrColor["HOSTS"]);
	if($find_session == "Y")
		$arColors[] = array($arrColor["SESSIONS"]);
	if($find_event == "Y")
		$arColors[] = array($arrColor["EVENTS"]);
	if(!$site_filtered)
	{
		if($find_guest == "Y")
			$arColors[] = array($arrColor["GUESTS"]);
		if($find_new_guest == "Y")
			$arColors[] = array($arrColor["NEW_GUESTS"]);
	}

	$dtu = ToUpper($find_graph_type);
	$arData = array();

	$arrY = array();
	$arrX = array();

	for ($i=$start; $i<=$end; $i++)
	{
		$arRec = array();

		if($find_hit == "Y")
			$arrY[] = $arRec[] = $ar[$dtu."_HIT_".$i];
		if($find_host == "Y")
			$arrY[] = $arRec[] = $ar[$dtu."_HOST_".$i];
		if($find_session == "Y")
			$arrY[] = $arRec[] = $ar[$dtu."_SESSION_".$i];
		if($find_event == "Y")
			$arrY[] = $arRec[] = $ar[$dtu."_EVENT_".$i];
		if(!$site_filtered)
		{
			if($find_guest == "Y")
				$arrY[] = $arRec[] = $ar[$dtu."_GUEST_".$i];
			if($find_new_guest == "Y")
				$arrY[] = $arRec[] = $ar[$dtu."_NEW_GUEST_".$i];
		}

		if($find_graph_type == "hour")
			$val = $i;
		elseif(LANGUAGE_ID=="ru" && function_exists("ImageTTFText"))
			$val = GetMessage("STAT_".$dtu."_".$i."_S");
		else
			$val = $M[$dtu."_".$i];

		$arData[$val] = array(
			"DATA" => $arRec,
			"COLORS" => $arColors,
		);
		$arrX[] = $val;
	}

	$arrY = GetArrayY($arrY, $MinY, $MaxY);

	$arrTTF_FONT["type"] = "bar";
	$gridInfo = DrawCoordinatGrid($arrX, $arrY, $width, $height, $ImageHandle, "FFFFFF", "B1B1B1", "000000", 15, 2, $arrTTF_FONT);

	/******************************************************
			data plot
	*******************************************************/
	if(is_array($gridInfo))
		Bar_Diagram($ImageHandle, $arData, $MinY, $MaxY, $gridInfo);
}
else
{
	$rsDays = CTraffic::GetDailyList("s_date", "asc", $v1, $arFilter);
	while($arData = $rsDays->Fetch())
	{
		$date = mktime(0, 0, 0, $arData["MONTH"], $arData["DAY"], $arData["YEAR"]);
		$date_tmp = 0;
		// when dates come not in order
		$next_date = AddTime($prev_date, 1, "D");
		if(($date > $next_date) && (intval($prev_date) > 0))
		{
			// fill date gaps
			$date_tmp = $next_date;
			while($date_tmp < $date)
			{
				$arrX[] = $date_tmp;
				if ($find_hit=="Y") $arrY_hit[] = 0;
				if ($find_host=="Y") $arrY_host[] = 0;
				if ($find_session=="Y") $arrY_session[] = 0;
				if ($find_event=="Y") $arrY_event[] = 0;
				if (!$site_filtered)
				{
					if ($find_guest=="Y") $arrY_guest[] = 0;
					if ($find_new_guest=="Y") $arrY_new_guest[] = 0;
				}
				$date_tmp = AddTime($date_tmp,1,"D");
			}
		}
		$arrX[] = $date;
		if ($find_hit=="Y") $arrY_hit[] = intval($arData["HITS"]);
		if ($find_host=="Y") $arrY_host[] = intval($arData["C_HOSTS"]);
		if ($find_session=="Y") $arrY_session[] = intval($arData["SESSIONS"]);
		if ($find_event=="Y") $arrY_event[] = intval($arData["C_EVENTS"]);
		if (!$site_filtered)
		{
			if ($find_guest=="Y") $arrY_guest[] = intval($arData["GUESTS"]);
			if ($find_new_guest=="Y") $arrY_new_guest[] = intval($arData["NEW_GUESTS"]);
		}
		$prev_date = $date;
	}

	/******************************************************
				axis X
	*******************************************************/

	$arrayX = GetArrayX($arrX, $MinX, $MaxX, $max_grid, $min_grid);

	/******************************************************
				axis Y
	*******************************************************/

	$arrY = array();
	if ($find_hit=="Y") $arrY = array_merge($arrY,$arrY_hit);
	if ($find_host=="Y") $arrY = array_merge($arrY,$arrY_host);
	if ($find_session=="Y") $arrY = array_merge($arrY,$arrY_session);
	if ($find_event=="Y") $arrY = array_merge($arrY,$arrY_event);
	if (!$site_filtered)
	{
		if ($find_guest=="Y") $arrY = array_merge($arrY,$arrY_guest);
		if ($find_new_guest=="Y") $arrY = array_merge($arrY,$arrY_new_guest);
	}

	$arrayY = GetArrayY($arrY, $MinY, $MaxY);

	DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle, "FFFFFF", "B1B1B1", "000000", 15, 2, $arrTTF_FONT);

	/******************************************************
			data plot
	*******************************************************/

	if ($find_hit=="Y")
		Graf($arrX, $arrY_hit, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HITS"], "N");

	if ($find_host=="Y")
		Graf($arrX, $arrY_host, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["HOSTS"], "N");

	if ($find_session=="Y")
		Graf($arrX, $arrY_session, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["SESSIONS"], "N");

	if ($find_event=="Y")
		Graf($arrX, $arrY_event, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["EVENTS"], "N");

	if (!$site_filtered)
	{
		if ($find_guest=="Y")
			Graf($arrX, $arrY_guest, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["GUESTS"], "N");

		if ($find_new_guest=="Y")
			Graf($arrX, $arrY_new_guest, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrColor["NEW_GUESTS"], "N");
	}
}

/******************************************************
		send image to client
*******************************************************/

ShowImageHeader($ImageHandle);
