<?php

namespace Bitrix\Vote\Attachment;

use Bitrix\Vote\Attach;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Loader;
use Bitrix\Vote\Integration\Im\ImVoteStopEventHandler;

final class ImMessageConnector extends Connector
{

	public function canRead($userId): bool
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			return false;
		}

		if ($this->entityId === null)
		{
			return true;
		}

		if (!Loader::includeModule('im'))
		{
			return false;
		}

		$message = new Message($this->entityId);

		return $message
			->getChat()
			->checkAccess($userId)
			->isSuccess()
		;
	}

	public function canEdit($userId): bool
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			return false;
		}

		if ($this->entityId === null)
		{
			return true;
		}

		if (!Loader::includeModule('im'))
		{
			return false;
		}

		$message = new Message($this->entityId);

		return $message->getAuthorId() === $userId
			&& $message
				->getChat()
				->checkAccess($userId)
				->isSuccess()
		;
	}

	public function onVoteStop(Attach $attach): void
	{
		$handler = new ImVoteStopEventHandler((int)$attach->getVoteId(), (int)$attach->getEntityId());
		$handler->handle();
	}
}