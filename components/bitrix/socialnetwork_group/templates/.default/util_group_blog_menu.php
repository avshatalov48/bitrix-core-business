<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $pageId */
/** @var string $blogPageId */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->IncludeComponent(
	'bitrix:socialnetwork.blog.menu',
	'',
	[
		'PATH_TO_USER' => $arParams['PATH_TO_USER'],
		'PATH_TO_POST_EDIT' => $arResult['PATH_TO_GROUP_BLOG_POST_EDIT'],
		'PATH_TO_DRAFT' => $arResult['PATH_TO_GROUP_BLOG_DRAFT'],
		'USER_ID' => $arResult['VARIABLES']['user_id'] ?? 0,
		'USER_VAR' => $arResult['ALIASES']['user_id'] ?? '',
		'PAGE_VAR' => $arResult['ALIASES']['blog_page'] ?? '',
		'POST_VAR' => $arResult['ALIASES']['post_id'] ?? '',
		'SOCNET_GROUP_ID' => $arResult['VARIABLES']['group_id'],
		'PATH_TO_GROUP_BLOG' => $arResult['PATH_TO_GROUP_BLOG'],
		'PATH_TO_GROUP' => $arResult['PATH_TO_GROUP'],
		'SET_NAV_CHAIN' => $arResult['SET_NAV_CHAIN'],
		'GROUP_ID' => $arParams['BLOG_GROUP_ID'],
		'PATH_TO_MODERATION' => $arResult['PATH_TO_GROUP_BLOG_MODERATION'],
		'CURRENT_PAGE' => $blogPageId,
		'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE'],
		'PATH_TO_BLOG' => (SITE_TEMPLATE_ID === 'bitrix24' ? $arResult['PATH_TO_GROUP'] : $arResult['PATH_TO_GROUP_BLOG']),
	],
	$this->getComponent()
);
