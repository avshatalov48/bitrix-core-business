<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("calendar"))
	return;

$bSocNet = CModule::IncludeModule("socialnetwork");

$arComponentParameters = array();

$arTypes = array();
$types = CCalendarType::GetList();

if (is_array($types))
{
	foreach($types as $type)
		$arTypes[$type["XML_ID"]] = "[".$type["XML_ID"]."] ".$type["NAME"];
}

$arParams = array(); // $arComponentParameters["PARAMETERS"]

$arParams["CALENDAR_TYPE"] = Array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("EC_TYPE"),
	"TYPE" => "LIST",
	"VALUES" => $arTypes,
	"REFRESH" => 'Y'
);

if (!isset($arCurrentValues["CALENDAR_TYPE"]) && count($arTypes) > 0)
{
	$arCurrentValues["CALENDAR_TYPE"] = each($arTypes);
	$arCurrentValues["CALENDAR_TYPE"] = $arCurrentValues["CALENDAR_TYPE"]["key"];
}

if ($arCurrentValues["CALENDAR_TYPE"] && $arCurrentValues["CALENDAR_TYPE"] != 'user' && $arCurrentValues["CALENDAR_TYPE"] != 'group')
{
	$Sect = CCalendar::GetSectionList(array('CAL_TYPE' => $arCurrentValues["CALENDAR_TYPE"]));
	if (count($Sect) > 0)
	{
		$arSections = array('0' => '- '.GetMessage("EC_CALENDAR_SECTION_ALL").' -');
		foreach($Sect as $section)
			$arSections[$section["ID"]] = "[".$section["ID"]."] ".$section["NAME"];

		$arParams["CALENDAR_SECTION_ID"] = Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("EC_CALENDAR_SECTION"),
			"TYPE" => "LIST",
			"VALUES" => $arSections
		);
	}
}

if (CCalendar::IsIntranetEnabled() && $bSocNet)
{
	$arParams["B_CUR_USER_LIST"] = Array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("ECL_P_CUR_USER_EVENT_LIST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
	);
}

$arParams["INIT_DATE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("ECL_P_INIT_DATE"),
	"DEFAULT" => '-'.GetMessage("ECL_P_SHOW_CUR_DATE").'-'
);

$arParams["FUTURE_MONTH_COUNT"] = array(
	"PARENT" => "BASE",
	"TYPE" => "LIST",
	"NAME" => GetMessage("ECL_P_FUTURE_MONTH_COUNT"),
	"VALUES" => Array("1" => "1","2" => "2","3" => "3","4" => "4","5" => "5","6" => "6","12" => "12","24" => "24"),
	"DEFAULT" => "2",
);

$arParams["DETAIL_URL"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("ECL_P_DETAIL_URL"),
	"DEFAULT" => ""
);

$arParams["EVENTS_COUNT"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("ECL_P_EVENTS_COUNT"),
	"DEFAULT" => "5"
);

$arParams["CACHE_TIME"] = Array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("ECL_P_CACHE_TIME"),
	"DEFAULT" => "3600",
);

$arComponentParameters["PARAMETERS"] = $arParams;
?>
