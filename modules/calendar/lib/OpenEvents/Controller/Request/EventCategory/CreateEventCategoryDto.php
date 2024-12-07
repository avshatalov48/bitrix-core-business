<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class CreateEventCategoryDto implements RequestDtoInterface
{
	public function __construct(
		public readonly string $name,
		public readonly string $description,
		public readonly bool $closed = false,
		public readonly array $attendees = [],
		public readonly array $departmentIds = [],
		public readonly ?int $channelId = null,

		public readonly ?bool $isPrimary = null,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		$channelId = $request['channelId'] ?? null;

		return new self(
			name: (string)$request['name'],
			description: (string)$request['description'],
			closed: $request['closed'] === 'true',
			attendees: $request['attendees'] ?? [],
			departmentIds: $request['departmentIds'] ?? [],
			channelId: $channelId ? (int)$channelId : null,
		);
	}
}
