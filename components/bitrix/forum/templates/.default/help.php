<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.help",
	"",
	array(
		"CONTENT" => $arParams["HELP_CONTENT"],
		
		"URL_TEMPLATES_INDEX" => $arResult["URL_TEMPLATES_INDEX"],
		
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
	),
	$component
);?><?
?>