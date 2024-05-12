<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.pm.list",
	"",
	array(
		"FID" => $arResult["FID"] ?? null,

		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"] ?? null,
		"URL_TEMPLATES_PM_READ" => $arResult["URL_TEMPLATES_PM_READ"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_PM_FOLDER" => $arResult["URL_TEMPLATES_PM_FOLDER"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PM_PER_PAGE" => $arParams["PM_PER_PAGE"] ?? null,
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,

		"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		"SET_TITLE" => $arResult["SET_TITLE"] ?? null,

		"SEO_USER" => $arParams["SEO_USER"] ?? null,
	),
	$component
);
?>
