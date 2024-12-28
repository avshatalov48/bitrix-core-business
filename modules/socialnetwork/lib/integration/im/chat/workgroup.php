<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Im\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Integration\Im\ChatFactory;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item;
use Bitrix\Socialnetwork\Provider\GroupProvider;
use Bitrix\Socialnetwork\UserToGroupTable;

Loc::loadMessages(__FILE__);

class Workgroup
{
	const CHAT_ENTITY_TYPE = "SONET_GROUP";
	private static $staticCache = array();

	public static function getUseChat(): bool
	{
		return Option::get('socialnetwork', 'use_workgroup_chat', "Y") === "Y";
	}

	public static function getChatData($params)
	{
		$result = array();

		if (
			!array($params)
			|| !isset($params['group_id'])
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


		$groupIds = $params['group_id'];

		if (!static::getUseChat())
		{
			$provider = GroupProvider::getInstance();
			$provider->loadGroupTypes(...$groupIds);

			$groupIds = array_filter(
				$groupIds,
				static fn (int $groupId): bool => $provider->getGroupType($groupId) === Item\Workgroup\Type::Collab
			);
		}

		if (empty($groupIds))
		{
			return $result;
		}

		if (
			!isset($params['skipAvailabilityCheck'])
			|| !$params['skipAvailabilityCheck']
		)
		{
			foreach($groupIds as $key => $value)
			{
				if (!self::getGroupChatAvailable($value))
				{
					unset($groupIds[$key]);
				}
			}
		}

		$res = ChatTable::getList(array(
			'select' => Array('ID', 'ENTITY_ID'),
			'filter' => array(
				'=ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
				'@ENTITY_ID' => $groupIds
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

		$groupType = GroupProvider::getInstance()->getGroupType($groupId);
		if ($groupType === Item\Workgroup\Type::Collab)
		{
			return true;
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
		$groupId = (int)($params['group_id'] ?? 0);
		if ($groupId <= 0)
		{
			return false;
		}

		if (!Loader::includeModule('im'))
		{
			return false;
		}

		$group = GroupRegistry::getInstance()->get($groupId);
		if ($group === null)
		{
			return false;
		}

		if (!$group->isCollab() && !static::getUseChat())
		{
			return false;
		}

		$result = ChatFactory::createChat($group);

		if ($result->isSuccess())
		{
			static::$staticCache = [];
		}

		return $result->isSuccess();
	}

	public static function buildChatName($groupName, $params = []): string
	{
		$isProject = (bool)($params['project'] ?? false);
		if ($isProject) // compatibility
		{
			$type = Item\Workgroup\Type::Project;
		}
		else
		{
			$type = Item\Workgroup\Type::tryFrom((string)($params['type'] ?? ''));
		}

		return ChatFactory::getChatTitle((string)$groupName, $type);
	}

	public static function setChatManagers($params)
	{
		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !isset($params['user_id'])
			|| !Loader::includeModule('im')
		)
		{
			return false;
		}

		$userIdList = (is_array($params['user_id']) ? $params['user_id'] : array($params['user_id']));
		$groupId = intval($params['group_id']);
		$setFlag = (isset($params['set']) && $params['set']);

		$groupType = GroupProvider::getInstance()->getGroupType($groupId);

		if ($groupType !== Item\Workgroup\Type::Collab && !static::getUseChat())
		{
			return false;
		}

		$chatData = self::getChatData(array(
			'group_id' => $groupId
		));

		if (
			empty($chatData)
			|| empty($chatData[$groupId])
			|| intval($chatData[$groupId]) <= 0
		)
		{
			return false;
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
		if (
			!array($params)
			|| !isset($params['group_id'])
			|| intval($params['group_id']) <= 0
			|| !self::getUseChat()
			|| !Loader::includeModule('im')
		)
		{
			return false;
		}

		$groupId = (int)$params['group_id'];

		$groupType = GroupProvider::getInstance()->getGroupType($groupId);
		if ($groupType === Item\Workgroup\Type::Collab)
		{
			return false;
		}

		$groupName = ($params['group_name'] ?? null);
		$isProject = ($params['group_project'] ?? null);

		if ($groupName === null || $isProject === null)
		{
			$queryObject = \Bitrix\Socialnetwork\WorkgroupTable::getList([
				'filter' => ['ID' => $groupId],
				'select' => ['NAME', 'PROJECT'],
			]);
			$groupFields = $queryObject->fetch();
			$groupName = ($groupFields ? $groupFields['NAME'] : '');
			$isProject = $groupFields['PROJECT'] === 'Y';
		}

		if ($groupName === '')
		{
			return false;
		}

		$chatMessageFields = array(
			"MESSAGE" => str_replace(
				'#GROUP_NAME#',
				$groupName,
				Loc::getMessage(
					(
						$isProject
							? "SOCIALNETWORK_WORKGROUP_CHAT_UNLINKED_PROJECT"
							: "SOCIALNETWORK_WORKGROUP_CHAT_UNLINKED"
					)
				)
			),
			"SYSTEM" => "Y"
		);

		$res = ChatTable::getList(array(
			'select' => Array('ID'),
			'filter' => array(
				'=ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
				'=ENTITY_ID' => $groupId
			)
		));
		while ($chat = $res->fetch())
		{
			if (ChatTable::update($chat['ID'], array(
				'ENTITY_TYPE' => false,
				'ENTITY_ID' => false
			))->isSuccess())
			{
				return \CIMChat::addMessage(array_merge(
					$chatMessageFields, array(
						"TO_CHAT_ID" => $chat['ID']
					)
				));
			}
		}

		return true;
	}

	/**
	 * The method returns the number of group members.
	 *
	 * @param int $groupId Group id.
	 * @return int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getNumberOfMembers(int $groupId): int
	{
		$query = new Query(UserToGroupTable::getEntity());

		$records = $query
			->setSelect([
				'USER_ID',
			])
			->where('GROUP_ID', $groupId)
			->countTotal(true)
			->exec()
		;

		return $records->getCount();
	}
}
?>