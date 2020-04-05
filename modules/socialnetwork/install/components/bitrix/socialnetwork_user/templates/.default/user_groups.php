<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


$pageId = "user_groups";
include("util_menu.php");
include("util_profile.php");

$APPLICATION->AddHeadScript("/bitrix/js/socialnetwork/sonet-iframe-popup.js");

$componentParams = Array(
	"THUMBNAIL_SIZE" => $arParams["GROUP_THUMBNAIL_SIZE"],
	"PATH_TO_USER" => $arResult["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
	"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
	"PATH_TO_GROUP_SEARCH" => $arResult["PATH_TO_GROUP_SEARCH"],
	"USER_VAR" => $arResult["ALIASES"]["user_id"],
	"USER_ID" => $arResult["VARIABLES"]["user_id"],
	"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	"ITEMS_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
	"PAGE" => "user_groups",
	"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"],
	"USE_KEYWORDS" => $arParams["GROUP_USE_KEYWORDS"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
);

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		'POPUP_COMPONENT_NAME' => "bitrix:socialnetwork.user_groups",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "",
		"POPUP_COMPONENT_PARAMS" => $componentParams,
		"POPUP_COMPONENT_PARENT" => $this->getComponent(),
	)
);
?>