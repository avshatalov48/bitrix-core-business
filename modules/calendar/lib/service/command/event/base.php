<?php

namespace Bitrix\Calendar\Service\Command\Event;

use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Core\Managers\Accessibility;
use Bitrix\Calendar\Integration\Intranet\UserService;
use Bitrix\Calendar\Internals\Exception\AttendeeBusy;
use Bitrix\Calendar\Internals\Exception\ExtranetPermissionDenied;
use Bitrix\Calendar\Internals\Exception\SectionNotFound;
use Bitrix\Calendar\Service\Command\Result;
use Bitrix\Calendar\Service\SectionRepository;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;

abstract class Base
{
	private int $currentUserId;
	private int $sectionId;
	private SectionRepository $sectionRepository;
	private UserService $intranetUserService;
	private array $initialParams;
	private array $section = [];
	private EventAccessController $eventAccessController;

	public function __construct(array $params)
	{
		$this->initialParams = $params;
		$this->currentUserId = $this->initialParams['user_id'];
		$this->sectionId = $this->initialParams['section_id'];
		$this->eventAccessController = new EventAccessController($this->currentUserId);
	}

	public function getAccessController(): EventAccessController
	{
		return $this->eventAccessController;
	}

	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	public function getCurrentUserId(): int
	{
		return $this->currentUserId;
	}

	public function getSection(): array
	{
		if (!empty($this->section))
		{
			return $this->section;
		}

		$section = $this->sectionRepository->getSectionById($this->sectionId);
		if (!$section)
		{
			throw new SectionNotFound();
		}

		if ($section['CAL_TYPE'] !== 'group' && $this->intranetUserService->isNotIntranetUser($this->getCurrentUserId()))
		{
			throw new ExtranetPermissionDenied();
		}

		$this->section = $section;

		return $this->section;
	}

	public function getId(): ?int
	{
		return $this->initialParams['id'] ?? null;
	}

	public function getInitialParams(): array
	{
		return $this->initialParams;
	}

	public function setSectionRepository(SectionRepository $sectionRepository): self
	{
		$this->sectionRepository = $sectionRepository;
		return $this;
	}

	public function setIntranetUserService(UserService $intranetUserService): self
	{
		$this->intranetUserService = $intranetUserService;
		return $this;
	}

	abstract public function checkPermissions(): void;
	abstract public function execute(): Result;

	protected function getAttendeeAccessCodes(): array
	{
		$codes = [];
		if (isset($this->initialParams['attendees_entity_list']) && is_array($this->initialParams['attendees_entity_list']))
		{
			$codes = Util::convertEntitiesToCodes($this->initialParams['attendees_entity_list']);
		}
		return \CCalendarEvent::handleAccessCodes($codes, ['userId' => $this->getCurrentUserId()]);
	}

	protected function isMeeting(array $accessCodes): bool
	{
		$section = $this->getSection();
		$calType = $section['SECTION_CAL_TYPE'] ?? '';

		return $accessCodes !== ['U'.$this->getCurrentUserId()]
			|| in_array($calType, ['group', 'company_calendar'], true);
	}

	protected function excludeAttendees(array $entryFields): array
	{
		$excludeUsers = $this->getExcludeUsers($entryFields);
		if (!empty($excludeUsers))
		{
			$entryFields['ATTENDEES_CODES'] = [];
			$entryFields['ATTENDEES'] = array_diff($entryFields['ATTENDEES'], $excludeUsers);
			foreach($entryFields['ATTENDEES'] as $attendee)
			{
				$entryFields['ATTENDEES_CODES'][] = 'U'. (int)$attendee;
			}
		}

		return $entryFields;
	}

	protected function getExcludeUsers(array $entryFields): array
	{
		$excludeUsers = [];
		if ($this->initialParams['exclude_users'] && !empty($entryFields['ATTENDEES']))
		{
			$excludeUsers = explode(',', $this->initialParams['exclude_users']);
		}

		return $excludeUsers;
	}

	protected function checkBusyAttendies(array $entryFields): void
	{
		$isMeeting = $entryFields['IS_MEETING'] ?? false;
		if ($isMeeting && $this->initialParams['is_planner_feature_enabled'])
		{
			$attendees = [];
			if ($this->initialParams['check_current_users_accessibility'])
			{
				$attendees = $entryFields['ATTENDEES'];
			}
			else if (is_array($this->initialParams['new_attendees_list']))
			{
				$attendees = array_diff($this->initialParams['new_attendees_list'], $this->getExcludeUsers($entryFields));
			}

			$timezoneName = \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
			$timezoneOffset = Util::getTimezoneOffsetUTC($timezoneName);
			$timestampFrom = \CCalendar::TimestampUTC($this->initialParams['dates']['date_from']) - $timezoneOffset;
			$timestampTo = \CCalendar::TimestampUTC($this->initialParams['dates']['date_to']) - $timezoneOffset;
			if ($this->initialParams['dates']['skip_time'])
			{
				$timestampTo += \CCalendar::GetDayLen();
			}
			$busyUsers = $this->getBusyUsersIds($attendees, $this->initialParams['id'], $timestampFrom, $timestampTo);
			if (!empty($busyUsers))
			{
				$busyUsersList = \CCalendarEvent::getUsersDetails($busyUsers);
				$busyUserName = current($busyUsersList)['DISPLAY_NAME'];
				$attendeeBusyException = (new AttendeeBusy())
					->setBusyUsersList($busyUsersList)
					->setAttendeeName($busyUserName);

				throw $attendeeBusyException;
			}
		}
	}

	private function getBusyUsersIds(array $attendees, int $curEventId, int $fromTs, int $toTs): array
	{
		$usersToCheck = $this->getUsersToCheck($attendees);
		if (empty($usersToCheck))
		{
			return [];
		}

		return (new Accessibility())
			->setCheckPermissions(false)
			->setSkipEventId($curEventId)
			->getBusyUsersIds($usersToCheck, $fromTs, $toTs);
	}

	private function getUsersToCheck(array $attendees): array
	{
		$usersToCheck = [];
		foreach ($attendees as $attId)
		{
			if ((int)$attId !== \CCalendar::GetUserId())
			{
				$userSettings = UserSettings::get((int)$attId);
				if ($userSettings && $userSettings['denyBusyInvitation'])
				{
					$usersToCheck[] = (int)$attId;
				}
			}
		}
		return $usersToCheck;
	}
}