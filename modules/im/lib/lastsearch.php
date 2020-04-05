<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Im;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Exception;

class LastSearch
{
	const LIMIT = 30;
	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/search/last/';

	public static function add($dialogId, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		if (!$dialogId || $dialogId == 'chat0')
		{
			return false;
		}

		if (!\Bitrix\Im\Common::isDialogId($dialogId))
		{
			return false;
		}

		if (\Bitrix\Im\Common::isChatId($dialogId))
		{
			$chatId = substr($dialogId, 4);
			$relations = Chat::getRelation($chatId);
			if (!$relations[$userId])
			{
				return false;
			}

			$relationId = $relations[$userId]['ID'];
		}
		else
		{
			$relations = \Bitrix\Im\Dialog::getRelation($userId, $dialogId);
			if (!$relations[$userId])
			{
				return false;
			}

			$chatId = $relations[$userId]['CHAT_ID'];
			$relationId = $relations[$userId]['ID'];
		}

		$orm = \Bitrix\Im\Model\LastSearchTable::getList(Array(
			'filter' => Array('USER_ID' => $userId, 'DIALOG_ID' => $dialogId),
			'order' => Array('ID' => 'DESC')
		));
		if ($orm->fetch())
		{
			return true;
		}

		$result = \Bitrix\Im\Model\LastSearchTable::add(Array(
			'USER_ID' => $userId,
			'DIALOG_ID' => $dialogId,
			'ITEM_CID' => $chatId,
			'ITEM_RID' => $relationId,
		));

		if (!$result->isSuccess())
			return false;

		$count = 0;
		$delete = Array();

		$orm = \Bitrix\Im\Model\LastSearchTable::getList(Array(
			'filter' => Array('USER_ID' => $userId),
			'order' => Array('ID' => 'DESC')
		));
		while ($row = $orm->fetch())
		{
			$count++;

			if ($count > self::LIMIT)
			{
				$delete[] = $row['ID'];
			}
		}

		foreach ($delete as $id)
		{
			\Bitrix\Im\Model\LastSearchTable::delete($id);
		}

		self::clearCache($userId);

		return $result->getId();
	}

	public static function delete($dialogId, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		if (!$dialogId || $dialogId == 'chat0')
		{
			return false;
		}

		if (!\Bitrix\Im\Common::isDialogId($dialogId))
		{
			return false;
		}

		$orm = \Bitrix\Im\Model\LastSearchTable::getList(Array(
			'filter' => Array(
				'USER_ID' => $userId,
				'DIALOG_ID' => $dialogId
			)
		));
		$row = $orm->fetch();
		if (!$row)
		{
			return false;
		}

		\Bitrix\Im\Model\LastSearchTable::delete($row['ID']);

		self::clearCache($userId);

		return true;
	}

	public static function get($userId = null, $options = array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$result = array();

		$cacheId = 'list_v2_'.$userId.'_'.Color::isEnabled();
		$cachePath = self::CACHE_PATH.\Bitrix\Im\Common::getCacheUserPostfix($userId);

		$cache = \Bitrix\Main\Application::getInstance()->getCache();
		$taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
		if($cache->initCache(self::CACHE_TTL, $cacheId, $cachePath))
		{
			$result = $cache->getVars();
		}
		else
		{
			$generalChatId = \CIMChat::GetGeneralChatId();

			$select = Array(
				'*',
				'RELATION_USER_ID' => 'RELATION.USER_ID',
				'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
				'CHAT_TITLE' => 'CHAT.TITLE',
				'CHAT_TYPE' => 'CHAT.TYPE',
				'CHAT_AVATAR' => 'CHAT.AVATAR',
				'CHAT_LAST_MESSAGE_STATUS' => 'CHAT.LAST_MESSAGE_STATUS',

				'CHAT_AUTHOR_ID' => 'CHAT.AUTHOR_ID',
				'CHAT_EXTRANET' => 'CHAT.EXTRANET',
				'CHAT_COLOR' => 'CHAT.COLOR',
				'CHAT_ENTITY_TYPE' => 'CHAT.ENTITY_TYPE',
				'CHAT_ENTITY_ID' => 'CHAT.ENTITY_ID',
				'CHAT_ENTITY_DATA_1' => 'CHAT.ENTITY_DATA_1',
				'CHAT_ENTITY_DATA_2' => 'CHAT.ENTITY_DATA_2',
				'CHAT_ENTITY_DATA_3' => 'CHAT.ENTITY_DATA_3',
				'CHAT_DATE_CREATE' => 'CHAT.DATE_CREATE',
			);

			$orm = \Bitrix\Im\Model\LastSearchTable::getList(Array(
				'select' => $select,
				'filter' => Array('=USER_ID' => $userId),
				'order' => Array('ID' => 'DESC')
			));
			while ($row = $orm->fetch())
			{
				$isUser = strpos($row['DIALOG_ID'], 'chat') !== 0;
				$id = $row['DIALOG_ID'];

				$item = Array(
					'ID' => $isUser? (int)$id: $id,
					'TYPE' => $isUser? 'user': 'chat',
					'AVATAR' => Array(),
					'TITLE' => Array(),
				);

				if ($isUser)
				{
					$item['USER'] = Array(
						'ID' => (int)$row['DIALOG_ID'],
					);
				}
				else
				{
					$avatar = \CIMChat::GetAvatarImage($row['CHAT_AVATAR'], 100, false);
					$color = strlen($row['CHAT_COLOR']) > 0? Color::getColor($row['CHAT_COLOR']): Color::getColorByNumber($row['ITEM_ID']);
					if ($row["CHAT_TYPE"] == IM_MESSAGE_PRIVATE)
					{
						$chatType = 'private';
					}
					else if ($row["CHAT_ENTITY_TYPE"] == 'CALL')
					{
						$chatType = 'call';
					}
					else if ($row["CHAT_ENTITY_TYPE"] == 'LINES')
					{
						$chatType = 'lines';
					}
					else if ($row["CHAT_ENTITY_TYPE"] == 'LIVECHAT')
					{
						$chatType = 'livechat';
					}
					else
					{
						if ($generalChatId == $row['ITEM_ID'])
						{
							$row["CHAT_ENTITY_TYPE"] = 'GENERAL';
						}
						$chatType = $row["CHAT_TYPE"] == IM_MESSAGE_OPEN? 'open': 'chat';
					}

					$muteList = Array();
					if ($row['RELATION_NOTIFY_BLOCK'] == 'Y')
					{
						$muteList = Array($row['RELATION_USER_ID'] => true);
					}

					$item['AVATAR'] = Array(
						'URL' => $avatar,
						'COLOR' => $color
					);
					$item['TITLE'] = $row['CHAT_TITLE'];
					$item['CHAT'] = Array(
						'ID' => (int)$row['ITEM_CID'],
						'NAME' => $row['CHAT_TITLE'],
						'OWNER' => (int)$row['CHAT_AUTHOR_ID'],
						'EXTRANET' => $row['CHAT_EXTRANET'] == 'Y',
						'AVATAR' => $avatar,
						'COLOR' => $color,
						'TYPE' => $chatType,
						'ENTITY_TYPE' => (string)$row['CHAT_ENTITY_TYPE'],
						'ENTITY_ID' => (string)$row['CHAT_ENTITY_ID'],
						'ENTITY_DATA_1' => (string)$row['CHAT_ENTITY_DATA_1'],
						'ENTITY_DATA_2' => (string)$row['CHAT_ENTITY_DATA_2'],
						'ENTITY_DATA_3' => (string)$row['CHAT_ENTITY_DATA_3'],
						'MUTE_LIST' => $muteList,
						'DATE_CREATE' => $row['CHAT_DATE_CREATE'],
						'MESSAGE_TYPE' => $row["CHAT_TYPE"],
					);
				}

				$result[$id] = $item;
			}

			$taggedCache->startTagCache($cachePath);
			$taggedCache->registerTag("USER_NAME");
			$taggedCache->endTagCache();

			$cache->startDataCache();
			$cache->endDataCache($result);
		}

		foreach ($result as $id => $item)
		{
			if ($options['SKIP_OPENLINES'] == 'Y')
			{
				if ($item['TYPE'] == 'chat' && $item['CHAT']['TYPE'] == 'lines')
				{
					unset($result[$id]);
					continue;
				}
			}
			if ($options['SKIP_CHAT'] == 'Y')
			{
				if ($item['TYPE'] == 'chat' && $item['CHAT']['TYPE'] != 'lines')
				{
					unset($result[$id]);
					continue;
				}
			}
			if ($options['SKIP_DIALOG'] == 'Y')
			{
				if ($item['TYPE'] == 'user')
				{
					unset($result[$id]);
					continue;
				}
			}

			if ($item['USER']['ID'] > 0)
			{
				$user = User::getInstance($item['USER']['ID'])->getArray();
				if (!$user)
				{
					$user = Array('ID' => 0);
				}
				else if ($item['TYPE'] == 'user')
				{
					$item['AVATAR'] = Array(
						'URL' => $user['AVATAR'],
						'COLOR' => $user['COLOR']
					);
					$item['TITLE'] = $user['NAME'];
				}

				$item['USER'] = $user;

				$result[$id] = $item;
			}
		}

		$result = array_values($result);

		if ($options['JSON'])
		{
			foreach ($result as $index => $item)
			{
				foreach ($item as $key => $value)
				{
					if ($value instanceof \Bitrix\Main\Type\DateTime)
					{
						$item[$key] = date('c', $value->getTimestamp());
					}
					else if (is_array($value))
					{
						foreach ($value as $subKey => $subValue)
						{
							if ($subValue instanceof \Bitrix\Main\Type\DateTime)
							{
								$value[$subKey] = date('c', $subValue->getTimestamp());
							}
							else if (is_string($subValue) && $subValue && in_array($subKey, Array('URL', 'AVATAR')) && strpos($subValue, 'http') !== 0)
							{
								$value[$subKey] = \Bitrix\Im\Common::getPublicDomain().$subValue;
							}
							else if (is_array($subValue))
							{
								$value[$subKey] = array_change_key_case($subValue, CASE_LOWER);
							}
						}
						$item[$key] = array_change_key_case($value, CASE_LOWER);
					}
				}
				$result[$index] = array_change_key_case($item, CASE_LOWER);
			}
		}

		return $result;
	}


	public static function clearCache($userId = null)
	{
		$cache = Application::getInstance()->getCache();
		$cache->cleanDir(self::CACHE_PATH.($userId? Common::getCacheUserPostfix($userId): ''));
	}
}