<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message\Push;

use Bitrix\Main\Messenger\Entity\AbstractMessage;
use Bitrix\Main\Messenger\Entity\MessageInterface;

class SubscribeSectionMessage extends AbstractMessage
{
	public function __construct(public readonly int $sectionId)
	{
	}

	public static function createFromData(array $data): MessageInterface
	{
		return new static($data['sectionId']);
	}

	public function jsonSerialize(): array
	{
		return [
			'sectionId' => $this->sectionId
		];
	}
}
