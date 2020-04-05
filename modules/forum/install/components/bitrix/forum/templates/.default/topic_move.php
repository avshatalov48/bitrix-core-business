<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.move",
	"",
	array(
		"FID" => $arResult["FID"],
		"TID" => $arResult["TID"],
	
		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_TOPIC_MOVE" => $arResult["URL_TEMPLATES_TOPIC_MOVE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"],
		
		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],

		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SEO_USER" => $arParams["SEO_USER"]
	),
	$component 
);
?>