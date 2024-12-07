<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class UpdateEventCategoryDto implements RequestDtoInterface
{
	public function __construct(
		public readonly ?string $name,
		public readonly ?string $description,
		public readonly ?bool $closed,
		public readonly ?array $attendees,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			name: ($request['name'] ?? null) ? (string)$request['name'] : null,
			description: ($request['description'] ?? null) ? (string)$request['description'] : null,
			closed: null,
			attendees: null,
		);
	}
}
