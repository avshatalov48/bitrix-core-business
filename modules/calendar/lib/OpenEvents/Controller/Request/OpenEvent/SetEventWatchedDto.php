<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\OpenEvent;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class SetEventWatchedDto implements RequestDtoInterface
{
	/**
	 * @param int[] $eventIds
	 */
	public function __construct(
		public readonly array $eventIds
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			array_map('intval', $request['eventIds']),
		);
	}
}
