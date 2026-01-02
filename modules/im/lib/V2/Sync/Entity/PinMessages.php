<?php

namespace Bitrix\Im\V2\Sync\Entity;

use Bitrix\Im\V2\Link\Pin\PinCollection;
use Bitrix\Im\V2\Sync\Entity;
use Bitrix\Im\V2\Sync\Event;

class PinMessages implements Entity
{
	private array $pinIds = [];
	private array $deletedPinIds = [];
	private PinCollection $pins;

	public function add(Event $event): void
	{
		$entityId = $event->entityId;
		switch ($event->eventName)
		{
			case Event::DELETE_EVENT:
				$this->deletedPinIds[$entityId] = $entityId;
				break;
			case Event::ADD_EVENT:
				$this->pinIds[$entityId] = $entityId;
				break;
		}
	}

	public function getPinCollection(): PinCollection
	{
		$this->pins ??= new PinCollection($this->pinIds);

		return $this->pins;
	}

	public static function getRestEntityName(): string
	{
		return 'pinSync';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'addedPins' => $this->pinIds,
			'deletedPins' => $this->deletedPinIds,
		];
	}
}
