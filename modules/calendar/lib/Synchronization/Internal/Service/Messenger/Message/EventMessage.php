<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message;

use Bitrix\Main\Messenger\Entity\AbstractMessage;
use Bitrix\Main\Messenger\Entity\MessageInterface;

class EventMessage extends AbstractMessage
{
	public function __construct(public readonly int $eventId)
	{
	}

	public function jsonSerialize(): mixed
	{
		return [
			'eventId' => $this->eventId,
		];
	}

	public static function createFromData(array $data): MessageInterface
	{
		return new static($data['eventId']);
	}
}
