<?php
namespace Bitrix\Im;

use Bitrix\Im\Model\BlockUserTable;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Chat
{
	const TYPE_SYSTEM = 'S';
	const TYPE_PRIVATE = 'P';
	const TYPE_OPEN = 'O';
	const TYPE_THREAD = 'T';
	const TYPE_GROUP = 'C';
	const TYPE_CHANNEL = 'N';
	const TYPE_OPEN_LINE = 'L';

	const STATUS_UNREAD = 0;
	const STATUS_NOTIFY = 1;
	const STATUS_READ = 2;

	const LIMIT_SEND_EVENT = 30;

	const FILTER_LIMIT = 50;

	public static function getTypes()
	{
		return Array(self::TYPE_GROUP, self::TYPE_OPEN_LINE, self::TYPE_OPEN, self::TYPE_THREAD);
	}

	public static function getType($chatData)
	{
		$messageType = $chatData["TYPE"] ?? $chatData["CHAT_TYPE"] ?? '';
		$entityType = $chatData["ENTITY_TYPE"] ?? $chatData["CHAT_ENTITY_TYPE"] ?? '';

		$messageType = trim($messageType);
		$entityType = trim($entityType);

		$chatId = null;
		if (isset($chatData['ID']))
		{
			$chatId = (int)$chatData['ID'];
		}
		else if (isset($chatData['CHAT_ID']))
		{
			$chatId = (int)$chatData['CHAT_ID'];
		}

		if ($messageType == IM_MESSAGE_PRIVATE)
		{
			$result = 'private';
		}
		else if (!empty($entityType))
		{
			// convert to camelCase
			$result = str_replace('_', '', lcfirst(ucwords(mb_strtolower($entityType), '_')));
		}
		else if ($chatId && $chatId === (int)\CIMChat::GetGeneralChatId())
		{
			$result = 'general';
		}
		else
		{
			$result = $messageType == IM_MESSAGE_OPEN? 'open': 'chat';
		}

		return htmlspecialcharsbx($result);
	}

	public static function getRelation($chatId, $params = [])
	{
		$chatId = intval($chatId);
		if ($chatId <= 0)
		{
			return false;
		}

		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$selectFields = '';
		if (isset($params['SELECT']))
		{
			$params['SELECT'][] = 'ID';
			$params['SELECT'][] = 'USER_ID';
			$map = \Bitrix\Im\Model\RelationTable::getMap();
			foreach ($params['SELECT'] as $key => $value)
			{
				if (is_int($key) && isset($map[$value]))
				{
					$selectFields .= "R.{$value}, ";
					unset($map[$value]);
				}
				else if (!is_int($key) && isset($map[$key]))
				{
					$value = (string)$value;
					$selectFields .= "R.{$key} '{$connection->getSqlHelper()->forSql($value)}', ";
					unset($map[$value]);
				}
			}
		}
		if (!$selectFields)
		{
			$selectFields = 'R.*, ';
		}

		$withUserFields = false;
		if (isset($params['USER_DATA']) && $params['USER_DATA'] == 'Y')
		{
			$withUserFields = true;
			$list = Array('ACTIVE', 'EXTERNAL_AUTH_ID');
			foreach ($list as $key)
			{
				$selectFields .= "U.{$key} USER_DATA_{$key}, ";
			}
		}
		$skipUsers = false;
		$skipUserInactiveSql = '';
		if (isset($params['SKIP_INACTIVE_USER']) && $params['SKIP_INACTIVE_USER'] === 'Y')
		{
			$skipUsers = true;
			$skipUserInactiveSql = "AND U.ACTIVE = 'Y'";
		}

		$skipUserTypes = $params['SKIP_USER_TYPES'] ?? [];
		if (isset($params['SKIP_CONNECTOR']) && $params['SKIP_CONNECTOR'] === 'Y')
		{
			$skipUserTypes[] = 'imconnector';
		}

		$skipUserTypesSql = '';
		if (!empty($skipUserTypes))
		{
			$skipUsers = true;
			if (count($skipUserTypes) === 1)
			{
				$skipUserTypesSql = "AND (U.EXTERNAL_AUTH_ID != '".$connection->getSqlHelper()->forSql($skipUserTypes[0])."' OR U.EXTERNAL_AUTH_ID IS NULL)";
			}
			else
			{
				$skipUserTypes = array_map(function($type) use ($connection) {
					return $connection->getSqlHelper()->forSql($type);
				}, $skipUserTypes);

				$skipUserTypesSql = "AND (U.EXTERNAL_AUTH_ID NOT IN ('".implode("','", $skipUserTypes)."') OR U.EXTERNAL_AUTH_ID IS NULL)";
			}
		}

		$whereFields = '';
		if (isset($params['FILTER']))
		{
			$map = \Bitrix\Im\Model\RelationTable::getMap();
			foreach ($params['FILTER'] as $key => $value)
			{
				if (!isset($map[$key]))
				{
					continue;
				}

				if (is_int($value))
				{
				}
				else if (is_bool($value))
				{
					$value = $value? "'Y'": "'N'";
				}
				else if (is_string($value))
				{
					$value = "'{$connection->getSqlHelper()->forSql($value)}'";
				}
				else
				{
					continue;
				}

				$whereFields .= " AND R.{$key} = {$value}";
			}
		}

		/*$skipUnmodifiedRecords = false;
		if (isset($params['SKIP_RELATION_WITH_UNMODIFIED_COUNTERS']) && $params['SKIP_RELATION_WITH_UNMODIFIED_COUNTERS'] == 'Y')
		{
			$skipUnmodifiedRecords = true;
		}*/

		/*$sqlSelectCounter = 'R.LAST_ID, R.COUNTER, R.COUNTER PREVIOUS_COUNTER';

		$customCounter = false;
		$customMaxId = 0;
		$customMinId = 0;
		$counters = [];

		if (isset($params['REAL_COUNTERS']) && $params['REAL_COUNTERS'] != 'N' || $skipUnmodifiedRecords)
		{
			if (is_array($params['REAL_COUNTERS']) && isset($params['REAL_COUNTERS']['LAST_ID']))
			{
				$sqlSelectCounter = "R.COUNTER PREVIOUS_COUNTER, (
					SELECT COUNT(1) FROM b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.ID > ".intval($params['REAL_COUNTERS']['LAST_ID'])."
				) COUNTER";
			}
			else
			{
				$customCounter = true;
				$query = $connection->query("
					SELECT ID FROM b_im_message
					WHERE CHAT_ID = {$chatId}
					ORDER BY DATE_CREATE DESC, ID DESC
					LIMIT 100
				");
				$messageCounter = 0;
				while ($row = $query->fetch())
				{
					if (!$customMaxId)
					{
						$customMaxId = $row['ID'];
					}
					$counters[$row['ID']] = $messageCounter++;
					$customMinId = $row['ID'];
				}
			}
		}*/

		$limit = '';
		if (isset($params['LIMIT']))
		{
			$limit = 'LIMIT '.(int)$params['LIMIT'];
		}

		$offset = '';
		if (isset($params['OFFSET']))
		{
			$offset = 'OFFSET '.(int)$params['OFFSET'];
		}

		$orderField = 'R.ID';

		if (isset($params['LAST_USER_ID']) && (int)$params['LAST_USER_ID'] >= 0)
		{
			$lastUserId = (int)$params['LAST_USER_ID'];
			$whereFields .= " AND R.USER_ID > {$lastUserId}";
			$orderField = 'R.USER_ID';
		}

		$selectFields = rtrim($selectFields, ', ');
		$sql = "
			SELECT {$selectFields}
			FROM b_im_relation R
			".($withUserFields && !$skipUsers? "LEFT JOIN b_user U ON R.USER_ID = U.ID": "")."
			".($skipUsers? "INNER JOIN b_user U ON R.USER_ID = U.ID {$skipUserInactiveSql} {$skipUserTypesSql}": "")."
			WHERE R.CHAT_ID = {$chatId} {$whereFields} 
			ORDER BY {$orderField} ASC
			{$limit} {$offset}
		";
		$relations = array();
		$query = $connection->query($sql);
		while ($row = $query->fetch())
		{
			/*if ($customCounter)
			{
				if (isset($counters[$row['LAST_ID']]))
				{
					$row['COUNTER'] = $counters[$row['LAST_ID']];
				}
				else if ($row['LAST_ID'] < $customMinId)
				{
					$row['COUNTER'] = count($counters);
				}
				else if ($row['LAST_ID'] > $customMaxId)
				{
					$row['COUNTER'] = 0;
				}
			}
			else
			{
				$row['COUNTER'] = $row['COUNTER'] > 99? 100: (int)$row['COUNTER'];
			}

			$row['PREVIOUS_COUNTER'] = (int)$row['PREVIOUS_COUNTER'];

			if ($skipUnmodifiedRecords && $row['COUNTER'] == $row['PREVIOUS_COUNTER'])
			{
				continue;
			}*/

			foreach ($row as $key => $value)
			{
				if (mb_strpos($key, 'USER_DATA_') === 0)
				{
					$row['USER_DATA'][mb_substr($key, 10)] = $value;
					unset($row[$key]);
				}
			}

			$relations[$row['USER_ID']] = $row;
		}

		// region New counter
		// todo: select counter only if it's need
		if (!isset($params['WITHOUT_COUNTERS']) || $params['WITHOUT_COUNTERS'] !== 'Y')
		{
			$userIds = array_keys($relations);
			$readService = new ReadService();
			$counters = $readService->getCounterService()->getByChatForEachUsers($chatId, $userIds);
			$lastIdInChat = $readService->getViewedService()->getLastMessageIdInChat($chatId) ?? 0;
			$lastReads = $readService->getViewedService()->getDateViewedByMessageIdForEachUser($lastIdInChat, $userIds);
			foreach ($relations as $userId => $relation)
			{
				$counter = $counters[$userId] ?? 0;
				$counter = $counter > 99 ? 100 : $counter;
				$relations[$userId]['COUNTER'] = $counter;
				$relations[$userId]['LAST_READ'] = $lastReads[$userId] ?? null;
			}
		}
		// endregion

		return $relations;
	}

	public static function mute($chatId, $action, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$chatId = intval($chatId);
		if (!$chatId)
		{
			return false;
		}

		$action = $action === true? 'Y': 'N';

		(new CounterService())->withContextUser($userId)->updateIsMuted($chatId, $action);

		$relation = self::getRelation($chatId, Array(
			'SELECT' => Array('ID', 'MESSAGE_TYPE', 'NOTIFY_BLOCK', 'COUNTER'),
			'FILTER' => Array(
				'USER_ID' => $userId
			),
		));
		if (!$relation)
		{
			return false;
		}

		if ($relation[$userId]['NOTIFY_BLOCK'] == $action)
		{
			return true;
		}

		\Bitrix\Im\Model\RelationTable::update($relation[$userId]['ID'], array('NOTIFY_BLOCK' => $action));

		Recent::clearCache($userId);
		//Counter::clearCache($userId);

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			$element = \Bitrix\Im\Model\RecentTable::getList([
				'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'UNREAD'],
				'filter' => [
					'=USER_ID' => $userId,
					'=ITEM_TYPE' => $relation[$userId]['MESSAGE_TYPE'],
					'=ITEM_ID' => $chatId
				]
			])->fetch();

			$counter = $relation[$userId]['COUNTER'];

			\Bitrix\Pull\Event::add($userId, Array(
				'module_id' => 'im',
				'command' => 'chatMuteNotify',
				'params' => Array(
					'chatId' => $chatId,
					'dialogId' => 'chat'.$chatId,
					'muted' => $action == 'Y',
					'mute' => $action == 'Y', // TODO remove this later
					'counter' => $counter,
					'lines' => $element['ITEM_TYPE'] === self::TYPE_OPEN_LINE,
					'unread' => ($element['UNREAD'] ?? 'N') === 'Y',
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		$chat = \Bitrix\Im\Chat::getById($chatId);
		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("im", "OnAfterChatMuteNotify") as $event)
		{
			ExecuteModuleEventEx($event, [[
				'CHAT_ID' => $chatId,
				'USER_ID' => $userId,
				'MUTE' => $action == 'Y',
				'CHAT' => $chat,
			]]);
		}

		return true;
	}

	public static function getMessageCount($chatId, $userId = null)
	{
		$chatId = intval($chatId);
		if (!$chatId)
		{
			return false;
		}

		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$relationData = \Bitrix\Im\Model\RelationTable::getList(Array(
			'select' => Array('START_ID'),
			'filter' => Array('=CHAT_ID' => $chatId, '=USER_ID' => $userId)
		))->fetch();

		if (!$relationData || $relationData['START_ID'] == 0)
		{
			$counter = \Bitrix\Im\Model\MessageTable::getList(array(
				'filter' => Array('CHAT_ID' => $chatId),
				'select' => array("CNT" => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
			))->fetch();
		}
		else
		{
			$counter = \Bitrix\Im\Model\MessageTable::getList(array(
				'filter' => Array('CHAT_ID' => $chatId, '>=ID' => $relationData['START_ID']),
				'select' => array("CNT" => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
			))->fetch();
		}

		return $counter && $counter['CNT'] > 0? intval($counter['CNT']): 0;
	}

	public static function hasAccess($chatId)
	{
		$chatId = intval($chatId);
		if (!$chatId)
		{
			return false;
		}

		return \Bitrix\Im\Dialog::hasAccess('chat'.$chatId);
	}

	/**
	 * @param $chatId
	 * @param null $userId
	 * @param array $options
	 * @return array|bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getMessages($chatId, $userId = null, $options = Array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
			'select' => Array(
				'CHAT_ID' => 'ID',
				'CHAT_TYPE' => 'TYPE',
				'CHAT_ENTITY_TYPE' => 'ENTITY_TYPE',
				'CHAT_ENTITY_ID' => 'ENTITY_ID',
				'RELATION_USER_ID' => 'RELATION.USER_ID',
				'RELATION_START_ID' => 'RELATION.START_ID',
				//'RELATION_UNREAD_ID' => 'RELATION.UNREAD_ID',
				'RELATION_LAST_ID' => 'RELATION.LAST_ID',
				//'RELATION_STATUS' => 'RELATION.STATUS',
				//'RELATION_COUNTER' => 'RELATION.COUNTER'
			),
			'filter' => Array('=ID' => $chatId),
			'runtime' => Array(
				new \Bitrix\Main\Entity\ReferenceField(
					'RELATION',
					'\Bitrix\Im\Model\RelationTable',
					array(
						"=ref.CHAT_ID" => "this.ID",
						"=ref.USER_ID" => new \Bitrix\Main\DB\SqlExpression('?', $userId)
					),
					array("join_type"=>"LEFT")
				)
			)
		))->fetch();
		if (!$chatData)
		{
			return false;
		}

		$readService = new ReadService($userId);

		$chatData['RELATION_UNREAD_ID'] = $readService->getCounterService()->getIdFirstUnreadMessage($chatId) ?? 0;
		$chatData['RELATION_COUNTER'] = $readService->getCounterService()->getByChat($chatId);
		$chatData['RELATION_START_ID'] = (int)$chatData['RELATION_START_ID'];

		if (isset($options['LIMIT']))
		{
			$options['LIMIT'] = intval($options['LIMIT']);
			$limit = $options['LIMIT'] >= 100? 100: $options['LIMIT'];
		}
		else
		{
			$limit = 50;
		}

		$filter = Array(
			'=CHAT_ID' => $chatId
		);

		$fileSort = 'ASC';
		$startFromUnread = false;
		if (
			!isset($options['LAST_ID'])
			&& !isset($options['FIRST_ID'])
			//&& $chatData['RELATION_STATUS'] != \Bitrix\Im\Chat::STATUS_READ
			&& $chatData['RELATION_COUNTER'] > 0
		)
		{
			if ($chatData['RELATION_COUNTER'] > $limit)
			{
				$startFromUnread = true;
				$options['FIRST_ID'] = $chatData['RELATION_LAST_ID'];
			}
			else
			{
				$limit += $chatData['RELATION_COUNTER'];
			}
		}

		if (isset($options['FIRST_ID']))
		{
			$orderId = [];
			$orderResult = [];

			if ($chatData['RELATION_START_ID'] > 0 && intval($options['FIRST_ID']) < $chatData['RELATION_START_ID'])
			{
				$filter['>=ID'] = $chatData['RELATION_START_ID'];
			}
			else
			{
				if (intval($options['FIRST_ID']) > 0)
				{
					$filter['>ID'] = $options['FIRST_ID'];
				}
			}
		}
		else
		{
			$fileSort = 'DESC';
			$orderId = Array('CHAT_ID' => 'ASC', 'ID' => 'DESC');
			$orderResult = Array('ID' => 'DESC');

			if ($chatData['RELATION_START_ID'] > 0)
			{
				$filter['>=ID'] = $chatData['RELATION_START_ID'];
			}

			if (isset($options['LAST_ID']) && intval($options['LAST_ID']) > 0)
			{
				$filter['<ID'] = intval($options['LAST_ID']);
			}
		}

		$orm = \Bitrix\Im\Model\MessageTable::getList(array(
			'select' => ['ID'],
			'filter' => $filter,
			'order' => $orderId,
			'limit' => $limit
		));
		$ids = array_map(fn ($item) => $item['ID'], $orm->fetchAll());
		if (empty($ids))
		{
			$result = [
				'CHAT_ID' => (int)$chatId,
				'MESSAGES' => [],
				'USERS' => [],
				'FILES' => [],
			];
			if ($options['JSON'])
			{
				$result = array_change_key_case($result, CASE_LOWER);
			}
			return $result;
		}

		$orm = \Bitrix\Im\Model\MessageTable::getList(array(
			'select' => [
				'ID', 'AUTHOR_ID', 'DATE_CREATE', 'NOTIFY_EVENT', 'MESSAGE',
				'USER_LAST_ACTIVITY_DATE' => 'AUTHOR.LAST_ACTIVITY_DATE',
				'USER_IDLE' => 'STATUS.IDLE',
				'USER_MOBILE_LAST_DATE' => 'STATUS.MOBILE_LAST_DATE',
				'USER_DESKTOP_LAST_DATE' => 'STATUS.DESKTOP_LAST_DATE',
				'MESSAGE_UUID' => 'UUID.UUID',
			],
			'filter' => ['=ID' => $ids],
			'order' => $orderResult,
		));

		$users = Array();

		$userOptions = ['SKIP_ONLINE' => 'Y'];
		if ($options['JSON'] == 'Y')
		{
			$userOptions['JSON'] = 'Y';
		}
		if ($chatData['CHAT_ENTITY_TYPE'] == 'LIVECHAT')
		{
			[$lineId] = explode('|', $chatData['CHAT_ENTITY_ID']);
			$userOptions['LIVECHAT'] = $lineId;
			$userOptions['USER_CODE'] = 'livechat|' . $lineId . '|' . $chatData['CHAT_ID'] . '|' . $userId;
		}

		$messages = Array();
		while($message = $orm->fetch())
		{
			if ($message['NOTIFY_EVENT'] == 'private_system')
			{
				$message['AUTHOR_ID'] = 0;
			}

			if ($options['USER_TAG_SPREAD'] === 'Y')
			{
				$message['MESSAGE'] = preg_replace_callback("/\[USER=([0-9]{1,})\]\[\/USER\]/i", Array('\Bitrix\Im\Text', 'modifyShortUserTag'), $message['MESSAGE']);
			}

			$messages[$message['ID']] = Array(
				'ID' => (int)$message['ID'],
				'CHAT_ID' => (int)$chatId,
				'AUTHOR_ID' => (int)$message['AUTHOR_ID'],
				'DATE' => $message['DATE_CREATE'],
				'TEXT' => (string)\Bitrix\Im\Text::parse($message['MESSAGE']),
				'UNREAD' => $chatData['RELATION_USER_ID'] > 0 && $chatData['RELATION_LAST_ID'] < $message['ID'],
				'UUID' => $message['MESSAGE_UUID'],
			);
			$messages[$message['ID']]['REPLACES'] = \Bitrix\Im\Text::getReplaceMap($messages[$message['ID']]['TEXT']);
			if ($message['AUTHOR_ID'] && !isset($users[$message['AUTHOR_ID']]))
			{
				$user = User::getInstance($message['AUTHOR_ID'])->getArray($userOptions);
				$user['last_activity_date'] = $message['USER_LAST_ACTIVITY_DATE']? date('c', $message['USER_LAST_ACTIVITY_DATE']->getTimestamp()): false;
				$user['desktop_last_date'] = $message['USER_DESKTOP_LAST_DATE']? date('c', $message['USER_DESKTOP_LAST_DATE']->getTimestamp()): false;
				$user['mobile_last_date'] = $message['USER_MOBILE_LAST_DATE']? date('c', $message['USER_MOBILE_LAST_DATE']->getTimestamp()): false;
				$user['idle'] = $message['USER_IDLE']?: false;

				$users[$message['AUTHOR_ID']] = $user;
			}
			if ($options['CONVERT_TEXT'])
			{
				$messages[$message['ID']]['TEXT_CONVERTED'] = \Bitrix\Im\Text::parseLegacyFormat($message['MESSAGE']);
			}
		}

		$params = \CIMMessageParam::Get(array_keys($messages));

		$fileIds = Array();
		foreach ($params as $messageId => $param)
		{
			$messages[$messageId]['PARAMS'] = empty($param)? []: $param;

			if (
				empty($messages[$messageId]['TEXT'])
				&& !isset($param['FILE_ID'])
				&& !isset($param['KEYBOARD'])
				&& !isset($param['ATTACH'])
			)
			{
				$messages[$messageId]['TEXT'] = Loc::getMessage('IM_CHAT_MESSAGE_DELETED');
				$messages[$messageId]['PARAMS']['IS_DELETED'] = 'Y';
			}

			if (isset($param['FILE_ID']))
			{
				foreach ($param['FILE_ID'] as $fileId)
				{
					$fileIds[$fileId] = $fileId;
				}
			}

			if (is_array($param['CHAT_USER']) > 0)
			{
				foreach ($param['CHAT_USER'] as $paramsUserId)
				{
					$users[$paramsUserId] = User::getInstance($paramsUserId)->getArray($userOptions);
				}
			}
		}

		$messages = \CIMMessageLink::prepareShow($messages, $params);

		$files = \CIMDisk::GetFiles($chatId, $fileIds);

		$result = Array(
			'CHAT_ID' => (int)$chatId,
			'MESSAGES' => $messages,
			'USERS' => array_values($users),
			'FILES' => array_values($files),
		);

		if (count($files) && $fileSort == 'DESC')
		{
			$result['FILES'] = array_reverse($result['FILES']);
		}

		if ($startFromUnread)
		{
			$result['MESSAGES'] = array_reverse($result['MESSAGES']);
			$additionalMessages = self::getMessages($chatId, $userId, [
				'LIMIT' => $limit,
				'LAST_ID' => $chatData['RELATION_UNREAD_ID']
			]);
			$result['MESSAGES'] = array_merge($result['MESSAGES'], $additionalMessages['MESSAGES']);
		}

		if ($options['JSON'])
		{
			foreach ($result['MESSAGES'] as $key => $value)
			{
				if ($value['DATE'] instanceof \Bitrix\Main\Type\DateTime)
				{
					$result['MESSAGES'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
				}

				if (isset($value['PARAMS']['CHAT_LAST_DATE']) && $value['PARAMS']['CHAT_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime)
				{
					$result['MESSAGES'][$key]['PARAMS']['CHAT_LAST_DATE'] = date('c', $value['PARAMS']['CHAT_LAST_DATE']->getTimestamp());
				}

				if (is_array($value['REPLACES']) && !empty($value['REPLACES']))
				{
					$result['MESSAGES'][$key]['REPLACES'] = Common::toJson($value['REPLACES']);
				}

				$result['MESSAGES'][$key] = array_change_key_case($result['MESSAGES'][$key], CASE_LOWER);
			}
			$result['MESSAGES'] = array_values($result['MESSAGES']);

			foreach ($result['FILES'] as $key => $value)
			{
				if ($value['date'] instanceof \Bitrix\Main\Type\DateTime)
				{
					$result['FILES'][$key]['date'] = date('c', $value['date']->getTimestamp());
				}

				foreach (['urlPreview', 'urlShow', 'urlDownload'] as $field)
				{
					$url = $result['FILES'][$key][$field];
					if (is_string($url) && $url && mb_strpos($url, 'http') !== 0)
					{
						$result['FILES'][$key][$field] = \Bitrix\Im\Common::getPublicDomain().$url;
					}
				}

			}

			$result = array_change_key_case($result, CASE_LOWER);
		}

		return $result;
	}

	public static function getUsers($chatId, $options = []): array
	{
		$params = [
			'SELECT' => ['ID', 'USER_ID'],
			'SKIP_INACTIVE_USER' => 'Y',
			'WITHOUT_COUNTERS' => 'Y',
		];

		$skipExternal = isset($options['SKIP_EXTERNAL']) || isset($options['SKIP_EXTERNAL_EXCEPT_TYPES']);
		if ($skipExternal)
		{
			$exceptType = $options['SKIP_EXTERNAL_EXCEPT_TYPES'] ?? [];
			$params['SKIP_USER_TYPES'] = \Bitrix\Im\Model\UserTable::filterExternalUserTypes($exceptType);
		}

		if (isset($options['LIMIT']))
		{
			$params['LIMIT'] = $options['LIMIT'];
		}
		if (isset($options['OFFSET']))
		{
			$params['OFFSET'] = $options['OFFSET'];
		}
		if (isset($options['LAST_ID']))
		{
			$params['LAST_USER_ID'] = (int)$options['LAST_ID'];
		}

		$users = [];
		$relations = self::getRelation($chatId, $params);
		foreach ($relations as $user)
		{
			$users[] = \Bitrix\Im\User::getInstance($user['USER_ID'])->getArray([
				'JSON' => $options['JSON'] === 'Y'? 'Y': 'N'
			]);
		}

		return $users;
	}

	/**
	 * @param $id
	 * @param array $params
	 * @return array|bool|mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getById($id, $params = array())
	{
		$userId = \Bitrix\Im\Common::getUserId();
		if (!$userId)
		{
			return false;
		}

		$checkAccessParam = $params['CHECK_ACCESS'] ?? null;
		$chats = self::getList(Array(
			'FILTER' => Array('ID' => $id),
			'SKIP_ACCESS_CHECK' => $checkAccessParam === 'Y'? 'N': 'Y',
 		));
		if ($chats)
		{
			$chat = $chats[0];
		}
		else
		{
			return false;
		}

		if (isset($params['LOAD_READED']) && $params['LOAD_READED'])
		{
			$userOptions = ['SKIP_ONLINE' => 'Y'];
			if ($chat['ENTITY_TYPE'] == 'LIVECHAT')
			{
				[$lineId] = explode('|', $chat['CHAT_ENTITY_ID']);
				$userOptions['LIVECHAT'] = $lineId;
				$userOptions['USER_CODE'] = 'livechat|' . $lineId . '|' . $id . '|' . $userId;
			}

			$relations = self::getRelation($id);

			$chat['READED_LIST'] = [];
			$chat['MANAGER_LIST'] = [];
			foreach ($relations as $relation)
			{
				if (
					$relation['USER_ID'] != $userId
					//&& $relation['STATUS'] == self::STATUS_READ
					&& \Bitrix\Im\User::getInstance($relation['USER_ID'])->isActive()
				)
				{
					$user = \Bitrix\Im\User::getInstance($relation['USER_ID'])->getArray($userOptions);
					$chat['READED_LIST'][] = [
						'USER_ID' => (int)$relation['USER_ID'],
						'USER_NAME' => $user['NAME'],
						'MESSAGE_ID' => (int)$relation['LAST_ID'],
						'DATE' => $relation['LAST_READ'],
					];
				}

				if ($relation['MANAGER'] === 'Y')
				{
					$chat['MANAGER_LIST'][] = (int)$relation['USER_ID'];
				}
			}

			// region v2

			$chatInstance = \Bitrix\Im\V2\Chat::getInstance((int)$id);
			$chat['LAST_MESSAGE_VIEWS'] = $chatInstance->getLastMessageViews();
			$chat['LAST_ID'] = (new ReadService($userId))->getLastIdByChatId((int)$id);
			$chat['MARKED_ID'] = Recent::getMarkedId($userId, $chatInstance->getType(), $chatInstance->getDialogId());

			// endregion
		}

		if ($params['JSON'] ?? null)
		{
			$chat = self::toJson($chat);
		}

		return $chat;
	}

	public static function getList($params = array())
	{
		$params = is_array($params)? $params: Array();

		if (!isset($params['CURRENT_USER']) && is_object($GLOBALS['USER']))
		{
			$params['CURRENT_USER'] = $GLOBALS['USER']->GetID();
		}

		$params['CURRENT_USER'] = intval($params['CURRENT_USER']);

		$params['SKIP_ACCESS_CHECK'] = $params['SKIP_ACCESS_CHECK'] === 'Y'? 'Y': 'N';

		$userId = $params['CURRENT_USER'];
		if ($userId <= 0)
		{
			return false;
		}

		$enableLimit = false;
		if (isset($params['OFFSET']))
		{
			$filterLimit = intval($params['LIMIT']);
			$filterLimit = $filterLimit <= 0? self::FILTER_LIMIT: $filterLimit;

			$filterOffset = intval($params['OFFSET']);

			$enableLimit = true;
		}
		else
		{
			$filterLimit = false;
			$filterOffset = false;
		}

		$ormParams = self::getListParams($params);
		if (!$ormParams)
		{
			return false;
		}
		if ($enableLimit)
		{
			$ormParams['offset'] = $filterOffset;
			$ormParams['limit'] = $filterLimit;
		}
		if (isset($params['ORDER']))
		{
			$ormParams['order'] = $params['ORDER'];
		}

		$orm = \Bitrix\Im\Model\ChatTable::getList($ormParams);
		$chatsRaw = $orm->fetchAll();
		$chatsRaw = self::fillCounterData($chatsRaw);

		$chats = array();
		foreach ($chatsRaw as $chatRaw)
		{
			$chats[] = self::formatChatData($chatRaw);
		}

		if (isset($params['JSON']) && $params['JSON'])
		{
			$chats = self::toJson($chats);
		}

		return $chats;
	}

	public static function formatChatData($chat): array
	{
		$generalChatId = \CIMChat::GetGeneralChatId();
		$avatar = \CIMChat::GetAvatarImage($chat['AVATAR'], 200, false);
		$color = $chat['COLOR'] <> ''? Color::getColor($chat['COLOR']): Color::getColorByNumber($chat['ID']);

		if ($generalChatId == $chat['ID'])
		{
			$chat["ENTITY_TYPE"] = 'GENERAL';
		}

		$chatType = \Bitrix\Im\Chat::getType($chat);

		$muteList = Array();
		if ($chat['RELATION_NOTIFY_BLOCK'] == 'Y')
		{
			$muteList[] = (int)$chat['RELATION_USER_ID'];
		}

		$counter = (int)$chat['RELATION_COUNTER'];
		$startCounter = (int)$chat['RELATION_START_COUNTER'];
		$userCounter = (int)$chat['USER_COUNT'];
		$unreadId = (int)$chat['RELATION_UNREAD_ID'];
		$lastMessageId = (int)$chat['LAST_MESSAGE_ID'];

		$publicOption = '';
		if ($chat['ALIAS_NAME'])
		{
			$publicOption = [
				'code' => $chat['ALIAS_NAME'],
				'link' => Alias::getPublicLink($chat['ENTITY_TYPE'], $chat['ALIAS_NAME'])
			];
		}

		$options = \CIMChat::GetChatOptions();
		$restrictions = $options['DEFAULT'];

		if ($chat["ENTITY_TYPE"] && in_array($chat["ENTITY_TYPE"], array_keys($options), true))
		{
			$restrictions = $options[$chat['ENTITY_TYPE']];
		}

		return Array(
			'ID' => (int)$chat['ID'],
			'NAME' => $chat['TITLE'],
			'DESCRIPTION' => $chat['DESCRIPTION'],
			'OWNER' => (int)$chat['AUTHOR_ID'],
			'EXTRANET' => $chat['EXTRANET'] == 'Y',
			'AVATAR' => $avatar,
			'COLOR' => $color,
			'TYPE' => $chatType,
			'COUNTER' => $counter,
			'USER_COUNTER' => $userCounter,
			'MESSAGE_COUNT' => (int)$chat['MESSAGE_COUNT'] - $startCounter,
			'UNREAD_ID' => $unreadId,
			'RESTRICTIONS' => $restrictions,
			'LAST_MESSAGE_ID' => $lastMessageId,
			'DISK_FOLDER_ID' => (int)$chat['DISK_FOLDER_ID'],
			'ENTITY_TYPE' => (string)$chat['ENTITY_TYPE'],
			'ENTITY_ID' => (string)$chat['ENTITY_ID'],
			'ENTITY_DATA_1' => (string)$chat['ENTITY_DATA_1'],
			'ENTITY_DATA_2' => (string)$chat['ENTITY_DATA_2'],
			'ENTITY_DATA_3' => (string)$chat['ENTITY_DATA_3'],
			'MUTE_LIST' => $muteList,
			'DATE_CREATE' => $chat['DATE_CREATE'],
			'MESSAGE_TYPE' => $chat["TYPE"],
			'PUBLIC' => $publicOption,
			'ROLE' => mb_strtolower(self::getRole($chat)),
		);
	}

	public static function getListParams($params)
	{
		if (!isset($params['CURRENT_USER']) && is_object($GLOBALS['USER']))
		{
			$params['CURRENT_USER'] = $GLOBALS['USER']->GetID();
		}

		$params['CURRENT_USER'] = intval($params['CURRENT_USER']);

		$userId = $params['CURRENT_USER'];
		if ($userId <= 0)
		{
			return null;
		}

		$filter = [];
		$runtime = [];

		if (isset($params['FILTER']['ID']))
		{
			$filter['=ID'] = $params['FILTER']['ID'];
		}
		else if (isset($params['FILTER']['SEARCH']))
		{
			$find = (string)$params['FILTER']['SEARCH'];

			$helper = Application::getConnection()->getSqlHelper();
			if (Model\ChatIndexTable::getEntity()->fullTextIndexEnabled('SEARCH_CONTENT'))
			{
				$find = trim($find);
				$find = \Bitrix\Main\Search\Content::prepareStringToken($find);

				if (\Bitrix\Main\Search\Content::canUseFulltextSearch($find, \Bitrix\Main\Search\Content::TYPE_MIXED))
				{
					$filter['*INDEX.SEARCH_CONTENT'] = $find;
				}
				else
				{
					return null;
				}
			}
			else
			{
				if (mb_strlen($find) < 3)
				{
					return null;
				}

				$filter['%=INDEX.SEARCH_TITLE'] = $helper->forSql($find).'%';
			}
		}

		if ($params['SKIP_ACCESS_CHECK'] === 'Y')
		{
			// do nothing
		}
		else if (
			User::getInstance($params['CURRENT_USER'])->isExtranet()
			|| User::getInstance($params['CURRENT_USER'])->isBot()
		)
		{
			$filter['=TYPE'] = [
				self::TYPE_CHANNEL,
				self::TYPE_GROUP,
				self::TYPE_THREAD,
				self::TYPE_PRIVATE
			];
			if (User::getInstance($params['CURRENT_USER'])->isBot())
			{
				$filter['=TYPE'][] = self::TYPE_OPEN_LINE;
			}
			$filter['=RELATION.USER_ID'] = $params['CURRENT_USER'];
		}
		else
		{
			$filter[] = [
				'LOGIC' => 'OR',
				[
					'=TYPE' => self::TYPE_OPEN,
				],
				[
					'=TYPE' => self::TYPE_GROUP,
					'=RELATION.USER_ID' => $params['CURRENT_USER']
				],
				[
					'=TYPE' => self::TYPE_THREAD,
					'=RELATION.USER_ID' => $params['CURRENT_USER']
				],
				[
					'=TYPE' => self::TYPE_PRIVATE,
					'=RELATION.USER_ID' => $params['CURRENT_USER']
				],
				[
					'=TYPE' => self::TYPE_OPEN_LINE,
					'=RELATION.USER_ID' => $params['CURRENT_USER']
				],
			];
		}

		$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
			'RELATION',
			'Bitrix\Im\Model\RelationTable',
			array(
				"=ref.CHAT_ID" => "this.ID",
				"=ref.USER_ID" => new \Bitrix\Main\DB\SqlExpression('?', $params['CURRENT_USER']),
			),
			array("join_type"=>"LEFT")
		);

		return [
			'select' => [
				'*',
				'RELATION_USER_ID' => 'RELATION.USER_ID',
				'RELATION_MANAGER' => 'RELATION.MANAGER',
				'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
				//'RELATION_COUNTER' => 'RELATION.COUNTER',
				'RELATION_START_COUNTER' => 'RELATION.START_COUNTER',
				'RELATION_LAST_ID' => 'RELATION.LAST_ID',
				//'RELATION_STATUS' => 'RELATION.STATUS',
				//'RELATION_UNREAD_ID' => 'RELATION.UNREAD_ID',
				'ALIAS_NAME' => 'ALIAS.ALIAS',
			],
			'filter' => $filter,
			'runtime' => $runtime
		];
	}

	public static function toJson($array)
	{
		return \Bitrix\Im\Common::toJson($array, false);
	}

	public static function isUserInChat($chatId, $userId = 0) : bool
	{
		if ($userId === 0)
		{
			$userId = \Bitrix\Im\Common::getUserId();
		}

		if (!$userId)
		{
			return false;
		}

		$result = \Bitrix\Im\Model\RelationTable::getList(
			[
				'select' => ["ID"],
				'filter' => [
					'=USER_ID' => $userId,
					'=CHAT_ID' => $chatId
				]
			]
		)->fetch();

		return (bool)$result['ID'];
	}

	public static function isUserKickedFromChat($chatId, $userId = 0) : bool
	{
		if ($userId === 0)
		{
			$userId = \Bitrix\Im\Common::getUserId();
		}

		if (!$userId)
		{
			return false;
		}

		$result = BlockUserTable::getList(
			[
				'select' => ["ID"],
				'filter' => [
					'=USER_ID' => $userId,
					'=CHAT_ID' => $chatId
				]
			]
		)->fetch();

		return (bool)$result['ID'];
	}

	public static function checkReplicaDeprecatedAgent(): string
	{
		return '';
	}

	/**
	 * Returns the value of the chat option by dialogId.
	 *
	 * @param int|string $dialogId
	 *
	 * @param string $action - chat option.
	 * @see \CIMChat::GetChatOptions()
	 *
	 * @param string|null $entityType - if $entityType is known, you can avoid accessing the database.
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function isActionAllowed($dialogId, $action, $entityType = null): bool
	{
		if (!\Bitrix\Im\Common::isChatId($dialogId))
		{
			return true;
		}

		$chatOptions = \CIMChat::GetChatOptions();
		$isAllowedByDefault = (bool)($chatOptions['DEFAULT'][$action] ?? true);

		if ($entityType && $chatOptions[$entityType])
		{
			return (bool)($chatOptions[$entityType][$action] ?? $isAllowedByDefault);
		}

		if ($entityType)
		{
			return $isAllowedByDefault;
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($dialogId);
		if (!$chatId)
		{
			return $isAllowedByDefault;
		}

		$generalChatId = (int)\CIMChat::GetGeneralChatId();
		if ($chatId === $generalChatId)
		{
			return (bool)($chatOptions['GENERAL'][$action] ?? $isAllowedByDefault);
		}

		$chat = \Bitrix\Im\Model\ChatTable::getList([
			'select' => [
				'ID',
				'ENTITY_TYPE',
			],
			'filter' => [
				'ID' => $chatId,
			]
		])->fetch();

		$entityType = ($chat && $chat['ENTITY_TYPE']) ? $chat['ENTITY_TYPE'] : null;

		if ($entityType && $chatOptions[$entityType])
		{
			return (bool)($chatOptions[$entityType][$action] ?? $isAllowedByDefault);
		}

		return $isAllowedByDefault;
	}

	/**
	 * Get chat authorId by dialogId
	 *
	 * @param int|string $dialogId
	 *
	 * @return int|null AUTHOR_ID
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getOwnerById($dialogId): ?int
	{
		$chatId = \Bitrix\Im\Dialog::getChatId($dialogId);
		if (!$chatId)
		{
			return null;
		}

		$chat = \Bitrix\Im\Model\ChatTable::getList([
			'select' => [
				'ID',
				'AUTHOR_ID',
			],
			'filter' => [
				'ID' => $chatId,
			]
		])->fetch();

		return ($chat && is_numeric($chat['AUTHOR_ID'])) ? (int)$chat['AUTHOR_ID'] : null;
	}

	public static function fillCounterData(array $chats): array
	{
		if (empty($chats))
		{
			return [];
		}

		$userId = \Bitrix\Im\Common::getUserId();
		$readService = new ReadService($userId);

		$chatIds = [];

		foreach ($chats as $chat)
		{
			$chatIds[] = (int)$chat['ID'];
		}

		$counters = $readService->getCounterService()->getForEachChat($chatIds);
		$unreadIds = $readService->getCounterService()->getIdFirstUnreadMessageForEachChats($chatIds);

		foreach ($chats as $key => $chat)
		{
			$id = (int)$chat['ID'];
			$chats[$key]['RELATION_COUNTER'] = $counters[$id] ?? 0;
			$chats[$key]['RELATION_UNREAD_ID'] = $unreadIds[$id] ?? 0;
		}

		return $chats;
	}

	private static function getRole(array $chat): string
	{
		if (!isset($chat['RELATION_USER_ID']))
		{
			return \Bitrix\Im\V2\Chat::ROLE_GUEST;
		}
		if ((int)$chat['RELATION_USER_ID'] === (int)$chat['AUTHOR_ID'])
		{
			return \Bitrix\Im\V2\Chat::ROLE_OWNER;
		}
		if ($chat['RELATION_MANAGER'] === 'Y')
		{
			return \Bitrix\Im\V2\Chat::ROLE_MANAGER;
		}

		return \Bitrix\Im\V2\Chat::ROLE_MEMBER;
	}
}
