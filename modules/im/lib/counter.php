<?php
namespace Bitrix\Im;

use Bitrix\Im\V2\Message\CounterServiceLegacy;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

class Counter
{
	const CACHE_TTL = 86400; // 1 month
	const CACHE_NAME = 'counter_v3'; // 1 month
	const CACHE_PATH = '/bx/im/counter/';

	const TYPE_MESSENGER = 'messenger';
	const MODULE_ID = 'im';

	public static function get($userId = null, $options = [])
	{
		if (isset($options['JSON']))
		{
			return \Bitrix\Im\Common::toJson((new CounterServiceLegacy($userId))->get());
		}
		return (new CounterServiceLegacy($userId))->get();
		$result = [
			'TYPE' => [
				'ALL' => 0,
				'NOTIFY' => 0,
				'DIALOG' => 0,
				'CHAT' => 0,
				'LINES' => 0,
			],
			'DIALOG' => [],
			'DIALOG_UNREAD' => [],
			'CHAT' => [],
			'CHAT_MUTED' => [],
			'CHAT_UNREAD' => [],
			'LINES' => [],
		];

		$userId = Common::getUserId($userId);
		if ($userId <= 0)
		{
			return $result;
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($cache->initCache(self::CACHE_TTL, self::CACHE_NAME.'_'.$userId, self::CACHE_PATH))
		{
			$result = $cache->getVars();
			if (isset($options['JSON']))
			{
				$result = \Bitrix\Im\Common::toJson($result);
			}
			return $result;
		}

		$query = "
			SELECT
				R1.CHAT_ID,
				R1.MESSAGE_TYPE, 
				IF(RC.ITEM_TYPE = '".IM_MESSAGE_PRIVATE."', RC.ITEM_ID, 0) PRIVATE_USER_ID,
				U.ACTIVE PRIVATE_USER_ACTIVE,
				R1.COUNTER,
				R1.NOTIFY_BLOCK MUTED,
				IF(RC.USER_ID > 0, 'Y', 'N') IN_RECENT,
				RC.UNREAD
			FROM b_im_relation R1 
			LEFT JOIN b_im_recent RC ON RC.ITEM_RID = R1.ID
			LEFT JOIN b_user U ON RC.ITEM_TYPE = '".IM_MESSAGE_PRIVATE."' AND U.ID = RC.ITEM_ID
			WHERE R1.USER_ID = ".intval($userId)." AND (R1.STATUS <> ".IM_STATUS_READ." OR RC.UNREAD = 'Y')
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
					if ($entity['PRIVATE_USER_ACTIVE'] === 'N')
					{
						continue;
					}

					if ($entity['COUNTER'] > 0)
					{
						$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
						$result['TYPE']['DIALOG'] += (int)$entity['COUNTER'];
						$result['DIALOG'][$entity['PRIVATE_USER_ID']] = (int)$entity['COUNTER'];
					}
					else if ($entity['UNREAD'] === 'Y')
					{
						$result['TYPE']['ALL']++;
						$result['TYPE']['DIALOG']++;
						$result['DIALOG_UNREAD'][] = (int)$entity['PRIVATE_USER_ID'];
					}
				}
				else if ($entity['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
					$result['TYPE']['LINES'] += (int)$entity['COUNTER'];
					$result['LINES'][$entity['CHAT_ID']] = (int)$entity['COUNTER'];
				}
				else
				{
					if ($entity['COUNTER'] > 0)
					{
						if ($entity['MUTED'] === 'N')
						{
							$result['TYPE']['ALL'] += (int)$entity['COUNTER'];
							$result['TYPE']['CHAT'] += (int)$entity['COUNTER'];
							$result['CHAT'][$entity['CHAT_ID']] = (int)$entity['COUNTER'];
						}
						else
						{
							$result['CHAT_MUTED'][$entity['CHAT_ID']] = (int)$entity['COUNTER'];
						}
					}
					else if ($entity['UNREAD'] === 'Y')
					{
						if ($entity['MUTED'] === 'N')
						{
							$result['TYPE']['ALL']++;
							$result['TYPE']['CHAT']++;
						}
						$result['CHAT_UNREAD'][] = (int)$entity['CHAT_ID'];
					}

				}
			}
		}

		$cache->startDataCache();
		$cache->endDataCache($result);

		if (isset($options['JSON']))
		{
			$result = \Bitrix\Im\Common::toJson($result);
		}

		return $result;
	}

	public static function clearCache($userId = null)
	{
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($userId)
		{
			$cache->clean(self::CACHE_NAME.'_'.$userId, self::CACHE_PATH);
		}
		else
		{
			$cache->cleanDir(self::CACHE_PATH);
		}

		return true;
	}

	public static function getChatCounter($chatId, $userId = null)
	{
		$chatId = intval($chatId);
		$userId = Common::getUserId($userId);

		if ($chatId <= 0 || $userId <= 0)
		{
			return false;
		}

		$counters = self::get($userId);

		return intval($counters['CHAT'][$chatId]);
	}

	public static function getDialogCounter($opponentUserId, $userId = null)
	{
		$userId = Common::getUserId($userId);
		$opponentUserId = intval($opponentUserId);
		if ($userId <= 0 || $opponentUserId <= 0)
		{
			return false;
		}

		$counters = self::get($userId);

		return intval($counters['DIALOG'][$opponentUserId]);
	}

	public static function getNotifyCounter($userId = null)
	{
		$userId = Common::getUserId($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$counters = self::get($userId);

		return intval($counters['TYPE']['NOTIFY']);
	}

	public static function countingLostCountersAgent($notifyRelationId = 0, $chatRelationId = 0)
	{
		return '';

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
				'NAME' => Loc::getMessage('IM_COUNTER_TYPE_MESSENGER_2'),
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