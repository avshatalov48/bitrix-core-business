<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var array $arGroup */

$pageId = "group_photo";
include("util_group_menu.php");

define('SONET_GROUP_NEEDED', true);
include("util_group_profile.php");

if ($arParams['FATAL_ERROR'] !== 'Y')
{
	include("util_copy_photo.php");
}

$propertyCodeList = $arParams["PHOTO"]["ALL"]["PROPERTY_CODE"];

if ($arParams["PHOTO"]["ALL"]["USE_RATING"] === "Y")
{
	$propertyCodeList[] = "PROPERTY_vote_count";
	$propertyCodeList[] = "PROPERTY_vote_sum";
	$propertyCodeList[] = "PROPERTY_RATING";
}

if ($arParams["PHOTO"]["ALL"]["USE_COMMENTS"] === "Y")
{
	if ($arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"] === "FORUM")
	{
		$propertyCodeList[] = "PROPERTY_FORUM_MESSAGE_CNT";
	}
	elseif ($arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"] === "BLOG")
	{
		$propertyCodeList[] = "PROPERTY_BLOG_COMMENTS_CNT";
	}
}

$photogallerySectionComponentParams = [
	"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
	"BEHAVIOUR" => "USER",
	"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
	"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
	"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"] ?? null,
	"DETAIL_SLIDE_SHOW_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_SLIDE_SHOW"],
	"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"],
	"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT_ICON"],
	"SECTIONS_TOP_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"],
	"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_SECTION"],
	"ALBUM_PHOTO_THUMBS_SIZE" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],
	"ALBUM_PHOTO_SIZE" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
	"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,
	"RETURN_SECTION_INFO" => "Y",
	"SET_STATUS_404" => $arParams["SET_STATUS_404"] ?? null,
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"SET_TITLE" => $arParams["SET_TITLE"],
	"ADD_CHAIN_ITEM" => "N",
	"SET_NAV_CHAIN" => "N",
	"DISPLAY_ALBUM_NAME" => "N",
	"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"]
];

$photogalleryDetailListComponentParams = [
	"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
	"BEHAVIOUR" => "USER",
	"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
	"IS_SOCNET" => "Y",
	"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
	"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"] ?? null,
	"INCLUDE_SUBSECTIONS" => "N", // Used to prevent displaying photos from subalbums in this section
	"ELEMENTS_LAST_COUNT" => "",
	"ELEMENT_LAST_TIME" => "",
	"ELEMENTS_LAST_TIME_FROM" => "",
	"ELEMENTS_LAST_TIME_TO" => "",
	"ELEMENT_SORT_FIELD" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_FIELD"],
	"ELEMENT_SORT_ORDER" => $arParams["PHOTO"]["ALL"]["ELEMENT_SORT_ORDER"],
	"ELEMENT_SORT_FIELD1" => "",
	"ELEMENT_SORT_ORDER1" => "",
	"ELEMENT_FILTER" => array(),
	"ELEMENT_SELECT_FIELDS" => array(),
	"PROPERTY_CODE" => $propertyCodeList,
	"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
	"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
	"DETAIL_SLIDE_SHOW_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_SLIDE_SHOW"],
	"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"SEARCH_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SEARCH"] ?? null,
	"USE_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["USE_PERMISSIONS"],
	"GROUP_PERMISSIONS" => $arParams["PHOTO"]["ALL"]["GROUP_PERMISSIONS"],
	"USE_DESC_PAGE" => $arParams["PHOTO"]["ALL"]["ELEMENTS_USE_DESC_PAGE"],
	"PAGE_ELEMENTS" => $arParams["PHOTO"]["ALL"]["ELEMENTS_PAGE_ELEMENTS"],
	"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
	"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_DETAIL"],
	"ADDITIONAL_SIGHTS" => $arParams["PHOTO"]["ALL"]["~ADDITIONAL_SIGHTS"] ?? null,
	"PICTURES_SIGHT" => "",
	"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,
	"SHOW_PHOTO_USER" => "Y",
	"GALLERY_AVATAR_SIZE" => $arParams["PHOTO"]["TEMPLATE"]["GALLERY_AVATAR_SIZE"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"SET_TITLE" => "N",
	"CELL_COUNT" => $arParams["PHOTO"]["TEMPLATE"]["CELL_COUNT"],
	"THUMBS_SIZE" => $arParams["PHOTO"]["ALL"]["THUMBS_SIZE"] ?? null,
	"SHOW_PAGE_NAVIGATION" => "bottom",
	"SHOW_CONTROLS" => "Y",
	"SHOW_RATING" => $arParams["PHOTO"]["ALL"]["USE_RATING"],
	"SHOW_SHOWS" => $arParams["PHOTO"]["TEMPLATE"]["SHOW_SHOWS"],
	"SHOW_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
	"SHOW_TAGS" => $arParams["PHOTO"]["ALL"]["SHOW_TAGS"],
	"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
	"USE_RATING" => $arParams["PHOTO"]["ALL"]["USE_RATING"],
	"DISPLAY_AS_RATING" => $arParams["PHOTO"]["ALL"]["DISPLAY_AS_RATING"],
	"RATING_MAIN_TYPE" => $arParams["PHOTO"]["ALL"]["RATING_MAIN_TYPE"],
	"READ_ONLY" => (isset($arGroup["CLOSED"]) && $arGroup["CLOSED"] === "Y" ? "Y" : ""),
	"USE_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
	"DRAG_SORT" => "Y",
	"MORE_PHOTO_NAV" => "Y",
	"CURRENT_ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
	"CURRENT_ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"] ?? null,
	"THUMBNAIL_SIZE" => $arParams["PHOTO"]["ALL"]["THUMBNAIL_SIZE"],
	"MAX_VOTE" => $arParams["PHOTO"]["ALL"]["MAX_VOTE"],
	"VOTE_NAMES" => $arParams["PHOTO"]["ALL"]["VOTE_NAMES"],
	"COMMENTS_COUNT" => $arParams["PHOTO"]["ALL"]["COMMENTS_COUNT"],
	"PATH_TO_SMILE" => $arParams["PHOTO"]["ALL"]["PATH_TO_SMILE"] ?? null,
	"FORUM_ID" => $arParams["PHOTO"]["ALL"]["FORUM_ID"],
	"USE_CAPTCHA" => $arParams["PHOTO"]["ALL"]["USE_CAPTCHA"],
	"POST_FIRST_MESSAGE" => $arParams["PHOTO"]["ALL"]["POST_FIRST_MESSAGE"] ?? null,
	"PREORDER" => $arParams["PHOTO"]["ALL"]["PREORDER"],
	"SHOW_LINK_TO_FORUM" => "N",
	"BLOG_URL" => $arParams["PHOTO"]["ALL"]["BLOG_URL"],
	"PATH_TO_BLOG" => $arParams["PHOTO"]["ALL"]["PATH_TO_BLOG"],
	"PATH_TO_USER" => $arParams["PHOTO"]["ALL"]["PATH_TO_USER"],
	"NAME_TEMPLATE" => $arParams["PHOTO"]["ALL"]["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["PHOTO"]["ALL"]["SHOW_LOGIN"] ?? null,
];

$photogallerySectionListComponentParams = [
	"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
	"BEHAVIOUR" => "USER",
	"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
	"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
	"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"] ?? null,
	"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"],
	"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"],
	"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
	"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"],
	"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT_ICON"],
	"SECTIONS_TOP_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"],
	"ALBUM_PHOTO_SIZE" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
	"ALBUM_PHOTO_THUMBS_SIZE" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],
	"PAGE_ELEMENTS" => $arParams["PHOTO"]["ALL"]["SECTION_PAGE_ELEMENTS"],
	"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
	"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_SECTION"],
	"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,
	"SHOW_PHOTO_USER" => $arParams["PHOTO"]["ALL"]["SHOW_PHOTO_USER"] ?? null,
	"GALLERY_AVATAR_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_AVATAR_SIZE"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"SET_TITLE" => $arParams["SET_TITLE"],
	"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
	"SHOW_TAGS" => $arParams["SHOW_TAGS"] ?? null,
	"USE_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
	"SHOW_COMMENTS" => $arParams["PHOTO"]["ALL"]["USE_COMMENTS"],
	"COMMENTS_TYPE" => $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"],
	"MAX_VOTE" => $arParams["PHOTO"]["ALL"]["MAX_VOTE"],
	"VOTE_NAMES" => $arParams["PHOTO"]["ALL"]["VOTE_NAMES"],
	"COMMENTS_COUNT" => $arParams["PHOTO"]["ALL"]["COMMENTS_COUNT"],
	"PATH_TO_SMILE" => $arParams["PHOTO"]["ALL"]["PATH_TO_SMILE"] ?? null,
	"FORUM_ID" => $arParams["PHOTO"]["ALL"]["FORUM_ID"],
	"USE_CAPTCHA" => $arParams["PHOTO"]["ALL"]["USE_CAPTCHA"],
	"POST_FIRST_MESSAGE" => $arParams["PHOTO"]["ALL"]["POST_FIRST_MESSAGE"] ?? null,
	"PREORDER" => $arParams["PHOTO"]["ALL"]["PREORDER"],
	"SHOW_LINK_TO_FORUM" => "N",
	"BLOG_URL" => $arParams["PHOTO"]["ALL"]["BLOG_URL"],
	"PATH_TO_BLOG" => $arParams["PHOTO"]["ALL"]["PATH_TO_BLOG"],
	"PATH_TO_USER" => $arParams["PHOTO"]["ALL"]["PATH_TO_USER"],
	"NAME_TEMPLATE" => $arParams["PHOTO"]["ALL"]["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["PHOTO"]["ALL"]["SHOW_LOGIN"] ?? null
];

$sliderComponentParams = [
	"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
	"PAGE_NAME" => "INDEX",
	"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
	"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
	"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
	"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"],
	"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"],
	"INDEX_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
	"GALLERIES_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERIES"],
	"GALLERY_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERY_EDIT"],
	"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"],
	"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT_ICON"],
	"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"],
	"ONLY_ONE_GALLERY" => $arParams["PHOTO"]["ALL"]["ONLY_ONE_GALLERY"],
	"GALLERY_GROUPS" => $arParams["PHOTO"]["ALL"]["GALLERY_GROUPS"],
	"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,
	"SET_NAV_CHAIN" => "N",
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
	"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
	'PARENT_FATAL_ERROR' => $arParams['FATAL_ERROR'],
	'PARENT_ERROR_MESSAGE' => $arParams['ERROR_MESSAGE'],
	'PARENT_NOTE_MESSAGE' => $arParams['NOTE_MESSAGE'],
	'PARENT_PAGE' => 'group_photo_section',
	'PARENT_PARAMS_SECTION' => $photogallerySectionComponentParams,
	'PARENT_PARAMS_DETAIL_LIST' => $photogalleryDetailListComponentParams,
	'PARENT_PARAMS_SECTION_LIST' => $photogallerySectionListComponentParams,
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:photogallery.user',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $sliderComponentParams,
		'POPUP_COMPONENT_PARENT' => $component,
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'USE_UI_TOOLBAR' => 'Y',
	]
);
