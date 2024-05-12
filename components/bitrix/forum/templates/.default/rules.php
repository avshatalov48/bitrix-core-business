<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.rules",
	"",
	array(
		"CONTENT" => $arResult["RULES_CONTENT"] ?? null,

		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"] ?? null,

		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE" => $arParams["SET_TITLE"] ?? null,
	),
	$component
);
?>
