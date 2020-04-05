<?php
namespace Bitrix\Im\Update;

class Notify
{
	public static function removeDuplicateChatAgent()
	{
		$hasDuplicate = false;
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();
		$users = $connection->query("
			SELECT AUTHOR_ID, COUNT(1) CNT 
			FROM b_im_chat
			WHERE TYPE = '".IM_MESSAGE_SYSTEM."'
			GROUP BY AUTHOR_ID
			HAVING CNT > 1
			LIMIT 100
		");
		while ($userData = $users->fetch())
		{
			$hasDuplicate = true;

			$result = Array();
			$chats = $connection->query("
				SELECT ID, LAST_MESSAGE_ID
				FROM b_im_chat
				WHERE TYPE = '".IM_MESSAGE_SYSTEM."' AND AUTHOR_ID = ".intval($userData['AUTHOR_ID'])."
			");
			while ($chatData = $chats->fetch())
			{
				$result[intval($chatData['ID'])] = intval($chatData['LAST_MESSAGE_ID']);
			}
			arsort($result);
			$result = array_slice($result, 1, null, true);
			$chatId = array_keys($result);
			if (!empty($chatId))
			{
				$connection->query("DELETE FROM b_im_relation WHERE CHAT_ID IN (".implode(", ", $chatId).")");
				$connection->query("DELETE FROM b_im_chat WHERE ID IN (".implode(", ", $chatId).")");
			}
		}

		return $hasDuplicate? "\Bitrix\Im\Update\Notify::removeDuplicateChatAgent();": "";
	}
}