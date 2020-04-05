<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);

$arResult = array();

if(\Bitrix\Main\Loader::includeModule('sale'))
{
	if(!empty($_REQUEST["search"]) && is_string($_REQUEST["search"]))
	{
		$search = $APPLICATION->UnJSEscape($_REQUEST["search"]);
		$search = str_replace('%', '', $search);

		$arParams = array();
		$params = explode(",", $_REQUEST["params"]);
		foreach($params as $param)
		{
			list($key, $val) = explode(":", $param);
			$arParams[$key] = $val;
		}

		$filter = \Bitrix\Sale\SalesZone::makeSearchFilter("city", $arParams["siteId"]);
		$filter["~CITY_NAME"] = $search."%";
		$filter["LID"] = LANGUAGE_ID;

		$rsLocationsList = CSaleLocation::GetList(
			array(
				"CITY_NAME_LANG" => "ASC",
				"COUNTRY_NAME_LANG" => "ASC",
				"SORT" => "ASC",
			),
			$filter,
			false,
			array("nTopCount" => 10),
			array(
				"ID", "CITY_ID", "CITY_NAME", "COUNTRY_NAME_LANG", "REGION_NAME_LANG"
			)
		);

		while ($arCity = $rsLocationsList->GetNext())
		{
			$arResult[] = array(
				"ID" => $arCity["ID"],
				"NAME" => $arCity["CITY_NAME"],
				"REGION_NAME" => $arCity["REGION_NAME_LANG"],
				"COUNTRY_NAME" => $arCity["COUNTRY_NAME_LANG"],
			);
		}

		$filter = \Bitrix\Sale\SalesZone::makeSearchFilter("region", $arParams["siteId"]);
		$filter["~REGION_NAME"] = $search."%";
		$filter["LID"] = LANGUAGE_ID;
		$filter["CITY_ID"] = false;
		$rsLocationsList = CSaleLocation::GetList(
			array(
				"CITY_NAME_LANG" => "ASC",
				"COUNTRY_NAME_LANG" => "ASC",
				"SORT" => "ASC",
			),
			$filter,
			false,
			array("nTopCount" => 10),
			array(
				"ID", "CITY_ID", "CITY_NAME", "COUNTRY_NAME_LANG", "REGION_NAME_LANG"
			)
		);
		while ($arCity = $rsLocationsList->GetNext())
		{
			$arResult[] = array(
				"ID" => $arCity["ID"],
				"NAME" => "",
				"REGION_NAME" => $arCity["REGION_NAME_LANG"],
				"COUNTRY_NAME" => $arCity["COUNTRY_NAME_LANG"],
			);
		}

		$filter = \Bitrix\Sale\SalesZone::makeSearchFilter("country", $arParams["siteId"]);
		$filter["~COUNTRY_NAME"] = $search."%";
		$filter["LID"] = LANGUAGE_ID;
		$filter["CITY_ID"] = false;
		$filter["REGION_ID"] = false;
		$rsLocationsList = CSaleLocation::GetList(
			array(
				"COUNTRY_NAME_LANG" => "ASC",
				"SORT" => "ASC",
			),
			$filter,
			false,
			array("nTopCount" => 10),
			array(
				"ID", "COUNTRY_NAME_LANG"
			)
		);
		while ($arCity = $rsLocationsList->GetNext())
		{
			$arResult[] = array(
				"ID" => $arCity["ID"],
				"NAME" => "",
				"REGION_NAME" => "",
				"COUNTRY_NAME" => $arCity["COUNTRY_NAME_LANG"],
			);
		}
	}
}

echo CUtil::PhpToJSObject($arResult);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
die();

?>