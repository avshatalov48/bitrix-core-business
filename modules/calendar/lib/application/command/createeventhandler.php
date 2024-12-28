<?php

namespace Bitrix\Calendar\Application\Command;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\EventCategoryAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Application\AttendeeService;
use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Event\Event\AfterCalendarEventCreated;
use Bitrix\Calendar\Integration\Intranet\UserService;
use Bitrix\Calendar\Internals\Exception\AttendeeBusy;
use Bitrix\Calendar\Internals\Exception\EditException;
use Bitrix\Calendar\Internals\Exception\ExtranetPermissionDenied;
use Bitrix\Calendar\Internals\Exception\LocationBusy;
use Bitrix\Calendar\Internals\Exception\PermissionDenied;
use Bitrix\Calendar\Internals\Exception\SectionNotFound;
use Bitrix\Calendar\Rooms\AccessibilityManager;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;

class CreateEventHandler implements CommandHandler
{
	/**
	 * @throws AttendeeBusy
	 * @throws ExtranetPermissionDenied
	 * @throws LocationBusy
	 * @throws PermissionDenied
	 * @throws SectionNotFound
	 */
	public function __invoke(CreateEventCommand $command): Event
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$section = $this->getSection($mapperFactory, $command);

		// check if the current user has access to create event operation
		$this->checkPermissions($command->getUserId(), $section, $command->getCategory());

		// convert array into domain Event
		$event = (new EventBuilderFromArray($this->getEventFields($command, $section)))->build();
		if ($event->isOpenEvent())
		{
			$event->setAttendeesCollection(null);
			$event->setOwner(null);
		}

		// save Event
		$createdEvent = $mapperFactory->getEvent()->create($event, [
			'UF' => $command->getUfFields(),
			'silentErrorMode' => false,
			'recursionEditMode' => $command->getRecEditMode(),
			'currentEventDateFrom' => $command->getCurrentDateFrom(),
			'sendInvitesToDeclined' => $command->isSendInvitesAgain(),
			'requestUid' => $command->getRequestUid(),
			'checkLocationOccupancy' => $command->isCheckLocationOccupancy(),
		]);

		if ($createdEvent === null)
		{
			throw new EditException();
		}

		(new AfterCalendarEventCreated($createdEvent->getId(), $command))->emit();

		return $createdEvent;
	}

	/**
	 * @throws PermissionDenied
	 */
	private function checkPermissions(int $userId, Section $section, ?int $categoryId = null): void
	{
		$eventAccessController = new EventAccessController($userId);
		$eventModel = $this->getEventModel($section);
		$canAdd = $eventAccessController->check(ActionDictionary::ACTION_EVENT_ADD, $eventModel);
		if (!$canAdd)
		{
			throw new PermissionDenied();
		}

		// check permission for open_event
		if ($categoryId !== null)
		{
			$canPostAtCategory = EventCategoryAccessController::can(
				$userId,
				ActionDictionary::ACTION_EVENT_CATEGORY_POST,
				$categoryId
			);

			if (!$canPostAtCategory)
			{
				throw new PermissionDenied();
			}
		}

		if (Util::isCollabUser($userId) && !$section->isCollab())
		{
			throw new PermissionDenied();
		}
	}

	/**
	 * @throws AttendeeBusy
	 * @throws LocationBusy
	 */
	private function getEventFields(CreateEventCommand $command, Section $section): array
	{
		$meetingHostId = $command->getMeetingHost() ? : $command->getUserId();

		$entryFields = [
			'ID' => 0,
			'DATE_FROM' => $command->getDateFrom(),
			'DATE_TO' => $command->getDateTo(),
			'SKIP_TIME' => $command->isSkipTime(),
 			'DT_SKIP_TIME' => $command->isSkipTime() ? 'Y' : 'N',
			'TZ_FROM' => $command->getTimeZoneFrom(),
			'TZ_TO' => $command->getTimeZoneTo(),
			'NAME' => $command->getName(),
			'DESCRIPTION' => $command->getDescription(),
			'SECTIONS' => [$command->getSectionId()],
			'COLOR' => $command->getColor(),
			'ACCESSIBILITY' => $command->getAccessibility(),
			'IMPORTANCE' => $command->getImportance(),
			'PRIVATE_EVENT' => $command->isPrivate(),
			'RRULE' => $command->getRrule(),
			'REMIND' => $command->getRemindList(),
			'SECTION_CAL_TYPE' => $section->getType(),
			'SECTION_OWNER_ID' => $section->getOwner()?->getId(),
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
		];

		// Attendees
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
			&& $section->getOwner()?->getId() !== $meetingHostId
		)
		{
			$additionalExcludeUsers[] = $section->getOwner()?->getId();
		}

		if ($isMeeting && $command->isPlannerFeatureEnabled())
		{
			$attendeeService->checkBusyAttendees(
				command: $command,
				paramAttendees: $attendeesAndCodes['attendees'],
				additionalExcludeUsers: $additionalExcludeUsers,
			);
		}

		// Location
		$entryFields['LOCATION'] = $command->getLocation();
		$isLocationBusy = !AccessibilityManager::checkAccessibility($command->getLocation(), ['fields' => $entryFields]);
		if ($isLocationBusy)
		{
			throw new LocationBusy();
		}

		return $entryFields;
	}

	private function getEventModel(Section $section): EventModel
	{
		return
			EventModel::createNew()
				->setOwnerId($section->getOwner()?->getId() ?? 0)
				->setSectionId($section->getId())
				->setSectionType($section->getType() ?? '')
			;
	}

	/**
	 * @throws ExtranetPermissionDenied
	 * @throws SectionNotFound
	 */
	public function getSection(Factory $mapperFactory, CreateEventCommand $command): Section
	{
		/** @var Section $section */
		$section = $mapperFactory->getSection()->getById($command->getSectionId());
		if (!$section)
		{
			throw new SectionNotFound();
		}

		if (!in_array($section->getType(), ['user', 'group'], true) && (new UserService())->isNotIntranetUser($command->getUserId()))
		{
			throw new ExtranetPermissionDenied();
		}

		return $section;
	}
}
