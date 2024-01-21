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
/** @var array $componentParams */

require_once __DIR__ . '/params.php';

$componentParams = array_merge(
	$componentParams,
	[
		'PAGE' => 'group_discussions',
		'PAGE_TYPE' => 'group',
		'PAGE_ID' => 'discussions',
		'GROUP_ID' => $arResult['VARIABLES']['group_id'],
	],
);

$listComponentParams = array_merge(
	$componentParams,
	[

	],
);

$menuComponentParams = array_merge(
	$componentParams,
	[

	],
);

$toolbarComponentParams = array_merge(
	$componentParams,
	[

	],
);

$contentComponentParams = array_merge(
	$componentParams,
	[
		'SHOW_YEAR' => 'Y',
		'SHOW_RATING' => 'Y',
		'LOG_NEW_TEMPLATE' => 'Y',
		'LOG_THUMBNAIL_SIZE' => 100,
		'LOG_COMMENT_THUMBNAIL_SIZE' => 100,
		'DATE_TIME_FORMAT' => $arResult['DATE_TIME_FORMAT'],
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'SHOW_LOGIN' => 'Y',
		'RATING_TYPE' => '',

		'PATH_TO_MESSAGES_CHAT' => $arResult['PATH_TO_MESSAGES_CHAT'],
		'PATH_TO_USER_BLOG_POST_IMPORTANT' => $arResult['PATH_TO_USER_BLOG_POST_IMPORTANT'],
		'PATH_TO_SEARCH_TAG' => $arResult['PATH_TO_SEARCH_TAG'],
		'PATH_TO_VIDEO_CALL' => $arResult['PATH_TO_VIDEO_CALL'],
		'PATH_TO_COMPANY_DEPARTMENT' => $arResult['PATH_TO_COMPANY_DEPARTMENT'],
		'PATH_TO_GROUP_PHOTO_SECTION' => $arResult['PATH_TO_GROUP_PHOTO_SECTION'],
	],
);

require_once __DIR__ . '/template.php';