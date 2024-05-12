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

$width = COption::GetOptionString("advertising", "BANNER_GRAPH_WEIGHT");
$height = COption::GetOptionString("advertising", "BANNER_GRAPH_HEIGHT");

// создаем изображение
$ImageHandle = CreateImageHandle($width, $height);

$arrX=Array(); // массив точек графика по X
$arrY=Array(); // массив точек графика по Y
$arrayX=Array(); // массив точек на оси X (деления)
$arrayY=Array(); // массив точек на оси Y (деления)

/******************************************************
				Собираем точки графика
*******************************************************/

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
$arShow = $find_what_show;
$arrDays = CAdvBanner::GetDynamicList($arFilter, $arrLegend, $is_filtered);
foreach ($arrDays as $keyD => $arD)
{
	$date = mktime(0,0,0,$arD["M"],$arD["D"],$arD["Y"]);
	$date_tmp = 0;
	// если даты пропущены (идут не по порядку) то
	$next_date = AddTime($prev_date,1,"D");
	if ($date>$next_date && intval($prev_date)>0)
	{
		// заполняем пропущенные даты
		$date_tmp = $next_date;
		while ($date_tmp<$date)
		{
			$arrX[] = $date_tmp;
			foreach ($arrLegend as $keyL => $arrS)
			{
				if (in_array("ctr", $arShow)) $arrY_ctr[$keyL][] = 0;
				if (in_array("show", $arShow)) $arrY_show[$keyL][] = 0;
				if (in_array("click", $arShow)) $arrY_click[$keyL][] = 0;
				if (in_array("visitor", $arShow)) $arrY_visitor[$keyL][] = 0;
				$arrY[] = 0;
			}
			$date_tmp = AddTime($date_tmp,1,"D");
		}
	}
	$arrX[] = $date;
	foreach ($arrLegend as $keyL => $arrS)
	{
		if ($arrS["COUNTER_TYPE"]=="DETAIL")
		{
			if (in_array("ctr", $arShow))
				$ctr_value = $arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_CTR"][$arrS["ID"]];
			if (in_array("show", $arShow))
				$show_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_SHOW"][$arrS["ID"]]);
			if (in_array("click", $arShow))
				$click_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_CLICK"][$arrS["ID"]]);
			if (in_array("visitor", $arShow))
				$visitor_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_VISITOR"][$arrS["ID"]]);
		}
		else
		{
			if (in_array("ctr", $arShow))
				$ctr_value = $arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_CTR"];
			if (in_array("show", $arShow))
				$show_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_SHOW"]);
			if (in_array("click", $arShow))
				$click_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_CLICK"]);
			if (in_array("visitor", $arShow))
				$visitor_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_VISITOR"]);
		}
		if (in_array("ctr", $arShow))
		{
			$arrY_ctr[$keyL][] = $ctr_value;
			$arrY[] = $ctr_value;
		}
		if (in_array("show", $arShow))
		{
			$arrY_show[$keyL][] = $show_value;
			$arrY[] = $show_value;
		}
		if (in_array("click", $arShow))
		{
			$arrY_click[$keyL][] = $click_value;
			$arrY[] = $click_value;
		}
		if (in_array("visitor", $arShow))
		{
			$arrY_visitor[$keyL][] = $visitor_value;
			$arrY[] = $visitor_value;
		}
	}
	$prev_date = $date;
}
/******************************************************
				Формируем ось X
*******************************************************/
$arrayX = GetArrayX($arrX, $MinX, $MaxX);
/******************************************************
				Формируем ось Y
*******************************************************/
$arrayY = GetArrayY($arrY, $MinY, $MaxY, 10, "Y", true);

/******************************************************
				Рисуем координатную сетку
*******************************************************/
DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);

/******************************************************
				Рисуем графики
*******************************************************/

foreach ($arrLegend as $keyL => $arrS)
{
	if ($keyL <> '')
	{
		if (in_array("ctr", $arShow))
		{
			//reset($arrX); echo "<pre>ctr - ".$keyL." color - <font style='color:#".$arrS["COLOR_CTR"]."'><b>".$arrS["COLOR_CTR"]."</b></font>\n"; while (list($key, $value) = each($arrX)) echo date("d.m.Y",$value)." = ".$arrY_ctr[$keyL][$key]."\n"; echo "\n</pre>";
			Graf($arrX, $arrY_ctr[$keyL], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrS["COLOR_CTR"]);
		}
		if (in_array("show", $arShow))
		{
			//reset($arrX); echo "<pre>show - ".$keyL." color - <font style='color:#".$arrS["COLOR_SHOW"]."'><b>".$arrS["COLOR_SHOW"]."</b></font>\n"; while (list($key, $value) = each($arrX)) echo date("d.m.Y",$value)." = ".$arrY_show[$keyL][$key]."\n"; echo "\n</pre>";
			Graf($arrX, $arrY_show[$keyL], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrS["COLOR_SHOW"]);
		}
		if (in_array("click", $arShow))
		{
			//reset($arrX); echo "<pre>click - ".$keyL." color - <font style='color:#".$arrS["COLOR_CLICK"]."'><b>".$arrS["COLOR_CLICK"]."</b></font>\n"; while (list($key, $value) = each($arrX)) echo date("d.m.Y",$value)." = ".$arrY_click[$keyL][$key]."\n"; echo "\n</pre>"; reset($arrX);
			Graf($arrX, $arrY_click[$keyL], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrS["COLOR_CLICK"]);
		}
		if (in_array("visitor", $arShow))
		{
			//reset($arrX); echo "<pre>visitor - ".$keyL." color - <font style='color:#".$arrS["COLOR_VISITOR"]."'><b>".$arrS["COLOR_VISITOR"]."</b></font>\n"; while (list($key, $value) = each($arrX)) echo date("d.m.Y",$value)." = ".$arrY_visitor[$keyL][$key]."\n"; echo "\n</pre>"; reset($arrX);
			Graf($arrX, $arrY_visitor[$keyL], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arrS["COLOR_VISITOR"]);
		}
	}
}

/******************************************************
				Отображаем изображение
*******************************************************/

ShowImageHeader($ImageHandle);
?>