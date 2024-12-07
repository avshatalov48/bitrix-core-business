<?php

namespace Bitrix\Calendar\Internals\Counter\Processor\Handler;

class OpenEventDeleted
{
	public function __invoke(int $eventId, int $categoryId): void
	{
		\Bitrix\Main\Update\Stepper::bindClass(
			\Bitrix\Calendar\Internals\Counter\Job\OpenEventDeleted::class,
			'calendar',
			10,
			[$eventId, $categoryId],
		);
	}
}
