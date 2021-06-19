<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "group_blog";
$blogPageId = 'moderation';
include("util_group_menu.php");
include("util_group_profile.php");

if (SITE_TEMPLATE_ID === 'bitrix24')
{
	include('util_group_blog_menu.php');
}

if($arParams["PATH_TO_USER_POST"] == '')
{
	$arParams["PATH_TO_USER_POST"] = "/company/personal/user/#user_id#/blog/#post_id#/";
}

if($arParams["PATH_TO_USER_POST_EDIT"] == '')
{
	$arParams["PATH_TO_USER_POST_EDIT"] = "/company/personal/user/#user_id#/blog/edit/#post_id#/";
}

$componentParameters = [
	"MESSAGE_COUNT" => "25",
	"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
	"PATH_TO_BLOG" => $arResult["PATH_TO_USER_BLOG"],
	"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
	"PATH_TO_BLOG_CATEGORY" => $arResult["PATH_TO_BLOG_CATEGORY"],
	"PATH_TO_POST" => $arParams["PATH_TO_USER_POST"],
	"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_POST_EDIT"],
	"PATH_TO_USER" => $arParams["PATH_TO_USER"],
	"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
	"USER_ID" => $arResult["VARIABLES"]["user_id"],
	"CACHE_TYPE" => $arResult["CACHE_TYPE"],
	"CACHE_TIME" => $arResult["CACHE_TIME"],
	"CACHE_TIME_LONG" => "604800",
	"SET_NAV_CHAIN" => "N",
	"SET_TITLE" => $arResult["SET_TITLE"],
	"NAV_TEMPLATE" => "",
	"POST_PROPERTY_LIST" => array(),
	"USER_VAR" => $arResult["ALIASES"]["user_id"],
	"PAGE_VAR" => $arResult["ALIASES"]["blog_page"],
	"POST_VAR" => $arResult["ALIASES"]["post_id"],
	"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"],
	"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
	"USE_SOCNET" => "Y",
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
	"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
	"PATH_TO_VIDEO_CALL" => $arResult["PATH_TO_VIDEO_CALL"],
	"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
	"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
	"ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"],
	"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
	"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
	"VERSION" => 2,
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'USE_PADDING' => false,
		'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.blog.moderation',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParameters,
		'POPUP_COMPONENT_PARENT' => $this->getComponent(),
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'USE_UI_TOOLBAR' => 'Y',
	]
);
