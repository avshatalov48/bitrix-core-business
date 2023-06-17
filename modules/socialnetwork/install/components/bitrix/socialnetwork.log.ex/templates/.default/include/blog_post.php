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
/** @var boolean $is_unread */
/** @var array $arEvent */
/** @var string $ind */

$component = $this->getComponent();

$ind = $ind ?? '';
$is_unread = $is_unread ?? false;

$arComponentParams = [
	"MODE" => $arParams["MODE"],
	"PATH_TO_BLOG" => $arParams["PATH_TO_USER_BLOG"] ?? '',
	"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"] ?? '',
	"PATH_TO_POST_IMPORTANT" => $arParams["PATH_TO_USER_BLOG_POST_IMPORTANT"] ?? '',
	"PATH_TO_BLOG_CATEGORY" => $arParams["PATH_TO_USER_BLOG_CATEGORY"] ?? '',
	"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"] ?? '',
	"PATH_TO_GROUP_BLOG" => $arParams["PATH_TO_GROUP_MICROBLOG"] ?? '',
	"PATH_TO_SEARCH_TAG" => $arParams["PATH_TO_SEARCH_TAG"] ?? '',
	"PATH_TO_LOG_TAG" => $arResult["PATH_TO_LOG_TAG"] ?? '',
	"PATH_TO_USER" => $arParams["PATH_TO_USER"] ?? '',
	"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"] ?? '',
	"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"] ?? '',
	"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"] ?? '',
	"SET_NAV_CHAIN" => "N",
	"SET_TITLE" => "N",
	"POST_PROPERTY" => $arParams["POST_PROPERTY"] ?? [],
	"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
	"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
	"TIME_FORMAT" => $arParams["TIME_FORMAT"],
	"CREATED_BY_ID" => (
		array_key_exists("log_filter_submit", $_REQUEST)
		&& array_key_exists("flt_comments", $_REQUEST)
		&& $_REQUEST["flt_comments"] === "Y"
			? $arParams["CREATED_BY_ID"]
			: false
	),
	"USER_ID" => $arEvent["USER_ID"],
	"ENTITY_TYPE" => SONET_ENTITY_USER,
	"ENTITY_ID" => $arEvent["ENTITY_ID"],
	"EVENT_ID" => $arEvent["EVENT_ID"],
	"EVENT_ID_FULLSET" => $arEvent["EVENT_ID_FULLSET"],
	"IND" => $ind,
	"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
	"SONET_GROUP_ID" => $arParams["GROUP_ID"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"SHOW_YEAR" => $arParams["SHOW_YEAR"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? null,
	"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
	"USE_SHARE" => $arParams["USE_SHARE"] ?? '',
	"SHARE_HIDE" => $arParams["SHARE_HIDE"] ?? '',
	"SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"] ?? '',
	"SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"] ?? '',
	"SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"] ?? '',
	"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"] ?? '',
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"RATING_TYPE" => $arParams["RATING_TYPE"],
	"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
	"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
	"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"] ?? '',
	"ID" => $arEvent["SOURCE_ID"],
	"LOG_ID" => $arEvent["ID"],
	"FROM_LOG" => "Y",
	"ADIT_MENU" => [],
	"IS_UNREAD" => $is_unread,
	"MARK_NEW_COMMENTS" => (
		$USER->isAuthorized()
			&& $arResult["COUNTER_TYPE"] === "**"
			&& $arResult["SHOW_UNREAD"] === "Y"
				? "Y"
				: "N"
	),
	"IS_HIDDEN" => false,
	"LAST_LOG_TS" => ($arResult["LAST_LOG_TS"] + $arResult["TZ_OFFSET"]),
	"CACHE_TIME" => $arParams["CACHE_TIME"] ?? 0,
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"ALLOW_VIDEO" => $arParams["BLOG_COMMENT_ALLOW_VIDEO"] ?? '',
	"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"] ?? '',
	"USE_CUT" => $arParams["BLOG_USE_CUT"] ?? '',
	"AVATAR_SIZE_COMMON" => $arParams["AVATAR_SIZE_COMMON"],
	"AVATAR_SIZE" => $arParams["AVATAR_SIZE"],
	"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"],
	"LAZYLOAD" => "Y",
	"CHECK_COMMENTS_PERMS" => (isset($arParams["CHECK_COMMENTS_PERMS"]) && $arParams["CHECK_COMMENTS_PERMS"] === "Y" ? "Y" : "N"),
	"GROUP_READ_ONLY" => (isset($arResult["Group"]["READ_ONLY"]) && $arResult["Group"]["READ_ONLY"]	=== "Y" ? "Y" : "N"),
	"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"] ?? '',
	"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"] ?? '',
	'TOP_RATING_DATA' => ($arResult['TOP_RATING_DATA'][$arEvent["ID"]] ?? false),
	"SELECTOR_VERSION" => 2,
	'FORM_ID' => $arParams['BLOG_FORM_ID'],
];

if ($arResult["SHOW_FOLLOW_CONTROL"] === "Y")
{
	$arComponentParams["FOLLOW"] = $arEvent["FOLLOW"];
}

if (
	(
		!isset($arParams["USE_FAVORITES"])
		|| $arParams["USE_FAVORITES"] !== "N"
	)
	&& $USER->isAuthorized()
)
{
	$arComponentParams["FAVORITES_USER_ID"] = (
		array_key_exists("FAVORITES_USER_ID", $arEvent)
		&& (int)$arEvent["FAVORITES_USER_ID"] > 0
			? (int)$arEvent["FAVORITES_USER_ID"]
			: 0
	);
}

if (!empty($arEvent['CONTENT_ID']))
{
	$arComponentParams['CONTENT_ID'] = $arEvent['CONTENT_ID'];
	$arComponentParams['CONTENT_VIEW_CNT'] = (
		!empty($arResult["ContentViewData"][$arEvent['CONTENT_ID']])
			? $arResult["ContentViewData"][$arEvent['CONTENT_ID']]['CNT']
			: 0
	);
}

if (
	!empty($arEvent['CONTENT_ITEM_TYPE'])
	&& !empty($arEvent['CONTENT_ITEM_ID'])
)
{
	$arComponentParams['LOG_CONTENT_ITEM_TYPE'] = $arEvent['CONTENT_ITEM_TYPE'];
	$arComponentParams['LOG_CONTENT_ITEM_ID'] = (int)$arEvent['CONTENT_ITEM_ID'];
}

$arComponentParams['PINNED_PANEL_DATA'] = [];
if (array_key_exists('PINNED_PANEL_DATA', $arEvent))
{
	$arComponentParams['PINNED_PANEL_DATA'] = $arEvent['PINNED_PANEL_DATA'];
}

if (isset($arEvent['PINNED_USER_ID']))
{
	$arComponentParams['PINNED'] = ((int)$arEvent['PINNED_USER_ID'] > 0 ? 'Y' : 'N');
}

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.blog.post",
	"",
	$arComponentParams,
	$component
);
