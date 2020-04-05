<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @var CMain $APPLICATION */
$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = COption::GetOptionString("statistic", "DIAGRAM_DIAMETER");
if (strlen($diagram_type)>0) $diagram_type .= "_";

$str = (is_array($find_country_id)) ? implode(" | ",$find_country_id) : "";
$arF = array(
	"COUNTRY_ID"	=> $str,
	"DATE1"			=> $find_date1,
	"DATE2"			=> $find_date2
	);
$arrDays = CCountry::GetGraphArray($arF, $arrLegend);

function data_sort($ar1, $ar2)
{
	global $find_data_type, $diagram_type;
	if ($ar1[$diagram_type.$find_data_type]<$ar2[$diagram_type.$find_data_type]) return 1;
	if ($ar1[$diagram_type.$find_data_type]>$ar2[$diagram_type.$find_data_type]) return -1;
	return 0;
}
uasort($arrLegend, "data_sort");

$arr = array();
reset($arrLegend);
while(list($keyL, $arrL) = each($arrLegend)) 
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
