<?php

namespace Bitrix\Calendar\OpenEvents\Provider\Event;

final class Filter
{
	public function __construct(
		public readonly ?array $categoriesIds = null,
		public ?string $fromDate = null,
		public ?string $toDate = null,
		public readonly ?int $creatorId = null,
		public readonly bool $deleted = false,
		public readonly ?bool $iAmAttendee = null,
		public readonly ?string $query = null,
	)
	{
	}
}
