<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Mail\MailboxTable;
use Bitrix\Mail\MailMessageTable;

/**
 * @see \Bitrix\Mail\MessageAccess
 */
class MessageAccess
{
	public static function createToken($mailBoxId, $messageId, $entityType, $entityId, $ufId = '0'): string
	{
		return md5(sprintf(
			'%u:%u:%u:%s:%s:%u', // '%u:%u:%u:%s:%s:%u'
			time(),
			$mailBoxId,
			$messageId,
			$entityType,
			$ufId,
			$entityId
		));
	}

	public static function createSecret(): string
	{
		return bin2hex(\Bitrix\Main\Security\Random::getBytes(16));
	}

	public static function isMailboxOwner(int $mailboxId, int $userId): bool
	{
		return Message::isMailboxOwner($mailboxId, $userId);
	}

	public static function isMessageOwner(\Bitrix\Mail\Item\Message $message, $userId): bool
	{
		return \Bitrix\Mail\MessageAccess::createForMessage($message, $userId)->isOwner();
	}

	public static function checkAccessForChat(int $chatId, int $userId): bool
	{
		if (Main\Loader::includeModule('im'))
		{
			$data = \CIMChat::GetChatData(['ID' => $chatId]);
			$userInChat = $data['userInChat'][$chatId] ?? [];
			if (in_array($userId, $userInChat, true))
			{
				return true;
			}
		}

		return false;
	}

	public static function checkAccessForCalendarEvent(int $calendarEventId, int $userId): bool
	{
		if (Main\Loader::includeModule('calendar'))
		{
			$attendeeIds = [];
			$entry = \CCalendarEvent::getEventForViewInterface($calendarEventId);

			if ($entry && isset($entry['ATTENDEE_LIST']))
			{
				foreach($entry['ATTENDEE_LIST'] as $attendee)
				{
					$attendeeId = (int)$attendee['id'];
					if ($attendeeId > 0)
					{
						$attendeeIds[] = $attendeeId;
					}
				}
			}

			if (in_array($userId, $attendeeIds, true))
			{
				return true;
			}
		}

		return false;
	}
}