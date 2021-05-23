<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.message.send",
	"",
	array(
		"UID" => $arResult["UID"],
		"TYPE" => $arResult["TYPE"],
		"SEND_MAIL" => $arParams["SEND_MAIL"],
		"SEND_ICQ" => $arParams["SEND_ICQ"],
		
		"URL_TEMPLATES_MESSAGE_SEND" => $arResult["URL_TEMPLATES_MESSAGE_SEND"],
		"URL_TEMPLATES_PROFILE_VIEW" => ($arParams["SEO_USER"] == "TEXT" ? "" : $arResult["URL_TEMPLATES_PROFILE_VIEW"]),

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"SET_TITLE" => $arResult["SET_TITLE"],

		"SEO_USER" => $arParams["SEO_USER"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
	),
	$component
);
?>