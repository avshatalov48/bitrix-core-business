<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Integration;

Loc::loadMessages(__FILE__);

class UserToGroup
{
	public const CHAT_ACTION_IN = 'in';
	public const CHAT_ACTION_OUT = 'out';

	public static function addRelationAutoMembership($params): void
	{
		global $USER;

		if (!empty($params['CURRENT_USER_ID']))
		{
			$currentUserId = (int)$params['CURRENT_USER_ID'];
		}
		elseif (is_object($USER))
		{
			$currentUserId = $USER->getId();
		}

		if ($currentUserId <= 0)
		{
			return;
		}

		static $helper = false;

		$userId = (int)($params['USER_ID'] ?? 0);
		$groupId = (int)($params['GROUP_ID'] ?? 0);
		$value = (isset($params['VALUE']) && in_array($params['VALUE'], WorkgroupTable::getAutoMembershipValuesAll(), true) ? $params['VALUE'] : 'Y');
		$notyfy = (isset($params['NOTIFY']) && $params['NOTIFY'] === "N" ? $params['NOTIFY'] : 'Y');

		if (
			$userId <= 0
			|| $groupId <= 0
		)
		{
			return;
		}

		if (!$helper)
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
		}

		$addFields = [
			'AUTO_MEMBER' => $value,
			'USER_ID' => $userId,
			'GROUP_ID' => $groupId,
			'ROLE' => (
				isset($params['ROLE'])
				&& in_array($params['ROLE'], UserToGroupTable::getRolesAll(), true)
					? $params['ROLE']
					: UserToGroupTable::ROLE_USER
			),
			'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
			'INITIATED_BY_USER_ID' => $currentUserId,
			'=DATE_CREATE' => $helper->getCurrentDateTimeFunction(),
			'=DATE_UPDATE' => $helper->getCurrentDateTimeFunction(),
		];

		$relationId = \CSocNetUserToGroup::add($addFields);

		if (
			!$notyfy
			|| $relationId <= 0
			|| $value !== 'Y'
		)
		{
			return;
		}

		self::notifyAutoMembership([
			'GROUP_ID' => $groupId,
			'USER_ID' => $userId,
			'RELATION_ID' => $relationId,
		]);

		self::addInfoToChat([
			'group_id' => $groupId,
			'user_id' => $userId,
			'action' => self::CHAT_ACTION_IN,
			'role' => $addFields['ROLE'],
		]);
	}

	public static function changeRelationAutoMembership($params): void
	{
		static $helper = false;

		$relationId = (int)($params['RELATION_ID'] ?? 0);
		$userId = (int)($params['USER_ID'] ?? 0);
		$groupId = (int)($params['GROUP_ID'] ?? 0);
		$value = (
			isset($params['VALUE'])
			&& in_array($params['VALUE'], WorkgroupTable::getAutoMembershipValuesAll(), true)
				? $params['VALUE']
				: 'Y'
		);
		$notyfy = (isset($params['NOTIFY']) && $params['NOTIFY'] === 'N' ? $params['NOTIFY'] : 'Y');

		if ($relationId <= 0)
		{
			return;
		}

		if (!$helper)
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();
		}

		$updateFields = [
			'AUTO_MEMBER' => $value,
			'=DATE_UPDATE' => $helper->getCurrentDateTimeFunction(),
		];
		if (
			isset($params['ROLE'])
			&& in_array($params['ROLE'], UserToGroupTable::getRolesAll(), true)
		)
		{
			$updateFields['ROLE'] = $params['ROLE'];
		}
		\CSocNetUserToGroup::update($relationId, $updateFields);

		if (
			!$notyfy
			|| $userId <= 0
			|| $groupId <= 0
			|| $value !== 'Y'
		)
		{
			return;
		}

		self::notifyAutoMembership([
			'GROUP_ID' => $groupId,
			'USER_ID' => $userId,
			'RELATION_ID' => $relationId,
		]);

		self::addInfoToChat([
			'group_id' => $groupId,
			'user_id' => $userId,
			'action' => self::CHAT_ACTION_IN,
			'role' => ($params['ROLE'] ?? false),
		]);
	}

	private static function notifyAutoMembership($params): void
	{
		$userId = (int)($params['USER_ID'] ?? 0);
		$groupId = (int)($params['GROUP_ID'] ?? 0);
		$relationId = (int)($params['RELATION_ID'] ?? 0);

		if (
			$userId <= 0
			|| $groupId <= 0
			|| $relationId <= 0
			|| !Loader::includeModule('im')
		)
		{
			return;
		}
		$groupItem = Workgroup::getById($groupId);
		$groupFields = $groupItem->getFields();
		$groupUrlData = $groupItem->getGroupUrlData([
			'USER_ID' => $userId,
		]);

		$messageFields = [
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
			"FROM_USER_ID" => $groupFields["OWNER_ID"],
			"TO_USER_ID" => $userId,
			"NOTIFY_MODULE" => "socialnetwork",
			"NOTIFY_EVENT" => "invite_group",
			"NOTIFY_TAG" => "SOCNET|INVITE_GROUP|" . $userId . '|' . $relationId,
			"NOTIFY_MESSAGE" => Loc::getMessage(($groupItem->isProject() ? "SOCIALNETWORK_ITEM_USERTOGROUP_AUTO_MEMBER_ADD_IM_PROJECT" : "SOCIALNETWORK_ITEM_USERTOGROUP_AUTO_MEMBER_ADD_IM"), [
					"#GROUP_NAME#" => "<a href=\"".$groupUrlData['DOMAIN'] . $groupUrlData['URL'] . "\" class=\"bx-notifier-item-action\">" . htmlspecialcharsEx($groupFields["NAME"]) . '</a>',
				]
			),
			"NOTIFY_MESSAGE_OUT" => Loc::getMessage("SOCIALNETWORK_ITEM_USERTOGROUP_AUTO_MEMBER_ADD_IM", [
						"#GROUP_NAME#" => htmlspecialcharsEx($groupFields["NAME"]),
					]
				) . ' (' . $groupUrlData['SERVER_NAME'] . $groupUrlData['URL'] . ')'
		];

		\CIMNotify::deleteBySubTag('SOCNET|REQUEST_GROUP|' . $userId . '|' . $groupId . '|' . $relationId);
		\CIMNotify::add($messageFields);
	}

	public static function onAfterUserAdd(&$fields): void
	{
		if (
			$fields['ID'] <= 0
			|| (
				isset($fields['ACTIVE'])
				&& $fields['ACTIVE'] !== 'Y'
			)
			|| !self::checkUF()
		)
		{
			return;
		}

		$deparmentIdList = [];
		if (
			isset($fields['UF_DEPARTMENT'])
			&& is_array($fields['UF_DEPARTMENT'])
			&& (int)$fields['UF_DEPARTMENT'][0] > 0
		)
		{
			$deparmentIdList = $fields['UF_DEPARTMENT'];
		}

		if (Loader::includeModule('intranet'))
		{
			$deparmentIdList = array_merge($deparmentIdList, \CIntranetUtils::getSubordinateDepartments($fields['ID'], false));
		}

		$deparmentIdList = array_unique($deparmentIdList);

		if (
			empty($deparmentIdList)
			|| !ModuleManager::isModuleInstalled('intranet')
			|| !Loader::includeModule('iblock')
		)
		{
			return;
		}

		$groupList = self::getConnectedGroups($deparmentIdList);
		if (empty($groupList))
		{
			return;
		}

		foreach($groupList as $groupId)
		{
			self::addRelationAutoMembership([
				'USER_ID' => $fields['ID'],
				'GROUP_ID' => $groupId,
				'NOTIFY' => 'N',
			]);
		}
	}

	public static function onAfterUserUpdate(&$userFields): void
	{
		if (!self::checkUF())
		{
			return;
		}

		if (
			(int)$userFields['ID'] <= 0
			|| !isset($userFields['UF_DEPARTMENT'])
			|| !ModuleManager::isModuleInstalled('intranet')
			|| !Loader::includeModule('iblock')
		)
		{
			return;
		}

		$oldGroupList = [];
		$oldGroupAutoList = [];
		$newGroupList = [];

		$res = UserToGroupTable::getList([
			'filter' => [
				'USER_ID' => (int)$userFields['ID']
			],
			'select' => [ 'GROUP_ID', 'AUTO_MEMBER' ]
		]);

		while($relation = $res->fetch())
		{
			$oldGroupList[] = $relation['GROUP_ID'];
			if ($relation['AUTO_MEMBER'] === 'Y')
			{
				$oldGroupAutoList[] = $relation['GROUP_ID'];
			}
		}
		$oldGroupList = array_unique($oldGroupList);
		$oldGroupAutoList = array_unique($oldGroupAutoList);

		if (
			!empty($userFields['UF_DEPARTMENT'])
			&& is_array($userFields['UF_DEPARTMENT'])
		)
		{
			$userFields['UF_DEPARTMENT'] = array_values($userFields['UF_DEPARTMENT']);
		}

		$departmentList = (
			!is_array($userFields['UF_DEPARTMENT'])
			|| empty($userFields['UF_DEPARTMENT'])
			|| (int)$userFields['UF_DEPARTMENT'][0] <= 0
				? []
				: $userFields['UF_DEPARTMENT']
		);

		if (Loader::includeModule('intranet'))
		{
			$departmentList = array_merge($departmentList, \CIntranetUtils::getSubordinateDepartments($userFields['ID'], false));
		}
		$departmentList = array_unique($departmentList);

		if (!empty($departmentList))
		{
			$newGroupList = self::getConnectedGroups($departmentList);
		}
		$groupListPlus = array_diff($newGroupList, $oldGroupList);
		$groupListMinus = array_diff($oldGroupAutoList, $newGroupList);
		$groupListMinus = array_diff($groupListMinus, $groupListPlus);

		if (!empty($groupListMinus))
		{
			$res = UserToGroupTable::getList([
				'filter' => [
					'=USER_ID' => (int)$userFields['ID'],
					'@GROUP_ID' => $groupListMinus,
					'@ROLE' => [ UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER ],
					'AUTO_MEMBER' => 'Y'
				],
				'select' => [ 'ID' ],
			]);
			while($relation = $res->fetch())
			{
				\CSocNetUserToGroup::delete($relation['ID']);
			}
		}

		$changeList = $addList = $noChangeList = [];
		if (!empty($groupListPlus))
		{
			$res = UserToGroupTable::getList([
				'filter' => [
					'=USER_ID' => (int)$userFields["ID"],
					'@GROUP_ID' => $groupListPlus,
					'@ROLE' => [ UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER ],
				],
				'select' => [ 'ID', 'GROUP_ID', 'AUTO_MEMBER' ],
			]);

			while ($relation = $res->fetch())
			{
				if (
					$relation['AUTO_MEMBER'] === 'Y'
					|| $relation['ROLE'] === UserToGroupTable::ROLE_OWNER
				)
				{
					$noChangeList[] = $relation['GROUP_ID'];
				}
				else // UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER, AUTO_MEMBER = 'N'
				{
					$noChangeList[] = $relation['GROUP_ID'];
					self::changeRelationAutoMembership([
						'RELATION_ID' => (int)$relation['ID'],
						'USER_ID' => (int)$userFields["ID"],
						'GROUP_ID' => (int)$relation['GROUP_ID'],
						'ROLE' => $relation['ROLE'],
						'VALUE' => 'Y',
						'NOTIFY' => 'N',
					]);
				}
			}

			$groupListPlus = array_diff($groupListPlus, $noChangeList);
		}

		if (!empty($groupListPlus))
		{
			$res = UserToGroupTable::getList([
				'filter' => [
					'=USER_ID' => (int)$userFields['ID'],
					'@GROUP_ID' => $groupListPlus,
					'@ROLE' => [ UserToGroupTable::ROLE_REQUEST, UserToGroupTable::ROLE_BAN ],
					'AUTO_MEMBER' => 'N',
				],
				'select' => [ 'ID', 'USER_ID', 'GROUP_ID' ],
			]);

			while ($relation = $res->fetch())
			{
				$changeList[] = (int)$relation['GROUP_ID'];
				self::changeRelationAutoMembership([
					'RELATION_ID' => (int)$relation['ID'],
					'USER_ID' => (int)$relation['USER_ID'],
					'GROUP_ID' => (int)$relation['GROUP_ID'],
					'ROLE' => UserToGroupTable::ROLE_USER,
					'VALUE' => 'Y',
				]);
			}
			$addList = array_diff($groupListPlus, $changeList);
		}

		foreach ($addList as $addGroupId)
		{
			self::addRelationAutoMembership([
				'USER_ID' => (int)$userFields['ID'],
				'GROUP_ID' => $addGroupId,
				'ROLE' => UserToGroupTable::ROLE_USER,
				'VALUE' => 'Y',
			]);
		}
	}

	public static function getConnectedGroups($departmentList): array
	{
		static $structureIBlockId = false;
		static $departmentChainCache = [];

		$result = [];

		if ($structureIBlockId === false)
		{
			$structureIBlockId = (int)Option::get('intranet', 'iblock_structure', 0);
		}

		if ((int)$structureIBlockId <= 0)
		{
			return $result;
		}

		$userDepartmentList = [];

		foreach($departmentList as $departmentId)
		{
			$departmentChain = [];
			if (isset($departmentChainCache[$departmentId]))
			{
				$departmentChain = $departmentChainCache[$departmentId];
			}
			else
			{
				$res = \CIBlockSection::getNavChain($structureIBlockId, $departmentId, [ 'ID' ]);
				while ($section = $res->fetch())
				{
					if ((int)$section['ID'] > 0)
					{
						$departmentChain[] = (int)$section['ID'];
					}
				}
				$departmentChainCache[$departmentId] = $departmentChain;
			}
			$userDepartmentList = array_merge($userDepartmentList, $departmentChain);
		}

		$userDepartmentList = array_unique($userDepartmentList);

		if (!empty($userDepartmentList))
		{
			$res = WorkgroupTable::getList([
				'filter' => [
					'@UF_SG_DEPT' => $userDepartmentList,
				],
				'select' => [ 'ID' ],
			]);
			while ($group = $res->fetch())
			{
				if ((int)$group['ID'] > 0)
				{
					$result[] = (int)$group['ID'];
				}
			}
		}

		return array_unique($result);
	}

	private static function checkUF(): bool
	{
		$res = \CUserTypeEntity::getList([], [
			'ENTITY_ID' => 'SONET_GROUP',
			'FIELD_NAME' => 'UF_SG_DEPT'
		]);
		return ($res && ($res->fetch()));
	}

	public static function addInfoToChat($params = [])
	{
		if (
			!is_array($params)
			|| !isset($params['group_id'], $params['user_id'], $params['action'])
			|| (int)$params['group_id'] <= 0
			|| (int)$params['user_id'] <= 0
			|| !Integration\Im\Chat\Workgroup::getUseChat()
			|| !Loader::includeModule('im')
			|| !in_array($params['action'], self::getChatActionList(), true)
		)
		{
			return false;
		}

		$groupId = (int)$params['group_id'];
		$userId = (int)$params['user_id'];
		$role = ($params['role'] ?? false);

		$sendMessage = (
			!isset($params['sendMessage'])
			|| $params['sendMessage']
		);

		$chatData = Integration\Im\Chat\Workgroup::getChatData([
			'group_id' => $groupId,
			'skipAvailabilityCheck' => true,
		]);

		if (
			empty($chatData)
			|| empty($chatData[$groupId])
			|| (int)$chatData[$groupId] <= 0
		)
		{
			return false;
		}

		$res = \CUser::getById($userId);
		$user = $res->fetch();

		if (
			empty($user)
			|| (
				isset($user['ACTIVE'])
				&& $user['ACTIVE'] === 'N'
			)
		)
		{
			return false;
		}

		$groupItem = Workgroup::getById($groupId);
		$projectSuffix = ($groupItem->isProject() ? '_PROJECT' : '');

		$userName = \CUser::formatName(\CSite::getNameFormat(), $user, true, false);
		switch($user['PERSONAL_GENDER'])
		{
			case "M":
				$genderSuffix = '_M';
				break;
			case "F":
				$genderSuffix = '_F';
				break;
			default:
				$genderSuffix = '';
		}

		$chatId = $chatData[$groupId];
		$chat = new \CIMChat(0);

		switch ($params['action'])
		{
			case self::CHAT_ACTION_IN:
				if ($chat->addUser($chatId, $userId, false, true, true))
				{
					if ($role === UserToGroupTable::ROLE_USER)
					{
						\Bitrix\Im\Chat::mute($chatId, true, $userId);
					}
					$chatMessage = str_replace('#USER_NAME#', $userName, Loc::getMessage('SOCIALNETWORK_ITEM_USERTOGROUP_CHAT_USER_ADD' . $projectSuffix . $genderSuffix));
				}
				else
				{
					$sendMessage = false;
				}
				break;
			case self::CHAT_ACTION_OUT:
				if ($chat->deleteUser($chatId, $userId, false, true))
				{
					$chatMessage = str_replace('#USER_NAME#', $userName, Loc::getMessage('SOCIALNETWORK_ITEM_USERTOGROUP_CHAT_USER_DELETE' . $projectSuffix . $genderSuffix));
				}
				else
				{
					$sendMessage = false;
				}
				break;
			default:
				$chatMessage = '';
				$sendMessage = false;
		}

		if ($sendMessage)
		{
			$chatMessageFields = [
				"MESSAGE" => $chatMessage,
				"SYSTEM" => "Y",
				"INCREMENT_COUNTER" => "N",
				"PUSH" => "N"
			];

			$availableChatData = Integration\Im\Chat\Workgroup::getChatData([
				'group_id' => $groupId,
			]);

			if (
				!empty($availableChatData)
				&& !empty($availableChatData[$groupId])
				&& (int)$availableChatData[$groupId] > 0
			)
			{
				return \CIMChat::addMessage(array_merge(
					$chatMessageFields, [
						'TO_CHAT_ID' => $chatId,
					]
				));
			}
		}
		else
		{
			return true;
		}

		return false;
	}

	private static function getChatActionList(): array
	{
		return [ self::CHAT_ACTION_IN, self::CHAT_ACTION_OUT ];
	}

	public static function addModerators($params = []): bool
	{
		global $USER;

		$result = false;

		if (
			!is_array($params)
			|| !isset($params['group_id'], $params['user_id'])
 			|| (int)$params['group_id'] <= 0
			|| empty($params['user_id'])
		)
		{
			return $result;
		}

		$groupId = (int)$params['group_id'];
		$userIdList = (
			is_array($params['user_id'])
				? $params['user_id']
				: [ $params['user_id'] ]
		);
		$currentUserId = (
			isset($params['current_user_id'])
			&& (int)$params['current_user_id'] > 0
				? (int)$params['current_user_id']
				: (
					is_object($USER)
					&& $USER->isAuthorized()
						? $USER->getId()
						: false
				)
		);

		if (!$currentUserId)
		{
			return $result;
		}

		$ownerRelationIdList = [];
		$memberRelationIdList = [];
		$otherRelationIdList = [];

		$resRelation = UserToGroupTable::getList([
			'filter' => [
				'GROUP_ID' => $groupId,
				'@USER_ID' => $userIdList
			],
			'select' => [ 'ID', 'USER_ID', 'ROLE' ],
		]);

		while ($relation = $resRelation->fetch())
		{
			if ($relation['ROLE'] === UserToGroupTable::ROLE_USER)
			{
				$memberRelationIdList[$relation['USER_ID']] = $relation['ID'];
			}
			elseif ($relation['ROLE'] === UserToGroupTable::ROLE_OWNER)
			{
				$ownerRelationIdList[$relation['USER_ID']] = $relation['ID'];
			}
			else // ban, request
			{
				$otherRelationIdList[$relation['USER_ID']] = $relation['ID'];
			}
		}

		if (!empty($memberRelationIdList))
		{
			\CSocNetUserToGroup::transferMember2Moderator($currentUserId, $groupId, $memberRelationIdList);
		}

		foreach ($userIdList as $userId)
		{
			if (
				!array_key_exists($userId, $memberRelationIdList)
				&& !array_key_exists($userId, $ownerRelationIdList)
			)
			{
				if (array_key_exists($userId, $otherRelationIdList))
				{
					$relationId = \CSocNetUserToGroup::update($otherRelationIdList[$userId], [
						'ROLE' => UserToGroupTable::ROLE_MODERATOR,
						'=DATE_UPDATE' => \CDatabase::CurrentTimeFunction(),
					]);
				}
				else
				{
					$relationId = \CSocNetUserToGroup::add([
						'USER_ID' => $userId,
						'GROUP_ID' => $groupId,
						'ROLE' => UserToGroupTable::ROLE_MODERATOR,
						'=DATE_CREATE' => \CDatabase::CurrentTimeFunction(),
						'=DATE_UPDATE' => \CDatabase::CurrentTimeFunction(),
						'MESSAGE' => '',
						'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
						'INITIATED_BY_USER_ID' => $currentUserId,
						'SEND_MAIL' => 'N',
					]);
				}

				if ($relationId)
				{
					\CSocNetUserToGroup::notifyModeratorAdded([
						'userId' => $currentUserId,
						'groupId' => $groupId,
						'relationId' => $relationId
					]);
				}
			}
		}

		return true;
	}

}
