<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\Sync\Entity;
use Bitrix\Im\V2\Sync\Event;

class Messages implements Entity
{
	private array $messageIds = [];
	private array $addedMessageIds = [];
	private array $updatedMessageIds = [];
	private array $completeDeletedMessageIds = [];

	public function add(Event $event): void
	{
		$entityId = $event->entityId;

		if ($event->entityType === Event::UPDATED_MESSAGE_ENTITY)
		{
			$this->messageIds[$entityId] = $entityId;
			$this->updatedMessageIds[$entityId] = $entityId;
			return;
		}

		switch ($event->eventName)
		{
			case Event::COMPLETE_DELETE_EVENT:
				$this->completeDeletedMessageIds[$entityId] = $entityId;
				break;
			case Event::ADD_EVENT:
				$this->messageIds[$entityId] = $entityId;
				$this->addedMessageIds[$entityId] = $entityId;
				break;
			case Event::DELETE_EVENT:
				$this->messageIds[$entityId] = $entityId;
				$this->updatedMessageIds[$entityId] = $entityId;
				break;
		}
	}

	public function getMessageIds(): array
	{
		return $this->messageIds;
	}

	public static function getRestEntityName(): string
	{
		return 'messageSync';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'addedMessages' => array_diff($this->addedMessageIds, $this->updatedMessageIds),
			'updatedMessages' => array_diff($this->updatedMessageIds, $this->completeDeletedMessageIds),
			'completeDeletedMessages' => $this->completeDeletedMessageIds,
		];
	}
}
