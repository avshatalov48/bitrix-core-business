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

$pageId = 'group_photo';
include('util_group_menu.php');
include('util_group_profile.php');

$sliderComponentList = [
	'bitrix:photogallery.user',
];

$sliderComponentTemplateList = [
	'',
];

$sliderComponentParamsList = [
	[
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
	]
];

if ($arParams['FATAL_ERROR'] !== 'Y')
{
	$sliderComponentList[] = 'bitrix:photogallery.upload';
	$sliderComponentTemplateList[] = '';
	$sliderComponentParamsList[] = [
		"IBLOCK_TYPE" => $arParams["PHOTO_GROUP_IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["VARIABLES"]["GALLERY"]["CODE"],
		"IS_SOCNET" => "Y",
		"PERMISSION" => $arResult["VARIABLES"]["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"] ?? null,
		"GALLERY_SIZE" => $arParams["PHOTO"]["ALL"]["GALLERY_SIZE"] ?? null,

		"SECTIONS_TOP_URL" => "",
		"GALLERY_URL" => $arResult["~PATH_TO_GROUP_PHOTO"],
		"SECTION_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION"],
		"SECTION_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_SECTION_EDIT"],
		"DETAIL_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT"],
		"DETAIL_EDIT_URL" => $arResult["~PATH_TO_GROUP_PHOTO_ELEMENT_EDIT"],

		"UPLOADER_TYPE" => $arParams["PHOTO_UPLOADER_TYPE"],
		"APPLET_LAYOUT" => $arParams["PHOTO_APPLET_LAYOUT"] ?? null,
		"UPLOAD_MAX_FILE" => $arParams["PHOTO"]["ALL"]["UPLOAD_MAX_FILE"],
		"UPLOAD_MAX_FILE_SIZE" => $arParams["PHOTO"]["ALL"]["UPLOAD_MAX_FILE_SIZE"],
		"ADDITIONAL_SIGHTS" => $arParams["PHOTO"]["ALL"]["~ADDITIONAL_SIGHTS"] ?? null,
		"MODERATION" => $arParams["PHOTO"]["ALL"]["MODERATION"],
		"PUBLIC_BY_DEFAULT" => "Y",
		"APPROVE_BY_DEFAULT" => "Y",

		"USE_WATERMARK" => "Y",
		"SHOW_WATERMARK" => $arParams["PHOTO_SHOW_WATERMARK"] ?? null,
		"WATERMARK_RULES" => $arParams["PHOTO"]["ALL"]["WATERMARK_RULES"],
		"WATERMARK_TYPE" => $arParams["PHOTO"]["ALL"]["WATERMARK_TYPE"],
		"WATERMARK_TEXT" => $arParams["PHOTO"]["ALL"]["WATERMARK_TEXT"],
		"WATERMARK_COLOR" => $arParams["PHOTO"]["ALL"]["WATERMARK_COLOR"],
		"WATERMARK_SIZE" => $arParams["PHOTO"]["ALL"]["WATERMARK_SIZE"],
		"WATERMARK_FILE" => $arParams["PHOTO"]["ALL"]["WATERMARK_FILE"],
		"WATERMARK_FILE_ORDER" => $arParams["PHOTO"]["ALL"]["WATERMARK_FILE_ORDER"],
		"WATERMARK_POSITION" => $arParams["PHOTO"]["ALL"]["WATERMARK_POSITION"],
		"WATERMARK_TRANSPARENCY" => $arParams["PHOTO"]["ALL"]["WATERMARK_TRANSPARENCY"],
		"PATH_TO_FONT" => $arParams["PHOTO"]["ALL"]["PATH_TO_FONT"],
		"WATERMARK_MIN_PICTURE_SIZE" => $arParams["PHOTO"]["ALL"]["WATERMARK_MIN_PICTURE_SIZE"],

		"ALBUM_PHOTO_WIDTH" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_WIDTH" => $arParams["PHOTO"]["ALL"]["ALBUM_PHOTO_THUMBS_SIZE"],

		"THUMBNAIL_SIZE" => $arParams["PHOTO"]["ALL"]["THUMBNAIL_SIZE"],
		"JPEG_QUALITY1" => $arParams["PHOTO"]["ALL"]["JPEG_QUALITY1"],
		"PREVIEW_SIZE" => $arParams["PHOTO"]["ALL"]["PREVIEW_SIZE"],
		"JPEG_QUALITY2" => $arParams["PHOTO"]["ALL"]["JPEG_QUALITY2"],
		"ORIGINAL_SIZE" => $arParams["PHOTO"]["ALL"]["ORIGINAL_SIZE"],
		"JPEG_QUALITY" => $arParams["PHOTO"]["ALL"]["JPEG_QUALITY"],

		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => "N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
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
