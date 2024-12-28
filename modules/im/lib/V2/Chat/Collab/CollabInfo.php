<?php

namespace Bitrix\Im\V2\Chat\Collab;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\PopupDataItem;

class CollabInfo implements PopupDataItem
{
	use ContextCustomer;

	private Chat\CollabChat $chat;
	private GuestCounter $guestCounter;

	public function __construct(Chat\CollabChat $chat)
	{
		$this->chat = $chat;
		$this->guestCounter = new GuestCounter($this->chat);
		$this->setContext($chat->getContext());
	}

	public function merge(PopupDataItem $item): PopupDataItem
	{
		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'collabInfo';
	}

	public function toRestFormat(array $option = []): ?array
	{
		$groupId = (int)$this->chat->getEntityLink()->getEntityId();
		$entities = (new Chat\Collab\Entity\Factory($groupId))->getEntities();
		$rest = [
			'guestCount' => $this->guestCounter->getGuestCount(),
			'collabId' => $groupId,
			'entities' => [],
		];

		foreach ($entities as $entity)
		{
			if ($entity::isAvailable())
			{
				$rest['entities'][$entity::getRestEntityName()] = $entity->toRestFormat($option);
			}
		}

		return $rest;
	}
}
