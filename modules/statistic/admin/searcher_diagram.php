<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");

$ImageHandle = CreateImageHandle($diameter, $diameter);

$arr = array();
$arFilter = Array(
	"ID" => implode(" | ",$find_searchers),
	"DATE1_PERIOD" => $find_date1,
	"DATE2_PERIOD" => $find_date2
);
if ($find_date1 <> '' || $find_date2 <> '')
	$period = "Y";
$by = ($period=="Y") ? "s_period_hits" : "s_total_hits";
$w = CSearcher::GetList($by, "desc", $arFilter);
while($wr = $w->Fetch())
{
	$total++;
	$count = ($period=="Y") ? $wr["PERIOD_HITS"] : $wr["TOTAL_HITS"];
	if($count>0)
		$arr[] = array("COUNTER"=>$count);
}
$arChart = array();
foreach($arr as $key=>$sector)
{
	$color = GetNextRGB($color, $total);
	$arChart[] = array("COUNTER"=>$sector["COUNTER"], "COLOR"=>$color);
}

Circular_Diagram($ImageHandle, $arChart, "FFFFFF", $diameter, $diameter/2, $diameter/2);

ShowImageHeader($ImageHandle);
