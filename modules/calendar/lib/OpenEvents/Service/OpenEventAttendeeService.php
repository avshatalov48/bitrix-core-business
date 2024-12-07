<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Event\Service\OpenEventPullService;
use Bitrix\Calendar\OpenEvents\Controller\Request\OpenEvent\SetEventAttendeeStatusDto;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\EventRepository;
use Bitrix\Calendar\Integration;
use Bitrix\Calendar\Internals\EventAttendee;
use Bitrix\Calendar\Internals\EventAttendeeTable;
use Bitrix\Calendar\Internals\Exception\PermissionDenied;
use Bitrix\Calendar\OpenEvents\Dto\Category\PullEventUserFields;
use Bitrix\Calendar\OpenEvents\Dto\Category\PullEventUserFieldsBuilder;
use Bitrix\Calendar\OpenEvents\Exception\EventBusyException;
use Bitrix\Calendar\OpenEvents\Exception\MaxAttendeesReachedException;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\SystemException;
use CCalendarEvent;

final class OpenEventAttendeeService
{
	private static ?self $instance;
	private Factory $mapperFactory;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	/**
	 * @throws EventBusyException
	 * @throws MaxAttendeesReachedException
	 * @throws PermissionDenied
	 */
	public function setEventAttendeeStatus(int $userId, SetEventAttendeeStatusDto $setEventAttendeeStatusDto): void
	{
		$canAttend = EventAccessController::can(
			$userId,
			ActionDictionary::ACTION_OPEN_EVENT_ATTEND,
			$setEventAttendeeStatusDto->eventId
		);
		if (!$canAttend)
		{
			throw new PermissionDenied();
		}

		$existEventAttendee = EventAttendeeTable::query()
			->addSelect('*', 'EVENT')
			->where('OWNER_ID', $userId)
			->where('EVENT_ID', $setEventAttendeeStatusDto->eventId)
		 	->fetchObject();
		// if prev attendee status equals current, no need to go further
		if (($existEventAttendee?->getMeetingStatus() === 'Y') === $setEventAttendeeStatusDto->attendeeStatus)
		{
			return;
		}

		$locker = Application::getConnection(EventAttendeeTable::getConnectionName());
		$lockName = sprintf('open_event_attend_lock_%d', $setEventAttendeeStatusDto->eventId);

		try
		{
			$isLocked = $locker->lock($lockName, 10);
			if (!$isLocked)
			{
				throw new EventBusyException();
			}

			$eventMapper = $this->mapperFactory->getEvent();
			/** @var Event $event */
			$event = $eventMapper->getById($setEventAttendeeStatusDto->eventId);

			$maxAttendees = $event->getEventOption()?->getOptions()?->maxAttendees ?? 0;

			// check max count reach only while attend new user to event (increment attendee count)
			if ($maxAttendees > 0 && $setEventAttendeeStatusDto->attendeeStatus)
			{
				$attendeesCount = EventRepository::getEventAttendeesCount($setEventAttendeeStatusDto->eventId);

				if ($attendeesCount >= $event->getEventOption()->getOptions()->maxAttendees)
				{
					throw new MaxAttendeesReachedException();
				}
			}

			$this->setAttendee($userId, $event, $setEventAttendeeStatusDto->attendeeStatus, $existEventAttendee);
		}
		finally
		{
			$locker->unlock($lockName);
		}

		$imIntegrationService = ServiceLocator::getInstance()->get(Integration\Im\EventCategoryServiceInterface::class);
		$channelId = $event->getEventOption()->getCategory()->getChannelId();
		if ($setEventAttendeeStatusDto->attendeeStatus && !$imIntegrationService->hasAccess($userId, $channelId))
		{
			$imIntegrationService->includeUserToChannel($userId, $channelId);
		}

		\CCalendar::ClearCache(['event_list']);
	}

	private function setAttendee(
		int $userId,
		Event $event,
		bool $attendeeStatus,
		?EventAttendee $existEventAttendee = null
	): void
	{
		// TODO: need transaction?
		/** @var Connection $connection */
		$connection = Application::getInstance()->getConnection();
		$connection->startTransaction();
		$commited = false;

		try
		{
			$meetingStatus = $attendeeStatus ? 'Y' : 'N';
			if (!$existEventAttendee)
			{
				EventAttendeeTable::add([
					'OWNER_ID' => $userId,
					'CREATED_BY' => $userId,
					'MEETING_STATUS' => $meetingStatus,
					'EVENT_ID' => $event->getId(),
					'SECTION_ID' => $event->getSection()->getId(),
					'REMIND' => serialize(\CCalendarReminder::prepareReminder(
						(new \Bitrix\Calendar\Core\Mappers\Event())->convertToArray($event)['REMIND'])
					),
				]);
			}
			else
			{
				$existEventAttendee->setDeleted(!$attendeeStatus);
				$existEventAttendee->setMeetingStatus($meetingStatus);
				$existEventAttendee->save();
			}

			$eventOptions = $event->getEventOption();
			$increment = $attendeeStatus ? 1 : -1;
			$incrementStr = $attendeeStatus ? '+ 1' : '- 1';

			$updateResult = OpenEventOptionTable::update($eventOptions->getId(), [
				'ATTENDEES_COUNT' => new SqlExpression('?# ' . $incrementStr, 'ATTENDEES_COUNT'),
			]);

			if (!$updateResult->isSuccess())
			{
				// this rollback transaction and unlock entity for changes (in previous call)
				throw new SystemException('failed to update event options');
			}

			$connection->commitTransaction();
			$commited = true;

			$eventOptions->setAttendeesCount($eventOptions->getAttendeesCount() + $increment);

			OpenEventPullService::getInstance()->updateCalendarEvent(
				event: $event,
				userParams: [
					$userId => PullEventUserFieldsBuilder::build(new PullEventUserFields(
						isAttendee: $attendeeStatus,
					)),
				],
			);
		}
		finally
		{
			if (!$commited)
			{
				$connection->rollbackTransaction();
			}
		}

		// update original event searchable content field
		$this->updateEventSearchableContent($event);
	}

	private function __construct()
	{
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	private function updateEventSearchableContent(Event $event): void
	{
		// this query need to fill static cache for attendees in CCalendarEvent
		// it uses in next calls
		$oldEvent = CCalendarEvent::GetList([
			'checkPermission' => false,
			'fetchAttendees' => true,
			'arFilter' => [
				'ID' => $event->getId(),
			],
		]);
		$attendeeListData = CCalendarEvent::getAttendeeList($event->getId());
		$eventParams = [
			'ID' => $event->getId(),
			'NAME' => $event->getName(),
			'DESCRIPTION' => $event->getDescription(),
			'LOCATION' => $event->getLocation(),
			'IS_MEETING' => true,
			'ATTENDEE_LIST' => $attendeeListData['attendeeList'][$event->getId()] ?? [],
		];
		CCalendarEvent::updateSearchIndex(
			params: [
				'events' => [
					$eventParams,
				],
			],
		);
	}
}
