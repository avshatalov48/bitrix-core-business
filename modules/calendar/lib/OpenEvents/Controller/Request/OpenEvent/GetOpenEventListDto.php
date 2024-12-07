<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\OpenEvent;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class GetOpenEventListDto implements RequestDtoInterface
{
	public function __construct(
		public readonly ?int $categoryId = null,
		public readonly ?bool $onlyCurrentUser = null,
		public readonly ?bool $onlyWithComments = null,
		public readonly ?int $fromYear = null,
		public readonly ?int $fromMonth = null,
		public readonly ?int $toYear = null,
		public readonly ?int $toMonth = null,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			categoryId: isset($request['categoryId'])
				? (int)$request['categoryId']
				: null,
			onlyCurrentUser: isset($request['onlyCurrentUser'])
				? $request['onlyCurrentUser'] === 'true'
				: null,
			onlyWithComments: isset($request['onlyWithComments'])
				? $request['onlyWithComments'] === 'true'
				: null,
			fromYear: $request['fromYear'] ? (int)$request['fromYear'] : null,
			fromMonth: $request['fromMonth'] ? (int)$request['fromMonth'] : null,
			toYear: $request['toYear'] ? (int)$request['toYear'] : null,
			toMonth: $request['toMonth'] ? (int)$request['toMonth'] : null,
		);
	}
}
