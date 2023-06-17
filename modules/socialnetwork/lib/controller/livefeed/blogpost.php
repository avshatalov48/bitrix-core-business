<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Blog\Item\Permissions;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\ArgumentException;
use Bitrix\Socialnetwork\Controller\Base;
use Bitrix\Socialnetwork\Item\Helper;

class BlogPost extends Base
{
	public function getDataAction(array $params = []): ?array
	{
		$postId = (int)($params['postId'] ?? 0);
		$public = ($params['public'] ?? 'N');
		$groupReadOnly = ($params['groupReadOnly'] ?? 'N');
		$pathToPost = ($params['pathToPost'] ?? '');
		$voteId = (int)($params['voteId'] ?? 0);
		$checkModeration = ($params['checkModeration'] ?? 'N');

		$currentUserId = (int)$this->getCurrentUser()->getId();
		$currentModuleAdmin = \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false);

		$logPinnedUserId = 0;

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'));
			return null;
		}

		if (
			!Loader::includeModule('blog')
			|| !Loader::includeModule('socialnetwork')
			|| !($postItem = \Bitrix\Blog\Item\Post::getById($postId))
		)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'));
			return null;
		}

		$postFields = $postItem->getFields();

		$logId = 0;
		$logFavoritesUserId = 0;
		$allowModerate = false;

		if (
			$postFields['PUBLISH_STATUS'] === BLOG_PUBLISH_STATUS_READY
			&& $checkModeration === 'Y'
		)
		{
			$postSocnetPermsList = \CBlogPost::getSocNetPerms($postId);
			if (
				!empty($postSocnetPermsList['SG'])
				&& is_array($postSocnetPermsList['SG'])
			)
			{
				$groupIdList = array_keys($postSocnetPermsList['SG']);
				foreach($groupIdList as $groupId)
				{
					if (
						\CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'full_post', $currentModuleAdmin)
						|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'write_post')
						|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $groupId, 'blog', 'moderate_post')
					)
					{
						$allowModerate = true;
						break;
					}
				}
			}
			elseif(
				(int)$postFields['AUTHOR_ID'] === $currentUserId
				|| $currentModuleAdmin
			)
			{
				$allowModerate = true;
			}
		}

		$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

		$filter = array(
			"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
			"SOURCE_ID" => $postId,
		);

		if (
			Loader::includeModule('extranet')
			&& \CExtranet::isExtranetSite(SITE_ID)
		)
		{
			$filter["SITE_ID"] = SITE_ID;
		}
		elseif ($public !== 'Y')
		{
			$filter["SITE_ID"] = [ SITE_ID, false ];
		}

		$res = \CSocNetLog::getList(
			[],
			$filter,
			false,
			false,
			[ 'ID', 'FAVORITES_USER_ID', 'PINNED_USER_ID' ],
			[ 'USE_PINNED' => 'Y' ]
		);

		if ($logEntry = $res->fetch())
		{
			$logId = (int)$logEntry['ID'];
			$logFavoritesUserId = (int)$logEntry['FAVORITES_USER_ID'];
			$logPinnedUserId = (int)$logEntry['PINNED_USER_ID'];
		}

		if ((int)$postFields["AUTHOR_ID"] === $currentUserId)
		{
			$perms = Permissions::FULL;
		}
		elseif (
			$currentModuleAdmin
			|| \CMain::getGroupRight('blog') >= 'W'
		)
		{
			$perms = Permissions::FULL;
		}
		elseif (!$logId)
		{
			$perms = Permissions::DENY;
		}
		else
		{
			$permsResult = $postItem->getSonetPerms([
				'PUBLIC' => ($public === 'Y'),
				'CHECK_FULL_PERMS' => true,
				'LOG_ID' => $logId
			]);
			$perms = $permsResult['PERM'];
			$groupReadOnly = (
				$permsResult['PERM'] <= \Bitrix\Blog\Item\Permissions::READ
				&& $permsResult['READ_BY_OSG']
					? 'Y'
					: 'N'
			);
		}

		$shareForbidden = ComponentHelper::getBlogPostLimitedViewStatus(array(
			'logId' => $logId,
			'postId' => $postId,
			'authorId' => $postFields['AUTHOR_ID']
		));

		$postUrl = \CComponentEngine::makePathFromTemplate(
			$pathToPost,
			[
				'post_id' => $postFields['ID'],
				'user_id' => $postFields['AUTHOR_ID']
			]
		);

		$voteExportUrl = '';

		if ($voteId > 0)
		{
			$voteExportUrl = \CHTTP::urlAddParams(
				\CHTTP::urlDeleteParams(
					$postUrl,
					[ 'exportVoting ' ]
				),
				[ 'exportVoting' => $voteId ]
			);
		}

		return [
			'perms' => $perms,
			'isGroupReadOnly' => $groupReadOnly,
			'isShareForbidden' => ($shareForbidden ? 'Y' : 'N'),
			'logId' => $logId,
			'logFavoritesUserId' => $logFavoritesUserId,
			'logPinnedUserId' => $logPinnedUserId,
			'authorId' => (int)$postFields['AUTHOR_ID'],
			'urlToPost' => $postUrl,
			'urlToVoteExport' => $voteExportUrl,
			'allowModerate' => ($allowModerate ? 'Y' : 'N'),
			'backgroundCode' => $postFields['BACKGROUND_CODE']
		];
	}

	public function shareAction(array $params = [])
	{
		$postId = (int)($params['postId'] ?? 0);
		$destCodesList = ($params['DEST_CODES'] ?? []);
		$destData = ($params['DEST_DATA'] ?? []);
		$invitedUserName = ($params['INVITED_USER_NAME'] ?? []);
		$invitedUserLastName = ($params['INVITED_USER_LAST_NAME'] ?? []);
		$invitedUserCrmEntity = ($params['INVITED_USER_CRM_ENTITY'] ?? []);
		$invitedUserCreateCrmContact = ($params['INVITED_USER_CREATE_CRM_CONTACT'] ?? []);
		$readOnly = (isset($params['readOnly']) && $params['readOnly'] === 'Y');
		$pathToUser = ($params['pathToUser'] ?? '');
		$pathToPost = ($params['pathToPost'] ?? '');
		$currentUserId = $this->getCurrentUser()->getId();

		$data = [
			'ALLOW_EMAIL_INVITATION' => (
				ModuleManager::isModuleInstalled('mail')
				&& ModuleManager::isModuleInstalled('intranet')
				&& (
					!Loader::includeModule('bitrix24')
					|| \CBitrix24::isEmailConfirmed()
				)
			)
		];

		if ($postId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_EMPTY'));
			return null;
		}

		if (
			!Loader::includeModule('blog')
			|| !($postItem = \Bitrix\Blog\Item\Post::getById($postId))
		)
		{
			$this->addError(
				new Error(
					Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'),
					'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'
				)
			);

			return null;
		}

		$currentUserPerm = Helper::getBlogPostPerm([
			'USER_ID' => $currentUserId,
			'POST_ID' => $postId,
		]);
		if ($currentUserPerm <= Permissions::DENY)
		{
			$this->addError(
				new Error(
					Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'),
					'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'
				)
			);

			return null;
		}

		$postFields = $postItem->getFields();

		if (
			(int)$postFields['AUTHOR_ID'] !== $currentUserId
			&& ComponentHelper::isCurrentUserExtranet()
		)
		{
			$visibleUserIdList = \CExtranet::getMyGroupsUsersSimple(SITE_ID);

			if (!empty(array_diff([(int)$postFields['AUTHOR_ID']], $visibleUserIdList)))
			{
				$this->addError(
					new Error(
						Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'),
						'SONET_CONTROLLER_LIVEFEED_BLOGPOST_NOT_FOUND'
					)
				);

				return null;
			}
		}

		$perms2update = [];
		$sonetPermsListOld = \CBlogPost::getSocNetPerms($postId);
		foreach($sonetPermsListOld as $type => $val)
		{
			foreach($val as $id => $values)
			{
				if($type !== 'U')
				{
					$perms2update[] = $type . $id;
				}
				else
				{
					$perms2update[] = (
						in_array('US' . $id, $values, true)
							? 'UA'
							: $type.$id
					);
				}
			}
		}

		$newRightsList = [];

		$sonetPermsListNew = [
			'UA' => [],
			'U' => [],
			'UE' => [],
			'SG' => [],
			'DR' => []
		];

		if (!empty($destData))
		{
			try
			{
				$entitites = Json::decode($destData);
				if (!empty($entitites))
				{
					$destCodesList = \Bitrix\Main\UI\EntitySelector\Converter::convertToFinderCodes($entitites);
				}
			}
			catch(ArgumentException $e)
			{
			}
		}

		foreach($destCodesList as $destCode)
		{
			if ($destCode === 'UA')
			{
				$sonetPermsListNew['UA'][] = 'UA';
			}
			elseif (preg_match('/^UE(.+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['UE'][] = $matches[1];
			}
			elseif (preg_match('/^U(\d+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['U'][] = 'U'.$matches[1];
			}
			elseif (preg_match('/^SG(\d+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['SG'][] = 'SG'.$matches[1];
			}
			elseif (preg_match('/^DR(\d+)$/i', $destCode, $matches))
			{
				$sonetPermsListNew['DR'][] = 'DR'.$matches[1];
			}
		}

		$HTTPPost = [
			'SONET_PERMS' => $sonetPermsListNew,
			'INVITED_USER_NAME' => $invitedUserName,
			'INVITED_USER_LAST_NAME' => $invitedUserLastName,
			'INVITED_USER_CRM_ENTITY' => $invitedUserCrmEntity,
			'INVITED_USER_CREATE_CRM_CONTACT' => $invitedUserCreateCrmContact
		];
		ComponentHelper::processBlogPostNewMailUser($HTTPPost, $data);
		$sonetPermsListNew = $HTTPPost['SONET_PERMS'];

		$currentAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
		$canPublish = true;

		foreach($sonetPermsListNew as $type => $val)
		{
			foreach($val as $code)
			{
				if(in_array($type, [ 'U', 'SG', 'DR', 'CRMCONTACT' ]))
				{
					if (!in_array($code, $perms2update))
					{
						if ($type === 'SG')
						{
							$sonetGroupId = (int)str_replace('SG', '', $code);

							$canPublish = (
								$currentAdmin
								|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $sonetGroupId, 'blog', 'write_post')
								|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $sonetGroupId, 'blog', 'moderate_post')
								|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $sonetGroupId, 'blog', 'full_post')
							);

							if (!$canPublish)
							{
								break;
							}
						}

						$perms2update[] = $code;
						$newRightsList[] = $code;
					}
				}
				elseif ($type === 'UA')
				{
					if (!in_array('UA', $perms2update, true))
					{
						$perms2update[] = 'UA';
						$newRightsList[] = 'UA';
					}
				}
			}

			if (!$canPublish)
			{
				break;
			}
		}

		if (
			!empty($newRightsList)
			&& $canPublish
		)
		{
			ComponentHelper::processBlogPostShare(
				[
					'POST_ID' => $postId,
					'BLOG_ID' => $postFields['BLOG_ID'],
					'SITE_ID' => SITE_ID,
					'SONET_RIGHTS' => $perms2update,
					'NEW_RIGHTS' => $newRightsList,
					'USER_ID' => $currentUserId
				],
				[
					'MENTION' => 'N',
					'LIVE' => 'Y',
					'CAN_USER_COMMENT' => (!$readOnly ? 'Y' : 'N'),
					'PATH_TO_USER' => $pathToUser,
					'PATH_TO_POST' => $pathToPost,
				]
			);
		}
		elseif (!$canPublish)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_LIVEFEED_BLOGPOST_SHARE_PREMODERATION'), 'SONET_CONTROLLER_LIVEFEED_BLOGPOST_SHARE_PREMODERATION'));
			return null;
		}
	}

	public function addAction(array $params = []): ?array
	{
		global $APPLICATION;

		$warnings = [];

		try
		{
			$postId = Helper::addBlogPost($params, $this->getScope(), $resultFields);
			if ($postId <= 0)
			{
				if (
					is_array($resultFields)
					&& !empty($resultFields['ERROR_MESSAGE_PUBLIC'])
				)
				{
					$this->addError(new Error($resultFields['ERROR_MESSAGE_PUBLIC'], 0, [
						'public' => 'Y'
					]));
					return null;
				}

				$e = $APPLICATION->getException();
				throw new \Exception($e ? $e->getString() : 'Cannot add blog post');
			}

			if (
				is_array($resultFields)
				&& !empty($resultFields['WARNING_MESSAGE_PUBLIC'])
			)
			{
				$warnings[] = $resultFields['WARNING_MESSAGE_PUBLIC'];
			}

		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'id' => $postId,
			'warnings' => $warnings
		];
	}

	public function updateAction($id = 0, array $params = []): ?array
	{
		global $APPLICATION;

		try
		{
			$params['POST_ID'] = $id;
			$postId = Helper::updateBlogPost($params, $this->getScope(), $resultFields);
			if ($postId <= 0)
			{
				if (
					is_array($resultFields)
					&& !empty($resultFields['ERROR_MESSAGE_PUBLIC'])
				)
				{
					$this->addError(new Error($resultFields['ERROR_MESSAGE_PUBLIC'], 0, [
						'public' => 'Y'
					]));
					return null;
				}

				$e = $APPLICATION->getException();
				throw new \Exception($e ? $e->getString() : 'Cannot update blog post');
			}
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'id' => $postId
		];
	}

	public function getBlogPostMobileFullDataAction(array $params = []): ?array
	{
		if (!Loader::includeModule('mobile'))
		{
			$this->addError(new Error('Mobile module not installed', 'SONET_CONTROLLER_LIVEFEED_MOBILE_MODULE_NOT_INSTALLED'));
			return null;
		}
		return \Bitrix\Mobile\Livefeed\Helper::getBlogPostFullData($params);
	}

	public function deleteAction($id = 0): ?bool
	{
		try
		{
			$result = Helper::deleteBlogPost([
				'POST_ID' => (int)$id,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return $result;
	}

}

