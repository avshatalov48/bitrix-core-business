<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class SetMuteEventCategoryDto implements RequestDtoInterface
{
	public function __construct(
		readonly public bool $muteState,
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			muteState: ($request['muteState'] ?? 'false') === 'true',
		);
	}
}
