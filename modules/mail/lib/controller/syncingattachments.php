<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Mail\Helper\MessageAccess;
use Bitrix\Mail\Helper\AttachmentHelper;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;

class SyncingAttachments extends Controller
{
	public function resyncAttachmentsAction(int $messageId, int $mailboxId): bool
	{
		$currentUserId = $this->getCurrentUser()?->getId();

		if (is_null($currentUserId) || !Loader::includeModule('mail') || is_null($currentUserId))
		{
			return false;
		}

		if(!MessageAccess::isMailboxOwner($mailboxId, $currentUserId))
		{
			return false;
		}

		$messageAttachments = new AttachmentHelper($mailboxId, $messageId);

		return $messageAttachments->update();
	}
}