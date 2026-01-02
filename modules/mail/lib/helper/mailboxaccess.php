<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Internals\MailboxAccessTable;
use Bitrix\Mail\MailboxTable;

class MailboxAccess
{
	public static function isMailboxOwner(int $mailboxId, int $userId): bool
	{
		$query = MailboxTable::query()
			->setSelect(['ID'])
			->where('ID', $mailboxId)
			->where('USER_ID', $userId)
			->setLimit(1)
		;

		return (bool)$query->fetch();
	}

	public static function isMailboxSharedWithUser($mailboxId, $userId): bool
	{
			$accessCodes = \CAccess::GetUserCodesArray($userId) ?? [];
			if (empty($accessCodes))
			{
				return false;
			}

		$query = MailboxAccessTable::query()
			->setSelect(['ID'])
			->where('MAILBOX_ID', $mailboxId)
			->whereIn('ACCESS_CODE', $accessCodes)
			->setLimit(1)
		;

		return (bool)$query->fetch();
	}

	public static function hasCurrentUserAccessToMailbox(int $mailboxId, bool $withSharedMailboxes = false): bool
	{
		global $USER;

		if ($USER->isAdmin() && $USER->canDoOperation('bitrix24_config'))
		{
			return true;
		}

		$userId = (int)$USER->getId();
		if (!$userId)
		{
			return false;
		}

		if (self::isMailboxOwner($mailboxId, $userId))
		{
			return true;
		}

		if (!$withSharedMailboxes)
		{
			return false;
		}

		return self::isMailboxSharedWithUser($mailboxId, $userId);
	}
}