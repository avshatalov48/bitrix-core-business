<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.active",
	"",
	array(
		"FID" => $arResult["FID"],
		
		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"],

		"PAGEN" => intVal($GLOBALS["NavNum"] + 1),
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"FID_RANGE" => $arParams["FID"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"SHOW_FORUM_ANOTHER_SITE" =>  $arResult["SHOW_FORUM_ANOTHER_SITE"],
		
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],

		"TMPLT_SHOW_ADDITIONAL_MARKER"	=>	$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"]
	),
	$component
);?><?
?>
