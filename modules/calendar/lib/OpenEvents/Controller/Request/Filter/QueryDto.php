<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\Filter;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class QueryDto implements RequestDtoInterface
{
	public function __construct(
		public readonly ?string $filterId = null,
		public readonly ?int $fromYear = null,
		public readonly ?int $fromMonth = null,
		public readonly ?int $fromDate = null,
		public readonly ?int $toYear = null,
		public readonly ?int $toMonth = null,
		public readonly ?int $toDate = null,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			filterId: $request['filterId'] ?? null,
			fromYear: $request['fromYear'] ? (int)$request['fromYear'] : null,
			fromMonth: $request['fromMonth'] ? (int)$request['fromMonth'] : null,
			fromDate: $request['fromDate'] ? (int)$request['fromDate'] : null,
			toYear: $request['toYear'] ? (int)$request['toYear'] : null,
			toMonth: $request['toMonth'] ? (int)$request['toMonth'] : null,
			toDate: $request['toDate'] ? (int)$request['toDate'] : null,
		);
	}
}
