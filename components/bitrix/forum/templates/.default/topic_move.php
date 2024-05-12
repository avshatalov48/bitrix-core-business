<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.topic.move",
	"",
	array(
		"FID" => $arResult["FID"] ?? null,
		"TID" => $arResult["TID"] ?? null,

		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"] ?? null,
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_TOPIC_MOVE" => $arResult["URL_TEMPLATES_TOPIC_MOVE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"] ?? null,

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,

		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null
	),
	$component
);
?>
