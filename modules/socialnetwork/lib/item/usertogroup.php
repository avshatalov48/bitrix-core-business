<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

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
	const CHAT_ACTION_IN = 'in';
	const CHAT_ACTION_OUT = 'out';

	public static function addRelationAutoMembership($params)
	{
		global $USER;

		if (!is_object($USER))
		{
			return;
		}

		static $helper = false;

		$userId = (isset($params['USER_ID']) ? intval($params['USER_ID']) : false);
		$groupId = (isset($params['GROUP_ID']) ? intval($params['GROUP_ID']) : false);
		$value = (isset($params['VALUE']) && in_array($params['VALUE'], WorkgroupTable::getAutoMembershipValuesAll()) ? $params['VALUE'] : "Y");
		$notyfy = (isset($params['NOTIFY']) && $params['NOTIFY'] == "N" ? $params['NOTIFY'] : "Y");

		if (
			intval($userId) > 0
			&& intval($groupId) > 0
		)
		{
			if (!$helper)
			{
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();
			}

			$addFields = array(
				'AUTO_MEMBER' => $value,
				'USER_ID' => $userId,
				'GROUP_ID' => $groupId,
				'ROLE' => (isset($params['ROLE']) && in_array($params['ROLE'], UserToGroupTable::getRolesAll()) ? $params['ROLE'] : UserToGroupTable::ROLE_USER),
				'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				'INITIATED_BY_USER_ID' => $USER->getId(),
				"=DATE_CREATE" => $helper->getCurrentDateTimeFunction(),
				"=DATE_UPDATE" => $helper->getCurrentDateTimeFunction(),
			);

			$relationId = \CSocNetUserToGroup::add($addFields);

			if (
				$notyfy
				&& $relationId
				&& $userId
				&& $groupId
				&& $value == 'Y'
			)
			{
				self::notifyAutoMembership(array(
					'GROUP_ID' => $groupId,
					'USER_ID' => $userId,
					'RELATION_ID' => $relationId
				));

				self::addInfoToChat(array(
					'group_id' => $groupId,
					'user_id' => $userId,
					'action' => self::CHAT_ACTION_IN
				));
			}
		}
	}

	public static function changeRelationAutoMembership($params)
	{
		static $helper = false;

		$relationId = (isset($params['RELATION_ID']) ? intval($params['RELATION_ID']) : false);
		$userId = (isset($params['USER_ID']) ? intval($params['USER_ID']) : false);
		$groupId = (isset($params['GROUP_ID']) ? intval($params['GROUP_ID']) : false);
		$value = (isset($params['VALUE']) && in_array($params['VALUE'], WorkgroupTable::getAutoMembershipValuesAll()) ? $params['VALUE'] : 'Y');
		$notyfy = (isset($params['NOTIFY']) && $params['NOTIFY'] == "N" ? $params['NOTIFY'] : "Y");

		if (intval($relationId) > 0)
		{
			if (!$helper)
			{
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();
			}

			$updateFields = array(
				'AUTO_MEMBER' => $value,
				'=DATE_UPDATE' => $helper->getCurrentDateTimeFunction(),
			);
			if (
				isset($params['ROLE'])
				&& in_array($params['ROLE'], UserToGroupTable::getRolesAll())
			)
			{
				$updateFields['ROLE'] = $params['ROLE'];
			}
			\CSocNetUserToGroup::update($relationId, $updateFields);

			if (
				$notyfy
				&& $userId
				&& $groupId
				&& $value == 'Y'
			)
			{
				self::notifyAutoMembership(array(
					'GROUP_ID' => $groupId,
					'USER_ID' => $userId,
					'RELATION_ID' => $relationId
				));

				self::addInfoToChat(array(
					'group_id' => $groupId,
					'user_id' => $userId,
					'action' => self::CHAT_ACTION_IN
				));
			}
		}
	}

	private static function notifyAutoMembership($params)
	{
		$userId = (isset($params['USER_ID']) ? intval($params['USER_ID']) : false);
		$groupId = (isset($params['GROUP_ID']) ? intval($params['GROUP_ID']) : false);
		$relationId = (isset($params['RELATION_ID']) ? intval($params['RELATION_ID']) : false);

		if (
			$userId
			&& $groupId
			&& $relationId
			&& Loader::includeModule('im')
		)
		{
			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
			$groupFields = $groupItem->getFields();
			$groupUrlData = $groupItem->getGroupUrlData(array(
				'USER_ID' => $userId
			));

			$messageFields = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
				"FROM_USER_ID" => $groupFields["OWNER_ID"],
				"TO_USER_ID" => $userId,
				"NOTIFY_MODULE" => "socialnetwork",
				"NOTIFY_EVENT" => "invite_group",
				"NOTIFY_TAG" => "SOCNET|INVITE_GROUP|".$userId."|".$relationId,
				"NOTIFY_MESSAGE" => Loc::getMessage(($groupItem->isProject() ? "SOCIALNETWORK_ITEM_USERTOGROUP_AUTO_MEMBER_ADD_IM_PROJECT" : "SOCIALNETWORK_ITEM_USERTOGROUP_AUTO_MEMBER_ADD_IM"), array(
						"#GROUP_NAME#" => "<a href=\"".$groupUrlData['DOMAIN'].$groupUrlData['URL']."\" class=\"bx-notifier-item-action\">".htmlspecialcharsEx($groupFields["NAME"])."</a>"
					)
				),
				"NOTIFY_MESSAGE_OUT" => Loc::getMessage("SOCIALNETWORK_ITEM_USERTOGROUP_AUTO_MEMBER_ADD_IM", array(
							"#GROUP_NAME#" => htmlspecialcharsEx($groupFields["NAME"])
						)
					)." (".$groupUrlData['SERVER_NAME'].$groupUrlData['URL'].")"
			);

			\CIMNotify::deleteBySubTag("SOCNET|REQUEST_GROUP|".$userId."|".$groupId."|".$relationId);
			\CIMNotify::add($messageFields);
		}
	}

	public static function onAfterUserAdd(&$fields)
	{
		if (!self::checkUF())
		{
			return;
		}

		if (
			$fields['ID'] > 0
			&& (!isset($fields['ACTIVE']) || $fields['ACTIVE'] == 'Y')
			&& isset($fields['UF_DEPARTMENT'])
			&& is_array($fields['UF_DEPARTMENT'])
			&& intval($fields['UF_DEPARTMENT'][0]) > 0
			&& ModuleManager::isModuleInstalled('intranet')
			&& Loader::includeModule('iblock')
		)
		{
			$groupList = self::getConnectedGroups($fields['UF_DEPARTMENT']);
			if (!empty($groupList))
			{
				foreach($groupList as $groupId)
				{
					self::addRelationAutoMembership(array(
						'USER_ID' => $fields['ID'],
						'GROUP_ID' => $groupId,
						'NOTIFY' => 'N'
					));
				}
			}
		}
	}

	public static function onAfterUserUpdate(&$userFields)
	{
		if (!self::checkUF())
		{
			return;
		}

		if (
			intval($userFields['ID']) > 0
			&& isset($userFields['UF_DEPARTMENT'])
			&& ModuleManager::isModuleInstalled('intranet')
			&& Loader::includeModule('iblock')
		)
		{
			$oldGroupList = $oldGroupAutoList = $newGroupList = array();
			$res = UserToGroupTable::getList(array(
				'filter' => array(
					'USER_ID' => intval($userFields['ID'])
				),
				'select' => array('GROUP_ID', 'AUTO_MEMBER')
			));
			while($relation = $res->fetch())
			{
				$oldGroupList[] = $relation['GROUP_ID'];
				if ($relation['AUTO_MEMBER'] == 'Y')
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
				!is_array($userFields['UF_DEPARTMENT']) || empty($userFields['UF_DEPARTMENT']) || intval($userFields['UF_DEPARTMENT'][0]) <= 0
					? array()
					: $userFields['UF_DEPARTMENT']
			);
			if (!empty($departmentList))
			{
				$newGroupList = self::getConnectedGroups($userFields['UF_DEPARTMENT']);
			}
			$groupListPlus = array_diff($newGroupList, $oldGroupList);
			$groupListMinus = array_diff($oldGroupAutoList, $newGroupList);
			$groupListMinus = array_diff($groupListMinus, $groupListPlus);

			if (!empty($groupListMinus))
			{
				$res = UserToGroupTable::getList(array(
					'filter' => array(
						'=USER_ID' => intval($userFields["ID"]),
						'@GROUP_ID' => $groupListMinus,
						'@ROLE' => array(UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER),
						'AUTO_MEMBER' => 'Y'
					),
					'select' => array('ID')
				));
				while($relation = $res->fetch())
				{
					\CSocNetUserToGroup::delete($relation['ID']);
				}
			}

			$changeList = $addList = $noChangeList = $setAutoList = array();
			if (!empty($groupListPlus))
			{
				$res = UserToGroupTable::getList(array(
					'filter' => array(
						'=USER_ID' => intval($userFields["ID"]),
						'@GROUP_ID' => $groupListPlus,
						'@ROLE' => array(UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER),
					),
					'select' => array('ID', 'GROUP_ID', 'AUTO_MEMBER')
				));
				while($relation = $res->fetch())
				{
					if (
						$relation['AUTO_MEMBER'] == 'Y'
						|| $relation['ROLE'] == UserToGroupTable::ROLE_OWNER
					)
					{
						$noChangeList[] = $relation['GROUP_ID'];
					}
					else // UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER, AUTO_MEMBER = 'N'
					{
						$noChangeList[] = $relation['GROUP_ID'];
						UserToGroup::changeRelationAutoMembership(array(
							'RELATION_ID' => intval($relation['ID']),
							'USER_ID' => intval($userFields["ID"]),
							'GROUP_ID' => intval($relation['GROUP_ID']),
							'ROLE' => $relation['ROLE'],
							'VALUE' => 'Y',
							'NOTIFY' => 'N'
						));
					}
				}

				$groupListPlus = array_diff($groupListPlus, $noChangeList);
			}

			if (!empty($groupListPlus))
			{
				$res = UserToGroupTable::getList(array(
					'filter' => array(
						'=USER_ID' => intval($userFields["ID"]),
						'@GROUP_ID' => $groupListPlus,
						'@ROLE' => array(UserToGroupTable::ROLE_REQUEST, UserToGroupTable::ROLE_BAN),
						'AUTO_MEMBER' => 'N'
					),
					'select' => array('ID', 'USER_ID', 'GROUP_ID')
				));
				while($relation = $res->fetch())
				{
					$changeList[] = intval($relation['GROUP_ID']);
					UserToGroup::changeRelationAutoMembership(array(
						'RELATION_ID' => intval($relation['ID']),
						'USER_ID' => intval($relation['USER_ID']),
						'GROUP_ID' => intval($relation['GROUP_ID']),
						'ROLE' => UserToGroupTable::ROLE_USER,
						'VALUE' => 'Y'
					));
				}
				$addList = array_diff($groupListPlus, $changeList);
			}

			foreach($addList as $addGroupId)
			{
				UserToGroup::addRelationAutoMembership(array(
					'USER_ID' => intval($userFields["ID"]),
					'GROUP_ID' => $addGroupId,
					'ROLE' => UserToGroupTable::ROLE_USER,
					'VALUE' => 'Y'
				));
			}
		}
	}

	public static function getConnectedGroups($departmentList)
	{
		static $structureIBlockId = false;
		static $departmentChainCache = array();

		$result = array();

		if ($structureIBlockId === false)
		{
			$structureIBlockId = intval(Option::get('intranet', 'iblock_structure', 0));
		}

		if (intval($structureIBlockId) > 0)
		{
			$userDepartmentList = array();

			foreach($departmentList as $departmentId)
			{
				$departmentChain = array();
				if (isset($departmentChainCache[$departmentId]))
				{
					$departmentChain = $departmentChainCache[$departmentId];
				}
				else
				{
					$res = \CIBlockSection::getNavChain($structureIBlockId, $departmentId, array("ID"));
					while ($section = $res->fetch())
					{
						if (intval($section['ID']) > 0)
						{
							$departmentChain[] = intval($section['ID']);
						}
					}
					$departmentChainCache[$departmentId] = $departmentChain;
				}
				$userDepartmentList = array_merge($userDepartmentList, $departmentChain);
			}
			$userDepartmentList = array_unique($userDepartmentList);

			if (!empty($userDepartmentList))
			{
				$res = WorkgroupTable::getList(array(
					'filter' => array(
						'@UF_SG_DEPT' => $userDepartmentList
					),
					'select' => array('ID')
				));
				while ($group = $res->fetch())
				{
					if (intval($group['ID']) > 0)
					{
						$result[] = intval($group['ID']);
					}
				}
			}
			$result = array_unique($result);
		}

		return $result;
	}

	private static function checkUF()
	{
		$res = \CUserTypeEntity::getList(array(), array("ENTITY_ID" => "SONET_GROUP", "FIELD_NAME" => "UF_SG_DEPT"));
		return ($res && ($uf = $res->fetch()));
	}

	public static function addInfoToChat($params = array())
	{
		$result = false;

		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !isset($params['user_id'])
			|| intval($params['user_id']) <= 0
			|| !isset($params['action'])
			|| !in_array($params['action'], self::getChatActionList())
			|| !Integration\Im\Chat\Workgroup::getUseChat()
			|| !Loader::includeModule('im')
		)
		{
			return $result;
		}

		$groupId = intval($params['group_id']);
		$userId = intval($params['user_id']);
		$sendMessage = (
			!isset($params['sendMessage'])
			|| $params['sendMessage']
		);

		$chatData = Integration\Im\Chat\Workgroup::getChatData(array(
			'group_id' => $groupId,
			'skipAvailabilityCheck' => true
		));

		if (
			empty($chatData)
			|| empty($chatData[$groupId])
			|| intval($chatData[$groupId]) <= 0
		)
		{
			return $result;
		}

		$res = \CUser::getById($userId);
		$user = $res->fetch();
		if (
			empty($user)
			|| (
				isset($user['ACTIVE'])
				&& $user['ACTIVE'] == 'N'
			)
		)
		{
			return $result;
		}

		$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
		$projectSuffix = ($groupItem->isProject() ? '_PROJECT' : '');

		$userName = \CUser::formatName(\CSite::getNameFormat(), $user, true);
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

		switch($params['action'])
		{
			case self::CHAT_ACTION_IN:
				$chat->addUser($chatId, $userId, false, true, true);
				$chatMessage = str_replace('#USER_NAME#', $userName, Loc::getMessage("SOCIALNETWORK_ITEM_USERTOGROUP_CHAT_USER_ADD".$projectSuffix.$genderSuffix));
				break;
			case self::CHAT_ACTION_OUT:
				$chat->deleteUser($chatId, $userId, false, true);
				$chatMessage = str_replace('#USER_NAME#', $userName, Loc::getMessage("SOCIALNETWORK_ITEM_USERTOGROUP_CHAT_USER_DELETE".$projectSuffix.$genderSuffix));
				break;
			default:
				$chatMessage = '';
		}

		if ($sendMessage)
		{
			$chatMessageFields = array(
				"MESSAGE" => $chatMessage,
				"SYSTEM" => "Y",
				"INCREMENT_COUNTER" => "N",
				"PUSH" => "N"
			);

			$availableChatData = Integration\Im\Chat\Workgroup::getChatData(array(
				'group_id' => $groupId
			));

			if (
				!empty($availableChatData)
				&& !empty($availableChatData[$groupId])
				&& intval($availableChatData[$groupId]) > 0
			)
			{
				return \CIMChat::addMessage(array_merge(
					$chatMessageFields, array(
						"TO_CHAT_ID" => $chatId
					)
				));
			}
		}
		else
		{
			return true;
		}

		return false;
	}

	private static function getChatActionList()
	{
		return array(self::CHAT_ACTION_IN, self::CHAT_ACTION_OUT);
	}

	public static function addModerators($params = array())
	{
		global $USER, $DB;

		$result = false;

		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !isset($params['user_id'])
			|| empty($params['user_id'])
		)
		{
			return $result;
		}

		$groupId = intval($params['group_id']);
		$userIdList = (
			is_array($params['user_id'])
				? $params['user_id']
				: array($params['user_id'])
		);
		$currentUserId = (
			isset($params['current_user_id'])
			&& intval($params['current_user_id']) > 0
				? intval($params['current_user_id'])
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

		$ownerRelationIdList = $memberRelationIdList = $otherRelationIdList = array();

		$resRelation = UserToGroupTable::getList(array(
			'filter' => array(
				'GROUP_ID' => $groupId,
				'@USER_ID' => $userIdList
			),
			'select' => array('ID', 'USER_ID', 'ROLE')
		));
		while($relation = $resRelation->fetch())
		{
			if ($relation['ROLE'] == UserToGroupTable::ROLE_USER)
			{
				$memberRelationIdList[$relation['USER_ID']] = $relation['ID'];
			}
			elseif ($relation['ROLE'] == UserToGroupTable::ROLE_OWNER)
			{
				$ownerRelationIdList[$relation['USER_ID']] = $relation['ID'];
			}
			else
			{
				$otherRelationIdList[$relation['USER_ID']] = $relation['ID'];
			}
		}

		if (!empty($memberRelationIdList))
		{
			\CSocNetUserToGroup::transferMember2Moderator($currentUserId, $groupId, $memberRelationIdList, \CSocNetUser::isCurrentUserModuleAdmin());
		}

		foreach($userIdList as $userId)
		{
			if (
				!array_key_exists($userId, $memberRelationIdList)
				&& !array_key_exists($userId, $ownerRelationIdList)
			)
			{
				if (array_key_exists($userId, $otherRelationIdList))
				{
					\CSocNetUserToGroup::update($otherRelationIdList[$userId], array(
						"ROLE" => UserToGroupTable::ROLE_MODERATOR,
						"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
					));
				}
				else
				{
					\CSocNetUserToGroup::add(array(
						"USER_ID" => $userId,
						"GROUP_ID" => $groupId,
						"ROLE" => UserToGroupTable::ROLE_MODERATOR,
						"=DATE_CREATE" => $DB->CurrentTimeFunction(),
						"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
						"MESSAGE" => "",
						"INITIATED_BY_TYPE" => UserToGroupTable::INITIATED_BY_GROUP,
						"INITIATED_BY_USER_ID" => $currentUserId,
						"SEND_MAIL" => "N"
					));
				}
			}
		}

		$result = true;

		return $result;
	}

}
