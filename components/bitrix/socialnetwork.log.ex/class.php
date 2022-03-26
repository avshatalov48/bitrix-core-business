<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\ComponentHelper;

if (!Loader::includeModule('socialnetwork'))
{
	ShowError(Loc::getMessage('SONET_LOG_LIST_SONET_MODULE_NOT_INSTALLED'));
	return false;
}

final class SocialnetworkLogList extends \Bitrix\Socialnetwork\Component\LogList
{
	protected function setTitle(array $options = []): void
	{
		global $APPLICATION;

		$title = Loc::getMessage(\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? 'SONET_LOG_LIST_PAGE_TITLE2' : 'SONET_LOG_LIST_PAGE_TITLE');

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->setTitle($title);

			if (!empty($options['GROUP']))
			{
				$APPLICATION->setPageProperty('title', ComponentHelper::getWorkgroupPageTitle([
					'WORKGROUP_NAME' => $options['GROUP']['NAME'],
					'TITLE' => $title
				]));
			}
		}

		if ($this->arParams['SET_NAV_CHAIN'] !== 'N')
		{
			$APPLICATION->addChainItem($title);
		}
	}

	public function executeComponent()
	{
		ComponentHelper::setModuleUsed();
		CPageOption::setOptionString('main', 'nav_page_in_session', 'N');

		$this->arResult = $this->prepareData();

		if (!empty($this->getErrors()))
		{
			ob_start();
			$this->printErrors();
			$this->arResult["FatalError"] = ob_get_contents();
			$this->arResult["ErrorList"] = $this->getErrors();
			ob_end_clean();
		}

		$this->includeComponentTemplate();

		if (
			isset($this->arParams['TARGET'])
			&& $this->arParams['TARGET'] === 'page'
		)
		{
			if (
				isset($this->arResult["ErrorList"])
				&& is_array($this->arResult["ErrorList"])
				&& !empty($this->arResult["ErrorList"])
			)
			{
				$this->errorCollection->add($this->arResult["ErrorList"]);
			}

			$this->arResult['LAST_TS'] = ($this->arResult['LAST_ENTRY_DATE_TS'] ? intval($this->arResult['LAST_ENTRY_DATE_TS']) : 0);
			$this->arResult['LAST_ID'] = ($this->arResult['dateLastPageId'] ? intval($this->arResult['dateLastPageId']) : 0);
			$this->arResult['EMPTY'] = (
				!$this->arResult["Events"]
				|| !is_array($this->arResult["Events"])
				|| count($this->arResult["Events"]) <= 0
					? 'Y'
					: 'N'
			);
		}

		return $this->arResult;
	}

	protected function listKeysSignedParameters()
	{
		return [

			'PATH_TO_USER',
			'PATH_TO_GROUP',
			'PATH_TO_SMILE',
			'PATH_TO_USER_MICROBLOG',
			'PATH_TO_GROUP_MICROBLOG',
			'PATH_TO_USER_BLOG_POST',
			'PATH_TO_USER_MICROBLOG_POST',
			'PATH_TO_USER_BLOG_POST_EDIT',
			'PATH_TO_USER_BLOG_POST_IMPORTANT',
			'PATH_TO_GROUP_BLOG_POST',
			'PATH_TO_GROUP_MICROBLOG_POST',
			'PATH_TO_USER_PHOTO',
			'PATH_TO_GROUP_PHOTO',
			'PATH_TO_USER_PHOTO_SECTION',
			'PATH_TO_GROUP_PHOTO_SECTION',
			'PATH_TO_USER_PHOTO_ELEMENT',
			'PATH_TO_GROUP_PHOTO_ELEMENT',
			'PATH_TO_SEARCH_TAG',
			'PATH_TO_CONPANY_DEPARTMENT',
			'PATH_TO_LOG_ENTRY',

			'USE_FOLLOW',
			'USE_FAVORITES',
			'PAGE_SIZE',
			'PUBLIC_MODE',
			'SHOW_REFRESH',
			'SHOW_NAV_STRING',
			'MODE',
//			'EMPTY_EXPLICIT',
			'FILTER_ID',
			'ORDER',
			'DESTINATION',
			'DESTINATION_AUTHOR_ID',
			'DISPLAY',

			'LOG_ID',
			'ENTITY_TYPE',
			'GROUP_ID',
			'USER_ID',
			'EVENT_ID',

			'NAME_TEMPLATE',
			'SHOW_LOGIN',
			'DATE_TIME_FORMAT',
			'DATE_TIME_FORMAT_WITHOUT_YEAR',
			'SHOW_YEAR',
			'CACHE_TYPE',
			'CACHE_TIME',
			'SHOW_EVENT_ID_FILTER',
			'SET_LOG_CACHE',
			'USE_COMMENTS',
			'CURRENT_USER_ID',

			'PHOTO_USER_IBLOCK_TYPE',
			'PHOTO_USER_IBLOCK_ID',
			'PHOTO_USE_COMMENTS',
			'PHOTO_COMMENTS_TYPE',
			'PHOTO_FORUM_ID',
			'PHOTO_USE_CAPTCHA',
			'PHOTO_THUMBNAIL_SIZE',

			'FORUM_ID',
			'CONTAINER_ID',
			'SHOW_RATING',
			'RATING_TYPE',
			'AVATAR_SIZE',
			'AVATAR_SIZE_COMMENT',
			'AVATAR_SIZE_COMMON',
			'AUTH',

			'CHECK_COMMENTS_PERMS',
			'BLOG_GROUP_ID',
			'BLOG_NO_URL_IN_COMMENTS',
			'BLOG_NO_URL_IN_COMMENTS_AUTHORITY',
			'BLOG_COMMENT_ALLOW_VIDEO',
			'BLOG_COMMENT_ALLOW_IMAGE_UPLOAD',

			'IS_CRM',
			'CRM_ENTITY_TYPE',
			'CRM_ENTITY_ID',
			'CRM_EXTENDED_MODE',
			'CRM_ENABLE_ACTIVITY_EDITOR',
			'HIDE_EDIT_FORM',
		];
	}
}