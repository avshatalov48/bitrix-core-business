<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Sale\SalesZone;

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
CUtil::InitJSCore(array('core', 'ajax'));
$arParams["AJAX_CALL"] = $arParams["AJAX_CALL"] == "Y" ? "Y" : "N";
$arParams["COUNTRY"] = intval($arParams["COUNTRY"]);
$arParams["REGION"] = intval($arParams["REGION"]);
$arParams["LOCATION_VALUE"] = intval($arParams["LOCATION_VALUE"]);
$arParams["ALLOW_EMPTY_CITY"] = $arParams["ALLOW_EMPTY_CITY"] == "N" ? "N" : "Y";
$arParams["ZIPCODE"] = intval($arParams["ZIPCODE"]);
$arParams["SHOW_QUICK_CHOOSE"] = $arParams["SHOW_QUICK_CHOOSE"] == "N" ? "N" : "Y";
$arParams["ADMIN_SECTION"] = (defined('ADMIN_SECTION') && ADMIN_SECTION === true)? "Y" : "N";


if ($arParams["ADMIN_SECTION"] != "Y")
{
	if ($arParams["SITE_ID"] == '')
		$arParams["SITE_ID"] = SITE_ID;
}

if ($arParams["ZIPCODE"] > 0)
{
	$arZip = CSaleLocation::GetByZIP($arParams["ZIPCODE"]);
	if (is_array($arZip) && count($arZip) > 1)
	{
		$arParams["LOCATION_VALUE"] = intval($arZip["ID"]);
	}
}

######################################################
######################################################
######################################################

if(!isset($arParams['CACHE_TIME']))
	$arParams['CACHE_TIME'] = 999999;
if(!isset($arParams['CACHE_TYPE']))
	$arParams['CACHE_TYPE'] = 'A';

// obtain cached data
$cacheNeeded = intval($arParams['CACHE_TIME']) > 0 && $arParams['CACHE_TYPE'] != 'N' && \Bitrix\Main\Config\Option::get("main", "component_cache_on", "Y") == "Y";
$cachedData = array();

if($cacheNeeded)
{
	$currentCache = \Bitrix\Main\Data\Cache::createInstance();
	$cacheDir = '/sale/location/legacy/component/sal';

	if($currentCache->startDataCache(intval($arParams['CACHE_TIME']), implode('|', array_merge($arParams, array(SITE_ID, LANGUAGE_ID))), $cacheDir))
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->StartTagCache($cacheDir);

		try
		{
			$cachedData['ZONE_IDS'] = SalesZone::getSelectedIds($arParams["SITE_ID"]);

			$cachedData['DEFAULT_LOCS'] = array();
			$res = CSaleLocation::GetList(
					array(
						"SORT" => "ASC",
						"COUNTRY_NAME_LANG" => "ASC",
						"CITY_NAME_LANG" => "ASC"
					),
					array("LOC_DEFAULT" => "Y", "LID" => LANGUAGE_ID),
					false,
					false,
					array("*")
			);
			while($item = $res->fetch())
			{
				$cachedData['DEFAULT_LOCS'][] = $item;
			}
		}
		catch (Exception $e)
		{
			if($cacheNeeded)
			{
				$CACHE_MANAGER->AbortTagCache();
				$currentCache->abortDataCache();
			}
		}

		$CACHE_MANAGER->RegisterTag(\Bitrix\Sale\Location\Admin\LocationHelper::LOCATION_LINK_DATA_CACHE_TAG);
		$CACHE_MANAGER->EndTagCache();
		$currentCache->endDataCache($cachedData);
	}
	else
	{
		$cachedData = $currentCache->getVars();
	}
}
else
{
	$cachedData['ZONE_IDS'] = SalesZone::getSelectedIds($arParams["SITE_ID"]);

	$cachedData['DEFAULT_LOCS'] = array();
	$res = CSaleLocation::GetList(
			array(
				"SORT" => "ASC",
				"COUNTRY_NAME_LANG" => "ASC",
				"CITY_NAME_LANG" => "ASC"
			),
			array("LOC_DEFAULT" => "Y", "LID" => LANGUAGE_ID),
			false,
			false,
			array("*")
	);
	while($item = $res->fetch())
	{
		$cachedData['DEFAULT_LOCS'][] = $item;
	}
}

SalesZone::setSelectedIds($arParams["SITE_ID"], $cachedData['ZONE_IDS']);

######################################################
######################################################
######################################################

// take into account sales zone
// 1. check if ONLY a single city is connected to sale zone
$arResult["SINGLE_CITY"] = "N";
$citiesIds = SalesZone::getCitiesIds($arParams["SITE_ID"]);

if(count($citiesIds) == 1 && $citiesIds[0] <> '')
{
	$rsLocationsList = CSaleLocation::GetList(
		array(),
		array("CITY_ID" => $citiesIds[0]),
		false,
		false,
		array("ID")
	);

	if ($arLoc = $rsLocationsList->GetNext())
	{
		$arParams["LOCATION_VALUE"] = $arLoc["ID"];
		$arResult["SINGLE_CITY"] = "Y";
	}
}

// 2. check location is connected to a sale zone
if(!SalesZone::checkLocationId($arParams["LOCATION_VALUE"], $arParams["SITE_ID"]))
	$arParams["LOCATION_VALUE"] = 0;

if ($arParams["LOCATION_VALUE"] > 0)
{
	if ($arLocation = CSaleLocation::GetByID($arParams["LOCATION_VALUE"]))
	{
		$arParams["COUNTRY"] = $arLocation["COUNTRY_ID"];
		$arParams["REGION"] = $arLocation["REGION_ID"];
		$arParams["CITY"] = $arLocation["CITY_ID"];
	}
}

//check in location city
$arResult["EMPTY_CITY"] = "N";
$arCityFilter = array("!CITY_ID" => "NULL", ">CITY_ID" => "0");
if ($arParams["COUNTRY"] > 0)
	$arCityFilter["COUNTRY_ID"] = $arParams["COUNTRY"];
$rsLocCount = CSaleLocation::GetList(array(), $arCityFilter, false, false, array("ID"));
if (!$rsLocCount->Fetch())
	$arResult["EMPTY_CITY"] = "Y";

//check in location region
$arResult["EMPTY_REGION"] = "N";
$arRegionFilter = array("!REGION_ID" => "NULL", ">REGION_ID" => "0");
if ($arParams["COUNTRY"] > 0 && $arParams["REGION"] > 0)
	$arRegionFilter["COUNTRY_ID"] = $arParams["COUNTRY"];
if ($arParams["REGION"] > 0)
	$arRegionFilter["REGION_ID"] = $arParams["REGION"];
$rsLocCount = CSaleLocation::GetList(array(), $arRegionFilter, false, false, array("ID"));
if (!$rsLocCount->Fetch())
	$arResult["EMPTY_REGION"] = "Y";

//check if exist another city
if ($arResult["EMPTY_CITY"] == "Y" && $arResult["EMPTY_REGION"] == "Y")
{
	$arCityFilter = array("!CITY_ID" => "NULL", ">CITY_ID" => "0");
	$rsLocCount = CSaleLocation::GetList(array(), $arCityFilter, false, false, array("ID"));
	if ($rsLocCount->Fetch())
		$arResult["EMPTY_CITY"] = "N";
}

//location default
$arParams["LOC_DEFAULT"] = array();
foreach($cachedData['DEFAULT_LOCS'] as $arLocDefault)
{
	if ($arLocDefault["LOC_DEFAULT"] == "Y"
		&& (!intval($arLocDefault["COUNTRY_ID"]) || (intval($arLocDefault["COUNTRY_ID"]) && SalesZone::checkCountryId($arLocDefault["COUNTRY_ID"], $arParams["SITE_ID"])))
		&& (!intval($arLocDefault["REGION_ID"]) || (intval($arLocDefault["REGION_ID"]) && SalesZone::checkRegionId($arLocDefault["REGION_ID"], $arParams["SITE_ID"])))
		&& SalesZone::checkCityId($arLocDefault["CITY_ID"], $arParams["SITE_ID"])
	)
	{
		$nameDefault = "";
		$nameDefault .= (($arLocDefault["COUNTRY_NAME"] == '') ? "" : $arLocDefault["COUNTRY_NAME"]);
		if ($arLocDefault["COUNTRY_NAME"] <> '' && $arLocDefault["REGION_NAME"] <> '')
			$nameDefault .= " - ".$arLocDefault["REGION_NAME"];
		elseif ($arLocDefault["REGION_NAME"] <> '')
			$nameDefault .= $arLocDefault["REGION_NAME"];

		if (($arLocDefault["COUNTRY_NAME"] <> '' || $arLocDefault["REGION_NAME"] <> '') && $arLocDefault["CITY_NAME"] <> '')
			$nameDefault .= " - ".$arLocDefault["CITY_NAME"];
		elseif ($arLocDefault["CITY_NAME"] <> '')
			$nameDefault .= $arLocDefault["CITY_NAME"];

		$arLocDefault["LOC_DEFAULT_NAME"] = $nameDefault;
		$arParams["LOC_DEFAULT"][] = $arLocDefault;
	}
}

//location value
if ($arParams["LOCATION_VALUE"] > 0 )
{
	if ($arLocation = CSaleLocation::GetByID($arParams["LOCATION_VALUE"]))
	{
		if ($arResult["EMPTY_REGION"] == "Y" && $arResult["EMPTY_CITY"] == "Y")
			$arParams["COUNTRY"] = $arParams["LOCATION_VALUE"];
		else
			$arParams["COUNTRY"] = $arLocation["COUNTRY_ID"];

		if ($arResult["EMPTY_CITY"] == "Y")
			$arParams["REGION"] = $arLocation["ID"];
		else
			$arParams["REGION"] = $arLocation["REGION_ID"];

		$arParams["CITY"] = $arParams["CITY_OUT_LOCATION"] == "Y" ? $arParams["LOCATION_VALUE"] : $arLocation["CITY_ID"];
	}
}

$locationString = "";

//select country
$arResult["COUNTRY_LIST"] = array();

if ($arResult["EMPTY_REGION"] == "Y" && $arResult["EMPTY_CITY"] == "Y")
{
	$rsCountryList = CSaleLocation::GetList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), array("LID" => LANGUAGE_ID), false, false, array("ID", "COUNTRY_ID", "COUNTRY_NAME_LANG"));
}
else
{
	$rsCountryList = CSaleLocation::GetCountryList(array("SORT" => "ASC", "NAME_LANG" => "ASC"));
}

while ($arCountry = $rsCountryList->GetNext())
{
	if(!SalesZone::checkCountryId($arCountry["ID"], $arParams["SITE_ID"]))
		continue;

	if ($arResult["EMPTY_REGION"] == "Y" && $arResult["EMPTY_CITY"] == "Y")
		$arCountry["NAME_LANG"] = $arCountry["COUNTRY_NAME_LANG"];

	$arResult["COUNTRY_LIST"][] = $arCountry;
	if ($arCountry["ID"] == $arParams["COUNTRY"] && $arCountry["NAME_LANG"] <> '')
		$locationString .= $arCountry["NAME_LANG"];
}

if (count($arResult["COUNTRY_LIST"]) <= 0)
	$arResult["COUNTRY_LIST"] = array();
elseif (count($arResult["COUNTRY_LIST"]) == 1)
	$arParams["COUNTRY"] = $arResult["COUNTRY_LIST"][0]["ID"];

//select region
$arResult["REGION_LIST"] = array();
if (($arParams["COUNTRY"] > 0 || count($arResult["COUNTRY_LIST"]) <= 0) && ($arParams["REGION_INPUT_NAME"] <> '' || $arParams["ZIPCODE"] > 0))
{
	$arRegionFilter = array("LID" => LANGUAGE_ID, "!REGION_ID" => "NULL", "!REGION_ID" => "0");
	if ($arParams["COUNTRY"] > 0)
		$arRegionFilter["COUNTRY_ID"] = intval($arParams["COUNTRY"]);

	if ($arResult["EMPTY_CITY"] == "Y")
	{
		$rsRegionList = CSaleLocation::GetList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), $arRegionFilter, false, false, array("ID", "REGION_ID", "REGION_NAME_LANG", "SORT"));
	}
	else
	{
		$rsRegionList = CSaleLocation::GetRegionList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), $arRegionFilter);
	}

	$regionSortIndex = array();

	while ($arRegion = $rsRegionList->GetNext())
	{
		if(!SalesZone::checkRegionId($arRegion["ID"], $arParams["SITE_ID"]))
		{
			continue;
		}

		if ($arResult["EMPTY_CITY"] == "Y")
			$arRegion["NAME_LANG"] = $arRegion["REGION_NAME_LANG"];

		$arResult["REGION_LIST"][$arRegion['ID']] = $arRegion;
		$regionSortIndex[$arRegion['SORT']][$arRegion['NAME_LANG']] = $arRegion['ID'];

		if ($arRegion["ID"] == $arParams["REGION"] && $arRegion["NAME_LANG"] <> '')
			$locationString = $arRegion["NAME_LANG"].", ".$locationString;
	}

	// also cities with no regions...
	if(count($arResult["REGION_LIST"]))
	{
		$filter = array("LID" => LANGUAGE_ID, "REGION_ID" => "NULL");
		if ($arParams["COUNTRY"] > 0)
			$filter["COUNTRY_ID"] = intval($arParams["COUNTRY"]);
		$res = CSaleLocation::GetList(array("SORT" => "ASC", "NAME_LANG" => "ASC"), $filter, false, false);//, array("ID", "CITY_ID", "CITY_NAME_LANG"));
		while($item = $res->fetch())
		{
			if(!intval($item["CITY_ID"]))
				continue;

			if(!SalesZone::checkCityId($item["CITY_ID"], $arParams["SITE_ID"]))
			{
				continue;
			}

			$arResult["REGION_LIST"][-$item['CITY_ID']] = array(
				'ID' => -$item['CITY_ID'],
				'NAME' => $item['CITY_NAME'],
				'NAME_ORIG' => $item['CITY_NAME_ORIG'],
				'NAME_LANG' => $item['CITY_NAME'],
			);
			$regionSortIndex[$item['SORT']][$item['CITY_NAME']] = -$item['CITY_ID'];
		}
	}
}

if(is_array($regionSortIndex))
{
	ksort($regionSortIndex);
	$regionListSorted = array();
	foreach($regionSortIndex as $s => &$v)
	{
		ksort($v, SORT_STRING);
		foreach($v as $regionId)
		{
			if($regionId < 0)
			{
				$regionListSorted[$regionId] = $arResult["REGION_LIST"][$regionId];
			}
			else
			{
				$regionListSorted[] = $arResult["REGION_LIST"][$regionId];
			}
		}
	}
}

$arResult["REGION_LIST"] = $regionListSorted;

if (!is_array($arResult["REGION_LIST"]))
	$arResult["REGION_LIST"] = array();
elseif (count($arResult["REGION_LIST"]) == 1)
	$arParams["REGION"] = $arResult["REGION_LIST"][0]["ID"];

//select city
$arResult["CITY_LIST"] = array();
if (
		$arResult["EMPTY_CITY"] == "N"
		&& ((count($arResult["COUNTRY_LIST"]) > 0 && count($arResult["REGION_LIST"]) > 0 && $arParams["COUNTRY"] > 0 && $arParams["REGION"] > 0)
		|| (count($arResult["COUNTRY_LIST"]) <= 0 && count($arResult["REGION_LIST"]) > 0 && $arParams["REGION"] > 0)
		|| (count($arResult["COUNTRY_LIST"]) > 0 && count($arResult["REGION_LIST"]) <= 0 && $arParams["COUNTRY"] > 0)
		|| (count($arResult["COUNTRY_LIST"]) <= 0 && count($arResult["REGION_LIST"]) <= 0))
	)
{
	$arCityFilter = array("LID" => LANGUAGE_ID);
	if ($arParams["COUNTRY"] > 0)
		$arCityFilter["COUNTRY_ID"] = $arParams["COUNTRY"];
	if ($arParams["REGION"] > 0)
		$arCityFilter["REGION_ID"] = $arParams["REGION"];

	if ($arParams['ALLOW_EMPTY_CITY'] == 'Y')
	{
		$rsLocationsList = CSaleLocation::GetList(
			array(
				"SORT" => "ASC",
				"COUNTRY_NAME_LANG" => "ASC",
				"CITY_NAME_LANG" => "ASC"
			),
			$arCityFilter,
			false,
			false,
			array(
				"ID", "CITY_ID", "CITY_NAME"
			)
		);

		while ($arCity = $rsLocationsList->GetNext())
		{
			if(!SalesZone::checkCityId($arCity["CITY_ID"], $arParams["SITE_ID"]))
				continue;

			$arResult["CITY_LIST"][] = array(
				"ID" => $arCity[$arParams["CITY_OUT_LOCATION"] == "Y" ? "ID" : "CITY_ID"],
				"CITY_ID" => $arCity['CITY_ID'],
				"CITY_NAME" => $arCity["CITY_NAME"],
			);
			if ($arCity["ID"] == $arParams["CITY"])
			{
				$locationString = ($arCity["CITY_NAME"] <> '' ? $arCity["CITY_NAME"].", " : "").$locationString;
				if(intval($arParams["LOCATION_VALUE"]) <= 0)
					$arParams["LOCATION_VALUE"] = $arCity["ID"];
				$arResult["LOCATION_DEFAULT"] = $arCity["ID"];
			}
		}//end while
	}//end if
}

$appendEmptyCity = true;

if($arParams["REGION"] < 0)
{
	$arParams["CITY"] = abs($arParams["REGION"]);
	$arParams["LOCATION_VALUE"] = $arParams["CITY"];

	$city = $arResult["REGION_LIST"][$arParams["REGION"]];
	$city['ID'] = abs($city['ID']);
	$locationString = ($city["NAME"] <> '' ? $city["NAME"].", " : "").$locationString;

	$arResult["CITY_LIST"][] = $city;

	$appendEmptyCity = false;
}
elseif(intval($arParams["CITY"]) && isset($arResult['REGION_LIST'][-$arParams["CITY"]]))
{
	$arParams["REGION"] = -$arParams["CITY"];

	$city = $arResult["REGION_LIST"][$arParams["REGION"]];
	$city['ID'] = abs($city['ID']);
	$locationString = ($city["NAME"] <> '' ? $city["NAME"].", " : "").$locationString;

	$arResult["CITY_LIST"][] = $city;

	$appendEmptyCity = false;
}

if($appendEmptyCity)
{
	$found = false;
	foreach($arResult['CITY_LIST'] as $city)
	{
		if((array_key_exists('NAME', $city) && $city['NAME'] == '') || (array_key_exists('CITY_NAME', $city) && $city['CITY_NAME'] == ''))
		{
			$found = true;
			break;
		}
	}

	if(!$found)
	{
		array_unshift($arResult['CITY_LIST'], array(
			"ID" => intval($arParams["REGION"]) ? $arParams["REGION"] : $arParams["COUNTRY"],
			"CITY_ID" => 0,
			"CITY_NAME" => '',
		));
	}
}

######################################################
######################################################
######################################################

if ($arResult["EMPTY_CITY"] == "Y")
	$arParams["REGION_INPUT_NAME"] = "";

if ($arResult["EMPTY_REGION"] == "Y" && $arResult["EMPTY_CITY"] == "Y")
	$arParams["COUNTRY_INPUT_NAME"] = "";

$arResult["LOCATION_STRING"] = $locationString;
$arParams["JS_CITY_INPUT_NAME"] = CUtil::JSEscape($arParams["CITY_INPUT_NAME"]);

$arResult["ONCITYCHANGE"] = '';

if(isset($arParams["ONCITYCHANGE"]) && $arParams["ONCITYCHANGE"] !== '')
{
	if(is_array($arParams["ONCITYCHANGE_WHITE_LIST"]) && !empty($arParams["ONCITYCHANGE_WHITE_LIST"]))
	{
		$onCityChangeWhiteList = $arParams["ONCITYCHANGE_WHITE_LIST"];
	}
	else
	{
		$onCityChangeWhiteList = ['CrmProductRowSetLocation', 'fChangeLocationCity', 'submitForm'];
	}

	if(in_array($arParams["ONCITYCHANGE"], $onCityChangeWhiteList, true))
	{
		$arResult["ONCITYCHANGE"] = (string)$arParams["ONCITYCHANGE"];
	}
}

$arTmpParams = array(
	"COUNTRY_INPUT_NAME" => $arParams["COUNTRY_INPUT_NAME"],
	"REGION_INPUT_NAME" => $arParams["REGION_INPUT_NAME"],
	"CITY_INPUT_NAME" => $arParams["CITY_INPUT_NAME"],
	"CITY_OUT_LOCATION" => $arParams["CITY_OUT_LOCATION"],
	"ALLOW_EMPTY_CITY" => $arParams["ALLOW_EMPTY_CITY"],
	"ONCITYCHANGE_WHITE_LIST" => $arParams["ONCITYCHANGE_WHITE_LIST"],
	"ONCITYCHANGE" => $arResult["ONCITYCHANGE"]
);

$arResult["JS_PARAMS"] = CUtil::PhpToJsObject($arTmpParams);

$serverName = COption::GetOptionString("main", "server_name", "");
if ($serverName <> '')
	$arParams["SERVER_NAME"] = "http://".$serverName;

$arResult["ADDITIONAL_VALUES"] = "siteId:".$arParams["SITE_ID"];

//_print_r('C: '.$arParams['COUNTRY']);
//_print_r('R: '.$arParams['REGION']);
//_print_r('C: '.$arParams['CITY']);
//_print_r('L: '.$arParams['LOCATION_VALUE']);

//_print_r($arResult['COUNTRY_LIST']);
//_print_r($arResult['REGION_LIST']);
//_print_r($arResult['CITY_LIST']);

$this->IncludeComponentTemplate();

if ($arParams["AJAX_CALL"] != "Y")
{
	IncludeAJAX();
	$template =& $this->GetTemplate();
	$APPLICATION->AddHeadScript($template->GetFolder().'/proceed.js');
}
?>