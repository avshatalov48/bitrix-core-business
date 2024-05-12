<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.message.move",
	"",
	array(
		"FID" =>  $arResult["FID"] ?? null,
		"TID" =>  $arResult["TID"] ?? null,
		"MID" =>  $arResult["MID"] ?? null,

		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"] ?? null,
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_TOPIC_NEW" => $arResult["URL_TEMPLATES_TOPIC_NEW"] ?? null,
		"URL_TEMPLATES_TOPIC_SEARCH" => $arResult["URL_TEMPLATES_TOPIC_SEARCH"] ?? null,

		"USER_FIELDS" => $arParams["USER_FIELDS"] ?? null,
		"WORD_LENGTH" => $arParams["WORD_LENGTH"] ?? null,
		"IMAGE_SIZE" => $arParams["IMAGE_SIZE"] ?? null,
		"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
		"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
		"DATE_FORMAT" =>  $arResult["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null,
		"SEO_USE_AN_EXTERNAL_SERVICE" => $arParams["SEO_USE_AN_EXTERNAL_SERVICE"] ?? null,

		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,

		"SHOW_TAGS" => $arParams["SHOW_TAGS"] ?? null,
	),
	$component
);
?>
