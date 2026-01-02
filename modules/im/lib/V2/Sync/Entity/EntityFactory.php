<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\Sync\Entities;
use Bitrix\Im\V2\Sync\Event;

class EntityFactory
{
	/**
	 * @param Event[] $events
	 * @return Entities
	 */
	public function createEntities(array $events): Entities
	{
		$messages = new Messages();
		$chats = new Chats();
		$pins = new PinMessages();

		foreach ($events as $event)
		{
			switch ($event->entityType)
			{
				case Event::CHAT_ENTITY:
					$chats->add($event);
					break;
				case Event::MESSAGE_ENTITY:
				case Event::UPDATED_MESSAGE_ENTITY:
					$messages->add($event);
					break;
				case Event::PIN_MESSAGE_ENTITY:
					$pins->add($event);
					break;
			}
		}

		$dialogIds = new DialogIds($chats);

		return new Entities($chats, $messages, $pins, $dialogIds);
	}
}
