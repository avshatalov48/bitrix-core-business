<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event;

use Bitrix\Calendar\Application\Command\CreateEventCommand;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\Event\AfterOpenEventCreated;
use Bitrix\Calendar\Integration\Im\OpenEventService as ImIntegration;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\Event\Dto\CreateChannelThreadForEventDto;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class CreateChannelThreadForEvent implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;
	use CalendarEventSubscriberTrait;

	public function __invoke(Event $event): EventResult
	{
		$calendarEvent = $this->getCalendarEvent($event);
		if (!$calendarEvent)
		{
			return $this->makeUndefinedResponse();
		}

		/** @var CreateEventCommand $createEventCommand */
		$createEventCommand = $event->getParameter('command');
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var EventCategory $eventCategory */
		$eventCategory = $mapperFactory->getEventCategory()->getById($createEventCommand->getCategory());

		$threadId = ImIntegration::getInstance()->sendCalendarEventMessage($calendarEvent, $eventCategory);

		return $this->makeSuccessResponse(new CreateChannelThreadForEventDto($threadId));
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventCreated::class,
		];
	}
}
