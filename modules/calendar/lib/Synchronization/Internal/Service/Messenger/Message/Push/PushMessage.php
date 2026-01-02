<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push;

use Bitrix\Main\Messenger\Entity\AbstractMessage;

class PushMessage extends AbstractMessage
{
	public function __construct(public readonly string $channelId, public readonly string $resourceId)
	{
	}

	public static function createFromData(array $data): self
	{
		return new static($data['channelId'], $data['resourceId']);
	}

	public function jsonSerialize(): array
	{
		return [
			'channelId' => $this->channelId,
			'resourceId' => $this->resourceId,
		];
	}
}
