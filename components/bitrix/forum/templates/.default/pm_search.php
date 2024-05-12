<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"bitrix:forum.pm.search",
	"",
	array(
		"PM_USER_PAGE" => $arResult["PM_USER_PAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"] ?? null,
		"URL_TEMPLATES_PM_READ" => $arResult["URL_TEMPLATES_PM_READ"] ?? null,
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"] ?? null,
		"URL_TEMPLATES_PM_SEARCH" => $arResult["URL_TEMPLATES_PM_SEARCH"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null
	),
	$component
);
?>
