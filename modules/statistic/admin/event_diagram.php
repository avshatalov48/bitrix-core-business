<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");

// create image
$ImageHandle = CreateImageHandle($diameter, $diameter, "FFFFFF", true);

// get plot data
$arr = array();
$arFilter = Array(
	"ID"			=> implode(" | ",$find_events),
	"DATE1_PERIOD"	=> $find_date1,
	"DATE2_PERIOD"	=> $find_date2
	);
if (strlen($find_date1)>0 || strlen($find_date2)>0)	$period = "Y";
$by = ($period=="Y") ? "s_period_counter" : "s_total_counter";
$w = CStatEventType::GetList($by, ($order="desc"), $arFilter, $is_filtered);
while ($wr = $w->Fetch())	
{
	$total++;
	$count = ($period=="Y") ? $wr["PERIOD_COUNTER"] : $wr["TOTAL_COUNTER"];
	if ($count>0) $arr[] = array("COUNTER"=>$count);
}
$arChart = array();
while(list($key,$sector)=each($arr))
{
	$color = GetNextRGB($color, $total);
	$arChart[] = array("COUNTER"=>$sector["COUNTER"], "COLOR"=>$color);
}

// draw pie chart
imagefill($ImageHandle, 0, 0, imagecolorallocate($ImageHandle, 255,255,255));
Circular_Diagram($ImageHandle, $arChart, "FFFFFF", $diameter, $diameter/2, $diameter/2, true);

// send to client
ShowImageHeader($ImageHandle);
