<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class ListDto implements RequestDtoInterface
{
	public function __construct(
		readonly public ?bool $isBanned = null,
		readonly public ?string $query = null,
		readonly public ?int $page = null,
		readonly public ?int $categoryId = null,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		$isBanned = $request['isBanned'] ?? null;
		$query = $request['query'] ?? null;
		$page = max(0, (int)($request['page'] ?? 0));
		$categoryId = $request['categoryId'] ?? null;

		return new self(
			isBanned: $isBanned === null ? null : $isBanned === 'true',
			query: $query,
			page: $page,
			categoryId: $categoryId,
		);
	}
}
