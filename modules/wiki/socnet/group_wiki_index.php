<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
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

$pageId = 'group_wiki';
include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_menu.php');
include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/socialnetwork_group/templates/.default/util_group_profile.php');

$componentParams = [
	'WIKI_MENU_PARAMS' => [
		'IBLOCK_TYPE' => COption::GetOptionString('wiki', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('wiki', 'socnet_iblock_id'),
		'ELEMENT_NAME' => $arResult['VARIABLES']['title'] ?? $arResult['VARIABLES']['wiki_name'],
		'MENU_TYPE' => 'page',
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_WIKI_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_WIKI_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_GROUP_WIKI_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_GROUP_WIKI_POST_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY_DIFF'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'USE_REVIEW' => COption::GetOptionString('wiki', 'socnet_use_review'),
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id']
	],
	'WIKI_SHOW_PARAMS' => [
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_WIKI_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_WIKI_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_GROUP_WIKI_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_GROUP_WIKI_POST_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY_DIFF'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'SHOW_RATING' => $arResult['SHOW_RATING'],
		'RATING_TYPE' => $arResult['RATING_TYPE'],
		'PATH_TO_USER' => $arResult['PATH_TO_USER'],
		'IBLOCK_TYPE' => COption::GetOptionString('wiki', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('wiki', 'socnet_iblock_id'),
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => $arResult['CACHE_TIME'],
		'ELEMENT_NAME' => $arResult['VARIABLES']['title'] ?? $arResult['VARIABLES']['wiki_name'],
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE']
	],
	'WIKI_DISCUSSION_PARAMS' => [
		'PATH_TO_POST' => $arResult['PATH_TO_GROUP_WIKI_POST'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_WIKI_POST_EDIT'],
		'PATH_TO_CATEGORIES' => $arResult['PATH_TO_GROUP_WIKI_CATEGORIES'],
		'PATH_TO_DISCUSSION' => $arResult['PATH_TO_GROUP_WIKI_POST_DISCUSSION'],
		'PATH_TO_HISTORY' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY'],
		'PATH_TO_HISTORY_DIFF' => $arResult['PATH_TO_GROUP_WIKI_POST_HISTORY_DIFF'],
		'PAGE_VAR' => 'title',
		'OPER_VAR' => 'oper',
		'IBLOCK_TYPE' => COption::GetOptionString('wiki', 'socnet_iblock_type_id'),
		'IBLOCK_ID' => COption::GetOptionString('wiki', 'socnet_iblock_id'),
		'USE_REVIEW' => COption::GetOptionString('wiki', 'socnet_use_review'),
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => $arResult['CACHE_TIME'],
		'ELEMENT_NAME' => $arResult['VARIABLES']['title'] ?? $arResult['VARIABLES']['wiki_name'],
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
		'MESSAGES_PER_PAGE' => COption::GetOptionString('wiki', 'socnet_message_per_page',20),
		'USE_CAPTCHA' => COption::GetOptionString('wiki', 'socnet_use_captcha'),
		'FORUM_ID' => COption::GetOptionString('wiki', 'socnet_forum_id'),
		'PATH_TO_SMILE' => $arResult['PATH_TO_SMILE'],
		'URL_TEMPLATES_READ' => '',
		'SHOW_LINK_TO_FORUM' => 'N',
		'POST_FIRST_MESSAGE' => 'Y',
		'SHOW_RATING' => $arResult['SHOW_RATING'],
		'RATING_TYPE' => $arResult['RATING_TYPE'],
		'PATH_TO_USER' => $arResult['PATH_TO_USER'],
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE']
	],
];

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.wiki.wrapper',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $componentParams,
		'POPUP_COMPONENT_PARENT' => $component,
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_TYPE' => 'SONET_GROUP',
		'POPUP_COMPONENT_BITRIX24_THEME_ENTITY_ID' => $arResult['VARIABLES']['group_id'],
		'USE_UI_TOOLBAR' => 'Y',
		'UI_TOOLBAR_FAVORITES_TITLE_TEMPLATE' => (isset($arParams['HIDE_OWNER_IN_TITLE']) && $arParams['HIDE_OWNER_IN_TITLE'] === 'Y' ? $arResult['PAGES_TITLE_TEMPLATE'] : ''),
	]
);

$APPLICATION->SetPageProperty('FavoriteTitleTemplate', (isset($arParams['HIDE_OWNER_IN_TITLE']) && $arParams['HIDE_OWNER_IN_TITLE'] === 'Y' ? $arResult['PAGES_TITLE_TEMPLATE'] : ''));
