<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.pm.folder",
	"",
	array(
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,
		"SET_NAVIGATION" => $arResult["SET_NAVIGATION"] ?? null,
		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"] ?? null,
		"URL_TEMPLATES_PM_FOLDER" => $arResult["URL_TEMPLATES_PM_FOLDER"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
	),
	$component
);
?>
