<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.search",
	"",
	array(
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"TOPICS_PER_PAGE" => $arResult["TOPICS_PER_PAGE"],
		"URL_TEMPLATES_LIST" => $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_TOPIC_SEARCH" => $arResult["URL_TEMPLATES_TOPIC_SEARCH"],
		
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
		
	),
	$component 
);
?>