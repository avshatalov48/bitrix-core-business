<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.message.move",
	"",
	array(
		"FID" =>  $arResult["FID"],
		"TID" =>  $arResult["TID"],
		"MID" =>  $arResult["MID"],
		
		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_TOPIC_NEW" => $arResult["URL_TEMPLATES_TOPIC_NEW"],
		"URL_TEMPLATES_TOPIC_SEARCH" => $arResult["URL_TEMPLATES_TOPIC_SEARCH"],

		"USER_FIELDS" => $arParams["USER_FIELDS"],
		"WORD_LENGTH" => $arParams["WORD_LENGTH"],
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"],
		"ATTACH_MODE" => $arParams["ATTACH_MODE"],
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"],
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SEO_USER" => $arParams["SEO_USER"],
		"SEO_USE_AN_EXTERNAL_SERVICE" => $arParams["SEO_USE_AN_EXTERNAL_SERVICE"],

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
	),
	$component
);
?>