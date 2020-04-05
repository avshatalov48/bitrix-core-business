<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.log.rss", 
	"", 
	Array(
		"EVENTS_VAR" => "events",
		"ENTITY_TYPE" => "G",
		"ENTITY_ID" => $arResult["VARIABLES"]["group_id"],		
		"PATH_TO_USER" => $arParams["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
		"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
		"SUBSCRIBE_ONLY" => "N",
		"RSS_TTL" => $arParams["LOG_RSS_TTL"]	
	),
	$component 
);
?>