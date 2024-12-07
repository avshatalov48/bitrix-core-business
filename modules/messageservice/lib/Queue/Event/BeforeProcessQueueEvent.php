<?php

namespace Bitrix\MessageService\Queue\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class BeforeProcessQueueEvent extends Event
{
	public const TYPE = 'OnBeforeProcessQueue';

	public function __construct()
	{
		parent::__construct('messageservice', self::TYPE);
	}

	public function canProcessQueue(): bool
	{
		foreach ($this->getResults() as $eventResult)
		{
			if ($eventResult->getType() === EventResult::ERROR)
			{
				return false;
			}
		}

		return true;
	}
}
