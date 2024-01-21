<?php
namespace Bitrix\Im\Update;

use Bitrix\Im\V2\Message;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

class MessageDisappearing
{
	public static function disappearMessagesAgent(): string
	{
		$connection = Application::getConnection();

		$messagesQuery = $connection->query("
			SELECT MESSAGE_ID
			FROM b_im_message_disappearing
			WHERE DATE_REMOVE < '" . (new DateTime())->format('Y-m-d H:i:s') . "'
		");
		$messages = $messagesQuery->fetchAll();


		if (empty($messages))
		{
			return __METHOD__ . '();';
		}

		foreach ($messages as $message)
		{
			$message = new Message($message['MESSAGE_ID']);
			if ($message->getId())
			{
				$message->deleteComplete();
			}
		}

		return __METHOD__ . '();';
	}
}
