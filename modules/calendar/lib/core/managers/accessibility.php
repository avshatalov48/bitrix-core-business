<?php

namespace Bitrix\Calendar\Core\Managers;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Sharing\Helper;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class Accessibility
{
	private array $canSeeNameCache = [];
	private int $skipEventId = 0;

	public function setSkipEventId(int $curEventId): self
	{
		$this->skipEventId = $curEventId;

		return $this;
	}

	/**
	 * @param array<int> $userIds
	 */
	public function getBusyUsersIds(array $userIds, int $timestampFrom, int $timestampTo): array
	{
		$dateFromTs = $timestampFrom - ($timestampFrom % \CCalendar::DAY_LENGTH);
		$dateToTs = $timestampTo - ($timestampTo % \CCalendar::DAY_LENGTH);
		$accessibility = $this
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

				if (Util::doIntervalsIntersect($timestampFrom, $timestampTo, $itemFrom, $itemTo))
				{
					$busyUsersList[] = $userId;
					continue 2;
				}
			}
		}

		return $busyUsersList;
	}

	/**
	 * @param array<int> $userIds
	 */
	public function getAccessibility(array $userIds, int $timestampFrom, int $timestampTo): array
	{
		$accessibilityTs = $this->getAccessibilityTs($userIds, $timestampFrom, $timestampTo);

		return $this->formatAccessibilityTs($accessibilityTs);
	}

	private function formatAccessibilityTs(array $accessibilityTs): array
	{
		return array_map(
			fn(array $userAccessibility) => array_map(
				fn(array $accessibilityItem) => array_merge($accessibilityItem, [
					'from' => $this->formatTimestamp($accessibilityItem['from'], $accessibilityItem['isFullDay']),
					'to' => $this->formatTimestamp($accessibilityItem['to'], $accessibilityItem['isFullDay']),
				]),
				$userAccessibility,
			),
			$accessibilityTs,
		);
	}

	private function formatTimestamp(int $timestamp, bool $isFullDay): string
	{
		if ($isFullDay)
		{
			return Util::formatDateTimestampUTC($timestamp);
		}

		return Util::formatDateTimeTimestampUTC($timestamp);
	}

	/**
	 * @param array<int> $userIds
	 */
	public function getAccessibilityTs(array $userIds, int $timestampFrom, int $timestampTo): array
	{
		$accessibility = [];

		if (empty($userIds))
		{
			return $accessibility;
		}

		$events = $this->getEventsTs($userIds, $timestampFrom, $timestampTo);
		$absences = $this->getAbsencesTs($userIds, $timestampFrom, $timestampTo);

		foreach ($userIds as $userId)
		{
			$accessibility[$userId] = array_merge($events[$userId] ?? [], $absences[$userId] ?? []);
		}

		return $accessibility;
	}

	/**
	 * @param array<int> $userIds
	 */
	public function getEventsTs(array $userIds, int $timestampFrom, int $timestampTo): array
	{
		[$from, $to] = $this->formatLimitFromTimestamps($timestampFrom, $timestampTo);
		$events = \CCalendarEvent::GetList([
			'arFilter' => [
				'FROM_LIMIT' => $from,
				'TO_LIMIT' => $to,
				'CAL_TYPE' => [
					Dictionary::CALENDAR_TYPE['user'],
					Dictionary::CALENDAR_TYPE['open_event'],
				],
				'OWNER_ID' => $userIds,
				'ACTIVE_SECTION' => 'Y'
			],
			'arSelect' => \CCalendarEvent::$defaultSelectEvent,
			'getUserfields' => false,
			'parseRecursion' => true,
			'fetchAttendees' => false,
			'fetchSection' => true,
			'parseDescription' => false,
			'setDefaultLimit' => false,
			'checkPermissions' => false,
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
				$from = \CCalendar::TimestampUTC($event['DATE_FROM']);
				$to = \CCalendar::TimestampUTC($event['DATE_TO']) + \CCalendar::GetDayLen();
			}
			else
			{
				$from = Helper::getEventTimestampUTC(new DateTime($event['DATE_FROM']), $event['TZ_FROM']);
				$to = Helper::getEventTimestampUTC(new DateTime($event['DATE_TO']), $event['TZ_TO']);
			}
			$accessibility[$event['OWNER_ID']][] = [
				'id' => (int)$event['ID'],
				'parentId' => (int)$event['PARENT_ID'],
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
		$eventId = (int)$event['ID'];
		$cachedValue = $this->canSeeNameCache[$eventId] ?? null;

		if ($cachedValue === null)
		{
			$accessController = new EventAccessController($currentUserId);
			$eventModel = EventModel::createFromArray($event);

			$canViewTitle = $accessController->check(ActionDictionary::ACTION_EVENT_VIEW_TITLE, $eventModel);
			$this->canSeeNameCache[$eventId] = !$this->isPrivate($event) && $canViewTitle;
		}

		return $this->canSeeNameCache[$eventId];
	}

	private function isPrivate(array $event): bool
	{
		$curUserId = \CCalendar::GetUserId();

		return $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] === 'user' && $event['OWNER_ID'] !== $curUserId;
	}

	/**
	 * @param array<int> $userIds
	 */
	public function getAbsencesTs(array $userIds, int $timestampFrom, int $timestampTo): array
	{
		if (!\CCalendar::IsIntranetEnabled())
		{
			return [];
		}

		[$from, $to] = $this->formatLimitFromTimestamps($timestampFrom, $timestampTo);
		$usersAbsence = \CIntranetUtils::GetAbsenceData(
			array(
				'DATE_START' => $from,
				'DATE_FINISH' => $to,
				'USERS' => $userIds,
				'PER_USER' => true,
			),
			BX_INTRANET_ABSENCE_HR,
		);

		$absenceTypes = \Bitrix\Intranet\UserAbsence::getVacationTypes();
		$vacationTypes = array_filter(
			$absenceTypes,
			fn ($type) => in_array($type['ID'], ['VACATION', 'LEAVESICK', 'LEAVEMATERINITY', 'LEAVEUNPAYED'], true),
		);
		$vacationTypesIds = array_map(fn ($type) => (int)$type['ENUM_ID'], $vacationTypes);

		$offset = (int)date('Z') + \CCalendar::GetOffset(\CCalendar::GetUserId());
		$accessibility = $this->initAccessibility($userIds);
		foreach($usersAbsence as $userId => $absenceData)
		{
			foreach($absenceData as $event)
			{
				$from = \CCalendar::TimestampUTC($event['DATE_ACTIVE_FROM']);
				$to = \CCalendar::TimestampUTC($event['DATE_ACTIVE_TO']);
				$isFullDay = $this->isFullDay($event['DATE_ACTIVE_FROM'], $event['DATE_ACTIVE_TO']);

				if ($this->isDateWithoutTimeOrIsMidnight($event['DATE_ACTIVE_TO']))
				{
					$to += \CCalendar::GetDayLen();
				}

				if (!$isFullDay)
				{
					$from -= $offset;
					$to -= $offset;
				}

				$accessibility[$userId][] = [
					'from' => $from,
					'to' => $to,
					'isFullDay' => $isFullDay,
					'name' => $event['PROPERTY_ABSENCE_TYPE_VALUE'] ?? null,
					'isVacation' => in_array((int)$event['PROPERTY_ABSENCE_TYPE_ENUM_ID'], $vacationTypesIds, true),
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

	private function formatLimitFromTimestamps(int $timestampFrom, int $timestampTo): array
	{
		return [
			Util::formatDateTimeTimestampUTC($timestampFrom),
			Util::formatDateTimeTimestampUTC($timestampTo),
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
}
