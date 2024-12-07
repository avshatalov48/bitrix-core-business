<?php

namespace Bitrix\Calendar\EventOption\Service;

use Bitrix\Calendar\Application\Command\CreateEventCommand;
use Bitrix\Calendar\Application\Command\UpdateEventCommand;
use Bitrix\Calendar\Core\Builders\EventOption\EventOptionBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\EventOption\EventOption;
use Bitrix\Calendar\Core\EventOption\OptionsDto;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Main\DI\ServiceLocator;

final class EventOptionService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function onEventCreated(int $eventId, CreateEventCommand $createEventCommand, int $threadId): void
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$event = $mapperFactory->getEvent()->getById($eventId);
		if (!$event)
		{
			return;
		}

		// create event options entity only for open_event
		if (!$this->isOpenEvent($event))
		{
			return;
		}

		$eventOption = $this->createEventOption(
			eventId: $event->getId(),
			categoryId: $createEventCommand->getCategory(),
			threadId: $threadId,
			maxAttendees: $createEventCommand->getMaxAttendees(),
		);
		$event->setEventOption($eventOption);
	}

	public function onEventEdited(int $eventId, UpdateEventCommand $updateEventCommand): void
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$event = $mapperFactory->getEvent()->getById($eventId);
		if (!$event)
		{
			return;
		}

		if (!$this->isOpenEvent($event))
		{
			return;
		}

		if ($updateEventCommand->getMaxAttendees() === null)
		{
			return;
		}

		$eventOption = $event->getEventOption();
		$eventOption->setOptions(new OptionsDto($updateEventCommand->getMaxAttendees()));
		$mapperFactory->getEventOption()->update($eventOption);
	}

	private function createEventOption(
		int $eventId,
		int $categoryId,
		?int $threadId,
		?int $maxAttendees = 0
	): EventOption
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$eventOption = (new EventOptionBuilderFromArray([
			'EVENT_ID' => $eventId,
			'CATEGORY_ID' => $categoryId,
			'OPTIONS' => [
				'max_attendees' => $maxAttendees ?? 0,
			],
			'THREAD_ID' => $threadId
		]))->build();
		$eventOptionFactory = $mapperFactory->getEventOption();
		$eventOptionFactory->create($eventOption);

		return $eventOption;
	}

	private function isOpenEvent(Event $event): bool
	{
		$section = $event->getSection();

		return $section->getType() === Dictionary::CALENDAR_TYPE['open_event'];
	}

	private function __construct()
	{
	}
}
