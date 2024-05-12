<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.subscribe.list",
	"",
	array(
		"UID" => $arParams["UID"] ?? null,

		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_SUBSCR_LIST" =>  $arResult["URL_TEMPLATES_SUBSCR_LIST"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" =>  $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"] ?? null,
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,

		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
	),
	$component
);
?>
