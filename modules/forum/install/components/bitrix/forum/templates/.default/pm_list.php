<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.pm.list",
	"",
	array(
		"FID" => $arResult["FID"],
		
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"],
		"URL_TEMPLATES_PM_READ" => $arResult["URL_TEMPLATES_PM_READ"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_PM_FOLDER" => $arResult["URL_TEMPLATES_PM_FOLDER"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PM_PER_PAGE" => $arParams["PM_PER_PAGE"],
		"DATE_FORMAT" =>  $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" =>  $arParams["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		
		"CACHE_TIME" => $arResult["CACHE_TIME"],
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"SET_TITLE" => $arResult["SET_TITLE"],

		"SEO_USER" => $arParams["SEO_USER"],
	),
	$component
);
?>