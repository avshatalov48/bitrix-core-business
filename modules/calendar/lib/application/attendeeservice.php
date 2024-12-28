<?php

namespace Bitrix\Calendar\Application;

use Bitrix\Calendar\Application\Command\BusyAttendees;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Managers\Accessibility;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Internals\Exception\AttendeeBusy;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;

class AttendeeService
{
	public function getAttendeeAccessCodes(?array $attendeesEntityList, int $userId): array
	{
		$codes = [];
		if (is_array($attendeesEntityList))
		{
			$codes = Util::convertEntitiesToCodes($attendeesEntityList);
		}
		return \CCalendarEvent::handleAccessCodes($codes, ['userId' => $userId]);
	}

	public function isMeeting(array $accessCodes, Section $section, int $userId): bool
	{
		$calType = $section->getType() ?? '';

		return $accessCodes !== ['U'.$userId]
			|| in_array($calType, ['group', 'company_calendar', Dictionary::CALENDAR_TYPE['open_event']], true);
	}

	public function excludeAttendees(array $attendees, array $attendeesCodes, ?string $paramExcludeUsers): array
	{
		$excludeUsers = $this->getExcludeUsers($attendees, $paramExcludeUsers);
		if (!empty($excludeUsers))
		{
			$attendeesCodes = [];
			$attendees = array_diff($attendees, $excludeUsers);
			foreach($attendees as $attendee)
			{
				$attendeesCodes[] = 'U'. (int)$attendee;
			}
		}

		return [
			'attendees' => $attendees,
			'codes' => $attendeesCodes,
		];
	}

	public function getExcludeUsers(?array $attendees, ?string $paramsExcludeUsers): array
	{
		$excludeUsers = [];
		if ($paramsExcludeUsers && !empty($attendees))
		{
			$excludeUsers = explode(',', $paramsExcludeUsers);
		}

		return $excludeUsers;
	}

	/**
	 * @throws AttendeeBusy
	 */
	public function checkBusyAttendees(BusyAttendees $command, ?array $paramAttendees, ?int $eventId = null, array $additionalExcludeUsers = []): void
	{
		$attendees = [];
		$eventId = $eventId ?? 0;

		if ($command->isCheckCurrentUsersAccessibility())
		{
			$attendees = $paramAttendees;
		}
		else if (is_array($command->getNewAttendeesList()))
		{
			$excludedUsers = explode(',', $command->getExcludeUsers());
			$attendees = array_diff($command->getNewAttendeesList(), $excludedUsers);
		}

		$timezoneFrom = !empty($command->getTimeZoneFrom()) ? $command->getTimeZoneFrom() : \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
		$timezoneTo = !empty($command->getTimeZoneTo()) ? $command->getTimeZoneTo() : $timezoneFrom;
		$timezoneOffsetFrom = Util::getTimezoneOffsetUTC($timezoneFrom);
		$timezoneOffsetTo = Util::getTimezoneOffsetUTC($timezoneTo);
		$timestampFrom = \CCalendar::TimestampUTC($command->getDateFrom()) - $timezoneOffsetFrom;
		$timestampTo = \CCalendar::TimestampUTC($command->getDateTo()) - $timezoneOffsetTo;
		if ($command->isSkipTime())
		{
			$timestampTo += \CCalendar::GetDayLen();
		}

		$busyUsers = $this->getBusyUsersIds($attendees, $eventId, $timestampFrom, $timestampTo, $additionalExcludeUsers);
		if (!empty($busyUsers))
		{
			$busyUsersList = \CCalendarEvent::getUsersDetails($busyUsers);
			$busyUserName = current($busyUsersList)['DISPLAY_NAME'];
			throw (new AttendeeBusy())
				->setBusyUsersList($busyUsersList)
				->setAttendeeName($busyUserName)
			;
		}
	}

	private function getBusyUsersIds(array $attendees, int $curEventId, int $fromTs, int $toTs, array $additionalExcludeUsers): array
	{
		$usersToCheck = $this->getUsersToCheck($attendees, $additionalExcludeUsers);
		if (empty($usersToCheck))
		{
			return [];
		}

		return (new Accessibility())
			->setSkipEventId($curEventId)
			->getBusyUsersIds($usersToCheck, $fromTs, $toTs);
	}

	private function getUsersToCheck(array $attendees, array $additionalExcludeUsers): array
	{
		$usersToCheck = [];
		foreach ($attendees as $attId)
		{
			$attendeeId = (int)$attId;
			if ($attendeeId !== \CCalendar::GetUserId() && !in_array($attendeeId, $additionalExcludeUsers, true))
			{
				$userSettings = UserSettings::get($attendeeId);
				if ($userSettings && $userSettings['denyBusyInvitation'])
				{
					$usersToCheck[] = $attendeeId;
				}
			}
		}
		return $usersToCheck;
	}
}
