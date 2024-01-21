<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\Sync\Event;

class EntityFactory
{
	/**
	 * @param Event[] $events
	 * @return array
	 */
	public function createEntities(array $events): array
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
					$messages->add($event);
					break;
				case Event::PIN_MESSAGE_ENTITY:
					$pins->add($event);
					break;
			}
		}

		return [$chats, $messages, $pins];
	}
}