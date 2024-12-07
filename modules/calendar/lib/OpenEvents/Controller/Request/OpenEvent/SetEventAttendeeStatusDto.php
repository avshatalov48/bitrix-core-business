<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request\OpenEvent;

use Bitrix\Calendar\OpenEvents\Controller\Request\RequestDtoInterface;

final class SetEventAttendeeStatusDto implements RequestDtoInterface
{
	public function __construct(
		public readonly int $eventId,
		public readonly bool $attendeeStatus
	)
	{
	}

	public static function fromRequest(array $request): self
	{
		return new self(
			eventId: (int)$request['eventId'],
			attendeeStatus: $request['attendeeStatus'] === 'true',
		);
	}
}
