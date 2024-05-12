<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:forum.user.profile.edit",
	"",
	array(
		"UID" => $arResult["UID"] ?? null,

		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"USER_PROPERTY" =>  $arParams["USER_PROPERTY"] ?? null,
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,

		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,

		"SET_TITLE" => $arParams["SET_TITLE"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
	),
	$component
);?><?
?>
