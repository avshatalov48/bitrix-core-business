<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\FeatureTable;
use Bitrix\Socialnetwork\FeaturePermTable;
use Bitrix\Socialnetwork\Item\UserToGroup;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;

class Workgroup
{
	public static function getSprintDurationList(): array
	{
		static $result = null;

		if ($result === null)
		{
			$oneWeek = \DateInterval::createFromDateString('1 week')->format('%d') * 86400;
			$twoWeek = \DateInterval::createFromDateString('2 weeks')->format('%d') * 86400;
			$threeWeek = \DateInterval::createFromDateString('3 weeks')->format('%d') * 86400;
			$fourWeek = \DateInterval::createFromDateString('4 weeks')->format('%d') * 86400;

			$result = [
				$oneWeek => [
					'TITLE' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_ONE_WEEK'),
				],
				$twoWeek => [
					'TITLE' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_TWO_WEEK'),
					'DEFAULT' => true,
				],
				$threeWeek => [
					'TITLE' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_THREE_WEEK'),
				],
				$fourWeek => [
					'TITLE' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_FOUR_WEEK'),
				],
			];
		}

		return $result;
	}

	public static function getSprintDurationValues(): array
	{
		$list = static::getSprintDurationList();

		$result = [];
		foreach ($list as $key => $item)
		{
			$result[$key] = $item['TITLE'];
		}

		return $result;
	}

	public static function getSprintDurationDefaultKey()
	{
		$list = static::getSprintDurationList();
		$result = array_key_first($list);
		foreach ($list as $key => $item)
		{
			if (
				isset($item['DEFAULT'])
				&& $item['DEFAULT'] === true
			)
			{
				$result = $key;
				break;
			}
		}

		return $result;
	}

	public static function getScrumTaskResponsibleList(): array
	{
		return [
			'A' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_TASK_RESPONSIBLE_AUTHOR'),
			'M' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_TASK_RESPONSIBLE_MASTER')
		];
	}

	public static function getTypeCodeByParams($params)
	{
		$result = false;

		if (empty($params['fields']))
		{
			return $result;
		}

		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: self::getTypes($params)
		);

		foreach ($typesList as $code => $type)
		{
			if (
				(
					!isset($params['fields']['OPENED'])
					|| $params['fields']['OPENED'] === $type['OPENED']
				)
				&& (
					!isset($params['fields']['VISIBLE'])
					|| $params['fields']['VISIBLE'] === $type['VISIBLE']
				)
				&& $params['fields']['PROJECT'] === $type['PROJECT']
				&& $params['fields']['EXTERNAL'] === $type['EXTERNAL']
				&& (
					!isset($params['fields']['SCRUM_PROJECT'])
					|| (
						isset($type['SCRUM_PROJECT'])
						&& $params['fields']['SCRUM_PROJECT'] === $type['SCRUM_PROJECT']
					)
				)
			)
			{
				$result = $code;
				break;
			}
		}

		return $result;
	}

	public static function getProjectTypeCodeByParams($params)
	{
		$result = false;

		if (empty($params['fields']))
		{
			return $result;
		}

		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: static::getProjectPresets($params)
		);

		foreach ($typesList as $code => $type)
		{
			if (
				$params['fields']['PROJECT'] === $type['PROJECT']
				&& $params['fields']['SCRUM_PROJECT'] === $type['SCRUM_PROJECT']
			)
			{
				$result = $code;
				break;
			}
		}

		return $result;
	}

	public static function getConfidentialityTypeCodeByParams($params)
	{
		$result = false;

		if (empty($params['fields']))
		{
			return $result;
		}

		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: self::getConfidentialityPresets($params)
		);

		foreach ($typesList as $code => $type)
		{
			if (
				$params['fields']['OPENED'] === $type['OPENED']
				&& (
					isset($params['fields']['VISIBLE'])
					&& $params['fields']['VISIBLE'] === $type['VISIBLE']
				)
				&& $params['fields']['PROJECT'] === $type['PROJECT']
			)
			{
				$result = $code;
				break;
			}
		}

		return $result;
	}

	public static function getTypeByCode($params = [])
	{
		$result = false;

		if (
			!is_array($params)
			|| empty($params['code'])
		)
		{
			return $result;
		}

		$code = $params['code'];
		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: self::getTypes($params)
		);

		if (
			!empty($typesList)
			&& is_array($typesList)
			&& !empty($typesList[$code])
		)
		{
			$result = $typesList[$code];
		}

		return $result;
	}

	public static function getEditFeaturesAvailability()
	{
		static $result = null;

		if ($result !== null)
		{
			return $result;
		}

		$result = true;

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $result;
		}

		if (\CBitrix24::isNfrLicense())
		{
			return $result;
		}

		if (\CBitrix24::isDemoLicense())
		{
			return $result;
		}

		if (
			\CBitrix24::getLicenseType() !== 'project'
			|| !Option::get('socialnetwork', 'demo_edit_features', false)
		)
		{
			return $result;
		}

		$result = false; // static!

		return $result;
	}

	/**
	 * returns array of workgroups filtered by access permissions of a user, only for the current site
	 * @param array $params
	 * @return array
	 */
	public static function getByFeatureOperation(array $params = []): array
	{
		global $USER, $CACHE_MANAGER;

		$result = [];

		$feature = (string)($params['feature'] ?? '');
		$operation = (string)($params['operation'] ?? '');
		$userId = (int)(
			isset($params['userId'])
				? (int)$params['userId']
				: (is_object($USER) && $USER instanceof \CUser ? $USER->getId() : 0)
		);

		if (
			$feature === ''
			|| $operation === ''
			|| $userId <= 0
		)
		{
			return $result;
		}

		$featuresSettings = \CSocNetAllowed::getAllowedFeatures();
		if (
			empty($featuresSettings)
			|| empty($featuresSettings[$feature])
			|| empty($featuresSettings[$feature]['allowed'])
			|| empty($featuresSettings[$feature]['operations'])
			|| empty($featuresSettings[$feature]['operations'][$operation])
			|| empty($featuresSettings[$feature]['operations'][$operation][FeatureTable::FEATURE_ENTITY_TYPE_GROUP])
			|| !in_array(FeatureTable::FEATURE_ENTITY_TYPE_GROUP, $featuresSettings[$feature]['allowed'], true)
		)
		{
			return $result;
		}

		$cacheTTL = 3600 * 24 * 30;
		$cacheDir = '/sonet/features_perms/' . FeatureTable::FEATURE_ENTITY_TYPE_GROUP . '/list/' . (int)($userId / 1000);
		$cacheId = implode(' ', [ 'entities_list', $feature, $operation, $userId ]);

		$cache = new \CPHPCache();
		if ($cache->initCache($cacheTTL, $cacheId, $cacheDir))
		{
			$cacheValue = $cache->getVars();
			if (is_array($cacheValue))
			{
				$result = $cacheValue;
			}
		}
		else
		{
			$cache->startDataCache();
			$CACHE_MANAGER->startTagCache($cacheDir);

			$CACHE_MANAGER->registerTag('sonet_group');
			$CACHE_MANAGER->registerTag('sonet_features');
			$CACHE_MANAGER->registerTag('sonet_features2perms');
			$CACHE_MANAGER->registerTag('sonet_user2group');

			$defaultRole = $featuresSettings[$feature]['operations'][$operation][FeatureTable::FEATURE_ENTITY_TYPE_GROUP];

			$query = new \Bitrix\Main\Entity\Query(WorkgroupTable::getEntity());
			$query->addFilter('=ACTIVE', 'Y');

			if (
				(
					!is_array($featuresSettings[$feature]['minoperation'])
					|| !in_array($operation, $featuresSettings[$feature]['minoperation'], true)
				)
				&& Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
			)
			{
				$query->addFilter('!=CLOSED', 'Y');
			}

			$query->addSelect('ID');

			$query->registerRuntimeField(
				'',
				new \Bitrix\Main\Entity\ReferenceField('F',
					FeatureTable::getEntity(),
					[
						'=ref.ENTITY_TYPE' => new SqlExpression('?s', FeatureTable::FEATURE_ENTITY_TYPE_GROUP),
						'=ref.ENTITY_ID' => 'this.ID',
						'=ref.FEATURE' => new SqlExpression('?s', $feature),
					],
					[ 'join_type' => 'LEFT' ]
				)
			);
			$query->addSelect('F.ID', 'FEATURE_ID');

			$query->registerRuntimeField(
				'',
				new \Bitrix\Main\Entity\ReferenceField('FP',
					FeaturePermTable::getEntity(),
					[
						'=ref.FEATURE_ID' => 'this.FEATURE_ID',
						'=ref.OPERATION_ID' => new SqlExpression('?s', $operation),
					],
					[ 'join_type' => 'LEFT' ]
				)
			);

			$query->registerRuntimeField(new \Bitrix\Main\Entity\ExpressionField(
				'PERM_ROLE_CALCULATED',
				'CASE WHEN %s IS NULL THEN \''.$defaultRole.'\' ELSE %s END',
				[ 'FP.ROLE', 'FP.ROLE' ]
			));

			$query->registerRuntimeField(
				'',
				new \Bitrix\Main\Entity\ReferenceField('UG',
					UserToGroupTable::getEntity(),
					[
						'=ref.GROUP_ID' => 'this.ID',
						'=ref.USER_ID' => new SqlExpression($userId),
					],
					[ 'join_type' => 'LEFT' ]
				)
			);

			$query->registerRuntimeField(new \Bitrix\Main\Entity\ExpressionField(
				'HAS_ACCESS',
				'CASE
				WHEN
					(
						%s NOT IN (\''.FeaturePermTable::PERM_OWNER.'\', \''.FeaturePermTable::PERM_MODERATOR.'\', \''.FeaturePermTable::PERM_USER.'\')
						OR %s >= %s
					) THEN \'Y\'
					ELSE \'N\'
			END',
				[
					'PERM_ROLE_CALCULATED',
					'PERM_ROLE_CALCULATED', 'UG.ROLE',
				]
			));

			$query->addFilter('=HAS_ACCESS', 'Y');

			$res = $query->exec();

			while ($row = $res->fetch())
			{
				$result[] = [
					'ID' => (int) $row['ID']
				];
			}

			$CACHE_MANAGER->endTagCache();
			$cache->endDataCache($result);
		}

		return $result;
	}

	public static function checkAnyOpened(array $idList = []): bool
	{
		if (empty($idList))
		{
			return false;
		}

		$res = WorkgroupTable::getList([
			'filter' => [
				'@ID' => $idList,
				'=OPENED' => 'Y',
				'=VISIBLE' => 'Y',
			],
			'select' => [ 'ID' ],
			'limit' => 1,
		]);
		if ($res->fetch())
		{
			return true;
		}

		return false;
	}

	public static function getPermissions(array $params = []): array
	{
		global $USER, $APPLICATION;

		static $result = null;

		$userId = (int)($params['userId'] ?? (is_object($USER) ? $USER->getId() : 0));
		$groupId = (int)($params['groupId'] ?? 0);
		if ($groupId <= 0)
		{
			$APPLICATION->throwException('Empty workgroup Id', 'SONET_HELPER_WORKGROUP_EMPTY_GROUP');
		}

		if (!$result[$userId][$groupId])
		{
			$groupFields = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId)->getFields();
			$result[$userId][$groupId] = \CSocNetUserToGroup::initUserPerms(
				$userId,
				$groupFields,
				\CSocNetUser::isCurrentUserModuleAdmin()
			);
		}

		return $result[$userId][$groupId];
	}

	public static function isGroupCopyFeatureEnabled(): bool
	{
		return
			!ModuleManager::isModuleInstalled('bitrix24')
			|| (
				Loader::includeModule('bitrix24')
				&& \Bitrix\Bitrix24\Feature::isFeatureEnabled('socnet_group_copy')
			)
		;
	}

	public static function setOwner(array $fields = []): bool
	{
		global $APPLICATION;

		$groupId = (int)($fields['groupId'] ?? 0);
		$newOwnerId = (int)($fields['userId'] ?? 0);
		$currentUserId = User::getCurrentUserId();

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($newOwnerId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		$filter = [
			'ID' => $groupId,
		];

		$isCurrentUserAdmin = static::isCurrentUserModuleAdmin();

		if (!$isCurrentUserAdmin)
		{
			$filter['CHECK_PERMISSIONS'] = $currentUserId;
		}

		$res = \CSocNetGroup::getList([], $filter);
		if (!($groupFields = $res->fetch()))
		{
			throw new ObjectNotFoundException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_GROUP_NO_FOUND'));
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		if (!$groupPerms['UserCanModifyGroup'])
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		if (!\CSocNetUserToGroup::setOwner($newOwnerId, $groupFields['ID'], $groupFields))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function setScrumMaster(array $fields = []): bool
	{
		$groupId = (int)($fields['groupId'] ?? 0);
		$newScrumMasterId = (int)($fields['userId'] ?? 0);
		$currentUserId = User::getCurrentUserId();

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($newScrumMasterId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		if (!static::canSetScrumMaster([
			'userId' => $newScrumMasterId,
			'groupId' => $groupId,
		]))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		if (!\CSocNetGroup::Update($groupId, [
			'SCRUM_MASTER_ID' => $newScrumMasterId,
		]))
		{
			throw new \Exception(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED'), 100);
		}

		$relation = UserToGroupTable::getList([
			'filter' => [
				'USER_ID' => $newScrumMasterId,
				'GROUP_ID' => $groupId,
			],
			'select' => [ 'ID', 'ROLE' ]
		])->fetchObject();

		if ($relation)
		{
			if (
				!in_array($relation->getRole(), [UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR], true)
				&& !\CSocNetUserToGroup::Update($relation->getId(), [
					'ROLE' => UserToGroupTable::ROLE_MODERATOR,
				])
			)
			{
				throw new \Exception(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED'), 100);
			}
		}
		else
		{
			static $helper = null;
			if (!$helper)
			{
				$connection = Application::getConnection();
				$helper = $connection->getSqlHelper();
			}

			if (!\CSocNetUserToGroup::Add([
				'AUTO_MEMBER' => 'N',
				'USER_ID' => $newScrumMasterId,
				'GROUP_ID' => $groupId,
				'ROLE' => UserToGroupTable::ROLE_MODERATOR,
				'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				'INITIATED_BY_USER_ID' => $currentUserId,
				'=DATE_CREATE' => $helper->getCurrentDateTimeFunction(),
				'=DATE_UPDATE' => $helper->getCurrentDateTimeFunction(),
			]))
			{
				throw new \RuntimeException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED'), 100);
			}
		}

		return true;
	}

	public static function setModerator(array $fields = []): bool
	{
		global $APPLICATION;

		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);
		$currentUserId = User::getCurrentUserId();

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!static::canSetModerator([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		if (!\CSocNetUserToGroup::transferMember2Moderator(
			$currentUserId,
			$groupId,
			[ $relation->getId() ]
		))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function removeModerator(array $fields = []): bool
	{
		global $APPLICATION;

		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);
		$currentUserId = User::getCurrentUserId();

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!static::canRemoveModerator([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		if (!\CSocNetUserToGroup::TransferModerator2Member(
			$currentUserId,
			$groupId,
			[ $relation->getId() ]
		))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function setModerators(array $fields = []): bool
	{
		$groupId = (int)($fields['groupId'] ?? 0);
		$userIds = array_map('intval', array_filter($fields['userIds'] ?? []));

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		$currentUserId = User::getCurrentUserId();
		$isCurrentUserModuleAdmin = static::isCurrentUserModuleAdmin();

		$groupPerms = static::getPermissions(['groupId' => $groupId]);
		if (!$groupPerms || (!$groupPerms['UserCanModifyGroup'] && !$isCurrentUserModuleAdmin))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		$currentModeratorRelations = static::getCurrentModeratorRelations($groupId);
		$moderatorsToAdd = array_diff($userIds, array_keys($currentModeratorRelations));
		$moderatorsToRemove = array_diff_key($currentModeratorRelations, array_fill_keys($userIds, true));

		if (!empty($moderatorsToRemove))
		{
			\CSocNetUserToGroup::TransferModerator2Member(
				$currentUserId,
				$groupId,
				$moderatorsToRemove
			);
		}

		if (!empty($moderatorsToAdd))
		{
			[$ownerRelations, $memberRelations, $otherRelations] = static::getUserRelations($groupId, $moderatorsToAdd);

			if (!empty($memberRelations))
			{
				\CSocNetUserToGroup::transferMember2Moderator(
					$currentUserId,
					$groupId,
					$memberRelations
				);
			}

			$moderatorsToAdd = array_diff($moderatorsToAdd, array_keys($memberRelations), array_keys($ownerRelations));
			foreach ($moderatorsToAdd as $userId)
			{
				if (array_key_exists($userId, $otherRelations))
				{
					$relationId = static::transferToModerators($otherRelations[$userId]);
				}
				else
				{
					$relationId = static::addToModerators($userId, $groupId);
				}

				if ($relationId)
				{
					static::sendNotifications($userId, $groupId, $relationId);
				}
			}
		}

		return true;
	}

	private static function getCurrentModeratorRelations(int $groupId): array
	{
		$currentModeratorRelations = [];

		$relationResult = UserToGroupTable::getList([
			'select' => ['ID', 'USER_ID'],
			'filter' => [
				'GROUP_ID' => $groupId,
				'ROLE' => UserToGroupTable::ROLE_MODERATOR,
			],
		]);
		while ($relation = $relationResult->fetch())
		{
			$currentModeratorRelations[$relation['USER_ID']] = $relation['ID'];
		}

		return $currentModeratorRelations;
	}

	private static function getUserRelations(int $groupId, array $userIds): array
	{
		$ownerRelations = [];
		$memberRelations = [];
		$otherRelations = [];

		$relationResult = UserToGroupTable::getList([
			'select' => ['ID', 'USER_ID', 'ROLE'],
			'filter' => [
				'GROUP_ID' => $groupId,
				'@USER_ID' => $userIds,
			],
		]);
		while ($relation = $relationResult->fetch())
		{
			$id = $relation['ID'];
			$userId = $relation['USER_ID'];

			switch ($relation['ROLE'])
			{
				case UserToGroupTable::ROLE_OWNER:
					$ownerRelations[$userId] = $id;
					break;

				case UserToGroupTable::ROLE_USER:
					$memberRelations[$userId] = $id;
					break;

				default:
					$otherRelations[$userId] = $id;
					break;
			}
		}

		return [$ownerRelations, $memberRelations, $otherRelations];
	}

	private static function transferToModerators(int $relationId)
	{
		return \CSocNetUserToGroup::update(
			$relationId,
			[
				'ROLE' => UserToGroupTable::ROLE_MODERATOR,
				'=DATE_UPDATE' => \CDatabase::CurrentTimeFunction(),
			]
		);
	}

	private static function addToModerators(int $userId, int $groupId)
	{
		return \CSocNetUserToGroup::add([
			'USER_ID' => $userId,
			'GROUP_ID' => $groupId,
			'ROLE' => UserToGroupTable::ROLE_MODERATOR,
			'=DATE_CREATE' => \CDatabase::CurrentTimeFunction(),
			'=DATE_UPDATE' => \CDatabase::CurrentTimeFunction(),
			'MESSAGE' => '',
			'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
			'INITIATED_BY_USER_ID' => User::getCurrentUserId(),
			'SEND_MAIL' => 'N',
		]);
	}

	private static function sendNotifications(int $userId, int $groupId, int $relationId): void
	{
		\CSocNetUserToGroup::notifyModeratorAdded([
			'userId' => User::getCurrentUserId(),
			'groupId' => $groupId,
			'relationId' => $relationId,
		]);
		UserToGroup::addInfoToChat([
			'group_id' => $groupId,
			'user_id' => $userId,
			'action' => UserToGroup::CHAT_ACTION_IN,
			'sendMessage' => false,
			'role' => UserToGroupTable::ROLE_MODERATOR,
		]);
	}

	public static function deleteOutgoingRequest(array $fields = []): bool
	{
		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!static::canDeleteOutgoingRequest([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		try
		{
			self::deleteRelation([
				'relationId' => $relation->getId(),
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	public static function deleteIncomingRequest(array $fields = []): bool
	{
		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!static::canDeleteIncomingRequest([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		try
		{
			self::deleteRelation([
				'relationId' => $relation->getId(),
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	public static function exclude(array $fields = []): bool
	{
		global $APPLICATION;

		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!static::canExclude([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			throw new AccessDeniedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_NO_PERMS'));
		}

		if (!\CSocNetUserToGroup::delete($relation->getId(), true))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function deleteRelation(array $fields = []): bool
	{
		global $APPLICATION;

		$relationId = (int)($fields['relationId'] ?? 0);

		if ($relationId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_RELATION_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=ID' => $relationId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!\CSocNetUserToGroup::delete($relation->getId()))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function acceptIncomingRequest(array $fields = []): bool
	{
		global $APPLICATION;

		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!\CSocNetUserToGroup::confirmRequestToBeMember(
			User::getCurrentUserId(),
			$groupId,
			[ $relation->getId() ]
		))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function rejectIncomingRequest(array $fields = []): bool
	{
		global $APPLICATION;

		$groupId = (int)($fields['groupId'] ?? 0);
		$userId = (int)($fields['userId'] ?? 0);

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($userId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_USER_ID'));
		}

		try
		{
			$relation = static::getRelation([
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}

		if (!\CSocNetUserToGroup::rejectRequestToBeMember(
			User::getCurrentUserId(),
			$groupId,
			[ $relation->getId() ]
		))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	public static function disconnectDepartment(array $fields = []): bool
	{
		global $APPLICATION;

		$departmentId = (int)($fields['departmentId'] ?? 0);
		$groupId = (int)($fields['groupId'] ?? 0);

		if ($groupId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if ($departmentId <= 0)
		{
			throw new ArgumentException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_DEPARTMENT_ID'));
		}

		if (!ModuleManager::isModuleInstalled('intranet'))
		{
			throw new NotImplementedException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED'));
		}

		$workgroup = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
		if (!$workgroup)
		{
			throw new ObjectNotFoundException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_WRONG_GROUP_ID'));
		}

		if (!isset($workgroup->getFields()['UF_SG_DEPT']))
		{
			throw new \Exception(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED'));
		}

		$workgroupFields = $workgroup->getFields();
		$currentDepartmentsList = $workgroupFields['UF_SG_DEPT']['VALUE'];

		if (
			!is_array($currentDepartmentsList)
			|| empty($currentDepartmentsList)
		)
		{
			throw new \Exception(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED'));
		}

		$currentDepartmentsList = array_map('intval', array_unique($currentDepartmentsList));

		if (!\CSocNetGroup::update(
			$groupId,
			[
				'NAME' => $workgroupFields['NAME'],
				'UF_SG_DEPT' => array_diff($currentDepartmentsList, [ $departmentId ]),
			]
		))
		{
			if ($ex = $APPLICATION->getException())
			{
				$errorMessage = $ex->getString();
				$errorCode = $ex->getId();
			}
			else
			{
				$errorMessage = Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_OPERATION_FAILED');
				$errorCode = 100;
			}

			throw new \Exception($errorMessage, $errorCode);
		}

		return true;
	}

	protected static function getRelation(array $filter = []): \Bitrix\Socialnetwork\EO_UserToGroup
	{
		$res = UserToGroupTable::getList([
			'filter' => $filter,
			'select' => [ 'ID', 'USER_ID', 'GROUP_ID', 'ROLE', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID', 'AUTO_MEMBER' ]
		]);

		if (!$result = $res->fetchObject())
		{
			throw new ObjectNotFoundException(Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_ERROR_RELATION_NOT_FOUND'));
		}

		return $result;
	}

	public static function canCreate(array $params = []): bool
	{
		$siteId = (string)($params['siteId'] ?? SITE_ID);
		$checkAdminSession = (bool)($params['checkAdminSession'] ?? true);

		return (
			\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, $checkAdminSession)
			|| (\CMain::getGroupRight('socialnetwork', false, 'Y', 'Y', [ $siteId, false ]) >= 'K')
		);
	}

	public static function canUpdate(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = (int)($params['userId'] ?? User::getCurrentUserId());

		if ($groupId <= 0)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
			'userId' => $userId,
		]);

		return (
			(
				$groupPerms
				&& $groupPerms['UserCanModifyGroup']
			)
		);
	}

	public static function canSetOwner(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);

		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		return (
			$groupPerms
			&& $groupPerms['UserCanModifyGroup']
			&& in_array($relation->getRole(), [ UserToGroupTable::ROLE_USER, UserToGroupTable::ROLE_MODERATOR ], true)
		);
	}

	public static function canSetScrumMaster(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);

		if (
			$groupId <= 0
			|| $userId <= 0
		)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);

		$res = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE' ],
		]);
		$relation = $res->fetchObject();

		return (
			$groupPerms
			&& $groupPerms['UserCanModifyGroup']
			&& ($group && $group->isScrumProject())
			&& $userId !== $group->getScrumMaster()
			&& (
				!$relation
				|| in_array($relation->getRole(), UserToGroupTable::getRolesMember(), true)
			)
		);
	}

	public static function canDeleteOutgoingRequest(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);
		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		return (
			$relation->getRole() === UserToGroupTable::ROLE_REQUEST
			&& $relation->getInitiatedByType() === UserToGroupTable::INITIATED_BY_GROUP
			&& $groupPerms
			&& (
				$groupPerms['UserCanProcessRequestsIn']
				|| self::isCurrentUserModuleAdmin()
				|| $relation->getInitiatedByUserId() === User::getCurrentUserId()
			)
		);
	}

	public static function canDeleteIncomingRequest(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);

		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		return (
			$relation->getRole() === UserToGroupTable::ROLE_REQUEST
			&& $relation->getInitiatedByType() === UserToGroupTable::INITIATED_BY_USER
			&& (
				self::isCurrentUserModuleAdmin(true)
				|| $relation->getInitiatedByUserId() === User::getCurrentUserId()
			)
		);
	}

	public static function canProcessIncomingRequest(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);

		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		return (
			$relation->getRole() === UserToGroupTable::ROLE_REQUEST
			&& $relation->getInitiatedByType() === UserToGroupTable::INITIATED_BY_USER
			&& $groupPerms
			&& (
				$groupPerms['UserCanProcessRequestsIn']
				|| self::isCurrentUserModuleAdmin()
			)
		);
	}

	public static function canExclude(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);

		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		$relationUserId = $relation->getUserId();
		if ($relationUserId <= 0)
		{
			$relationUserId = $relation->getUser()->getId();
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
		$scrumMasterId = ($group ? $group->getScrumMaster() : 0);

		return (
			$groupPerms
			&& (
				$groupPerms['UserCanModifyGroup']
				|| self::isCurrentUserModuleAdmin()
			)
			&& !$relation->getAutoMember()
			&& !in_array($relationUserId, [ User::getCurrentUserId(), $scrumMasterId ], true)
			&& in_array($relation->getRole(), [ UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER ], true)
		);
	}

	public static function canSetModerator(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);

		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		return (
			$relation->getRole() === UserToGroupTable::ROLE_USER
			&& $groupPerms
			&& (
				$groupPerms['UserCanModifyGroup']
				|| self::isCurrentUserModuleAdmin()
			)
		);
	}

	public static function canRemoveModerator(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$relation = ($params['relation'] ?? null);

		if (
			$groupId <= 0
			|| !($relation instanceof EO_UserToGroup)
		)
		{
			return false;
		}

		$groupPerms = static::getPermissions([
			'groupId' => $groupId,
		]);

		$relationUserId = $relation->getUserId();
		if ($relationUserId <= 0)
		{
			$relationUserId = $relation->getUser()->getId();
		}

		$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
		$scrumMasterId = ($group ? $group->getScrumMaster() : 0);

		return (
			$relation->getRole() === UserToGroupTable::ROLE_MODERATOR
			&& $groupPerms
			&& !in_array($relationUserId, [ User::getCurrentUserId(), $scrumMasterId ], true)
			&& (
				$groupPerms['UserCanModifyGroup']
				|| self::isCurrentUserModuleAdmin()
			)
		);
	}

	public static function isCurrentUserModuleAdmin(bool $checkSession = false): bool
	{
		$result = null;
		if ($result === null)
		{
			$result = \CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, $checkSession);
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	public static function getCurrentUserId(): int
	{
		return User::getCurrentUserId();
	}

	public static function getProjectPresets($params = []): array
	{
		static $useProjects = null;
		static $extranetInstalled = null;

		if ($extranetInstalled === null)
		{
			$extranetInstalled = self::isExtranetInstalled();
		}

		$entityOptions = (
			!empty($params)
			&& is_array($params['entityOptions'])
			&& !empty($params['entityOptions'])
				? $params['entityOptions']
				: []
		);

		$result = [];
		$sort = 0;

		if ($useProjects === null)
		{
			$useProjects = (
				ModuleManager::isModuleInstalled('intranet')
				&& self::checkEntityOption([ 'project' ], $entityOptions)
			);
		}

		if ($useProjects)
		{
			if (self::checkEntityOption([ '!landing', '!scrum' ], $entityOptions))
			{
				$result['project'] = [
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_PROJECT_PRESET_PROJECT'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_PROJECT_PRESET_PROJECT_DESC'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'PROJECT' => 'Y',
					'SCRUM_PROJECT' => 'N',
					'EXTERNAL' => 'N',
				];
			}

			if (self::checkEntityOption([ 'scrum', '!extranet', '!landing' ], $entityOptions))
			{
				$result['scrum'] = [
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_PROJECT_PRESET_SCRUM'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_PROJECT_PRESET_SCRUM_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'SCRUM_PROJECT' => 'Y',
					'EXTERNAL' => 'N',
				];
			}
		}

		if (self::checkEntityOption([ '!scrum' ], $entityOptions))
		{
			$result['group'] = [
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_PROJECT_PRESET_GROUP'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_PROJECT_PRESET_GROUP_DESC'),
				'VISIBLE' => 'Y',
				'OPENED' => 'Y',
				'PROJECT' => 'N',
				'SCRUM_PROJECT' => 'N',
				'EXTERNAL' => 'N',
			];
		}

		return $result;
	}

	public static function getConfidentialityPresets(array $params = []): array
	{
		static $useProjects = null;

		$currentExtranetSite = (
			!empty($params)
			&& isset($params['currentExtranetSite'])
			&& $params['currentExtranetSite']
		);

		$entityOptions = (
			!empty($params)
			&& is_array($params['entityOptions'])
			&& !empty($params['entityOptions'])
				? $params['entityOptions']
				: []
		);

		$result = [];
		$sort = 0;

		if ($useProjects === null)
		{
			$useProjects = (
				ModuleManager::isModuleInstalled('intranet')
				&& self::checkEntityOption([ 'project' ], $entityOptions)
			);
		}

		if (!$currentExtranetSite)
		{
			if (self::checkEntityOption([ 'open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['open'] = [
					'SORT' => $sort += 10,
					'NAME' => ($useProjects ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN_DESC3'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'EXTERNAL' => 'N',
				];
			}

			if (self::checkEntityOption([ '!open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['closed'] = [
					'SORT' => $sort += 10,
					'NAME' => ($useProjects ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_DESC3'),
					'VISIBLE' => 'Y',
					'OPENED' => 'N',
					'EXTERNAL' => 'N',
				];
			}

			if (self::checkEntityOption([ '!open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['secret'] = [
					'SORT' => $sort += 10,
					'NAME' => ($useProjects ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_SECRET') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_SECRET')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_SECRET_DESC3'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'EXTERNAL' => 'N',
				];
			}
		}

		return $result;
	}

	protected static function checkEntityOption(array $keysList = [], array $entityOptions = []): bool
	{
		$result = true;

		foreach ($keysList as $key)
		{
			if (
				!empty($entityOptions)
				&& (
					(
						isset($entityOptions[$key])
						&& !$entityOptions[$key]
					)
					|| (
						preg_match('/^\!(\w+)$/', $key, $matches)
						&& isset($entityOptions[$matches[1]])
						&& $entityOptions[$matches[1]]
					)
				)
			)
			{
				$result = false;
				break;
			}
		}

		return $result;
	}

	public static function getPresets($params = []): array
	{
		static $useProjects = null;
		static $extranetInstalled = null;
		static $landingInstalled = null;

		if ($extranetInstalled === null)
		{
			$extranetInstalled = self::isExtranetInstalled();
		}

		if ($landingInstalled === null)
		{
			$landingInstalled = ModuleManager::isModuleInstalled('landing');
		}

		$currentExtranetSite = (
			!empty($params)
			&& isset($params['currentExtranetSite'])
			&& $params['currentExtranetSite']
		);

		$entityOptions = (
			!empty($params)
			&& is_array($params['entityOptions'])
			&& !empty($params['entityOptions'])
				? $params['entityOptions']
				: []
		);

		$fullMode = (
			!empty($params)
			&& isset($params['fullMode'])
			&& $params['fullMode']
		);

		$result = [];
		$sort = 0;

		if ($useProjects === null)
		{
			$useProjects = (
				ModuleManager::isModuleInstalled('intranet')
				&& self::checkEntityOption([ 'project' ], $entityOptions)
			);
		}

		if (!$currentExtranetSite)
		{
			if (self::checkEntityOption([ 'open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['project-open'] = [
					'SORT' => $sort += 10,
					'NAME' => ($useProjects ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_OPEN_DESC2'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'PROJECT' => ($useProjects ? 'Y' : 'N' ),
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-open ' . ($useProjects ? 'social-group-tile-item-icon-project-open' : 'social-group-tile-item-icon-group-open')
				];
			}

			if (self::checkEntityOption([ '!open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['project-closed'] = [
					'SORT' => $sort += 10,
					'NAME' => ($useProjects ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => ($useProjects ? 'Y' : 'N' ),
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-close ' . ($useProjects ? 'social-group-tile-item-icon-project-close' : 'social-group-tile-item-icon-group-close')
				];
			}

			if (
				$useProjects
				&& self::checkEntityOption([ 'project', 'scrum', '!extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-scrum'] = [
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'SCRUM_PROJECT' => 'Y',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-scrum social-group-tile-item-icon-project-scrum'
				];
			}

			if (
				$fullMode
				&& self::checkEntityOption([ '!open', '!extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-closed-visible'] = [
					'SORT' => $sort += 10,
					'NAME' => ($useProjects ? Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_VISIBLE') : Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE')),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_VISIBLE_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_CLOSED_VISIBLE_DESC'),
					'VISIBLE' => 'Y',
					'OPENED' => 'N',
					'PROJECT' => ($useProjects ? 'Y' : 'N' ),
					'EXTERNAL' => 'N',
					'TILE_CLASS' => ''
				];
			}
		}

		if (
			$extranetInstalled
			&& self::checkEntityOption([ 'extranet', '!landing' ], $entityOptions)
		)
		{
			$result['project-external'] = [
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_EXTERNAL'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_EXTERNAL_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GP_EXTERNAL_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => ($useProjects ? 'Y' : 'N' ),
				'EXTERNAL' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-outer social-group-tile-item-icon-project-outer'
			];
		}

		if (
			$landingInstalled
			&& self::checkEntityOption([ '!project', 'landing', '!extranet' ], $entityOptions)
		)
		{
			$result['group-landing'] = [
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING2'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC2'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC2'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'EXTERNAL' => 'N',
				'LANDING' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-public social-group-tile-item-icon-group-public'
			];
		}

		return $result;
	}

	public static function getTypes($params = []): array
	{
		static $intranetInstalled = null;
		static $extranetInstalled = null;
		static $landingInstalled = null;

		if ($intranetInstalled === null)
		{
			$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		}

		if ($extranetInstalled === null)
		{
			$extranetInstalled = static::isExtranetInstalled();
		}

		if ($landingInstalled === null)
		{
			$landingInstalled = ModuleManager::isModuleInstalled('landing');
		}

		$currentExtranetSite = (
			!empty($params)
			&& isset($params['currentExtranetSite'])
			&& $params['currentExtranetSite']
		);

		$categoryList = (
			!empty($params)
			&& is_array($params['category'])
			&& !empty($params['category'])
				? $params['category']
				: []
		);

		$entityOptions = (
			!empty($params)
			&& is_array($params['entityOptions'])
			&& !empty($params['entityOptions'])
				? $params['entityOptions']
				: []
		);

		$fullMode = (
			!empty($params)
			&& isset($params['fullMode'])
			&& $params['fullMode']
		);

		$result = [];
		$sort = 0;

		if (
			$intranetInstalled
			&& (
				empty($categoryList)
				|| in_array('projects', $categoryList, true)
			)
		)
		{
			if (!$currentExtranetSite)
			{
				if (self::checkEntityOption([ 'project', 'open', '!extranet', '!landing' ], $entityOptions))
				{
					$result['project-open'] = array(
						'SORT' => $sort += 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_OPEN_DESC2'),
						'VISIBLE' => 'Y',
						'OPENED' => 'Y',
						'PROJECT' => 'Y',
						'SCRUM_PROJECT' => 'N',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => 'social-group-tile-item-cover-open social-group-tile-item-icon-project-open'
					);
				}

				if (self::checkEntityOption([ 'project', '!open', '!extranet', '!landing' ], $entityOptions))
				{
					$result['project-closed'] = array(
						'SORT' => $sort += 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_DESC'),
						'VISIBLE' => 'N',
						'OPENED' => 'N',
						'PROJECT' => 'Y',
						'SCRUM_PROJECT' => 'N',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => 'social-group-tile-item-cover-close social-group-tile-item-icon-project-close'
					);
				}

				if (self::checkEntityOption([ 'project', 'scrum', '!extranet', '!landing' ], $entityOptions))
				{
					$result['project-scrum'] = [
						'SORT' => $sort += 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM2'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC2'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_DESC2'),
						'VISIBLE' => 'N',
						'OPENED' => 'N',
						'PROJECT' => 'Y',
						'SCRUM_PROJECT' => 'Y',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => 'social-group-tile-item-cover-scrum social-group-tile-item-icon-project-scrum'
					];
				}

				if (
					$fullMode
					&& self::checkEntityOption([ 'project', '!open', '!extranet', '!landing' ], $entityOptions)
				)
				{
					$result['project-closed-visible'] = array(
						'SORT' => $sort += 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_VISIBLE'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_VISIBLE_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_CLOSED_VISIBLE_DESC'),
						'VISIBLE' => 'Y',
						'OPENED' => 'N',
						'PROJECT' => 'Y',
						'SCRUM_PROJECT' => 'N',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => ''
					);
				}
			}

			if (
				$extranetInstalled
				&& self::checkEntityOption([ 'project', 'scrum', 'extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-scrum-extranet'] = [
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_EXTERNAL'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_EXTERNAL_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_SCRUM_EXTERNAL_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'SCRUM_PROJECT' => 'Y',
					'EXTERNAL' => 'Y',
					'TILE_CLASS' => 'social-group-tile-item-cover-scrum social-group-tile-item-icon-project-scrum'
				];
			}

			if (
				$extranetInstalled
				&& self::checkEntityOption([ 'project', 'extranet', '!landing' ], $entityOptions)
			)
			{
				$result['project-external'] = array(
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_EXTERNAL'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_EXTERNAL_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_PROJECT_EXTERNAL_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'Y',
					'SCRUM_PROJECT' => 'N',
					'EXTERNAL' => 'Y',
					'TILE_CLASS' => 'social-group-tile-item-cover-outer social-group-tile-item-icon-project-outer'
				);
			}
		}

		if (
			!$currentExtranetSite
			&& (
				empty($categoryList)
				|| in_array('groups', $categoryList)
			)
		)
		{
			if (self::checkEntityOption([ '!project', 'open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['group-open'] = array(
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_OPEN_DESC2'),
					'VISIBLE' => 'Y',
					'OPENED' => 'Y',
					'PROJECT' => 'N',
					'SCRUM_PROJECT' => 'N',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-open social-group-tile-item-icon-group-open'
				);
			}

			if (self::checkEntityOption([ '!project', '!open', '!extranet', '!landing' ], $entityOptions))
			{
				$result['group-closed'] = array(
					'SORT' => $sort += 10,
					'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED'),
					'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_DESC'),
					'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_DESC'),
					'VISIBLE' => 'N',
					'OPENED' => 'N',
					'PROJECT' => 'N',
					'SCRUM_PROJECT' => 'N',
					'EXTERNAL' => 'N',
					'TILE_CLASS' => 'social-group-tile-item-cover-close social-group-tile-item-icon-group-close'
				);
				if ($fullMode)
				{
					$result['group-closed-visible'] = array(
						'SORT' => $sort = $sort + 10,
						'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE'),
						'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE_DESC'),
						'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_CLOSED_VISIBLE_DESC'),
						'VISIBLE' => 'Y',
						'OPENED' => 'N',
						'PROJECT' => 'N',
						'SCRUM_PROJECT' => 'N',
						'EXTERNAL' => 'N',
						'TILE_CLASS' => ''
					);
				}
			}
		}

		if (
			$extranetInstalled
			&& self::checkEntityOption([ '!project', 'extranet', '!landing' ], $entityOptions)
		)
		{
			$result['group-external'] = array(
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_EXTERNAL'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_EXTERNAL_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_EXTERNAL_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'SCRUM_PROJECT' => 'N',
				'EXTERNAL' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-outer social-group-tile-item-icon-group-outer'
			);
		}

		if (
			$landingInstalled
			&& self::checkEntityOption([ '!project', 'landing', '!extranet' ], $entityOptions)
		)
		{
			$result['group-landing'] = array(
				'SORT' => $sort += 10,
				'NAME' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING'),
				'DESCRIPTION' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC'),
				'DESCRIPTION2' => Loc::getMessage('SOCIALNETWORK_ITEM_WORKGROUP_TYPE_GROUP_LANDING_DESC'),
				'VISIBLE' => 'N',
				'OPENED' => 'N',
				'PROJECT' => 'N',
				'SCRUM_PROJECT' => 'N',
				'EXTERNAL' => 'N',
				'LANDING' => 'Y',
				'TILE_CLASS' => 'social-group-tile-item-cover-public social-group-tile-item-icon-group-public'
			);
		}

		return $result;
	}

	protected static function isExtranetInstalled(): bool
	{
		return (
			ModuleManager::isModuleInstalled('extranet')
			&& Option::get('extranet', 'extranet_site') !== ''
		);
	}

	public static function getAvatarTypes(): array
	{
		return [
			'folder' => [
				'sort' => 100,
				'mobileUrl' => '/bitrix/images/socialnetwork/workgroup/form/mobile/folder.png',
				'webCssClass' => 'folder',
				'entitySelectorUrl' => '/bitrix/images/socialnetwork/workgroup/entity-selector/folder.png',
			],
			'checks' => [
				'sort' => 200,
				'mobileUrl' => '/bitrix/images/socialnetwork/workgroup/form/mobile/checks.png',
				'webCssClass' => 'tasks',
				'entitySelectorUrl' => '/bitrix/images/socialnetwork/workgroup/entity-selector/tasks.png',
			],
			'pie' => [
				'sort' => 300,
				'mobileUrl' => '/bitrix/images/socialnetwork/workgroup/form/mobile/pie.png',
				'webCssClass' => 'chart',
				'entitySelectorUrl' => '/bitrix/images/socialnetwork/workgroup/entity-selector/chart.png',
			],
			'bag' => [
				'sort' => 400,
				'mobileUrl' => '/bitrix/images/socialnetwork/workgroup/form/mobile/bag.png',
				'webCssClass' => 'briefcase',
				'entitySelectorUrl' => '/bitrix/images/socialnetwork/workgroup/entity-selector/briefcase.png',
			],
			'members' => [
				'sort' => 500,
				'mobileUrl' => '/bitrix/images/socialnetwork/workgroup/form/mobile/members.png',
				'webCssClass' => 'group',
				'entitySelectorUrl' => '/bitrix/images/socialnetwork/workgroup/entity-selector/group.png',
			],
		];
	}

	public static function getAvatarTypeWebCssClass($type = ''): string
	{
		$result = '';
		$types = static::getAvatarTypes();
		if (empty($types[$type]))
		{
			return $result;
		}

		return $types[$type]['webCssClass'];
	}

	public static function getAvatarEntitySelectorUrl($type = ''): string
	{
		$result = '';
		$types = static::getAvatarTypes();
		if (empty($types[$type]))
		{
			return $result;
		}

		return $types[$type]['entitySelectorUrl'];
	}

	public static function getAdditionalData(array $params = []): array
	{
		global $USER;

		$ids = (
			is_array($params['ids'])
				? array_filter(
					array_map(
						static function($val) { return (int)$val; },
						$params['ids']
					),
					static function ($val) { return $val > 0; }
				)
				: []
		);
		$features = (
			is_array($params['features'])
				? array_filter(
					array_map(
						static function($val) { return trim((string)$val); },
						$params['features']
					),
					static function ($val) { return !empty($val); }
				)
				: []
		);
		$mandatoryFeatures = (
			is_array($params['mandatoryFeatures'])
				? array_filter(
					array_map(
						static function($val) { return trim((string)$val); },
						$params['mandatoryFeatures']
					),
					static function ($val) { return !empty($val); }
				)
			: []
		);
		$currentUserId = (int)($params['currentUserId'] ?? $USER->getId());
		if (empty($ids))
		{
			return $ids;
		}

		$featuresSettings = \CSocNetAllowed::getAllowedFeatures();

		$result = [];
		$userRoles = [];

		$res = UserToGroupTable::getList([
			'filter' => [
				'GROUP_ID' => $ids,
				'USER_ID' => $currentUserId,
			],
			'select' => [ 'GROUP_ID', 'ROLE', 'INITIATED_BY_TYPE' ]

		]);
		while ($relationFields = $res->fetch())
		{
			$userRoles[(int)$relationFields['GROUP_ID']] = [
				'ROLE' => $relationFields['ROLE'],
				'INITIATED_BY_TYPE' => $relationFields['INITIATED_BY_TYPE'],
			];
		}

		foreach ($features as $feature)
		{
			$activeFeaturesList = \CSocNetFeatures::isActiveFeature(SONET_ENTITY_GROUP, $ids, $feature);
			$filteredIds = array_keys(array_filter($activeFeaturesList, static function($val) { return $val; }));

			if (
				empty($filteredIds)
				|| !isset($featuresSettings[$feature])
			)
			{
				$permissions = [];
			}
			else
			{
				$minOperationList = $featuresSettings[$feature]['minoperation'];
				if (!is_array($minOperationList))
				{
					$minOperationList = [ $minOperationList ];
				}

				$permissions = [];
				foreach ($minOperationList as $minOperation)
				{
					$operationPermissions = \CSocNetFeaturesPerms::getOperationPerm(SONET_ENTITY_GROUP, $filteredIds, $feature, $minOperation);
					foreach ($operationPermissions as $groupId => $role)
					{
						if (
							!isset($permissions[$groupId])
							|| $role > $permissions[$groupId]
						)
						{
							$permissions[$groupId] = $role;
						}
					}
				}
			}

			foreach ($ids as $id)
			{
				if (!isset($result[$id]))
				{
					$result[$id] = [];
				}

				if (!isset($result[$id]['FEATURES']))
				{
					$result[$id]['FEATURES'] = [];
				}

				if (
					in_array($feature, $mandatoryFeatures, true)
					|| (
						isset($permissions[$id])
						&& (
							!in_array($permissions[$id], UserToGroupTable::getRolesMember(), true)
							|| (
								isset($userRoles[$id])
								&& $userRoles[$id]['ROLE'] <= $permissions[$id]
							)
						)
					)
				)
				{
					$result[$id]['FEATURES'][] = $feature;
				}
			}
		}

		foreach ($ids as $id)
		{
			$result[$id]['ROLE'] = ($userRoles[$id]['ROLE'] ?? '');
			$result[$id]['INITIATED_BY_TYPE'] = ($userRoles[$id]['INITIATED_BY_TYPE'] ?? '');
		}

		return $result;
	}

	public static function mutateScrumFormFields(array &$fields = []): void
	{
		if (empty($fields['SCRUM_MASTER_ID']))
		{
			return;
		}

		$fields['PROJECT'] = 'Y';

		if (empty($fields['SUBJECT_ID']))
		{
			$siteId = (!empty($fields['SITE_ID']) ? $fields['SITE_ID'] : SITE_ID);

			$subjectQueryObject = \CSocNetGroupSubject::getList(
				[
					'SORT' => 'ASC',
					'NAME' => 'ASC'
				],
				[
					'SITE_ID' => $siteId,
				],
				false,
				false,
				[ 'ID' ]
			);
			if ($subject = $subjectQueryObject->fetch())
			{
				$fields['SUBJECT_ID'] = (int)$subject['ID'];
			}
		}
	}
}
