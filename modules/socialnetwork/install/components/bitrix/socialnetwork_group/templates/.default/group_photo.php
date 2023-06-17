<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
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

$pageId = "group_photo";
include("util_group_menu.php");
include("util_group_profile.php");

$sliderComponentList = [
	'bitrix:photogallery.user',
];
$sliderComponentTemplateList = [
	''
];

$sliderComponentParamsList = [
	[
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"PAGE_NAME" => "INDEX",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"] ?? null,
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"] ?? null,
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"] ?? null,

		"SORT_BY" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_BY"] ?? null,
		"SORT_ORD" => $arParams["PHOTO"]["ALL"]["SECTION_SORT_ORD"] ?? null,

		"INDEX_URL" => $arResult["~PATH_TO_GROUP_PHOTO"] ?? null,
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"] ?? null,
		"GALLERIES_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERIES"] ?? null,
		"GALLERY_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERY_EDIT"] ?? null,
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"] ?? null,
		"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT_ICON"] ?? null,
		"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"] ?? null,

		"ONLY_ONE_GALLERY" => $arParams["PHOTO"]["ALL"]["ONLY_ONE_GALLERY"] ?? null,
		"GALLERY_GROUPS" => $arParams["PHOTO"]["ALL"]["GALLERY_GROUPS"] ?? null,
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,
		"RETURN_ARRAY" => "Y",
		"SET_TITLE" => "N",
		"SET_NAV_CHAIN" => "N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"] ?? null,
		'SHOW_CONTROLS' => (($arParams['PERMISSION'] ?? null) >= 'U' ? 'Y' : 'N'),
		'PARENT_FATAL_ERROR' => $arParams['FATAL_ERROR'] ?? null,
		'PARENT_ERROR_MESSAGE' => $arParams['ERROR_MESSAGE'] ?? null,
		'PARENT_NOTE_MESSAGE' => $arParams['NOTE_MESSAGE'] ?? null,
	]
];

if ($arParams['FATAL_ERROR'] !== 'Y')
{
	$sliderComponentList[] = 'bitrix:photogallery.section.list';
	$sliderComponentTemplateList[] = '';

	$sliderComponentParamsList[] = [
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
		"GALLERIES_URL" => $arResult["~PATH_TO_GROUP_PHOTO_GALLERIES"],
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"],
		"SECTION_EDIT_ICON_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT_ICON"],
		"UPLOAD_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_UPLOAD"],
		"PAGE_ELEMENTS" => $arParams["PHOTO"]["ALL"]["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PHOTO"]["ALL"]["PAGE_NAVIGATION_TEMPLATE"],
		"DATE_TIME_FORMAT" => $arParams["PHOTO"]["ALL"]["DATE_TIME_FORMAT_SECTION"],
		"ALBUM_PHOTO_THUMBS_SIZE" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => ($arResult["VARIABLES"]["SECTION_ID"] > 0 ? $arParams["SET_TITLE"] : "N"),
		"ADD_CHAIN_ITEM" => "N",
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"DISPLAY_AS_RATING" => $arParams["PHOTO"]["ALL"]["DISPLAY_AS_RATING"],
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
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => $sliderComponentList,
		'POPUP_COMPONENT_TEMPLATE_NAME' => $sliderComponentTemplateList,
		'POPUP_COMPONENT_PARAMS' => $sliderComponentParamsList,
		'POPUP_COMPONENT_PARENT' => $component,
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'USE_UI_TOOLBAR' => 'Y',
		'UI_TOOLBAR_FAVORITES_TITLE_TEMPLATE' => (isset($arParams['HIDE_OWNER_IN_TITLE']) && $arParams['HIDE_OWNER_IN_TITLE'] === 'Y' ? $arResult['PAGES_TITLE_TEMPLATE'] : ''),
	]
);

$APPLICATION->SetPageProperty('FavoriteTitleTemplate', (isset($arParams['HIDE_OWNER_IN_TITLE']) && $arParams['HIDE_OWNER_IN_TITLE'] === 'Y' ? $arResult['PAGES_TITLE_TEMPLATE'] : ''));
