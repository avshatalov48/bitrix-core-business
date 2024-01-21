<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\Chat;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Sync\Entity;
use Bitrix\Im\V2\Sync\Event;

class Chats implements Entity
{
	private array $chatIds = [];
	private array $shortInfoChatIds = [];
	private bool $readAll = false;

	public function add(Event $event): void
	{
		$entityId = $event->entityId;
		switch ($event->eventName)
		{
			case Event::DELETE_EVENT:
				$this->shortInfoChatIds[$entityId] = $entityId;
				break;
			case Event::ADD_EVENT:
				$this->chatIds[$entityId] = $entityId;
				break;
			case Event::READ_ALL_EVENT:
				$this->readAll = true;
				break;
		}
	}

	public function getData(): array
	{
		$addedRecent = [];
		$addedChats = [];

		if (!empty($this->chatIds))
		{
			$addedRecent = Recent::get(null, ['JSON' => 'Y', 'CHAT_IDS' => $this->chatIds, 'SKIP_OPENLINES' => 'Y']);
			$addedChats = Chat::getList(['FILTER' => ['ID' => $this->chatIds], 'JSON' => true, 'SKIP_ACCESS_CHECK' => 'Y']);
		}

		$result = [
			'addedRecent' => $addedRecent,
			'addedChats' => $addedChats,
			'deletedChats' => $this->shortInfoChatIds,
		];

		if ($this->readAll)
		{
			$result['readAllChats'] = true;
		}

		return $result;
	}
}