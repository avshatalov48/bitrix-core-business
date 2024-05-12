<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.search",
	"",
	array(
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		"TOPICS_PER_PAGE" => $arResult["TOPICS_PER_PAGE"] ?? null,
		"URL_TEMPLATES_LIST" => $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_TOPIC_SEARCH" => $arResult["URL_TEMPLATES_TOPIC_SEARCH"] ?? null,

		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null

	),
	$component
);
?>
