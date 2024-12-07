<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventCategory;

use Bitrix\Calendar\Core\Event\Event as CalendarEvent;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\Event\AfterOpenEventCreated;
use Bitrix\Calendar\Event\Event\AfterOpenEventDeleted;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberResponseTrait;
use Bitrix\Calendar\OpenEvents\Service\CategoryService;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class UpdateLastActivity implements EventSubscriberInterface
{
	use EventSubscriberResponseTrait;

	public function __invoke(Event $event): EventResult
	{
		$eventId = (int)$event->getParameter('eventId');

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var CalendarEvent $event */
		$calendarEvent = $mapperFactory->getEvent()->getById($eventId);

		$categoryId = $calendarEvent->getEventOption()->getCategoryId();
		CategoryService::getInstance()->updateLastActivity($categoryId);

		return $this->makeSuccessResponse();
	}

	public function getEventClasses(): array
	{
		return [
			AfterOpenEventCreated::class,
			AfterOpenEventDeleted::class,
		];
	}
}
