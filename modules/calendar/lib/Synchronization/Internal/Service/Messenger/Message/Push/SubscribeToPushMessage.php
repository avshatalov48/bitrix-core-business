<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push;

use Bitrix\Main\Messenger\Entity\AbstractMessage;
use Bitrix\Main\Messenger\Entity\MessageInterface;

class SubscribeToPushMessage extends AbstractMessage
{
	public function __construct(public readonly int $connectionId)
	{
	}

	public function jsonSerialize(): mixed
	{
		return [
			'connectionId' => $this->connectionId,
		];
	}

	public static function createFromData(array $data): MessageInterface
	{
		return new static($data['connectionId']);
	}
}
