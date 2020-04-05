<?php
namespace Bitrix\Im;

class Chat
{
	const TYPE_SYSTEM = 'S';
	const TYPE_PRIVATE = 'P';
	const TYPE_OPEN = 'O';
	const TYPE_THREAD = 'T';
	const TYPE_GROUP = 'C';
	const TYPE_OPEN_LINE = 'L';

	const LIMIT_SEND_EVENT = 30;

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
					$selectFields .= "R.{$key} {$connection->getSqlHelper()->forSql($value)}, ";
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

				$whereFields .= " AND R.{$key} = {$connection->getSqlHelper()->forSql($value)}";
			}
		}

		$skipUnmodifiedRecords = false;
		if (isset($params['SKIP_RELATION_WITH_UNMODIFIED_COUNTERS']) && $params['SKIP_RELATION_WITH_UNMODIFIED_COUNTERS'] == 'Y')
		{
			$skipUnmodifiedRecords = true;
		}

		if (isset($params['REAL_COUNTERS']) && $params['REAL_COUNTERS'] != 'N' || $skipUnmodifiedRecords)
		{
			$lastId = "R.LAST_ID";
			if (is_array($params['REAL_COUNTERS']))
			{
				if (isset($params['REAL_COUNTERS']['LAST_ID']) && intval($params['REAL_COUNTERS']['LAST_ID']) > 0)
				{
					$lastId = intval($params['REAL_COUNTERS']['LAST_ID']);
				}
			}

			$sqlSelectCounter = "R.COUNTER PREVIOUS_COUNTER, (
				SELECT COUNT(1) FROM b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.ID > $lastId
			) COUNTER";
		}
		else
		{
			$sqlSelectCounter = 'R.COUNTER, R.COUNTER PREVIOUS_COUNTER';
		}

		$sql = "
			SELECT {$selectFields} {$sqlSelectCounter}
			FROM b_im_relation R
			".($withUserFields? "LEFT JOIN b_user U ON R.USER_ID = U.ID": "")."
			WHERE R.CHAT_ID = {$chatId} {$whereFields}
			".($skipUnmodifiedRecords? ' HAVING COUNTER <> PREVIOUS_COUNTER': '')."
		";
		$relations = array();
		$query = \Bitrix\Main\Application::getInstance()->getConnection()->query($sql);
		while ($row = $query->fetch())
		{
			$row['COUNTER'] = $row['COUNTER']>99? 100: intval($row['COUNTER']);
			$row['PREVIOUS_COUNTER'] = intval($row['PREVIOUS_COUNTER']);

			if ($skipUnmodifiedRecords && $row['COUNTER'] == $row['PREVIOUS_COUNTER'])
			{
				continue;
			}

			foreach ($row as $key => $value)
			{
				if (strpos($key, 'USER_DATA_') === 0)
				{
					$row['USER_DATA'][substr($key, 10)] = $value;
					unset($row[$key]);
				}
			}

			$relations[$row['USER_ID']] = $row;
		}

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

		$relation = self::getRelation($chatId, Array(
			'SELECT' => Array('ID', 'NOTIFY_BLOCK'),
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

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			\Bitrix\Pull\Event::add($userId, Array(
				'module_id' => 'im',
				'command' => 'chatMuteNotify',
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
					'mute' => $action == 'Y'
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
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

	public static function getMessages($chatId, $options)
	{
		$returnJson = $options['JSON'] == 'Y';

		$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
			'select' => Array(
				'CHAT_ID' => 'ID',
				'CHAT_TYPE' => 'TYPE',
				'RELATION_START_ID' => 'RELATION.START_ID'
			),
			'filter' => Array('=ID' => $chatId)
		))->fetch();
		if (!$chatData)
		{
			return false;
		}

		$chatData['RELATION_START_ID'] = intval($chatData['RELATION_START_ID']);

		$filter = Array(
			'=CHAT_ID' => $chatId
		);

		if (isset($options['FIRST_ID']))
		{
			$order = array();
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
			$order = Array('CHAT_ID' => 'ASC', 'ID' => 'DESC');

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
			'filter' => $filter,
			'select' => Array('ID', 'AUTHOR_ID', 'DATE_CREATE', 'MESSAGE'),
			'order' => $order,
			'limit' => intval($options['LIMIT']) <= 50? intval($options['LIMIT']): 50
		));

		$users = Array();
		$userOptions = $options['JSON'] == 'Y'? Array('JSON' => 'Y'): Array();

		$messages = Array();
		while($message = $orm->fetch())
		{
			$messages[$message['ID']] = Array(
				'ID' => (int)$message['ID'],
				'CHAT_ID' => (int)$chatId,
				'AUTHOR_ID' => (int)$message['AUTHOR_ID'],
				'DATE' => $message['DATE_CREATE'],
				'TEXT' => (string)$message['MESSAGE']
			);
			if ($message['AUTHOR_ID'] && !isset($users[$message['AUTHOR_ID']]))
			{
				$users[$message['AUTHOR_ID']] = User::getInstance($message['AUTHOR_ID'])->getArray($userOptions);
			}
		}

		$params = \CIMMessageParam::Get(array_keys($messages));

		$fileIds = Array();
		foreach ($params as $messageId => $param)
		{
			$messages[$messageId]['params'] = empty($param)? null: $param;

			if (isset($param['FILE_ID']))
			{
				foreach ($param['FILE_ID'] as $fileId)
				{
					$fileIds[$fileId] = $fileId;
				}
			}
		}

		$messages = \CIMMessageLink::prepareShow($messages, $params);

		$files = \CIMDisk::GetFiles($chatId, $fileIds);

		$result = Array(
			'MESSAGES' => $messages,
			'USERS' => $users,
			'FILES' => $files,
		);

		if ($options['JSON'])
		{
			foreach ($result['MESSAGES'] as $key => $value)
			{
				if ($value['DATE'] instanceof \Bitrix\Main\Type\DateTime)
				{
					$result['MESSAGES'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
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
					foreach ($result['FILES'][$key][$field] as $type => $url)
					{
						if (is_string($url) && $url && strpos($url, 'http') !== 0)
						{
							$result['FILES'][$key][$field][$type] = \Bitrix\Im\Common::getPublicDomain().$url;
						}
					}
				}

			}

			$result = array_change_key_case($result, CASE_LOWER);
		}



		return $result;
	}
}