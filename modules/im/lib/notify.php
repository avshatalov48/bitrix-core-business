<?php
namespace Bitrix\Im;

class Notify
{
	public static function getRealCounter($chatId): int
	{
		return self::getCounters($chatId, true)[$chatId];
	}

	public static function getRealCounters($chatId)
	{
		return self::getCounters($chatId, true);
	}

	public static function getCounter($chatId): int
	{
		return self::getCounters($chatId)[$chatId];
	}

	public static function getCounters($chatId, $isReal = false)
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

		if ($isReal)
		{
			$query = "
				SELECT CHAT_ID, COUNT(1) COUNTER
				FROM b_im_message
				WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
					  AND NOTIFY_READ <> 'Y'
				GROUP BY CHAT_ID
			";
		}
		else
		{
			$query = "
				SELECT CHAT_ID, COUNTER 
				FROM b_im_relation
				WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
			";
		}

		$orm = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);
		while($row = $orm->fetch())
		{
			$result[$row['CHAT_ID']] = (int)$row['COUNTER'];
		}

		return $result;
	}
}