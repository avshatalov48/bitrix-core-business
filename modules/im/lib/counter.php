<?php
namespace Bitrix\Im;

use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class Counter
{
	const CACHE_TTL = 2678400; // 1 month
	const CACHE_NAME = 'counter_v2'; // 1 month
	const CACHE_PATH = '/bx/im/counter/';

	const TYPE_MESSENGER = 'messenger';
	const MODULE_ID = 'im';

	public static function get($userId)
	{
		$result = Array(
			'TYPE' => Array(
				'ALL' => 0,
				'NOTIFY' => 0,
				'DIALOG' => 0,
				'CHAT' => 0,
				'LINES' => 0,
			),
			'DIALOG' => Array(),
			'CHAT' => Array(),
			'LINES' => Array(),
		);
		if ($userId <= 0)
		{
			return $result;
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, self::CACHE_NAME.'_'.$userId, self::CACHE_PATH))
		{
			return $cache->getVars();
		}

		$query = "
			SELECT R1.CHAT_ID, R1.MESSAGE_TYPE, IF(R2.USER_ID > 0, R2.USER_ID, 0) PRIVATE_USER_ID, R1.COUNTER, IF(RC.USER_ID > 0, 'Y', 'N') IN_RECENT
			FROM b_im_relation R1 
			LEFT JOIN b_im_relation R2 ON R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."' AND R2.CHAT_ID = R1.CHAT_ID AND R2.USER_ID <> R1.USER_ID
			LEFT JOIN b_im_recent RC ON RC.USER_ID = R1.USER_ID AND RC.ITEM_TYPE = R1.MESSAGE_TYPE AND RC.ITEM_ID = IF(R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."', R2.USER_ID, R1.CHAT_ID)
			WHERE R1.USER_ID = ".intval($userId)." AND R1.STATUS <> ".IM_STATUS_READ."
		";
		$counters = \Bitrix\Main\Application::getInstance()->getConnection()->query($query)->fetchAll();

		foreach ($counters as $entity)
		{
			if ($entity['MESSAGE_TYPE'] == IM_MESSAGE_SYSTEM)
			{
				$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
				$result['TYPE']['NOTIFY'] += (int)$entity['COUNTER'];
			}
			else
			{
				if ($entity['IN_RECENT'] == 'N')
				{
					continue;
				}
				if ($entity['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
				{
					$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
					$result['TYPE']['DIALOG'] += (int)$entity['COUNTER'];
					$result['DIALOG'][$entity['PRIVATE_USER_ID']] = (int)$entity['COUNTER'];
				}
				else if ($entity['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
					$result['TYPE']['LINES'] += (int)$entity['COUNTER'];
					$result['LINES'][$entity['CHAT_ID']] = (int)$entity['COUNTER'];
				}
				else
				{
					$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
					$result['TYPE']['CHAT'] += (int)$entity['COUNTER'];
					$result['CHAT'][$entity['CHAT_ID']] = (int)$entity['COUNTER'];
				}
			}
		}

		$cache->startDataCache();
		$cache->endDataCache($result);

		return $result;
	}

	public static function clearCache($userId = null)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($userId)
		{
			$cache->clean(self::CACHE_NAME.'_'.$userId, self::CACHE_PATH);
			\CIMContactList::ClearRecentCache($userId);
		}
		else
		{
			$cache->cleanDir(self::CACHE_PATH);
			\CIMContactList::ClearRecentCache();
		}

		return true;
	}

	public static function getChatCounter($chatId, $userId)
	{
		$chatId = intval($chatId);
		$userId = intval($userId);
		if ($chatId <= 0 || $userId <= 0)
		{
			return false;
		}

		$counters = self::get($userId);

		return intval($counters['CHAT'][$chatId]);
	}

	public static function getDialogCounter($userId, $opponentUserId)
	{
		$userId = intval($userId);
		$opponentUserId = intval($opponentUserId);
		if ($userId <= 0 || $opponentUserId <= 0)
		{
			return false;
		}

		$counters = self::get($userId);

		return intval($counters['DIALOG'][$opponentUserId]);
	}

	public static function getNotifyCounter($userId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$counters = self::get($userId);

		return intval($counters['TYPE']['NOTIFY']);
	}

	public static function countingLostCountersAgent($notifyRelationId = 0, $chatRelationId = 0)
	{
		$foundNotify = false;
		$foundChat = false;

		$notifyStartId = intval($notifyRelationId);

		if ($notifyStartId >= 0)
		{
			$query = "
				SELECT COUNT(1) CNT, R.ID, R.USER_ID
				FROM b_im_message M
				INNER JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND R.COUNTER = 0
				WHERE M.NOTIFY_READ <> 'Y' AND R.ID > ".$notifyStartId."
				GROUP BY R.ID, R.USER_ID
				HAVING CNT > 0
			";
			$cursor = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);

			$count = 0;
			while ($row = $cursor->fetch())
			{
				$notifyRelationId = $row['ID'];
				$foundNotify = true;

				\Bitrix\Im\Model\RelationTable::update($row['ID'], Array(
					'STATUS' => IM_STATUS_UNREAD,
					"MESSAGE_STATUS" => IM_MESSAGE_STATUS_RECEIVED,
					'COUNTER' => $row['CNT'],
				));

				$count++;
				if ($count > 100)
				{
					break;
				}
			}
		}

		$chatRelationId = intval($chatRelationId);

		if ($chatRelationId >= 0)
		{
			$query = "
				SELECT R.ID, R.COUNTER PREVIOUS_COUNTER, (
					SELECT COUNT(1) FROM b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.ID > R.LAST_ID
				) COUNTER
				FROM b_im_relation R
				WHERE R.STATUS <> ".IM_STATUS_READ." AND R.COUNTER = 0 AND R.ID > ".$chatRelationId."
				ORDER BY R.ID ASC
				LIMIT 0, 100;
			";
			$cursor = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);

			while ($row = $cursor->fetch())
			{
				$chatRelationId = $row['ID'];
				$foundChat = true;

				if ($row['COUNTER'] == 0)
				{
					$update = Array(
						'STATUS' => IM_STATUS_READ,
						"MESSAGE_STATUS" => IM_MESSAGE_STATUS_RECEIVED,
					);
				}
				else if ($row['PREVIOUS_COUNTER'] == $row['COUNTER'])
				{
					continue;
				}
				else
				{
					$update = Array(
						'COUNTER' => $row['COUNTER']
					);
				}
				\Bitrix\Im\Model\RelationTable::update($row['ID'], $update);
			}
		}

		if ($foundNotify || $foundChat)
		{
			return '\Bitrix\Im\Counter::countingLostCountersAgent('.($foundNotify? $notifyRelationId: -1).', '.($foundChat? $chatRelationId: -1).');';
		}
		else
		{
			return '';
		}
	}

	public static function onGetMobileCounterTypes(\Bitrix\Main\Event $event)
	{
		return new EventResult(EventResult::SUCCESS, Array(
			self::TYPE_MESSENGER => Array(
				'NAME' => Loc::getMessage('IM_COUNTER_TYPE_MESSENGER'),
				'DEFAULT' => true
			),
		), self::MODULE_ID);
	}

	public static function onGetMobileCounter(\Bitrix\Main\Event $event)
	{
		$params = $event->getParameters();

		$counters = self::get($params['USER_ID']);

		return new EventResult(EventResult::SUCCESS, Array(
			'TYPE' => self::TYPE_MESSENGER,
			'COUNTER' => $counters['TYPE']['ALL']
		), self::MODULE_ID);
	}
}