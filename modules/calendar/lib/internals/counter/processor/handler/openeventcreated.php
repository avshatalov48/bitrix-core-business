<?php

namespace Bitrix\Calendar\Internals\Counter\Processor\Handler;

use Bitrix\Calendar\Internals\Counter\Job\OpenEventAdded;

class OpenEventCreated
{
	public function __invoke(int $eventId): void
	{
		\Bitrix\Main\Update\Stepper::bindClass(
			OpenEventAdded::class,
			'calendar',
			0,
			[$eventId],
		);
	}
}
