<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Sync\Entity;
use Bitrix\Im\V2\Sync\Event;

class Messages implements Entity
{
	private array $messageIds = [];
	private array $completeDeletedMessageIds = [];

	public function add(Event $event): void
	{
		$entityId = $event->entityId;
		switch ($event->eventName)
		{
			case Event::COMPLETE_DELETE_EVENT:
				$this->completeDeletedMessageIds[$entityId] = $entityId;
				break;
			case Event::ADD_EVENT:
			case Event::DELETE_EVENT:
				$this->messageIds[$entityId] = $entityId;
				break;
		}
	}

	public function getData(): array
	{
		$fullMessage = new MessageCollection($this->messageIds);

		return [
			'messages' => (new RestAdapter($fullMessage))->toRestFormat(),
			'completeDeletedMessages' => $this->completeDeletedMessageIds,
		];
	}
}