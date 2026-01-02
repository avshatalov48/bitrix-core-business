<?php

namespace Bitrix\Calendar\Core\Queue\Processor;

use Bitrix\Calendar\Core\Queue\Interfaces;

class EventDelayedSync implements Interfaces\Processor
{
	public function process(Interfaces\Message $message): string
	{
		return self::ACK;
	}
}