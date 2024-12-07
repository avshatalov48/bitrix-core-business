<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class SetBanDto implements RequestDtoInterface
{
	public function __construct(
		readonly public bool $banState,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			banState: ($request['banState'] ?? 'false') === 'true',
		);
	}
}
