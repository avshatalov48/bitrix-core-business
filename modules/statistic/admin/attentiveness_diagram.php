<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");

if ($show=="time")
	$arrM = array("AM_1", "AM_1_3", "AM_3_6", "AM_6_9", "AM_9_12", "AM_12_15", "AM_15_18", "AM_18_21", "AM_21_24", "AM_24");
else
	$arrM = array("AH_1", "AH_2_5", "AH_6_9", "AH_10_13", "AH_14_17", "AH_18_21", "AH_22_25", "AH_26_29", "AH_30_33", "AH_34");

$arFilter = Array(
	"DATE1"		=> $find_date1,
	"DATE2"		=> $find_date2,
	"SITE_ID"	=> $find_site_id
	);

$rs = CTraffic::GetDailyList("s_date", "asc", $arMaxMin, $arFilter);
while ($ar = $rs->Fetch())
{
	foreach($arrM as $key) $arSum[$key] = intval($arSum[$key]) + intval($ar[$key]);
}

$arr = array();
foreach ($arSum as $key => $value)
{
	$arr[] = array("COLOR"=> $arrColor[$key], "COUNTER" => $value);
}

// image init
$ImageHendle = CreateImageHandle($diameter, $diameter);

// plot pie diagram
Circular_Diagram($ImageHendle, $arr, "FFFFFF", $diameter, $diameter/2, $diameter/2);

// send image
ShowImageHeader($ImageHendle);
