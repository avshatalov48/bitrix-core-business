<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "user_blog";
include("util_menu.php");
include("util_profile.php");

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.menu",
	"",
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"],
		"PATH_TO_DRAFT" => $arResult["PATH_TO_USER_BLOG_DRAFT"],
		"PATH_TO_TAGS" => $arResult["PATH_TO_USER_BLOG_TAGS"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"] ?? null,
		"PAGE_VAR" => $arResult["ALIASES"]["blog_page"] ?? null,
		"POST_VAR" => $arResult["ALIASES"]["post_id"] ?? null,
		"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_MODERATION" => $arResult["PATH_TO_USER_BLOG_MODERATION"],
		"CURRENT_PAGE" => "tags",
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE']
	),
	$this->getComponent()
);

$arResult["PATH_TO_BLOG_CATEGORY"] = $arResult["PATH_TO_USER_BLOG"].(mb_strpos($arResult["PATH_TO_USER_BLOG"], "?") === false ? "?" : "&")."category=#category_id#";

$arComponentParams = [
	"SOCNET" => "Y",
	"USER_ID" => $arResult["VARIABLES"]["user_id"],
	"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
	"SET_TITLE" => "N"
];

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		'POPUP_COMPONENT_NAME' => "bitrix:blog.category",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "socialnetwork",
		"POPUP_COMPONENT_PARAMS" => $arComponentParams,
		"POPUP_COMPONENT_PARENT" => $this->getComponent(),
	)
);
?>
