<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";

if($bAdmin!="Y" && $bSupportTeam!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

$diameter = (intval($diameter)>0) ? intval($diameter) : 180;

InitBVar($find_responsible_exact_match);
$arFilter = Array(
	"SITE"						=> $find_site,
	"DATE_CREATE_1"				=> $find_date1,
	"DATE_CREATE_2"				=> $find_date2,
	"RESPONSIBLE_ID"			=> $find_responsible_id,
	"RESPONSIBLE"				=> $find_responsible,
	"RESPONSIBLE_EXACT_MATCH"	=> $find_responsible_exact_match,		
	"SLA"						=> $find_sla_id,
	"CATEGORY"					=> $find_category_id,
	"CRITICALITY"				=> $find_criticality_id,
	"STATUS"					=> $find_status_id,
	"MARK"						=> $find_mark_id,
	"SOURCE"					=> $find_source_id,
	);
$CHECK_RIGHTS = ($bDemo=="Y") ? "N" : "Y";

global $by, $order;

$rsTickets = CTicket::GetList($by, $order, $arFilter, null, $CHECK_RIGHTS, "N", "N");
$arrTime = array();
$arrTime["1"] = 0;
$arrTime["1_2"] = 0;
$arrTime["2_3"] = 0;
$arrTime["3_4"] = 0;
$arrTime["4_5"] = 0;
$arrTime["5_6"] = 0;
$arrTime["6_7"] = 0;
$arrTime["7"] = 0;
while ($arTicket = $rsTickets->Fetch())
{
	if ($arTicket["DATE_CLOSE"] <> '')
	{
		// запоминаем время обработки тикетов
		$day_sec = 86400;
		$TT = $arTicket["TICKET_TIME"];
		if ($TT<$day_sec) $arrTime["1"] += 1;
		if ($TT>$day_sec && $TT<=2*$day_sec) $arrTime["1_2"] += 1;
		if ($TT>2*$day_sec && $TT<=3*$day_sec) $arrTime["2_3"] += 1;
		if ($TT>3*$day_sec && $TT<=4*$day_sec) $arrTime["3_4"] += 1;
		if ($TT>4*$day_sec && $TT<=5*$day_sec) $arrTime["4_5"] += 1;
		if ($TT>5*$day_sec && $TT<=6*$day_sec) $arrTime["5_6"] += 1;
		if ($TT>6*$day_sec && $TT<=7*$day_sec) $arrTime["6_7"] += 1;
		if ($TT>7*$day_sec) $arrTime["7"] += 1;
	}
}
$arr = array();
foreach ($arrTime as $key => $value)
{
	$arr[] = array("COLOR"=> $arrColor[$key], "COUNTER" => $arrTime[$key]);
}

// создаем изображение
$ImageHendle = CreateImageHandle($diameter, $diameter);

// рисуем круговую диаграмму
Circular_Diagram($ImageHendle, $arr, "FFFFFF", $diameter, $diameter/2, $diameter/2);

// отображаем
ShowImageHeader($ImageHendle);
?>