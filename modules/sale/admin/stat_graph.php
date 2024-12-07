<?php
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

/**
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

const STOP_STATISTICS = true;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if($saleModulePermissions=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$width = (int)($_GET['width'] ?? 0);
$max_width = COption::GetOptionInt("sale", "GRAPH_WEIGHT", 600);
if($width <= 0 || $width > $max_width)
	$width = $max_width;

$height = (int)($_GET['height'] ?? 0);
$max_height = COption::GetOptionInt("sale", "GRAPH_HEIGHT", 600);
if($height <= 0 || $height > $max_height)
	$height = $max_height;

$mode = (($_REQUEST['mode'] ?? '') !== 'money' ? 'count' : 'money');

$arColor = Array("08738C", "C6B59C", "0000FF", "FF0000", "FFFF00", "F7C684" ,"8CD694", "9CADCE", "B584BD", "C684BD", "FF94C6", "BDE794", "F7949C", "7BCE6B", "FF6342", "E2F86B", "A5DE63", "42BD6B", "52BDA5", "F79473", "5AC6DE", "94D6C6", "9C52AD", "BD52AD", "9C94C6", "FF63AD", "FF6384", "FE881D", "FF9C21", "FFAD7B", "EFFF29", "7BCE6B", "42BD6B", "52C6AD", "6B8CBD", "3963AD", "F7298C", "A51800", "9CA510", "528C21", "689EB9", "217B29", "6B8CC6", "D6496C", "C6A56B", "00B0A4", "AD844A", "9710B4", "946331", "AD3908", "734210", "008400", "3EC19A", "28D7D7", "6B63AD", "A4C13E", "7BCE31", "A5DE94", "94D6E7", "9C8C73", "FF8C4A", "A7588B", "03CF45", "F7B54A", "808040", "947BBD", "840084", "737373", "C48322", "809254", "1E8259", "63C6DE", "46128D", "8080C0");

$arStatus = Array();
$dbStatusList = CSaleStatus::GetList(
		array("SORT" => "ASC"),
		array("LID" => LANGUAGE_ID),
		false,
		false,
		array("ID", "NAME", "SORT")
	);
while ($arStatusList = $dbStatusList->GetNext())
{
	$arStatus[$arStatusList["ID"]] = $arStatusList["NAME"];
}

$arCurrency = Array();
$dbCur = CCurrency::GetList("sort", "asc", LANGUAGE_ID);
while($arCur = $dbCur->GetNext())
{
	$arCurrency[$arCur["CURRENCY"]] = $arCur["FULL_NAME"];
}

$arSite = [];
$dbSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
while($arSites = $dbSite->GetNext())
{
	$arSite[$arSites["LID"]] = $arSites["NAME"];
}

$arFind = Array(
	"find_canceled" => $_REQUEST['find_canceled'] ?? '',
	"find_allow_delivery" => $_REQUEST['find_allow_delivery'] ?? '',
	"find_payed" => $_REQUEST['find_payed'] ?? '',
	"find_all" => $_REQUEST['find_all'] ?? '',
	"filter_by" => $_REQUEST['filter_by'] ?? '',
	"mode" => $mode,
);

foreach($arCurrency as $k1 => $v1)
{
	if(isset($_REQUEST["find_all_".$k1]) && $_REQUEST["find_all_".$k1] == "Y")
		$arFind["find_all_".$k1] = "Y";
	if(isset($_REQUEST["find_payed_".$k1]) && $_REQUEST["find_payed_".$k1] == "Y")
		$arFind["find_payed_".$k1] = "Y";
	if(isset($_REQUEST["find_allow_delivery_".$k1]) && $_REQUEST["find_allow_delivery_".$k1] == "Y")
		$arFind["find_allow_delivery_".$k1] = "Y";
	if(isset($_REQUEST["find_canceled_".$k1]) && $_REQUEST["find_canceled_".$k1] == "Y")
		$arFind["find_canceled_".$k1] = "Y";

	foreach($arStatus as $k2 => $v2)
	{
		if(isset($_REQUEST["find_status_".$k2]) && $_REQUEST["find_status_".$k2] == "Y")
			$arFind["find_status_".$k2] = "Y";

		if(isset($_REQUEST["find_status_".$k2."_".$k1]) && $_REQUEST["find_status_".$k2."_".$k1] == "Y")
			$arFind["find_status_".$k2."_".$k1] = "Y";
	}
}

// create image canvas
$ImageHandle = CreateImageHandle($width, $height, "FFFFFF", true);

$colorFFFFFF = ImageColorAllocate($ImageHandle,255,255,255);
ImageFill($ImageHandle, 0, 0, $colorFFFFFF);

$arrayX=Array();
$arrayY=Array();
$arFilter=Array();

/******************************************************
				Plot data
*******************************************************/

if (!empty($_REQUEST['filter_date_from']))
{
	$arFilter["DATE_FROM"] = trim($_REQUEST['filter_date_from']);
}

$filter_date_to = '';
if (!empty($_REQUEST['filter_date_to']))
{
	if ($arDate = ParseDateTime($_REQUEST['filter_date_to'], CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (mb_strlen($_REQUEST['filter_date_to']) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilter["DATE_TO"] = $filter_date_to;
	}
}

if (!empty($find_lid))
{
	$filter_site_id = $find_lid;
}
elseif(empty($filter_site_id) || !is_array($filter_site_id))
{
	$filter_site_id = array_keys($arSite);
}

$arFilter["LID"] = $filter_site_id;

if ($saleModulePermissions != "W")
{
	$arFilter["STATUS_PERMS_GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
	$arFilter[">=STATUS_PERMS_PERM_VIEW"] = "Y";
}

$arResult = Array();
$arCurUsed = Array();
$arY = Array();

$MinX = 0;
$MaxX = 0;

if(!empty($_REQUEST['filter_date_from']))
	$MinX = MakeTimeStamp($_REQUEST['filter_date_from']);
if($filter_date_to <> '')
	$MaxX = MakeTimeStamp($filter_date_to);
else
	$MaxX = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

function bxStatSort($a, $b): int
{
	global $filter_by;
	if($filter_by == "weekday")
	{
		if(intval($a["DATE"]) == intval($b["DATE"]))
			return 0;
		elseif(intval($a["DATE"]) > intval($b["DATE"]))
			return 1;
		else
			return -1;
	}
	else
	{
		if(MakeTimeStamp($a["DATE"]) == MakeTimeStamp($b["DATE"]))
			return 0;
		elseif(MakeTimeStamp($a["DATE"]) > MakeTimeStamp($b["DATE"]))
			return 1;
		else
			return -1;
	}
}

$CACHE = intval($_REQUEST["cache_time"]);
$obCache = new CPHPCache;
$cache_id = "sale_stat_graph5_".md5(serialize($arFilter)."_".serialize($arFind));
if($obCache->InitCache($CACHE, $cache_id, "/"))
{
	$vars = $obCache->GetVars();
	$arX1 = $vars["arX1"];
	$arX = $vars["arX"];
	$arY = $vars["arY"];
	$arCountY = $vars["arCountY"];
	$arPayedY = $vars["arPayedY"];
	$arCancelY = $vars["arCancelY"];
	$arDelivY = $vars["arDelivY"];
	$arStatusY = $vars["arStatusY"];
	$arPriceY = $vars["arPriceY"];
	$MaxX = $vars["MaxX"];
	$MinX = $vars["MinX"];
	$MaxY = $vars["MaxY"];
	$MinY = $vars["MinY"];
	$arrayX = $vars["arrayX"];
	$arrayY = $vars["arrayY"];
	$arResult = $vars["arResult"];
}
else
{
	$arSelectedFields = Array("ID", "PAYED", "DATE_PAYED", "CANCELED", "DATE_CANCELED", "STATUS_ID", "DATE_STATUS", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "TAX_VALUE", "LID");
	$dbOrder = CSaleOrder::GetList(Array(), $arFilter, false, false, $arSelectedFields);
	while ($arOrder = $dbOrder->Fetch())
	{
		$currency = $arOrder['CURRENCY'];
		$statusId = $arOrder['STATUS_ID'];
		$price = (float)$arOrder['PRICE'];

		$arOrder["DATE_INSERT"] = ConvertDateTime($arOrder["DATE_INSERT"], FORMAT_DATE);

		$tstm = MakeTimeStamp($arOrder["DATE_INSERT"], FORMAT_DATE);
		if($arFind["filter_by"] == "day")
		{
			$key = ConvertDateTime($arOrder["DATE_INSERT"], FORMAT_DATE);
			if($tstm < $MinX || $MinX <= 0)
				$MinX = $tstm;
		}
		elseif($arFind["filter_by"] == "week")
		{
			$d = date("w", $tstm);
			if($d < 1)
				$d = 6;
			elseif($d > 1)
				$d = $d-1;
			else
				$d = 0;

			$tmp = AddToTimeStamp(Array("DD" => "-".$d), $tstm);
			$key = ConvertTimeStamp($tmp);

			if($tmp < $MinX || $MinX <= 0)
				$MinX = $tmp;
		}
		elseif($arFind["filter_by"] == "weekday")
		{
			$key = date("w", $tstm);

			if($tstm < $MinX || $MinX <= 0)
				$MinX = $tstm;
		}
		elseif($arFind["filter_by"] == "month")
		{
			$tmp = mktime(0, 0, 0, date("n", $tstm), 1, date("Y", $tstm));
			$key = ConvertTimeStamp($tmp);
			if($tmp < $MinX || $MinX <= 0)
				$MinX = $tmp;
		}
		else
		{
			$tmp = mktime(0, 0, 0, 1, 1, date("Y", $tstm));
			$key = ConvertTimeStamp($tmp);
			if($tmp < $MinX || $MinX <= 0)
				$MinX = $tmp;
		}

		$arResult[$key] ??= [];
		$arResult[$key]['DATE'] = $key;
		$arResult[$key]['COUNT'] ??= 0;
		$arResult[$key]['PRICE'] ??= [];
		$arResult[$key]['PRICE'][$currency] ??= 0;

		$arResult[$key]['COUNT']++;

		$arResult[$key]['PRICE'][$currency] += $price;

		if ($arFind["mode"] != "count")
		{
			$arResult[$key]['PAYED'] ??= [];
			$arResult[$key]['PAYED'][$currency] ??= 0;

			$arResult[$key]['CANCELED'] ??= [];
			$arResult[$key]['CANCELED'][$currency] ??= 0;

			$arResult[$key]['ALLOW_DELIVERY'] ??= [];
			$arResult[$key]['ALLOW_DELIVERY'][$currency] ??= 0;

			$arResult[$key]['STATUS'] ??= [];
			$arResult[$key]['STATUS'][$statusId] ??= [];
			$arResult[$key]['STATUS'][$statusId][$currency] ??= 0;

			if ($arOrder['PAYED'] === 'Y')
			{
				$arResult[$key]['PAYED'][$currency] += $price;
			}

			if ($arOrder['CANCELED'] === 'Y')
			{
				$arResult[$key]['CANCELED'][$currency] += $price;
			}

			if ($arOrder['ALLOW_DELIVERY'] === 'Y')
			{
				$arResult[$key]['ALLOW_DELIVERY'][$currency] += $price;
			}

			$arResult[$key]['STATUS'][$statusId][$currency] += $price;
		}
		else
		{
			$arResult[$key]['PAYED'] ??= 0;
			$arResult[$key]['CANCELED'] ??= 0;
			$arResult[$key]['ALLOW_DELIVERY'] ??= 0;
			$arResult[$key]['STATUS'] ??= [];
			$arResult[$key]['STATUS'][$statusId] ??= 0;

			if ($arOrder['PAYED'] === 'Y')
			{
				$arResult[$key]['PAYED']++;
			}

			if ($arOrder['CANCELED'] === 'Y')
			{
				$arResult[$key]['CANCELED']++;
			}

			if ($arOrder['ALLOW_DELIVERY'] === 'Y')
			{
				$arResult[$key]['ALLOW_DELIVERY']++;
			}

			$arResult[$key]['STATUS'][$statusId]++;
		}

		if (!in_array($currency, $arCurUsed))
		{
			$arCurUsed[] = $currency;
		}
	}

	if($arFind["filter_by"] == "day" || $arFind["filter_by"] == "week" || $arFind["filter_by"] == "year")
	{
		if($arFind["filter_by"] == "day")
			$period = 60*60*24;
		elseif($arFind["filter_by"] == "week")
		{
			$period = 60*60*24*7;
			$d = date("w", $MinX);
			if($d < 1)
				$d = 6;
			elseif($d > 1)
				$d = $d-1;
			else
				$d = 0;

			$MinX = AddToTimeStamp(Array("DD" => "-".$d), $MinX);
		}
		else
		{
			$period = 60*60*24*365;
			$MinX = mktime(0, 0, 0, 1, 1, date("Y", $MinX));
		}

		for($i=$MinX; $i<$MaxX; $i += $period)
		{
			$tm = ConvertTimeStamp($i);
			if($arFind["filter_by"] == "year")
				$tm = ConvertTimeStamp(mktime(0, 0, 0, 1, 1, date("Y", $i)));

			if(empty($arResult[$tm]))
				$arResult[$tm] = Array("DATE" => $tm);
		}
	}
	elseif($arFind["filter_by"] == "month") // month
	{
		$minMonth = (int)date("n", $MinX);
		$minYear = (int)date("Y", $MinX);
		$maxMonth = (int)date("n", $MaxX);
		$maxYear = (int)date("Y", $MaxX);
		$m = ($maxYear-$minYear)*12 + ($maxMonth-$minMonth);
		for($i = 0; $i <= $m; $i++)
		{
			$tm = ConvertTimeStamp(mktime(0, 0, 0, $minMonth + $i, 1, $minYear));
			if(empty($arResult[$tm]))
				$arResult[$tm] = Array("DATE" => $tm);
		}

		$MinX = (mktime(0, 0, 0, $minMonth, 1, $minYear));
		$MaxX = (mktime(0, 0, 0, $maxMonth, 1, $maxYear));
	}
	elseif($arFind["filter_by"] == "weekday")
	{
		for($i = 0; $i <= 6; $i++)
		{
			if(empty($arResult[$i]))
				$arResult[$i] = Array("DATE" => $i);
		}
	}

	uasort($arResult, "bxStatSort");

	$i = 0;
	$arX1 = Array();
	$arX = Array();
	$arY = Array();
	$arCountY = Array();
	$arPayedY = Array();
	$arCancelY = Array();
	$arDelivY = Array();
	$arStatusY = Array();
	$arPriceY = [];

	if($arFind["filter_by"] == "weekday")
	{
		$arX1[] = GetMessage("STAT_WEEKDAY_0");
		$arX1[] = GetMessage("STAT_WEEKDAY_1");
		$arX1[] = GetMessage("STAT_WEEKDAY_2");
		$arX1[] = GetMessage("STAT_WEEKDAY_3");
		$arX1[] = GetMessage("STAT_WEEKDAY_4");
		$arX1[] = GetMessage("STAT_WEEKDAY_5");
		$arX1[] = GetMessage("STAT_WEEKDAY_6");

		$arX = Array(0,1,2,3,4,5,6);
	}
	//echo "<pre>";print_r($arResult);echo "</pre>";die();
	foreach($arResult as $k => $v)
	{
		if($arFind["filter_by"] == "week")
			$arX1[] = ConvertDateTime($k, "DD.MM");
		elseif($arFind["filter_by"] == "month")
			$arX1[] = GetMessage("STAT_M_".ConvertDateTime($k, "MM"))." ".ConvertDateTime($k, "YYYY");
		elseif($arFind["filter_by"] == "year")
			$arX1[] = ConvertDateTime($k, "YYYY");

		if($arFind["filter_by"] != "weekday")
			$arX[] = MakeTimeStamp($k);

		if($arFind["mode"] == "count")
		{
			if (($arFind['find_all'] ?? null) === 'Y')
			{
				$fieldValue = (int)($v['COUNT'] ?? 0);
				$arY[] = $fieldValue;
				$arCountY[] = $fieldValue;
				unset($fieldValue);
			}
			if (($arFind['find_payed'] ?? null) === 'Y')
			{
				$fieldValue = (int)($v['PAYED'] ?? 0);
				$arY[] = $fieldValue;
				$arPayedY[] = $fieldValue;
				unset($fieldValue);
			}
			if (($arFind['find_allow_delivery'] ?? null) === 'Y')
			{
				$fieldValue = (int)($v['ALLOW_DELIVERY'] ?? 0);
				$arY[] = $fieldValue;
				$arDelivY[] = $fieldValue;
				unset($fieldValue);
			}
			if (($arFind['find_canceled'] ?? null) === 'Y')
			{
				$fieldValue = (int)($v['CANCELED'] ?? 0);
				$arY[] = $fieldValue;
				$arCancelY[] = $fieldValue;
				unset($fieldValue);
			}
			foreach($arStatus as $k1 => $v1)
			{
				if (($arFind['find_status_' . $k1] ?? null) === 'Y')
				{
					$fieldValue = (int)($v['STATUS'][$k1] ?? 0);
					$arY[] = $fieldValue;
					$arStatusY[$k1][] = $fieldValue;
					unset($fieldValue);
				}
			}
		}
		else
		{
			foreach($arCurrency as $k1 => $v1)
			{
				if (($arFind['find_all_' . $k1] ?? null) === 'Y')
				{
					$fieldValue = round($v['PRICE'][$k1] ?? 0, SALE_VALUE_PRECISION);
					$arY[] = $fieldValue;
					$arPriceY[$k1][] = $fieldValue;
					unset($fieldValue);
				}
				if (($arFind['find_payed_' . $k1] ?? null) === 'Y')
				{
					$fieldValue = round($v['PAYED'][$k1] ?? 0, SALE_VALUE_PRECISION);
					$arY[] = $fieldValue;
					$arPayedY[$k1][] = $fieldValue;
					unset($fieldValue);
				}
				if (($arFind['find_allow_delivery_' . $k1] ?? null) === 'Y')
				{
					$fieldValue = round($v['ALLOW_DELIVERY'][$k1] ?? 0, SALE_VALUE_PRECISION);
					$arY[] = $fieldValue;
					$arDelivY[$k1][] = $fieldValue;
					unset($fieldValue);
				}
				if (($arFind['find_canceled_' . $k1] ?? null) === 'Y')
				{
					$fieldValue = round($v['CANCELED'][$k1] ?? 0, SALE_VALUE_PRECISION);
					$arY[] = $fieldValue;
					$arCancelY[$k1][] = $fieldValue;
					unset($fieldValue);
				}
				foreach($arStatus as $k2 => $v2)
				{
					if (($arFind['find_status_' . $k2 . '_' . $k1] ?? null) === 'Y')
					{
						$fieldValue = round($v["STATUS"][$k2][$k1] ?? 0, SALE_VALUE_PRECISION);
						$arY[] = $fieldValue;
						$arStatusY[$k2][$k1][] = $fieldValue;
						unset($fieldValue);
					}
				}
			}
		}
	}

	$arrayY = GetArrayY($arY, $MinY, $MaxY, 10);

	if($arFind["filter_by"] == "weekday")
	{
		$arrayX = $arX1;
		$MinX = 0;
		$MaxX = 6;
	}
	elseif(count($arX1) <= 10)
	{
		$arrayX = $arX1;
	}
	else
	{
		$arrayX = GetArrayX($arX, $MinX, $MaxX);
	}

	if($obCache->StartDataCache())
	{
		$arCacheData = array(
			"arX1" => $arX1,
			"arX" => $arX,
			"arY" => $arY,
			"arCountY" => $arCountY,
			"arPayedY" => $arPayedY,
			"arCancelY" => $arCancelY,
			"arDelivY" => $arDelivY,
			"arStatusY" => $arStatusY,
			"arPriceY" => $arPriceY,
			"MaxX" => $MaxX,
			"MinX" => $MinX,
			"MaxY" => $MaxY,
			"MinY" => $MinY,
			"arrayX" => $arrayX,
			"arrayY" => $arrayY,
			"arResult" => $arResult,
		);

		$obCache->EndDataCache($arCacheData);
	}
}

/*
print_r($arX);
print_r($arrayX);
print_r($arY);
print_r($arrayY);
EchoGraphData($arrayX, $MinX, $MaxX, $arrayY, $MinY, $MaxY, $arX, $arY);
die();
*/

$arrTTF_FONT = false;
if (($arFind["filter_by"]=="month" || $arFind["filter_by"]=="weekday") && LANGUAGE_ID!="en")
{
	$arrTTF_FONT["X"] = array(
		"FONT_PATH"		=> "/bitrix/modules/sale/ttf/verdana.ttf",
		"FONT_SIZE"		=> 8,
		"FONT_SHIFT"	=> 12
		);
}
DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle, "FFFFFF", "B1B1B1", "000000", 15, 2, $arrTTF_FONT);
//DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHandle);
/******************************************************
		data plot
*******************************************************/
/*
print_r($arY);

echo "<br>";
print_r($MinX);
echo "<br>";
print_r($MaxX);
echo "<br>";
print_r($MinY);
echo "<br>";
print_r($MaxY);
die();
*/
if($arFind["mode"] == "count")
{
	if($arFind["find_all"] == "Y")
		Graf($arX, $arCountY, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[0], "N");

	if($arFind["find_payed"] == "Y")
		Graf($arX, $arPayedY, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[1], "N");

	if($arFind["find_allow_delivery"] == "Y")
		Graf($arX, $arDelivY, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[2], "N");

	if($arFind["find_canceled"] == "Y")
		Graf($arX, $arCancelY, $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[3], "N");

	$i = 4;
	foreach($arStatus as $k => $v)
	{
		if(($arFind["find_status_".$k] ?? '') == "Y")
		{
			Graf($arX, $arStatusY[$k], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[$i], "N");
		}
		$i++;
	}
}
else
{

	$i = 0;
	foreach($arCurrency as $k1 => $v)
	{
		if(($arFind["find_all_".$k1] ?? '') == "Y")
			Graf($arX, $arPriceY[$k1], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[$i], "N");
		$i++;
		if(($arFind["find_payed_".$k1] ?? '') == "Y")
			Graf($arX, $arPayedY[$k1], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[$i], "N");
		$i++;
		if(($arFind["find_allow_delivery_".$k1] ?? '') == "Y")
			Graf($arX, $arDelivY[$k1], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[$i], "N");
		$i++;
		if(($arFind["find_canceled_".$k1] ?? '') == "Y")
			Graf($arX, $arCancelY[$k1], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[$i], "N");
		$i++;
		foreach($arStatus as $k2 => $v2)
		{
			if(($arFind["find_status_".$k2."_".$k1] ?? '') == "Y")
				Graf($arX, $arStatusY[$k2][$k1], $ImageHandle, $MinX, $MaxX, $MinY, $MaxY, $arColor[$i], "N");
			$i++;
		}
	}
}


/******************************************************
		send image to client
*******************************************************/

ShowImageHeader($ImageHandle);
