<?php

namespace Bitrix\Calendar\Core\Managers;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Sharing\Helper;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;

class Accessibility
{
	private bool $checkPermissions = true;
	private int $skipEventId = 0;

	public function setCheckPermissions(bool $checkPermissions): self
	{
		$this->checkPermissions = $checkPermissions;

		return $this;
	}

	public function setSkipEventId(int $curEventId): self
	{
		$this->skipEventId = $curEventId;

		return $this;
	}

	/**
	 * @param array<int> $userIds
	 * @param int $timestampFromUTC
	 * @param int $timestampToUTC
	 * @return array
	 */
	public function getBusyUsersIds(array $userIds, int $timestampFromUTC, int $timestampToUTC): array
	{
		$dateFromTs = \CCalendar::TimestampUTC(Util::formatDateTimestampUTC($timestampFromUTC));
		$dateToTs = \CCalendar::TimestampUTC(Util::formatDateTimestampUTC($timestampToUTC));
		$accessibility = $this
			->setCheckPermissions(false)
			->getAccessibility($userIds, $dateFromTs, $dateToTs)
		;

		$busyUsersList = [];
		$timezoneName = \CCalendar::GetUserTimezoneName(\CCalendar::GetUserId());
		$timezoneOffset = Util::getTimezoneOffsetUTC($timezoneName);
		foreach ($accessibility as $userId => $events)
		{
			foreach ($events as $accessibilityItem)
			{
				$itemFrom = \CCalendar::TimestampUTC($accessibilityItem['from']);
				$itemTo = \CCalendar::TimestampUTC($accessibilityItem['to']);

				if ($accessibilityItem['isFullDay'])
				{
					$itemFrom -= $timezoneOffset;
					$itemTo -= $timezoneOffset;
				}

				if ($this->doIntervalsIntersect($timestampFromUTC, $timestampToUTC, $itemFrom, $itemTo))
				{
					$busyUsersList[] = $userId;
				}
			}
		}

		return $busyUsersList;
	}
	
	/**
	 * @param array<int> $userIds
	 * @param int $timestampFromUTC
	 * @param int $timestampToUTC
	 * @return array
	 * @throws ObjectException
	 */
	public function getAccessibility(array $userIds, int $timestampFromUTC, int $timestampToUTC): array
	{
		$accessibility = [];
		
		if (empty($userIds))
		{
			return $accessibility;
		}
		
		$events = $this->getEvents($userIds, $timestampFromUTC, $timestampToUTC);
		$absences = $this->getAbsences($userIds, $timestampFromUTC, $timestampToUTC);

		foreach ($userIds as $userId)
		{
			$accessibility[$userId] = array_merge($events[$userId], $absences[$userId]);
		}

		return $accessibility;
	}
	
	/**
	 * @param array<int> $userIds
	 * @param int $timestampFromUTC
	 * @param int $timestampToUTC
	 * @return array
	 * @throws ObjectException
	 */
	public function getEvents(array $userIds, int $timestampFromUTC, int $timestampToUTC): array
	{
		[$from, $to] = $this->formatLimitFromTimestamps($timestampFromUTC, $timestampToUTC);
		$events = \CCalendarEvent::GetList([
			'arFilter' => [
				'FROM_LIMIT' => $from,
				'TO_LIMIT' => $to,
				'CAL_TYPE' => 'user',
				'OWNER_ID' => $userIds,
				'ACTIVE_SECTION' => 'Y'
			],
			'arSelect' => [
				'ID',
				'PARENT_ID',
				'OWNER_ID',
				'EVENT_TYPE',
				'NAME',
				'DATE_FROM',
				'DATE_TO',
				'TZ_FROM',
				'TZ_TO',
				'TZ_OFFSET_FROM',
				'TZ_OFFSET_TO',
				'DATE_FROM_TS_UTC',
				'DATE_TO_TS_UTC',
				'DT_SKIP_TIME',
				'ACCESSIBILITY',
				'IMPORTANCE',
				'RRULE',
				'EXDATE',
				'SECTION_ID',
				'CAL_TYPE',
				'MEETING_STATUS',
				'IS_MEETING',
				'DT_LENGTH',
				'PRIVATE_EVENT'
			],
			'parseRecursion' => true,
			'fetchAttendees' => false,
			'fetchSection' => true,
			'parseDescription' => false,
			'setDefaultLimit' => false,
			'checkPermissions' => $this->checkPermissions
		]);

		$accessibility = $this->initAccessibility($userIds);
		foreach ($events as $event)
		{
			if ((int)$event['ID'] === $this->skipEventId || (int)$event['PARENT_ID'] === $this->skipEventId)
			{
				continue;
			}
			if ($event['ACCESSIBILITY'] === 'free')
			{
				continue;
			}
			if ($event['IS_MEETING'] && $event['MEETING_STATUS'] === 'N')
			{
				continue;
			}
			if (\CCalendarSect::CheckGoogleVirtualSection($event['SECTION_DAV_XML_ID']))
			{
				continue;
			}

			$isFullDay = $event['DT_SKIP_TIME'] === 'Y';
			if ($isFullDay)
			{
				$from = Util::formatDateTimestampUTC(\CCalendar::TimestampUTC($event['DATE_FROM']));
				$to = Util::formatDateTimestampUTC(\CCalendar::TimestampUTC($event['DATE_TO']) + \CCalendar::GetDayLen());
			}
			else
			{
				$eventTsFromUTC = Helper::getEventTimestampUTC(new DateTime($event['DATE_FROM']), $event['TZ_FROM']);
				$eventTsToUTC = Helper::getEventTimestampUTC(new DateTime($event['DATE_TO']), $event['TZ_TO']);
				$from = Util::formatDateTimeTimestampUTC($eventTsFromUTC);
				$to = Util::formatDateTimeTimestampUTC($eventTsToUTC);
			}
			$accessibility[$event['OWNER_ID']][] = [
				'id' => $event['ID'],
				'name' => $this->getEventName($event),
				'from' => $from,
				'to' => $to,
				'isFullDay' => $isFullDay,
			];
		}

		return $accessibility;
	}

	private function getEventName(array $event): string
	{
		if (!$this->canSeeName($event))
		{
			return '[' . Loc::getMessage('EC_ACCESSIBILITY_BUSY') . ']';
		}

		return !empty($event['NAME']) ? $event['NAME'] : Loc::getMessage('EC_T_NEW_EVENT');
	}

	private function canSeeName(array $event): bool
	{
		$currentUserId = \CCalendar::GetUserId();
		$accessController = new EventAccessController($currentUserId);
		$eventModel = EventModel::createFromArray($event);

		return !$this->isPrivate($event) && $accessController->check(ActionDictionary::ACTION_EVENT_VIEW_TITLE, $eventModel);
	}

	private function isPrivate(array $event): bool
	{
		$curUserId = \CCalendar::GetUserId();

		return $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] === 'user' && $event['OWNER_ID'] !== $curUserId;
	}

	/**
	 * @param array<int> $userIds
	 * @param int $timestampFromUTC
	 * @param int $timestampToUTC
	 * @return array
	 */
	public function getAbsences(array $userIds, int $timestampFromUTC, int $timestampToUTC): array
	{
		if (!\CCalendar::IsIntranetEnabled())
		{
			return [];
		}

		[$from, $to] = $this->formatLimitFromTimestamps($timestampFromUTC, $timestampToUTC);
		$usersAbsence = \CIntranetUtils::GetAbsenceData(
			array(
				'DATE_START' => $from,
				'DATE_FINISH' => $to,
				'USERS' => $userIds,
				'PER_USER' => true,
			),
			BX_INTRANET_ABSENCE_HR
		);

		$serverOffset = (int)date('Z');
		$userOffset = \CCalendar::GetOffset(\CCalendar::GetUserId());
		$accessibility = $this->initAccessibility($userIds);
		foreach($usersAbsence as $userId => $absenceData)
		{
			foreach($absenceData as $event)
			{
				$fromUTC = \CCalendar::TimestampUTC($event['DATE_ACTIVE_FROM']);
				$toUTC = \CCalendar::TimestampUTC($event['DATE_ACTIVE_TO']);
				$isFullDay = $this->isFullDay($event['DATE_ACTIVE_FROM'], $event['DATE_ACTIVE_TO']);
				if ($this->isDateWithoutTimeOrIsMidnight($event['DATE_ACTIVE_TO']))
				{
					$toUTC += \CCalendar::GetDayLen();
				}

				if ($isFullDay)
				{
					$from = Util::formatDateTimestampUTC($fromUTC);
					$to = Util::formatDateTimestampUTC($toUTC);
				}
				else
				{
					$from = Util::formatDateTimeTimestampUTC($fromUTC - $serverOffset - $userOffset);
					$to = Util::formatDateTimeTimestampUTC($toUTC - $serverOffset - $userOffset);
				}
				$accessibility[$userId][] = [
					'from' => $from,
					'to' => $to,
					'isFullDay' => $isFullDay,
					'name' => $event['PROPERTY_ABSENCE_TYPE_VALUE'] ?? null,
				];
			}
		}

		return $accessibility;
	}

	private function isFullDay(string $from, string $to): bool
	{
		return $this->isDateWithoutTimeOrIsMidnight($from) && $this->isDateWithoutTimeOrIsMidnight($to);
	}

	private function isDateWithoutTimeOrIsMidnight(string $date): bool
	{
		return \CCalendar::TimestampUTC(Util::formatDateTimeTimestampUTC(\CCalendar::TimestampUTC($date)))
			=== \CCalendar::TimestampUTC(Util::formatDateTimestampUTC(\CCalendar::TimestampUTC($date)));
	}

	private function formatLimitFromTimestamps(int $timestampFromUTC, int $timestampToUTC): array
	{
		return [
			Util::formatDateTimeTimestampUTC($timestampFromUTC),
			Util::formatDateTimeTimestampUTC($timestampToUTC)
		];
	}

	private function initAccessibility(array $userIds): array
	{
		$accessibility = [];
		foreach($userIds as $userId)
		{
			$accessibility[$userId] = [];
		}

		return $accessibility;
	}

	private function doIntervalsIntersect($from1, $to1, $from2, $to2): bool
	{
		return $this->oneIntervalIntersectsAnother($from1, $to1, $from2, $to2)
			|| $this->oneIntervalIntersectsAnother($from2, $to2, $from1, $to1);
	}

	private function oneIntervalIntersectsAnother($from1, $to1, $from2, $to2): bool
	{
		$startsInside = $from2 <= $from1 && $from1 < $to2;
		$endsInside = $from2 < $to1 && $to1 <= $to2;
		$startsBeforeEndsAfter = $from1 <= $from2 && $to1 >= $to2;

		return $startsInside || $endsInside || $startsBeforeEndsAfter;
	}
}
