<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('advertising');

$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();
$isAdmin = CAdvContract::IsAdmin();

if(!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = COption::GetOptionString("advertising", "BANNER_DIAGRAM_DIAMETER");
$diameter = (intval($diameter)>0) ? intval($diameter) : 180;

$arFilter = Array(
	"DATE_1"			=> $find_date1,
	"DATE_2"			=> $find_date2,
	"CONTRACT_ID"		=> $find_contract_id,
	"CONTRACT_SUMMA"	=> $find_contract_summa,
	"GROUP_SID"			=> $find_group_sid,
	"GROUP_SUMMA"		=> $find_group_summa,
	"BANNER_ID"			=> $find_banner_id,
	"BANNER_SUMMA"		=> $find_banner_summa,
	"WHAT_SHOW"			=> $find_what_show
	);
$arrDays = CAdvBanner::GetDynamicList($arFilter, $arrLegend, $is_filtered);

$arr = array();
foreach ($arrLegend as $keyL => $arrS)
{
	if ($arrS["COUNTER_TYPE"]=="DETAIL" && $arrS["TYPE"]==$diagram_type)
	{
		$arr[] = array("COLOR"=> $arrS["COLOR"], "COUNTER" => $arrS[$counter_type]);
	}
	elseif ($diagram_type == '')
	{
		$arr[] = array("COLOR"=> $arrS["COLOR"], "COUNTER" => $arrS[$counter_type]);
	}
}

// создаем изображение
$ImageHandle = CreateImageHandle($diameter, $diameter);

// рисуем круговую диаграмму
Circular_Diagram($ImageHandle, $arr, "FFFFFF", $diameter, $diameter/2, $diameter/2);

// отображаем
ShowImageHeader($ImageHandle);
?>