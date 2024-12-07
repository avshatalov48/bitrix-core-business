<?php

namespace Bitrix\Calendar\Internals\EventManager;

use Bitrix\Main\Event;

interface EventManagerInterface
{
	public function addEventHandler($fromModuleId, $eventType, $callback, $includeFile = false, $sort = 100);
	public function send(Event $event): void;
}
