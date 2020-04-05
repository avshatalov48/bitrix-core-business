<?php
namespace Bitrix\Im;

class Notify
{
	public static function getRealCounter($chatId): int
	{
		$chatId = intval($chatId);
		if (!$chatId)
		{
			return 0;
		}

		$query = "
			SELECT COUNT(1) COUNTER
			FROM b_im_message
			WHERE CHAT_ID = {$chatId} AND NOTIFY_READ <> 'Y'
		";

		$result = \Bitrix\Main\Application::getInstance()->getConnection()->query($query)->fetch();
		$counter = $result? $result['COUNTER']: 0;

		return $counter;
	}

	public static function getCounter($chatId): int
	{
		return self::getCounters($chatId)[$chatId];
	}

	public static function getCounters($chatId)
	{
		$result = Array();
		$chatList = Array();
		if (is_array($chatId))
		{
			foreach($chatId as $id)
			{
				$id = intval($id);
				if ($id)
				{
					$result[$id] = 0;
					$chatList[$id] = $id;
				}
			}
			$chatList = array_values($chatList);
			$isMulti = count($chatList) > 1;
		}
		else
		{
			$id = intval($chatId);
			if ($id)
			{
				$result[$id] = 0;
				$chatList[] = $id;
			}
			$isMulti = false;
		}

		if (!$chatList)
		{
			return false;
		}

		$query = "
			SELECT CHAT_ID, COUNTER 
			FROM b_im_relation
			WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
		";
		$orm = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);
		while($row = $orm->fetch())
		{
			$result[$row['CHAT_ID']] = (int)$row['COUNTER'];
		}

		return $result;
	}
}