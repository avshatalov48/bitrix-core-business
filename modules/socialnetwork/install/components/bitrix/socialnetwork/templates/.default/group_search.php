<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (CModule::IncludeModule('intranet'))
	$GLOBALS['INTRANET_TOOLBAR']->Show();

?><?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.user_groups", 
	"", 
	Array(
		"THUMBNAIL_SIZE" => $arParams["GROUP_THUMBNAIL_SIZE"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"COLUMNS_COUNT" => 3,
		"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"PAGE" => "user_groups",
		"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
		"USE_KEYWORDS" => $arParams["GROUP_USE_KEYWORDS"],
	),
	$component 
);
?><?

// if (IsModuleInstalled("search")):
if (false && IsModuleInstalled("search")):
	$arrFilterAdd = array("PARAMS" => array("entity" => "sonet_group"));
	?>
	<?
	$APPLICATION->IncludeComponent(
		"bitrix:search.tags.cloud",
		"",
		Array(
			"FONT_MAX" => (IntVal($arParams["FONT_MAX"]) >0 ? $arParams["FONT_MAX"] : 20), 
			"FONT_MIN" => (IntVal($arParams["FONT_MIN"]) >0 ? $arParams["FONT_MIN"] : 10),
			"COLOR_NEW" => (strlen($arParams["COLOR_NEW"]) >0 ? $arParams["COLOR_NEW"] : "3f75a2"),
			"COLOR_OLD" => (strlen($arParams["COLOR_OLD"]) >0 ? $arParams["COLOR_OLD"] : "8D8D8D"),
			"ANGULARITY" => $arParams["ANGULARITY"], 
			"PERIOD_NEW_TAGS" => $arResult["PERIOD_NEW_TAGS"], 
			"SHOW_CHAIN" => "N", 
			"COLOR_TYPE" => $arParams["COLOR_TYPE"], 
			"WIDTH" => $arParams["WIDTH"], 
			"SEARCH" => "", 
			"TAGS" => "", 
			"SORT" => "NAME", 
			"PAGE_ELEMENTS" => "150", 
			"PERIOD" => $arParams["PERIOD"], 
			"URL_SEARCH" => $arResult["PATH_TO_GROUP_SEARCH"], 
			"TAGS_INHERIT" => "N", 
			"CHECK_DATES" => "Y", 
			"FILTER_NAME" => "arrFilterAdd",
			"arrFILTER" => Array("socialnetwork"), 
			"CACHE_TYPE" => "A", 
			"CACHE_TIME" => "3600" 
		),
		$component
	);
	?>
<?endif;?>

<br/>