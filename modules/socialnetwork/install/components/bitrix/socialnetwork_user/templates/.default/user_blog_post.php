<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?><div class="feed-blog-post-list feed-blog-post-detail"><?php

$pageId = "user_blog";
include("util_menu.php");
include("util_profile.php");

if(($arResult["PATH_TO_USER_BLOG_CATEGORY"] ?? null) == '')
{
	$arResult["PATH_TO_USER_BLOG_CATEGORY"] = $arResult["PATH_TO_USER_BLOG"].(mb_strpos("?", $arResult["PATH_TO_USER_BLOG"]) === false ? "?" : "&")."category=#category_id#";
}

$arComponentParams = [
	"POST_VAR" => $arResult["ALIASES"]["post_id"] ?? null,
	"USER_VAR" => $arResult["ALIASES"]["user_id"] ?? null,
	"PAGE_VAR" => $arResult["ALIASES"]["blog_page"] ?? null,
	"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"] ?? null,
	"PATH_TO_GROUP_BLOG" => $arParams["PATH_TO_GROUP_BLOG"] ?? null,
	"PATH_TO_POST" => $arResult["PATH_TO_USER_BLOG_POST"] ?? null,
	"PATH_TO_POST_IMPORTANT" => $arResult["PATH_TO_USER_BLOG_POST_IMPORTANT"] ?? null,
	"PATH_TO_BLOG_CATEGORY" => $arResult["PATH_TO_USER_BLOG_CATEGORY"] ?? null,
	"PATH_TO_POST_EDIT" => $arResult["PATH_TO_USER_BLOG_POST_EDIT"] ?? null,
	"PATH_TO_USER" => $arResult["PATH_TO_USER"] ?? null,
	"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"] ?? null,
	"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"] ?? null,
	"ID" => $arResult["VARIABLES"]["post_id"] ?? null,
	"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
	"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,
	"SET_NAV_CHAIN" => "N",
	"SET_TITLE" => "N",
	"POST_PROPERTY" => $arParams["POST_PROPERTY"] ?? null,
	"DATE_TIME_FORMAT" => $arResult["DATE_TIME_FORMAT"] ?? null,
	"USER_ID" => $arResult["VARIABLES"]["user_id"] ?? null,
	"GROUP_ID" => $arParams["BLOG_GROUP_ID"] ?? null,
	"USE_SOCNET" => "Y",
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"] ?? null,
	"SHOW_YEAR" => $arParams["SHOW_YEAR"] ?? null,
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? null,
	"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"] ?? null,
	"USE_SHARE" => $arParams["USE_SHARE"] ?? null,
	"SHARE_HIDE" => $arParams["SHARE_HIDE"] ?? null,
	"SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"] ?? null,
	"SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"] ?? null,
	"SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"] ?? null,
	"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"] ?? null,
	"SHOW_RATING" => $arParams["SHOW_RATING"] ?? null,
	"RATING_TYPE" => $arParams["RATING_TYPE"] ?? null,
	"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"] ?? null,
	"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"] ?? null,
	"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"] ?? null,
	"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"] ?? null,
	"ALLOW_VIDEO" => $arParams["BLOG_COMMENT_ALLOW_VIDEO"] ?? null,
	"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"] ?? null,
	"GET_FOLLOW" => "Y",
	"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"] ?? null,
	"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"] ?? null,
	"SELECTOR_VERSION" => 2
];

$APPLICATION->IncludeComponent(
	"bitrix:ui.sidepanel.wrapper",
	"",
	array(
		'POPUP_COMPONENT_NAME' => "bitrix:socialnetwork.blog.post",
		"POPUP_COMPONENT_TEMPLATE_NAME" => "",
		"POPUP_COMPONENT_PARAMS" => $arComponentParams,
		"POPUP_COMPONENT_PARENT" => $this->getComponent(),
	)
);
?></div>
