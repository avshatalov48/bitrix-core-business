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
$rsTickets = CTicket::GetList($by, $order, $arFilter, $is_filtered, $CHECK_RIGHTS, "N", "N");
$arrMess = array();
$arrMess["2_m"] = 0;
$arrMess["3_m"] = 0;
$arrMess["4_m"] = 0;
$arrMess["5_m"] = 0;
$arrMess["6_m"] = 0;
$arrMess["7_m"] = 0;
$arrMess["8_m"] = 0;
$arrMess["9_m"] = 0;
$arrMess["10_m"] = 0;
while ($arTicket = $rsTickets->Fetch())
{
	if (strlen($arTicket["DATE_CLOSE"])>0)
	{
		$MC = $arTicket["MESSAGES"];
		if ($MC<=2) $arrMess["2_m"] += 1;
		elseif ($MC>=10) $arrMess["10_m"] += 1;
		else $arrMess[$MC."_m"] += 1;
	}
}
$arr = array();
while (list($key,$value)=each($arrMess))
{
	$arr[] = array("COLOR"=> $arrColor[$key], "COUNTER" => $arrMess[$key]);
}
// создаем изображение
$ImageHendle = CreateImageHandle($diameter, $diameter);

// рисуем круговую диаграмму
Circular_Diagram($ImageHendle, $arr, "FFFFFF", $diameter, $diameter/2, $diameter/2);

// отображаем
ShowImageHeader($ImageHendle);
?>