<?php

/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2021 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Blog;

use Bitrix\Blog\Item\Permissions;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Helper\Path;
use Bitrix\Socialnetwork\WorkgroupTable;

class Mention
{
	public static function processCommentShare($params = [])
	{
		$commentText = (string)($params['commentText'] ?? '');
		$excludedUserIdList = (isset($params['excludedUserIdList']) && is_array($params['excludedUserIdList']) ? $params['excludedUserIdList'] : []);
		$postId = (int)($params['postId'] ?? 0);
		$blogId = (int)($params['blogId'] ?? 0);
		$siteId = (string)($params['siteId'] ?? SITE_ID);

		$authorId = (int)($params['authorId'] ?? 0);
		if ($authorId > 0)
		{
			$excludedUserIdList[] = $authorId;
		}
		$excludedUserIdList = array_map(function ($item) { return (int)$item; }, $excludedUserIdList);
		$excludedUserIdList = array_unique($excludedUserIdList);

		if (
			$commentText === ''
			|| $postId <= 0
		)
		{
			return false;
		}

		if ($blogId <= 0)
		{
			$postFields = \CBlogPost::getById($postId);
			$blogId = (int)$postFields['BLOG_ID'];
		}

		if (
			$blogId <= 0
			|| !Loader::includeModule('blog')
		)
		{
			return false;
		}

		$newRightsList = self::parseUserList([
			'commentText' => $commentText,
			'postId' => $postId,
			'excludedUserIdList' => $excludedUserIdList,
		]);

		$newRightsList = array_merge($newRightsList, self::parseProjectList([
			'commentText' => $commentText,
			'postId' => $postId,
		]));

		$newRightsList = array_merge($newRightsList, self::parseDepartmentList([
			'commentText' => $commentText,
			'postId' => $postId,
		]));

		if (empty($newRightsList))
		{
			return false;
		}

		$fullRightsList = $newRightsList;

		$blogPermsList = \CBlogPost::getSocnetPerms($postId);
		foreach ($blogPermsList as $entitiesList)
		{
			foreach ($entitiesList as $rightsList)
			{
				$fullRightsList = array_merge($fullRightsList, $rightsList);
			}
		}

		$fullRightsList = array_unique($fullRightsList);

		return \Bitrix\Socialnetwork\ComponentHelper::processBlogPostShare(
			[
				'POST_ID' => $postId,
				'BLOG_ID' => $blogId,
				'SITE_ID' => $siteId,
				'SONET_RIGHTS' => $fullRightsList,
				'NEW_RIGHTS' => $newRightsList,
				'USER_ID' => $authorId,
			],
			[
				'PATH_TO_USER' => Option::get('main', 'TOOLTIP_PATH_TO_USER', SITE_DIR . 'company/personal/user/#user_id#/', $siteId),
				'PATH_TO_POST' => Path::get('userblogpost_page', $siteId),
				'NAME_TEMPLATE' => \CSite::getNameFormat(),
				'SHOW_LOGIN' => 'Y',
				'LIVE' => 'N',
				'MENTION' => 'Y',
				'CLEAR_COMMENTS_CACHE' => (isset($params['clearCache']) && $params['clearCache'] === false ? 'N' : 'Y')
			]
		);
	}

	private static function parseUserList(array $params = []): array
	{
		$result = [];

		$commentText = (string)($params['commentText'] ?? '');
		$excludedUserIdList = (isset($params['excludedUserIdList']) && is_array($params['excludedUserIdList']) ? $params['excludedUserIdList'] : []);
		$postId = (int)($params['postId'] ?? 0);

		if (
			$postId <= 0
			|| !Loader::includeModule('blog')
		)
		{
			return $result;
		}

		$mentionedUserIdList = \Bitrix\Socialnetwork\Helper\Mention::getUserIds($commentText);
		if (empty($mentionedUserIdList))
		{
			return $result;
		}

		$userIdToShareList = [];

		foreach ($mentionedUserIdList as $userId)
		{
			$userId = (int)$userId;
			if (
				$userId <= 0
				|| in_array($userId, $excludedUserIdList, true)
			)
			{
				continue;
			}

			$postPerm = \CBlogPost::getSocNetPostPerms([
				'POST_ID' => $postId,
				'NEED_FULL' => true,
				'USER_ID' => $userId,
				'IGNORE_ADMIN' => true,
			]);

			if ($postPerm >= Permissions::PREMODERATE)
			{
				continue;
			}

			$userIdToShareList[] = $userId;
		}

		$userIdToShareList = array_unique($userIdToShareList);
		if (empty($userIdToShareList))
		{
			return $result;
		}

		foreach ($userIdToShareList as $userId)
		{
			$result[] = 'U' . $userId;
		}

		return $result;
	}

	private static function parseProjectList(array $params = []): array
	{
		global $USER;

		$result = [];

		$commentText = (string)($params['commentText'] ?? '');

		$postId = (int)($params['postId'] ?? 0);

		if (
			$postId <= 0
			|| !Loader::includeModule('blog')
		)
		{
			return $result;
		}

		$mentionedProjectIdList = \Bitrix\Socialnetwork\Helper\Mention::getProjectIds($commentText);
		if (empty($mentionedProjectIdList))
		{
			return $result;
		}

		$projectIdToShareList = [];

		$currentUserId = $USER->getId();
		$currentAdmin = \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false);
		$postPermsData = self::getSocNetPerms([
			'postId' => $postId,
		]);

		foreach ($mentionedProjectIdList as $projectId)
		{
			$projectId = (int)$projectId;
			if (
				$projectId <= 0
				|| (
					isset($postPermsData['SG'])
					&& isset($postPermsData['SG'][$projectId])
				)
			)
			{
				continue;
			}

			$canPublish = (
				$currentAdmin
				|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $projectId, 'blog', 'write_post')
				|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $projectId, 'blog', 'moderate_post')
				|| \CSocNetFeaturesPerms::canPerformOperation($currentUserId, SONET_ENTITY_GROUP, $projectId, 'blog', 'full_post')
			);

			if (!$canPublish)
			{
				continue;
			}

			$projectIdToShareList[] = $projectId;
		}

		$projectIdToShareList = array_unique($projectIdToShareList);
		if (empty($projectIdToShareList))
		{
			return $result;
		}

		$res = WorkgroupTable::getList([
			'filter' => [
				'@ID' => $projectIdToShareList,
			],
			'select' => [ 'ID' ],
		]);
		while ($workgroupFields = $res->fetch())
		{
			$result[] = 'SG' . $workgroupFields['ID'];
		}

		return $result;
	}

	private static function parseDepartmentList(array $params = []): array
	{
		global $USER;

		$result = [];

		$commentText = (string)($params['commentText'] ?? '');

		$postId = (int)($params['postId'] ?? 0);

		if (
			$postId <= 0
			|| !ModuleManager::isModuleInstalled('intranet')
			|| !Loader::includeModule('blog')
			|| !Loader::includeModule('iblock')
		)
		{
			return $result;
		}

		if (
			Loader::includeModule('extranet')
			&& !\CExtranet::isIntranetUser()
		)
		{
			return $result;
		}

		$mentionedDepartmentIdList = \Bitrix\Socialnetwork\Helper\Mention::getDepartmentIds($commentText);
		if (empty($mentionedDepartmentIdList))
		{
			return $result;
		}

		$departmentIdToShareList = [];

		$postPermsData = self::getSocNetPerms([
			'postId' => $postId,
		]);

		foreach ($mentionedDepartmentIdList as $departmentId)
		{
			$departmentId = (int)$departmentId;
			if (
				$departmentId <= 0
				|| (
					isset($postPermsData['DR'])
					&& isset($postPermsData['DR'][$departmentId])
				)
			)
			{
				continue;
			}

			$departmentIdToShareList[] = $departmentId;
		}

		$departmentIdToShareList = array_unique($departmentIdToShareList);
		if (empty($departmentIdToShareList))
		{
			return $result;
		}

		$res = SectionTable::getList([
			'filter' => [
				'@ID' => $departmentIdToShareList,
				'=ACTIVE' => 'Y',
			],
			'select' => [ 'ID' ],
		]);
		while ($sectionFields = $res->fetch())
		{
			$result[] = 'DR' . $sectionFields['ID'];
		}

		return $result;
	}

	private static function getSocNetPerms(array $params = [])
	{
		static $cache = [];

		$result = [];

		$postId = (int)($params['postId'] ?? 0);

		if (
			$postId <= 0
			|| !Loader::includeModule('blog')
		)
		{
			return $result;
		}

		if (isset($cache[$postId]))
		{
			$result = $cache[$postId];
		}
		else
		{
			$result = \CBlogPost::getSocnetPerms($postId);
			$cache[$postId] = $result;
		}

		return $result;
	}
}
