<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

interface EventSubscriberInterface
{
	public function __invoke(Event $event): EventResult;

	/**
	 * @return string[]
	 */
	public function getEventClasses(): array;
}