<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Im\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Item;
use Bitrix\Socialnetwork\UserToGroupTable;

Loc::loadMessages(__FILE__);

class Workgroup
{
	const CHAT_ENTITY_TYPE = "SONET_GROUP";
	private static $staticCache = array();

	public static function getUseChat()
	{
		return (Option::get('socialnetwork', 'use_workgroup_chat', "Y") == "Y");
	}

	public static function getChatData($params)
	{
		$result = array();

		if (
			!array($params)
			|| !isset($params['group_id'])
			|| !self::getUseChat()
			|| !Loader::includeModule('im')
		)
		{
			return $result;
		}

		if (!is_array($params['group_id']))
		{
			$params['group_id'] = array($params['group_id']);
		}

		$cacheKey = serialize($params);
		if (isset(self::$staticCache[$cacheKey]))
		{
			return self::$staticCache[$cacheKey];
		}

		$params['group_id'] = array_values(array_unique(array_filter(array_map(
			function($groupId) {
				return (is_array($groupId) || intval($groupId) <= 0 ? false : intval($groupId));
			},
			$params['group_id']
		))));

		if (
			!isset($params['skipAvailabilityCheck'])
			|| !$params['skipAvailabilityCheck']
		)
		{
			foreach($params['group_id'] as $key => $value)
			{
				if (!self::getGroupChatAvailable($value))
				{
					unset($params['group_id'][$key]);
				}
			}
		}

		$res = ChatTable::getList(array(
			'select' => Array('ID', 'ENTITY_ID'),
			'filter' => array(
				'=ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
				'@ENTITY_ID' => $params['group_id']
			)
		));
		while ($chat = $res->fetch())
		{
			$result[$chat['ENTITY_ID']] = $chat['ID'];
		}

		self::$staticCache[$cacheKey] = $result;

		return $result;
	}

	public static function getGroupChatAvailable($groupId = false)
	{
		$result = false;

		if (intval($groupId) <= 0)
		{
			return $result;
		}

		$activeFeatures = \CSocNetFeatures::getActiveFeatures(SONET_ENTITY_GROUP, $groupId);
		if (
			is_array($activeFeatures)
			&& in_array('chat', $activeFeatures)
		)
		{
			$result = true;
		}

		return $result;
	}

	public static function createChat($params)
	{
		$result = false;

		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !self::getUseChat()
			|| !Loader::includeModule('im')
		)
		{
			return $result;
		}

		$groupItem = Item\Workgroup::getById($params['group_id']);
		if (!$groupItem)
		{
			return $result;
		}

		$groupFields = $groupItem->getFields();
		$project = $groupItem->isProject();

		$userIdList = array();

		$res = UserToGroupTable::getList(array(
			'filter' => array(
				'GROUP_ID' => $params['group_id'],
				'@ROLE' => UserToGroupTable::getRolesMember()
			),
			'select' => array('USER_ID')
		));

		while($relation = $res->fetch())
		{
			$userIdList[] = intval($relation['USER_ID']);
		}

		if (empty($userIdList))
		{
			$userIdList = array($groupFields['OWNER_ID']);
		}

		$chatFields = array(
			'TITLE' => self::buildChatName($groupFields['NAME'], array(
				'project' => $project
			)),
			'TYPE' => IM_MESSAGE_CHAT,
			'ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
			'ENTITY_ID' => intval($params['group_id']),
			'SKIP_ADD_MESSAGE' => 'Y',
			'AUTHOR_ID' => $groupFields['OWNER_ID'],
			'USERS' => $userIdList
		);

		$groupItem = Item\Workgroup::getById($params['group_id'], false);
		if ($groupItem)
		{
			$groupFields = $groupItem->getFields();
			if (!empty($groupFields['IMAGE_ID']))
			{
				$chatFields['AVATAR_ID'] = $groupFields['IMAGE_ID'];
			}
		}

		$chat = new \CIMChat(0);
		$result = $chat->add($chatFields);

		if ($result)
		{
			self::$staticCache = array();
		}

		return $result;
	}

	public static function buildChatName($groupName, $params = array())
	{
		$project = (
			is_array($params)
			&& isset($params['project'])
			&& $params['project']
		);
		return Loc::getMessage(($project ? "SOCIALNETWORK_WORKGROUP_CHAT_TITLE_PROJECT" : "SOCIALNETWORK_WORKGROUP_CHAT_TITLE"), array(
			"#GROUP_NAME#" => $groupName
		));
	}

	public static function setChatManagers($params)
	{
		$result = false;

		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !isset($params['user_id'])
			|| !self::getUseChat()
			|| !Loader::includeModule('im')
		)
		{
			return $result;
		}

		$userIdList = (is_array($params['user_id']) ? $params['user_id'] : array($params['user_id']));
		$groupId = intval($params['group_id']);
		$setFlag = (isset($params['set']) && $params['set']);

		$chatData = self::getChatData(array(
			'group_id' => $groupId
		));

		if (
			empty($chatData)
			|| empty($chatData[$groupId])
			|| intval($chatData[$groupId]) <= 0
		)
		{
			return $result;
		}

		$chatId = $chatData[$groupId];

		$chat = new \CIMChat();

		$managersInfo = array();
		foreach($userIdList as $userId)
		{
			$managersInfo[$userId] = $setFlag;
		}

		return $chat->setManagers($chatId, $managersInfo, false);
	}

	public static function unlinkChat($params)
	{
		$result = false;

		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !self::getUseChat()
			|| !Loader::includeModule('im')
		)
		{
			return $result;
		}

		$groupItem = Item\Workgroup::getById($params['group_id']);
		if (!$groupItem)
		{
			return $result;
		}

		$groupFields = $groupItem->getFields();

		$chatMessageFields = array(
			"MESSAGE" => str_replace('#GROUP_NAME#', $groupFields['NAME'], Loc::getMessage($groupItem->isProject() ? "SOCIALNETWORK_WORKGROUP_CHAT_UNLINKED_PROJECT" : "SOCIALNETWORK_WORKGROUP_CHAT_UNLINKED")),
			"SYSTEM" => "Y"
		);

		$res = ChatTable::getList(array(
			'select' => Array('ID'),
			'filter' => array(
				'=ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
				'=ENTITY_ID' => $params['group_id']
			)
		));
		while ($chat = $res->fetch())
		{
			if (ChatTable::update($chat['ID'], array(
				'ENTITY_TYPE' => false,
				'ENTITY_ID' => false
			)))
			{
				return \CIMChat::addMessage(array_merge(
					$chatMessageFields, array(
						"TO_CHAT_ID" => $chat['ID']
					)
				));
			}
		}

		$result = true;

		return $result;
	}
}
?>