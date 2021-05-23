<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.pm.folder",
	"",
	array(
		"SET_TITLE" => $arResult["SET_TITLE"],
		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"],
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"],
		"URL_TEMPLATES_PM_FOLDER" => $arResult["URL_TEMPLATES_PM_FOLDER"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
	),
	$component
);
?>