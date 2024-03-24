<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

use Bitrix\Intranet\Integration\Wizards\Portal\Ids;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Livefeed\Context\Context;

Loader::includeModule('intranet');
?>

<div class="sn-spaces__user-discussions">
<?php
	$APPLICATION->includeComponent(
		'bitrix:socialnetwork.log.ex',
		'',
		[
			'CONTEXT' => Context::SPACES,
			'DISPLAY' => 'commonSpace',
			'PATH_TO_LOG_ENTRY' => '/company/personal/log/#log_id#/',
			'PATH_TO_USER' => '/company/personal/user/#user_id#/',
			'PATH_TO_MESSAGES_CHAT' => '/company/personal/messages/chat/#user_id#/',
			'PATH_TO_VIDEO_CALL' => '/company/personal/video/#user_id#/',
			'PATH_TO_GROUP' => '/spaces/group/#group_id#/',
			'PATH_TO_SMILE' => '/bitrix/images/socialnetwork/smile/',
			'PATH_TO_USER_MICROBLOG' => '/company/personal/user/#user_id#/blog/',
			'PATH_TO_GROUP_MICROBLOG' => '/workgroups/group/#group_id#/blog/',
			'PATH_TO_USER_BLOG_POST' => '/company/personal/user/#user_id#/blog/#post_id#/',
			'PATH_TO_USER_MICROBLOG_POST' => '/company/personal/user/#user_id#/blog/#post_id#/',
			'PATH_TO_USER_BLOG_POST_EDIT' => '/company/personal/user/#user_id#/blog/edit/#post_id#/',
			'PATH_TO_USER_BLOG_POST_IMPORTANT' => '/company/personal/user/#user_id#/blog/important/',
			'PATH_TO_GROUP_BLOG_POST' => '/workgroups/group/#group_id#/blog/#post_id#/',
			'PATH_TO_GROUP_MICROBLOG_POST' => '/workgroups/group/#group_id#/blog/#post_id#/',
			'PATH_TO_USER_PHOTO' => '/company/personal/user/#user_id#/photo/',
			'PATH_TO_GROUP_PHOTO' => '/workgroups/group/#group_id#/photo/',
			'PATH_TO_USER_PHOTO_SECTION' => '/company/personal/user/#user_id#/photo/album/#section_id#/',
			'PATH_TO_GROUP_PHOTO_SECTION' => '/workgroups/group/#group_id#/photo/album/#section_id#/',
			'PATH_TO_USER_PHOTO_ELEMENT' => '/company/personal/user/#user_id#/photo/photo/#section_id#/#element_id#/',
			'PATH_TO_GROUP_PHOTO_ELEMENT' => '/workgroups/group/#group_id#/photo/#section_id#/#element_id#/',
			'PATH_TO_SEARCH_TAG' => '/search/?tags=#tag#',
			'SET_NAV_CHAIN' => 'Y',
			'SET_TITLE' => 'Y',
			'ITEMS_COUNT' => '32',
			'NAME_TEMPLATE' => '',
			'SHOW_LOGIN' => 'Y',
			'DATE_TIME_FORMAT' => CIntranetUtils::getCurrentDateTimeFormat(),
			'DATE_TIME_FORMAT_WITHOUT_YEAR' => CIntranetUtils::getCurrentDateTimeFormat([
				'woYear' => true
			]),
			'SHOW_YEAR' => 'M',
			'CACHE_TYPE' => 'A',
			'CACHE_TIME' => '3600',
			'PATH_TO_CONPANY_DEPARTMENT' => '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#',
			'SHOW_EVENT_ID_FILTER' => 'Y',
			'SHOW_SETTINGS_LINK' => 'Y',
			'SET_LOG_CACHE' => 'Y',
			'USE_COMMENTS' => 'Y',
			'BLOG_ALLOW_POST_CODE' => 'Y',
			'BLOG_GROUP_ID' => Ids::getBlogId(),
			'PHOTO_USER_IBLOCK_TYPE' => 'photos',
			'PHOTO_USER_IBLOCK_ID' => Ids::getIblockId('user_photogallery'),
			'PHOTO_USE_COMMENTS' => 'Y',
			'PHOTO_COMMENTS_TYPE' => 'FORUM',
			'PHOTO_FORUM_ID' => Ids::getForumId('PHOTOGALLERY_COMMENTS'),
			'PHOTO_USE_CAPTCHA' => 'N',
			'FORUM_ID' => Ids::getForumId('USERS_AND_GROUPS'),
			'PAGER_DESC_NUMBERING' => 'N',
			'AJAX_MODE' => 'N',
			'AJAX_OPTION_SHADOW' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'Y',
			'CONTAINER_ID' => 'log_external_container',
			'SHOW_RATING' => '',
			'RATING_TYPE' => '',
			'NEW_TEMPLATE' => 'Y',
			'AVATAR_SIZE' => 100,
			'AVATAR_SIZE_COMMENT' => 100,
			'AVATAR_SIZE_COMMON' => 100,
			'AUTH' => 'Y',
			'SPACE_USER_ID' => $arResult['userId'],
			'GROUP_ID' => $arResult['groupId'],
		]
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
