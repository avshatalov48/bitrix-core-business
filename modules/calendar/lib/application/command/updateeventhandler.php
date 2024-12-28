<?php

namespace Bitrix\Calendar\Application\Command;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Application\AttendeeService;
use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Event\Event\AfterCalendarEventEdited;
use Bitrix\Calendar\Integration\Intranet\UserService;
use Bitrix\Calendar\Internals\Exception\EditException;
use Bitrix\Calendar\Internals\Exception\EventNotFound;
use Bitrix\Calendar\Internals\Exception\ExtranetPermissionDenied;
use Bitrix\Calendar\Internals\Exception\LocationBusy;
use Bitrix\Calendar\Internals\Exception\PermissionDenied;
use Bitrix\Calendar\Internals\Exception\SectionNotFound;
use Bitrix\Calendar\Rooms\AccessibilityManager;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;

class UpdateEventHandler implements CommandHandler
{
	private bool $canEdit;
	private bool $canEditAttendees;
	private bool $canEditLocation;

	public function __invoke(UpdateEventCommand $command): Event
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		// check if the current user has access to edit event operation
		$this->checkPermissions($command->getId(), $command->getUserId());

		$section = $this->getSection($mapperFactory, $command);
		$event = $this->getEvent($mapperFactory, $command->getId());
		$entryFields = $this->convertToArray($event, $mapperFactory);
		$currentFields = $entryFields;

		// base fields
		$entryFields = ($this->canEdit)
			? $this->editBaseFields($entryFields, $command, $event, $section)
			: $this->editPersonalFields($entryFields, $command);

		// attendees
		$entryFields = ($this->canEdit || $this->canEditAttendees)
			? $this->editAttendees($entryFields, $command, $event->getId(), $section)
			: $entryFields;

		// location
		$entryFields = ($this->canEdit || $this->canEditLocation)
			? $this->editLocation($entryFields, $command, $event->getId())
			: $entryFields;

		// update event
		$event = (new EventBuilderFromArray($entryFields))->build();
		$updatedEvent = $mapperFactory->getEvent()->update($event, [
			'checkPermission' => false,
			'UF' => $command->getUfFields(),
			'silentErrorMode' => false,
			'recursionEditMode' => $this->getRecursionEditMode($command),
			'currentEventDateFrom' => $command->getCurrentDateFrom(),
			'sendInvitesToDeclined' => $command->isSendInvitesAgain(),
			'requestUid' => $command->getRequestUid(),
			'checkLocationOccupancy' => $command->isCheckLocationOccupancy(),
			'userId' => \CCalendar::GetUserId(),
		]);

		if ($updatedEvent === null)
		{
			throw new EditException();
		}

		if ($updatedEvent && $updatedEvent->isMeeting())
		{
			$this->notifyEventAuthor($mapperFactory, $updatedEvent, $command->getUserId(), $currentFields);
		}

		(new AfterCalendarEventEdited($event->getId(), $command))->emit();

		return $updatedEvent;
	}

	private function editBaseFields(array $entryFields, UpdateEventCommand $command, Event $event, Section $section): array
	{
		$meetingHostId = $command->getMeetingHost() ? : $command->getUserId();
		$sectionId = $event->getSection()->getId();
		if ($event->getCalendarType() === $section->getType())
		{
			$sectionId = $command->getSectionId();
		}

		return array_merge($entryFields, [
			'DATE_FROM' => $command->getDateFrom(),
			'DATE_TO' => $command->getDateTo(),
			'SKIP_TIME' => $command->isSkipTime(),
			'DT_SKIP_TIME' => $command->isSkipTime() ? 'Y' : 'N',
			'TZ_FROM' => $command->getTimeZoneFrom(),
			'TZ_TO' => $command->getTimeZoneTo(),
			'NAME' => $command->getName(),
			'DESCRIPTION' => $command->getDescription(),
			'SECTIONS' => [$sectionId],
			'COLOR' => $command->getColor(),
			'ACCESSIBILITY' => $command->getAccessibility(),
			'IMPORTANCE' => $command->getImportance(),
			'PRIVATE_EVENT' => $command->isPrivate(),
			'RRULE' => $command->getRrule(),
			'REMIND' => $command->getRemindList(),
			'MEETING_HOST' => $meetingHostId,
			'OWNER_ID' => $command->getUserId(),
			'MEETING' => [
				'HOST_NAME' => \CCalendar::GetUserName($meetingHostId),
				'NOTIFY' => $command->isMeetingNotify(),
				'REINVITE' => $command->isMeetingReinvite(),
				'ALLOW_INVITE' => $command->isAllowInvite(),
				'MEETING_CREATOR' => $meetingHostId,
				'HIDE_GUESTS' => $command->isHideGuests(),
				'CHAT_ID' => $command->getChatId(),
			],
		]);
	}

	private function editPersonalFields(array $entryFields, UpdateEventCommand $command): array
	{
		return array_merge($entryFields, [
			'REMIND' => $command->getRemindList(),
		]);
	}

	private function editAttendees(array $entryFields, UpdateEventCommand $command, int $eventId, Section $section): array
	{
		$attendeeService = new AttendeeService();
		$attendeeCodes = $attendeeService->getAttendeeAccessCodes($command->getAttendeesEntityList(), $command->getUserId());
		$attendees = \CCalendar::GetDestinationUsers($attendeeCodes);
		$isMeeting = $attendeeService->isMeeting($attendeeCodes, $section, $command->getUserId());
		$attendeesAndCodes = $attendeeService->excludeAttendees($attendees, $attendeeCodes, $command->getExcludeUsers());

		$entryFields['ATTENDEES_CODES'] = $attendeesAndCodes['codes'];
		$entryFields['ATTENDEES'] = $attendeesAndCodes['attendees'];
		$entryFields['IS_MEETING'] = $isMeeting;

		$additionalExcludeUsers = [];
		if (
			$section->getType() === Dictionary::CALENDAR_TYPE['user']
			&& $section->getOwner()?->getId()
			&& $section->getOwner()?->getId() !== (int)$entryFields['MEETING_HOST']
		)
		{
			$additionalExcludeUsers[] = $section->getOwner()?->getId();
		}


		if ($isMeeting && $command->isPlannerFeatureEnabled())
		{
			$attendeesToCheck = array_diff($attendeesAndCodes['attendees'], [$entryFields['MEETING_HOST']]);
			$attendeeService->checkBusyAttendees($command, $attendeesToCheck, $eventId, $additionalExcludeUsers);
		}

		return $entryFields;
	}

	private function editLocation(array $entryFields, UpdateEventCommand $command, int $eventId): array
	{
		$entryFields['LOCATION'] = $command->getLocation();

		$params = [
			'ID' => $eventId,
			'SKIP_TIME' => $entryFields['SKIP_TIME'] ?? ($entryFields['DT_SKIP_TIME'] === 'Y'),
			'DATE_FROM' => $entryFields['DATE_FROM'],
			'DATE_TO' => $entryFields['DATE_TO'],
			'TZ_FROM' => $entryFields['TZ_FROM'],
			'TZ_TO' => $entryFields['TZ_TO'],
		];
		$isLocationBusy = !AccessibilityManager::checkAccessibility($command->getLocation(), ['fields' => $params]);
		if ($isLocationBusy)
		{
			throw new LocationBusy();
		}

		return $entryFields;
	}

	private function checkPermissions(int $eventId, int $userId): void
	{
		$controller = new EventAccessController($userId);
		$model = $this->getEventAccessModel($eventId, $userId);
		$this->canEdit = $controller->check(ActionDictionary::ACTION_EVENT_EDIT, $model);
		$this->canEditAttendees = $controller->check(ActionDictionary::ACTION_EVENT_EDIT_ATTENDEES, $model);
		$this->canEditLocation = $controller->check(ActionDictionary::ACTION_EVENT_EDIT_LOCATION, $model);

		if (!($this->canEdit || $this->canEditAttendees || $this->canEditLocation))
		{
			throw new PermissionDenied();
		}
	}

	private function getEvent(Factory $factoryMapper, int $eventId, $isParent = false): Event
	{
		$event = $factoryMapper->getEvent()->getById($eventId);
		if (!$event)
		{
			throw new EventNotFound();
		}

		return ($event->getId() !== $event->getParentId() && !$isParent)
			? $this->getEvent($factoryMapper, $event->getParentId(), true)
			: $event;
	}

	private function convertToArray(Event $event, Factory $mapper): array
	{
		$entryFields = $mapper->getEvent()->convertToArray($event);

		unset($entryFields['SECTION_ID']);
		unset($entryFields['RECURRENCE_ID']);

		return $entryFields;
	}

	private function getEventAccessModel(int $eventId, int $userId): EventModel
	{
		return \CCalendarEvent::getEventModelForPermissionCheck($eventId, [], $userId);
	}

	private function getSection(Factory $mapperFactory, UpdateEventCommand $command): Section
	{
		$section = $mapperFactory->getSection()->getById($command->getSectionId());
		if (!$section)
		{
			throw new SectionNotFound();
		}

		if (!in_array($section->getType(), ['user', 'group'], true) && (new UserService())->isNotIntranetUser($command->getUserId()))
		{
			throw new ExtranetPermissionDenied();
		}

		if (Util::isCollabUser($command->getUserId()) && !$section->isCollab())
		{
			throw new PermissionDenied();
		}

		return $section;
	}

	private function notifyEventAuthor(Factory $mapperFactory, Event $updatedEvent, int $userId, array $currentFields): void
	{
		$isMeeting = $updatedEvent->isMeeting();
		$meetingHostId = $updatedEvent->getEventHost()?->getId();
		$type = $updatedEvent->getCalendarType();

		if (!($isMeeting && $meetingHostId && $userId !== $meetingHostId && $type === 'user'))
		{
			return;
		}

		// TODO: refactor all this crap in the future
		$entryFields = $mapperFactory->getEvent()->convertToArray($updatedEvent);
		$entryChanges = \CCalendarEvent::CheckEntryChanges($entryFields, $currentFields);
		if (empty($entryChanges))
		{
			return;
		}

		$fromTo = \CCalendarEvent::GetEventFromToForUser($entryFields, $userId);
		\CCalendarNotify::Send([
			'mode' => 'change_notify',
			'name' => $entryFields['NAME'] ?? null,
			"from" => $fromTo['DATE_FROM'] ?? null,
			"to" => $fromTo['DATE_TO'] ?? null,
			"location" => \CCalendar::GetTextLocation($entryFields["LOCATION"] ?? null),
			"guestId" => $meetingHostId,
			"eventId" => $updatedEvent->getId(),
			"userId" => $userId,
			"fields" => $entryFields,
			"isSharing" => $updatedEvent->getCalendarType() === Dictionary::EVENT_TYPE['shared'],
			"entryChanges" => $entryChanges,
		]);
	}

	/**
	 * @throws PermissionDenied
	 */
	private function getRecursionEditMode(UpdateEventCommand $command): ?string
	{
		$recursionEditMode = $command->getRecEditMode();
		$canEditOnlyThis = $this->canEditAttendees && $this->canEditLocation && !$this->canEdit;

		if ($canEditOnlyThis && in_array($recursionEditMode, ['next', 'all']))
		{
			throw new PermissionDenied();
		}

		return $recursionEditMode;
	}
}
