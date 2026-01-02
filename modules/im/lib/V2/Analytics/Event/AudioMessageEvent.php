<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics\Event;

class AudioMessageEvent extends ChatEvent
{
	protected function getCategory(string $eventName): string
	{
		return 'audiomessage';
	}
}
