<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.active",
	"",
	array(
		"FID" => $arResult["FID"] ?? null,

		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"PAGEN" => intval($GLOBALS["NavNum"] + 1),
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"] ?? null,
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"] ?? null,
		"FID_RANGE" => $arParams["FID"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"SHOW_FORUM_ANOTHER_SITE" =>  $arResult["SHOW_FORUM_ANOTHER_SITE"] ?? null,

		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE" => $arParams["SET_TITLE"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,

		"TMPLT_SHOW_ADDITIONAL_MARKER"	=>	$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"] ?? null
	),
	$component
);?><?
?>
