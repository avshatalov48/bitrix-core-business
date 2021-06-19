<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
if ($diagram_type <> '') $diagram_type .= "_";

$arF = array(
	"COUNTRY_ID" => $find_country_id,
	"DATE1" => $find_date1,
	"DATE2" => $find_date2
);
$arrDays = CCity::GetGraphArray($arF, $arrLegend, $diagram_type.$find_data_type, 20);

$arr = array();
foreach ($arrLegend as $keyL => $arrL)
{
	if ($arrL[$diagram_type.$find_data_type] > 0)
		$arr[] = array("COLOR"=> $arrL["COLOR"], "COUNTER" => intval($arrL[$diagram_type.$find_data_type]));
}


// create image
$ImageHandle = CreateImageHandle($diameter, $diameter);

// draw pie diagram
Circular_Diagram($ImageHandle, $arr, "FFFFFF", $diameter, $diameter/2, $diameter/2);

// send it out
ShowImageHeader($ImageHandle);
