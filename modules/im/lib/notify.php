<?php
namespace Bitrix\Im;

class Notify
{
	public static function getCounter($userId)
	{
		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		$query = "
			SELECT COUNT(1) CNT
			FROM b_im_message M
			INNER JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."'
			WHERE R.USER_ID = ".$userId." AND NOTIFY_READ <> 'Y'
		";
		$result = \Bitrix\Main\Application::getInstance()->getConnection()->query($query)->fetch();

		return intval($result['CNT']);
	}

	public static function getCounterByChatId($chatId)
	{
		$result = self::getCountersByChatId($chatId);
		if (!$result)
		{
			return 0;
		}

		return intval($result[$chatId]);
	}

	public static function getCountersByChatId($chatId)
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
			SELECT COUNT(1) COUNTER, M.CHAT_ID
			FROM b_im_message M 
			WHERE 
				M.CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])." 
				AND M.NOTIFY_READ <> 'Y'
			".($isMulti? 'GROUP BY M.CHAT_ID': '')."
		";
		$orm = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);
		while($row = $orm->fetch())
		{
			if (!$row['CHAT_ID'])
			{
				continue;
			}
			$result[$row['CHAT_ID']] = $row['COUNTER'];
		}

		return $result;
	}
}