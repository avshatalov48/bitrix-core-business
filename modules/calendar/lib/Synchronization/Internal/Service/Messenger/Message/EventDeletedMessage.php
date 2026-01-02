<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message;

use Bitrix\Main\Messenger\Entity\MessageInterface;

class EventDeletedMessage extends EventMessage
{
	public function __construct(
		int $eventId,
		public readonly int $userId,
		public readonly string $vendorEventId,
		public readonly string $vendorSectionId
	)
	{
		parent::__construct($eventId);
	}

	public function jsonSerialize(): mixed
	{
		return [
			'eventId' => $this->eventId,
			'userId' => $this->userId,
			'vendorEventId' => $this->vendorEventId,
			'vendorSectionId' => $this->vendorSectionId,
		];
	}

	public static function createFromData(array $data): MessageInterface
	{
		return new static($data['eventId'], $data['userId'], $data['vendorEventId'], $data['vendorSectionId']);
	}
}
