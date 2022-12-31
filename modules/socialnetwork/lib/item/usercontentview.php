<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserContentViewTable;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Socialnetwork\Livefeed;

class UserContentView
{
	public static function getAvailability()
	{
		static $result = null;
		if ($result !== null)
		{
			return $result;
		}

		$result = true;
/*

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $result;
		}

		if (Loader::includeModule('bitrix24'))
		{
			$result = (
				!in_array(\CBitrix24::getLicenseType(), array('project'), true)
				|| \CBitrix24::isNfrLicense()
				|| \CBitrix24::isDemoLicense()
			);
		}
*/
		return $result;

	}

	public static function getViewData($params = [])
	{
		if (!is_array($params))
		{
			return false;
		}

		$contentId = ($params['contentId'] ?? false);
		if (empty($contentId))
		{
			return false;
		}

		$result = [];

		if (!is_array($contentId))
		{
			$contentId = [ $contentId ];
		}

		$res = UserContentViewTable::getList([
			'filter' => [
				'@CONTENT_ID' => $contentId
			],
			'select' => [ 'CNT', 'CONTENT_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID' ],
			'runtime' => [
				new ExpressionField('CNT', 'COUNT(*)')
			],
			'group' => [ 'CONTENT_ID' ]
		]);

		while ($content = $res->fetch())
		{
			$result[$content['CONTENT_ID']] = $content;
		}

		return $result;
	}

	public static function getUserList(array $params = []): array
	{
		global $USER;

		$result = [
			'items' => [],
			'hiddenCount' => 0
		];

		$contentId = (!empty($params['contentId']) ? $params['contentId'] : false);
		$pageNum = (!empty($params['page']) ? (int)$params['page'] : 1);
		$pathToUserProfile = (!empty($params['pathToUserProfile']) ? $params['pathToUserProfile'] : '');
		$pageSize = 10;

		if (
			!$contentId
			&& $pageNum <= 0
		)
		{
			return $result;
		}

		$select = [
			'USER_ID',
			'DATE_VIEW',
			'USER_NAME' => 'USER.NAME',
			'USER_LAST_NAME' => 'USER.LAST_NAME',
			'USER_SECOND_NAME' => 'USER.SECOND_NAME',
			'USER_LOGIN' => 'USER.LOGIN',
			'USER_PERSONAL_PHOTO' => 'USER.PERSONAL_PHOTO',
			'USER_PERSONAL_GENDER' => 'USER.PERSONAL_GENDER'
		];

		$extranetInstalled = $mailInstalled = false;
		$extranetIdList = [];

		if (ModuleManager::isModuleInstalled('extranet'))
		{
			$extranetInstalled = true;
			$select['USER_UF_DEPARTMENT'] = "USER.UF_DEPARTMENT";
		}

		if (IsModuleInstalled('mail'))
		{
			$mailInstalled = true;
			$select['USER_EXTERNAL_AUTH_ID'] = "USER.EXTERNAL_AUTH_ID";
		}

		$queryParams = [
			'order' => [
				'DATE_VIEW' => 'DESC'
			],
			'filter' => [
				'=CONTENT_ID' => $contentId
			],
			'select' => $select
		];

		if (!$extranetInstalled)
		{
			$queryParams['limit'] = $pageSize;
			$queryParams['offset'] = ($pageNum - 1) * $pageSize;
		}

		$userList = [];
		$timeZoneOffset = \CTimeZone::getOffset();

		$res = UserContentViewTable::getList($queryParams);

		while ($fields = $res->fetch())
		{
			$photoSrc = '';

			if ((int)$fields['USER_PERSONAL_PHOTO'] <= 0)
			{
				switch ($fields['USER_PERSONAL_GENDER'])
				{
					case "M":
						$suffix = "male";
						break;
					case "F":
						$suffix = "female";
						break;
					default:
						$suffix = "unknown";
				}
				$fields['USER_PERSONAL_PHOTO'] = Option::get('socialnetwork', 'default_user_picture_' . $suffix, false, SITE_ID);
			}

			if (
				!empty($fields['USER_PERSONAL_PHOTO'])
				&& (int)$fields['USER_PERSONAL_PHOTO'] > 0
			)
			{
				$file = \CFile::resizeImageGet(
					$fields["USER_PERSONAL_PHOTO"],
					[ 'width' => 58, 'height' => 58 ],
					BX_RESIZE_IMAGE_EXACT,
					false,
					false,
					true
				);
				$photoSrc = $file["src"];
			}

			$userFields = [
				'NAME' => $fields['USER_NAME'],
				'LAST_NAME' => $fields['USER_LAST_NAME'],
				'SECOND_NAME' => $fields['USER_SECOND_NAME'],
				'LOGIN' => $fields['USER_LOGIN'],
			];

			$userType = '';
			if (
				$mailInstalled
				&& $fields["USER_EXTERNAL_AUTH_ID"] === "email"
			)
			{
				$userType = "mail";
			}
			elseif (
				$extranetInstalled
				&& (
					empty($fields["USER_UF_DEPARTMENT"])
					|| (int)$fields["USER_UF_DEPARTMENT"][0] <= 0
				)
			)
			{
				$userType = "extranet";
				$extranetIdList[] = $fields["USER_ID"];
			}

			$dateView = (
				$fields['DATE_VIEW'] instanceof \Bitrix\Main\Type\DateTime
					? $fields['DATE_VIEW']->toString()
					: ''
			);

			$userList[$fields['USER_ID']] = [
				'ID' => $fields['USER_ID'],
				'TYPE' => $userType,
				'URL' => \CUtil::jSEscape(\CComponentEngine::makePathFromTemplate($pathToUserProfile, [
					"UID" => $fields["USER_ID"],
					"user_id" => $fields["USER_ID"],
					"USER_ID" => $fields["USER_ID"]
				])),
				'PHOTO_SRC' => $photoSrc,
				'FULL_NAME' => \CUser::formatName(\CSite::getNameFormat(), $userFields, true, true),
				'DATE_VIEW' => $dateView,
				'DATE_VIEW_FORMATTED' => (
					!empty($dateView)
						? \CComponentUtil::getDateTimeFormatted(MakeTimeStamp($dateView), "FULL", $timeZoneOffset)
						: ''
				)
			];
		}

		$userIdToCheckList = [];

		if (Loader::includeModule('extranet'))
		{
			$userIdToCheckList = (
				\CExtranet::isIntranetUser(SITE_ID, $USER->getId())
					? $extranetIdList
					: array_keys($userList)
			);
		}

		if (!empty($userIdToCheckList))
		{
			$myGroupsUserList = \CExtranet::getMyGroupsUsersSimple(\CExtranet::getExtranetSiteID());
			foreach ($userIdToCheckList as $userIdToCheck)
			{
				if (
					!in_array($userIdToCheck, $myGroupsUserList)
					&& $userIdToCheck != $USER->getId()
				)
				{
					unset($userList[$userIdToCheck]);
					$result['hiddenCount']++;
				}
			}
		}

		if (!$extranetInstalled)
		{
			$result['items'] = $userList;
		}
		elseif ($pageNum <= ((count($userList) / $pageSize) + 1))
		{
			$res = new \CDBResult();
			$res->initFromArray($userList);
			$res->navStart($pageSize, false, $pageNum);

			while($user = $res->fetch())
			{
				$result['items'][] = $user;
			}
		}
		else
		{
			$result['items'] = [];
		}

		return $result;
	}

	public static function deleteNoDemand($userId = 0): bool
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			return false;
		}

		$result = true;

		try
		{
			\Bitrix\Main\Application::getConnection()->queryExecute("DELETE FROM ".UserContentViewTable::getTableName()." WHERE USER_ID = ".$userId);
		}
		catch (SqlQueryException $exception)
		{
			$result = false;
		}

		return $result;
	}

	public static function set(array $params = []): void
	{
		$xmlIdList = (
			isset($params["xmlIdList"])
			&& is_array($params["xmlIdList"])
				? $params["xmlIdList"]
				: []
		);

		$context = ($params['context'] ?? '');

		$userId = (
			isset($params['userId'])
			&& (int)$params['userId'] > 0
				? (int)$params['userId'] :
				0
		);

		if (!empty($xmlIdList))
		{
			foreach ($xmlIdList as $val)
			{
				$xmlId = $val['xmlId'];
				$save = (
					!isset($val['save'])
					|| $val['save'] !== 'N'
				);

				[ $entityType, $entityId ] = self::parseXmlId($xmlId);

				if (
					!empty($entityType)
					&& $entityId > 0
				)
				{
					$provider = Livefeed\Provider::init([
						'ENTITY_TYPE' => $entityType,
						'ENTITY_ID' => $entityId,
					]);
					if ($provider)
					{
						$hasPermissions = true;
						if (
							isset($val['checkAccess'])
							&& $val['checkAccess'] === true
						)
						{
							$provider->setOption('checkAccess', true);
							$provider->initSourceFields();

							if (empty($provider->getSourceFields()))
							{
								$hasPermissions = false;
							}
						}

						if ($hasPermissions)
						{
							$provider->setContentView([
								'save' => $save,
							]);
						}

/*
TODO: https://bitrix24.team/company/personal/user/15/tasks/task/view/167281/
						$provider->deleteCounter([
							'userId' => $this->getCurrentUser()->getId(),
							'siteId' => SITE_ID
						]);
*/
					}
				}
			}

			self::finalize([
				'userId' => $userId,
				'context' => $context
			]);
		}
	}

	public static function finalize($params = []): bool
	{
		$userId = (!empty($params['userId']) ? (int)$params['userId'] : 0);
		$context = (!empty($params['context']) ? $params['context'] : '');

		if (!$userId)
		{
			return false;
		}

		if (
			$context !== 'forum.comments/mobile'
			&& ModuleManager::isModuleInstalled('tasks')
		)
		{
			$taskIdList = \Bitrix\Socialnetwork\Integration\Forum\TaskComment::getViewedCommentsTasksList();
			if (!empty($taskIdList))
			{
				$event = new \Bitrix\Main\Event(
					'socialnetwork', 'onContentFinalizeView',
					[
						'userId' => $userId,
						'commentsTaskIdList' => $taskIdList
					]
				);
				$event->send();
			}
		}

		return true;
	}

	public static function parseXmlId(string $xmlId = ''): array
	{
		$tmp = explode('-', $xmlId, 2);
		$entityType = trim($tmp[0]);
		$entityId = (int)$tmp[1];

		return [ $entityType, $entityId ];
	}
}
