<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message;

use Bitrix\Main\Messenger\Entity\MessageInterface;

class SectionDeletedMessage extends SectionMessage
{
	public function __construct(
		int $sectionId,
		public readonly int $userId,
		public readonly string $vendorId
	)
	{
		parent::__construct($sectionId);
	}

	public function jsonSerialize(): mixed
	{
		return [
			'sectionId' => $this->sectionId,
			'userId' => $this->userId,
			'vendorId' => $this->vendorId,
		];
	}

	public static function createFromData(array $data): MessageInterface
	{
		return new static($data['sectionId'], $data['userId'], $data['vendorId']);
	}
}
