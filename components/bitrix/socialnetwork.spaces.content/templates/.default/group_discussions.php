<?php

use Bitrix\Socialnetwork\Livefeed\Context\Context;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

$params = [
	'CONTEXT' => Context::SPACES,
	'ENTITY_TYPE' => '',
	'GROUP_ID' => $arParams['GROUP_ID'],
	'PATH_TO_USER' => $arParams['PATH_TO_USER'],
	'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
	'SET_TITLE' => 'Y',
	'AUTH' => 'Y',
	'SET_NAV_CHAIN' => 'N',
	'PATH_TO_MESSAGES_CHAT' => $arParams['PATH_TO_MESSAGES_CHAT'],
	'PATH_TO_VIDEO_CALL' => $arParams['PATH_TO_VIDEO_CALL'],
	'PATH_TO_USER_BLOG_POST_IMPORTANT' => $arParams['PATH_TO_USER_BLOG_POST_IMPORTANT'],
	'PATH_TO_CONPANY_DEPARTMENT' => $arParams['PATH_TO_COMPANY_DEPARTMENT'],
	'PATH_TO_GROUP_PHOTO_SECTION' => $arParams['PATH_TO_GROUP_PHOTO_SECTION'],
	'PATH_TO_SEARCH_TAG' => $arParams['PATH_TO_SEARCH_TAG'],
	'DATE_TIME_FORMAT' => $arParams['DATE_TIME_FORMAT'],
	'SHOW_YEAR' => $arParams['SHOW_YEAR'],
	'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
	'SHOW_LOGIN' => $arParams['SHOW_LOGIN'],
	'SUBSCRIBE_ONLY' => 'N',
	'SHOW_EVENT_ID_FILTER' => 'Y',
	'SHOW_FOLLOW_FILTER' => 'N',
	'USE_COMMENTS' => 'Y',
	'PHOTO_THUMBNAIL_SIZE' => '48',
	'PAGE_ISDESC' => 'N',
	'AJAX_MODE' => 'N',
	'AJAX_OPTION_SHADOW' => 'N',
	'AJAX_OPTION_HISTORY' => 'N',
	'AJAX_OPTION_JUMP' => 'N',
	'AJAX_OPTION_STYLE' => 'Y',
	'CONTAINER_ID' => 'log_external_container',
	'PAGE_SIZE' => 10,
	'SHOW_RATING' => $arParams['SHOW_RATING'],
	'RATING_TYPE' => $arParams['RATING_TYPE'],
	'SHOW_SETTINGS_LINK' => 'Y',
	'AVATAR_SIZE' => $arParams['LOG_THUMBNAIL_SIZE'],
	'AVATAR_SIZE_COMMENT' => $arParams['LOG_COMMENT_THUMBNAIL_SIZE'],
	'NEW_TEMPLATE' => $arParams['LOG_NEW_TEMPLATE'],
	'SET_LOG_CACHE' => 'Y',
	'SPACE_USER_ID' => $arResult['userId'],
];
?>

<div class="sn-spaces__group-discussions">
	<?php
	$APPLICATION->includeComponent(
		'bitrix:socialnetwork.log.ex',
		'',
		$params,
		$component,
		['HIDE_ICONS' => 'Y']
	);

	if ($arResult['storage'])
	{
		$APPLICATION->IncludeComponent(
			'bitrix:disk.file.upload',
			'',
			[
				'STORAGE' => $arResult['storage'],
				'FOLDER' => $arResult['folder'],
				'CID' => 'FolderList',
				'DROPZONE' => 'document.getElementById("bx-disk-container")',
			],
			$component,
			["HIDE_ICONS" => "Y"]
		);
	}
	?>
</div>