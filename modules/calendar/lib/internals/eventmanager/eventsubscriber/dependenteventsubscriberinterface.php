<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

interface DependentEventSubscriberInterface
{
	public static function getDependencies(): array;

	public function handle(Event $event): EventResult;
}