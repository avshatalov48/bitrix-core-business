<?php

namespace Bitrix\Im\V2\Relation;

use Bitrix\Im\V2\Chat\ExternalChat;
use Bitrix\Im\V2\Chat\ExternalChat\Event\FilterUsersByAccessEvent;

class ExternalChatRelations extends ChatRelations
{
	protected const NEED_ADDITIONAL_FILTER_BY_ACCESS = true;

	public function filterUserIdsByAccess(array $userIds): array
	{
		$chat = ExternalChat::getInstance($this->chatId);
		if (!$chat instanceof ExternalChat)
		{
			return $userIds;
		}

		$event = new FilterUsersByAccessEvent($chat, $userIds);
		$event->send();
		if (!$event->hasResult())
		{
			return $userIds;
		}

		$result = $event->getResult();
		$usersWithAccess = $result->getResult()['userIds'] ?? [];
		self::deleteUsersWithoutAccess($this->chatId, $userIds, $usersWithAccess);

		return $usersWithAccess;
	}

	private static function deleteUsersWithoutAccess(int $chatId, array $userIds, array $usersWithAccess): void
	{
		$cleaner = LazyCleaner::getInstance();
		if (!$cleaner->canMarkForDeletion())
		{
			return;
		}

		$toDelete = array_diff($userIds, $usersWithAccess);

		foreach ($toDelete as $userId)
		{
			$canMark = LazyCleaner::getInstance()->markForDeletion($chatId, $userId);
			if (!$canMark)
			{
				break;
			}
		}
	}
}