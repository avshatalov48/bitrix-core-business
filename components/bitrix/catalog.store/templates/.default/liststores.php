<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
$APPLICATION->IncludeComponent(
	"bitrix:catalog.store.list",
	"",
	array(
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"PHONE" => $arParams["PHONE"],
		"SCHEDULE" => $arParams["SCHEDULE"],
		"TITLE" => $arParams["TITLE"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"PATH_TO_ELEMENT" => $arResult["PATH_TO_ELEMENT"],
		"MAP_TYPE" => $arParams["MAP_TYPE"],
	),
	$component
);