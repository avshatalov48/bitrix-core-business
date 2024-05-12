<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Sync\Entity;
use Bitrix\Im\V2\Sync\Event;

class Messages implements Entity
{
	private array $messageIds = [];
	private array $addedMessageIds = [];
	private array $updatedMessageIds = [];
	private array $completeDeletedMessageIds = [];
	private MessageCollection $fullMessages;

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

	public function getFullMessages(): MessageCollection
	{
		$this->fullMessages ??= new MessageCollection($this->messageIds);

		return $this->fullMessages;
	}

	public function getData(): array
	{
		$fullMessages = $this->getFullMessages();
		[$messages, $updatedMessages] = $this->divideByEventType($fullMessages);

		return [
			'messages' => (new RestAdapter($messages))->toRestFormat(),
			'updatedMessages' => (new RestAdapter($updatedMessages))->toRestFormat(),
			'completeDeletedMessages' => $this->completeDeletedMessageIds,
		];
	}

	protected function divideByEventType(MessageCollection $messageCollection): array
	{
		$messageCollection->fillAllForRest();
		$addedMessages = clone $messageCollection;
		$updatedMessages = clone $messageCollection;

		$addedMessages->unsetByKeys(array_diff($this->messageIds, $this->addedMessageIds));
		$updatedMessages->unsetByKeys(array_diff($this->messageIds, $this->updatedMessageIds));

		return [$addedMessages, $updatedMessages];
	}
}