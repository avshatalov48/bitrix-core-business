<?php

namespace Bitrix\MessageService\Queue\Event;

use Bitrix\Main\Event;

final class AfterProcessQueueEvent extends Event
{
	public const TYPE = 'OnAfterProcessQueue';

	public function __construct()
	{
		parent::__construct('messageservice', self::TYPE);
	}
}
