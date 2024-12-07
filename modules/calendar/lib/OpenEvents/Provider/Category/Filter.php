<?php

namespace Bitrix\Calendar\OpenEvents\Provider\Category;

final class Filter
{
	public function __construct(
		public readonly ?string $query = null,
		public readonly ?bool $isBanned = null,
		public readonly ?int $channelId = null,
		public readonly ?int $categoryId = null,
	)
	{
	}
}
