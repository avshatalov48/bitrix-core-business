<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper\Workgroup;

use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Permission\GroupDictionary;
use Bitrix\Socialnetwork\Permission\Model\GroupModel;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Item\Workgroup\AccessManager;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

class Access
{
	public static function isRuleEnabled(string $rule = ''): bool
	{
		if (empty($rule))
		{
			return Option::get('socialnetwork', 'group_access_rules_enabled', 'Y') === 'Y';
		}

		return Option::get('socialnetwork', 'group_access_rules_enabled_rule_'. $rule, 'Y') === 'Y';
	}

	public static function canCreate(array $params = []): bool
	{
		$siteId = (string)($params['siteId'] ?? SITE_ID);
		$userId = (int)($params['userId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::CREATE))
		{
			return GroupAccessController::getInstance($userId)
				->check(GroupDictionary::CREATE, GroupModel::createFromArray(['siteIds' => [$siteId]]));
		}

		$checkAdminSession = (bool)($params['checkAdminSession'] ?? true);

		return (
			\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, $checkAdminSession)
			|| (\CMain::getGroupRight('socialnetwork', false, 'Y', 'Y', [ $siteId, false ]) >= 'K')
		);
	}

	public static function canView(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$currentUserId = (int)($params['userId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::VIEW))
		{
			return GroupAccessController::can($currentUserId, GroupDictionary::VIEW, $groupId);
		}

		if ($groupId <= 0)
		{
			return false;
		}

		if ($currentUserId <= 0)
		{
			return false;
		}

		$checkAdminSession = (bool)($params['checkAdminSession'] ?? true);

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID', 'VISIBLE' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$currentUserRelation,
			$currentUserRelation,
			[],
			[
				'checkAdminSession' => $checkAdminSession,
			]
		);

		return $accessManager->canView();
	}

	public static function canModify(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$currentUserId = (int)($params['userId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::UPDATE))
		{
			return GroupAccessController::can($currentUserId, GroupDictionary::UPDATE, $groupId);
		}

		$checkAdminSession = (bool)($params['checkAdminSession'] ?? true);

		if ($groupId <= 0)
		{
			return false;
		}

		if ($currentUserId <= 0)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$currentUserRelation,
			$currentUserRelation,
			[],
			[
				'checkAdminSession' => $checkAdminSession,
			]
		);

		return $accessManager->canModify();
	}

	public static function canUpdate(array $params = []): bool
	{
		return static::canModify($params);
	}

	public static function canDelete(array $params = []): bool
	{
		return static::canModify($params);
	}

	public static function canSetOwner(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = (int)($params['userId'] ?? 0);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::SET_OWNER))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::SET_OWNER,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canSetOwner();
	}

	public static function canSetScrumMaster(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::SET_SCRUM_MASTER))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::SET_SCRUM_MASTER,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canSetScrumMaster();
	}

	public static function canDeleteOutgoingRequest(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::DELETE_OUTGOING_REQUEST))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::DELETE_OUTGOING_REQUEST,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID', 'INITIATE_PERMS' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canDeleteOutgoingRequest();
	}

	public static function canDeleteIncomingRequest(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::DELETE_INCOMING_REQUEST))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::DELETE_INCOMING_REQUEST,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'GROUP_ID', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canDeleteIncomingRequest();
	}

	public static function canProcessIncomingRequest(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::PROCESS_INCOMING_REQUEST))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::PROCESS_INCOMING_REQUEST,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID', 'INITIATE_PERMS' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'GROUP_ID', 'INITIATED_BY_TYPE' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canProcessIncomingRequest();
	}

	public static function canExclude(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::EXCLUDE))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::EXCLUDE,
				$groupId,
				['userId' => $userId]
			);
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID', 'INITIATE_PERMS' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'AUTO_MEMBER' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canExclude();
	}

	public static function canJoin(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::JOIN))
		{
			return GroupAccessController::can($userId, GroupDictionary::JOIN, $groupId);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'VISIBLE' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'INITIATED_BY_TYPE' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			null,
			$currentUserRelation
		);

		return $accessManager->canJoin();
	}

	public static function canLeave(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::LEAVE))
		{
			return GroupAccessController::can($userId, GroupDictionary::LEAVE, $groupId);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID', 'AUTO_MEMBER' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			null,
			$currentUserRelation
		);

		return $accessManager->canLeave();
	}

	public static function canSetModerator(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::SET_MODERATOR))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::SET_MODERATOR,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canSetModerator();
	}

	public static function canRemoveModerator(array $params = []): bool
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$userId = ($params['userId'] ?? null);
		$currentUserId = (int)($params['fromUserId'] ?? Helper\User::getCurrentUserId());

		if (static::isRuleEnabled(GroupDictionary::REMOVE_MODERATOR))
		{
			return GroupAccessController::can(
				$currentUserId,
				GroupDictionary::REMOVE_MODERATOR,
				$groupId,
				['userId' => $userId]
			);
		}

		if (
			$groupId <= 0
			|| $userId <= 0
			|| $currentUserId <= 0
		)
		{
			return false;
		}

		$group = WorkgroupTable::getList([
			'filter' => [
				'=ID' => $groupId,
			],
			'select' => [ 'ID', 'CLOSED', 'PROJECT', 'SCRUM_MASTER_ID' ],
		])->fetchObject();
		if (!$group)
		{
			return false;
		}

		$targetUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $userId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$currentUserRelation = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId,
				'=USER_ID' => $currentUserId,
			],
			'select' => [ 'ID', 'ROLE', 'USER_ID', 'GROUP_ID' ],
		])->fetchObject();

		$accessManager = new AccessManager(
			$group,
			$targetUserRelation,
			$currentUserRelation
		);

		return $accessManager->canRemoveModerator();
	}
}
