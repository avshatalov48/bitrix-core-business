<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Event\Event\AfterOpenEventCreated;
use Bitrix\Calendar\Internals\Counter\CounterService;
use Bitrix\Calendar\Internals\Counter\Event\EventDictionary;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class UpdateNewEventsNotify implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$eventId = (int)$event->getParameter('eventId');

		CounterService::addEvent(EventDictionary::OPEN_EVENT_CREATED, [
			'event_id' => $eventId,
		]);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventCreated::class,
		];
	}
}