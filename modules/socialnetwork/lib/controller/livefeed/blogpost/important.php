<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed\BlogPost;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Controller\Base;

class Important extends Base
{
	public function getUsersAction(array $params = [])
	{
		global $CACHE_MANAGER;

		$result = [
			'post_id' => $params['POST_ID'],
			'items' => [],
			'StatusPage' => "done",
			'RecordCount' => 0
		];

		$pageSize = 10;
		$postId = (isset($params['POST_ID']) && (int)$params['POST_ID'] > 0 ? (int)$params['POST_ID'] : 0);
		$propertyName = (isset($params['NAME']) && $params['NAME'] <> '' ? trim($params['NAME']) : 'BLOG_POST_IMPRTNT');
		$propertyValue = (isset($params['VALUE']) && $params['VALUE'] <> '' ? trim($params['VALUE']) : 'Y');
		$pathToUser = (isset($params['PATH_TO_USER']) && $params['PATH_TO_USER'] <> '' ? $params['PATH_TO_USER'] : SITE_DIR.'company/personal/user/#USER_ID#/');
		$nameTemplate = (isset($params['NAME_TEMPLATE']) && $params['NAME_TEMPLATE'] <> '' ? $params['NAME_TEMPLATE'] :  \CSite::getNameFormat(false));
		$pageNumber = (isset($params['PAGE_NUMBER']) && intval($params['PAGE_NUMBER']) > 0 ? intval($params['PAGE_NUMBER']) : 1);
		$avatarSize = (isset($params['AVATAR_SIZE']) && (int)$params['AVATAR_SIZE'] > 0 ? (int)$params['AVATAR_SIZE'] : 21);

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_POST_ID_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_POST_ID_EMPTY'));
			return null;
		}

		$cacheTime = ($pageNumber >= 2 ? 0 : 600);

		$cache = new \CPHPCache();
		$cacheId = 'blog_post_param_'.serialize([
			$pageSize,
			$postId,
			$propertyName,
			$pageNumber,
			$propertyValue,
			$nameTemplate,
			$pathToUser,
			$avatarSize
		]);

		$cachePath = $CACHE_MANAGER->getCompCachePath(\CComponentEngine::makeComponentPath('socialnetwork.blog.blog')).'/'.$postId;

		$result = (
			$cacheTime > 0
			&& $cache->initCache($cacheTime, $cacheId, $cachePath)
				? $cache->getVars()
				: []
		);

		if (
			(
				!is_array($result)
				|| empty($result)
			)
			&& Loader::includeModule('blog')
		)
		{
			$mailInstalled = ModuleManager::isModuleInstalled('mail');
			$extranetInstalled = ModuleManager::isModuleInstalled('extranet');
			$userIdList = [];

			$res = \CBlogUserOptions::getList(
				[
					'RANK' => 'DESC',
					'OWNER_ID' => $this->getCurrentUser()->getId()
				],
				[
					'POST_ID' => $postId,
					'NAME' => $propertyName,
					'VALUE' => $propertyValue,
					'USER_ACTIVE' => 'Y'
				],
				[
					'iNumPage' => $pageNumber,
					'bDescPageNumbering' => false,
					'nPageSize' => $pageSize,
					'bShowAll' => false,
					'SELECT' => [ 'USER_ID', 'USER_NAME', 'USER_LAST_NAME', 'USER_SECOND_NAME', 'USER_LOGIN', 'USER_PERSONAL_PHOTO' ]
				]
			);

			$result['items'] = [];
			if ($res && ($userOptionFields = $res->fetch()))
			{
				$result['StatusPage'] = (
					(
						$res->NavPageNomer >= $res->NavPageCount
						|| $pageSize > $res->NavRecordCount
					)
						? 'done'
						: 'continue'
				);
				$result['RecordCount'] = $res->NavRecordCount;
				if ($pageNumber <= $res->NavPageCount)
				{
					do {
						$userFields = [
							'ID' =>  $userOptionFields['USER_ID'],
							'PHOTO' => '',
							'PHOTO_SRC' => '',
							'FULL_NAME' => \CUser::formatName(
								$nameTemplate,
								[
									'NAME' => $userOptionFields['USER_NAME'],
									'LAST_NAME' => $userOptionFields['USER_LAST_NAME'],
									'SECOND_NAME' => $userOptionFields['USER_SECOND_NAME'],
									'LOGIN' => $userOptionFields['USER_LOGIN']
								]
							),
							'URL' => \CUtil::jsEscape(
								\CComponentEngine::makePathFromTemplate(
									$pathToUser,
									[
										'UID' => $userOptionFields['USER_ID'],
										'user_id' => $userOptionFields['USER_ID'],
										'USER_ID' => $userOptionFields['USER_ID']
									]
								)
							),
							'TYPE' => ''
						];
						if (array_key_exists('USER_PERSONAL_PHOTO', $userOptionFields))
						{
							$fileFields = \CFile::resizeImageGet(
								$userOptionFields['USER_PERSONAL_PHOTO'],
								[ 'width' => $avatarSize, 'height' => $avatarSize ],
								BX_RESIZE_IMAGE_EXACT,
								false,
								false,
								true
							);
							$userFields['PHOTO_SRC'] = ($fileFields['src'] ?? '');
							$userFields["PHOTO"] = \CFile::showImage(
								($fileFields['src'] ?? ''),
								21,
								21,
								'border=0'
							);
						}

						$result['items'][$userFields['ID']] = $userFields;
						$userIdList[] = $userFields['ID'];
					} while ($userOptionFields = $res->fetch());

					if (
						!empty($userIdList)
						&& ($mailInstalled || $extranetInstalled)
					)
					{
						$select = [];
						if ($mailInstalled)
						{
							$select["FIELDS"] = [ 'ID', 'EXTERNAL_AUTH_ID' ];
						}
						if ($extranetInstalled)
						{
							$select["SELECT"] = [ 'UF_DEPARTMENT' ];
						}

						$res = \CUser::getList(
							"ID",
							"ASC",
							[ 'ID' => implode("|", $userIdList) ],
							$select
						);
						while($userFields = $res->fetch())
						{
							if (
								$mailInstalled
								&& $userFields['EXTERNAL_AUTH_ID'] == 'email'
							)
							{
								$result['items'][$userFields['ID']]['TYPE'] = 'mail';
							}
							elseif (
								$extranetInstalled
								&& (
									empty($userFields['UF_DEPARTMENT'])
									|| intval($userFields['UF_DEPARTMENT'][0]) <= 0
								)
							)
							{
								$result['items'][$userFields['ID']]['TYPE'] = 'extranet';
							}
						}
					}
				}
			}
		}

		return $result;
	}

	public function voteAction(array $params = [])
	{
		global $CACHE_MANAGER;

		$currentUserId = $this->getCurrentUser()->getId();
		$postId = (isset($params['POST_ID']) && intval($params['POST_ID']) > 0 ? intval($params['POST_ID']) : 0);

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_POST_ID_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_POST_ID_EMPTY'));
			return null;
		}

		if (
			!$currentUserId
			|| !\CSocNetFeatures::isActiveFeature(SONET_ENTITY_USER, $currentUserId, 'blog')
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_NO_READ_PERMS'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_NO_READ_PERMS'));
			return null;
		}

		if (!Loader::includeModule('blog'))
		{
			$this->addError(new Error('SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_NO_BLOG_MODULE', 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_IMPORTANT_NO_BLOG_MODULE'));
			return null;
		}

		\CBlogUserOptions::setOption($postId, 'BLOG_POST_IMPRTNT', 'Y', $currentUserId);

		if (defined('BX_COMP_MANAGED_CACHE'))
		{
			$CACHE_MANAGER->clearByTag('BLOG_POST_IMPRTNT'.$postId);
			$CACHE_MANAGER->clearByTag('BLOG_POST_IMPRTNT'.$postId."_".$currentUserId);
			$CACHE_MANAGER->clearByTag('BLOG_POST_IMPRTNT'."_USER_".$currentUserId);
		}

		$options = [
			[
				'post_id' => $postId,
				'name' => 'BLOG_POST_IMPRTNT',
				'value' => 'Y'
			]
		];

		$res = getModuleEvents('socialnetwork', 'OnAfterCBlogUserOptionsSet');
		while ($eventFields = $res->fetch())
		{
			executeModuleEventEx($eventFields, [ $options, '', '' ]);
		}

		return [
			'success' => 'Y'
		];
	}
}

