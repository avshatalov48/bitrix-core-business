<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage calendar
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Calendar\Rooms;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

class OccupancyChecker
{
	private const OPTION_NAME = 'isOccupancyCheckerEnabled';
	private const OPTION_ENABLED = 'Y';
	private const OPTION_DISABLED = 'N';

	private const CHECK_LIMIT = 2 * 365 * 86400;
	private const DISTURBING_EVENTS_LIMIT = 3;
	private const KEY_PART_START = 'start';
	private const KEY_PART_END = 'end';

	private array $timeline = [];
	private int $checkEventId = 0;
	private array $cachedEvents = [];

	public function __construct()
	{

	}

	/**
	 * Checks if saving event location booking has intersection with other events location booking.
	 * If so, returns \Bitrix\Main\Result object with disturbing event data.
	 * @param array $event
	 * @return Result
	 */
	public function check(array $event): Result
	{
		$result = new Result();

		if (!$this->canCheck($event))
		{
			return $result;
		}

		$location = Util::parseLocation($event['LOCATION']['NEW']);
		$roomId = $location['room_id'];

		if (!$roomId)
		{
			return $result;
		}

		$eventsForCheck = $this->getEventsForCheck($event);

		$existingEvents = $this->getExistingEvents($roomId, $event);

		$this->fillTimeline($event, $eventsForCheck, $existingEvents);
		asort($this->timeline, SORT_NUMERIC);

		$checkingEventsStartedCount = 0;
		$existingEventsStartedCount = 0;
		$disturbingEventDates = [];
		$isDisturbingEventsAmountOverShowLimit = false;
		foreach ($this->timeline as $key => $value)
		{
			[$eventId, $repeatNumber, $isEnd] = $this->parseTimelineKey($key);
			if ($eventId === $this->checkEventId)
			{
				if ($isEnd)
				{
					$checkingEventsStartedCount--;
				}
				else
				{
					$checkingEventsStartedCount++;
				}
			}
			else
			{
				if ($isEnd)
				{
					$existingEventsStartedCount--;
				}
				else
				{
					$existingEventsStartedCount++;
				}
			}

			if ($checkingEventsStartedCount > 0 && $existingEventsStartedCount > 0)
			{
				$disturbingEvent = $this->cachedEvents[$this->getCachedEventKey($eventId, $repeatNumber)];;
				if (count($disturbingEventDates) >= self::DISTURBING_EVENTS_LIMIT)
				{
					$isDisturbingEventsAmountOverShowLimit = true;
					break;
				}
				$disturbingEventDate = $this->getEventFormattedDate($disturbingEvent);
				if (!in_array($disturbingEventDate, $disturbingEventDates))
				{
					$disturbingEventDates[] = $disturbingEventDate;
				}
			}
		}

		if (!empty($disturbingEventDates))
		{
			$result->addError(new Error('ROOM IS OCCUPIED'));
			$result->setData([
				'disturbingEventsFormatted' => implode(', ', $disturbingEventDates),
				'isDisturbingEventsAmountOverShowLimit' => $isDisturbingEventsAmountOverShowLimit,
			]);
		}

		return $result;
	}

	/**
	 * @param array $event
	 * @return bool
	 */
	private function canCheck(array $event): bool
	{
		return
			$this->isEnabled()
			&& !empty($event['LOCATION']['NEW'])
			&& !empty($event['DATE_FROM_TS_UTC'])
			&& !empty($event['DATE_TO_TS_UTC'])
			&& !empty($event['RRULE'])
			&& !empty($event['DATE_FROM'])
			&& !empty($event['DATE_TO'])
		;
	}

	/**
	 * @return bool
	 */
	private function isEnabled(): bool
	{
		return Option::get('calendar', self::OPTION_NAME, self::OPTION_ENABLED, '-') !== self::OPTION_DISABLED;
	}

	/**
	 * @param array $event
	 * @return array
	 */
	protected function getEventsForCheck(array $event): array
	{
		$checkedEvents = [];
		$toLimit = $this->getCheckLimit($event);

		\CCalendarEvent::ParseRecursion(
			$checkedEvents,
			$event,
			[
				'userId' => \CCalendar::GetCurUserId(),
				'fromLimit' => null,
				'toLimitTs' => $toLimit,
				'loadLimit' =>  null,
				'instanceCount' => false,
				'preciseLimits' => false,
				'checkPermission' => false,
			]
		);

		return $checkedEvents;
	}

	/**
	 * @param array $event
	 * @return int
	 */
	private function getCheckLimit(array $event): int
	{
		return min($event['DATE_FROM_TS_UTC'] + self::CHECK_LIMIT, $event['DATE_TO_TS_UTC']);
	}

	/**
	 * @param array $event
	 * @return array
	 * @throws ObjectException
	 */
	private function getEventTimestamps(array $event): array
	{
		$dateFrom = new DateTime($event['DATE_FROM']);
		$dateTo = new DateTime($event['DATE_TO']);
		$timezoneFrom = $event['TZ_FROM'] ?? null;
		$timezoneTo = $event['TZ_TO'] ?? null;
		$timestampFrom = \Bitrix\Calendar\Util::getDateTimestampUtc($dateFrom, $timezoneFrom);
		$timestampTo = \Bitrix\Calendar\Util::getDateTimestampUtc($dateTo, $timezoneTo);
		if (($timestampFrom === $timestampTo) || (($event['DT_SKIP_TIME'] ?? null) === 'Y'))
		{
			$timestampTo += \CCalendar::GetDayLen();
		}

		//This is done to check weak inequality
		$timestampFrom++;
		$timestampTo--;

		return [$timestampFrom, $timestampTo];
	}

	/**
	 * @param int $roomId
	 * @param array $event
	 * @return array|null
	 */
	protected function getExistingEvents(int $roomId, array $event): ?array
	{
		$arSelect = [
			'ID',
			'RRULE',
			'DT_SKIP_TIME',
			'DT_LENGTH',
			'PARENT_ID',
			'DATE_FROM',
			'DATE_TO',
			'PARENT_ID',
			'TZ_FROM',
			'TZ_TO',
			'TZ_OFFSET_FROM',
			'TZ_OFFSET_TO',
			'DATE_FROM_TS_UTC',
			'DATE_TO_TS_UTC',
			'CREATED_BY',
			'ACCESSIBILITY',
			'REMIND',
			'MEETING_HOST',
			'MEETING_STATUS',
			'IMPORTANCE',
		];

		$toLimit = $this->getCheckLimit($event);

		return \CCalendarEvent::GetList(
			[
				'arSelect' => $arSelect,
				'arFilter' => [
					'SECTION' => [$roomId],
					'FROM_LIMIT' => $event['DATE_FROM'],
					'TO_LIMIT' => DateTime::createFromTimestamp($toLimit)->toString(),
					'DELETED' => 'N',
					'ACTIVE' => 'Y'
				],
				'parseRecursion' => true,
				'fetchAttendees' => false,
				'fetchMeetings' => false,
				'setDefaultLimit' => false,
				'limit' => null,
				'checkPermissions' => false,
				'parseDescription' => false,
				'fetchSection' => false,
				'getUserfields' => false,
			]
		);
	}

	/**
	 * @param array $event
	 * @param array $eventsForCheck
	 * @param array $existingEvents
	 * @return void
	 */
	private function fillTimeline(array $event, array $eventsForCheck, array $existingEvents): void
	{
		if (!empty($event['ID']) && $event['ID'] > 0)
		{
			$this->checkEventId = $event['ID'];
		}

		$currentIndex = 1;
		$previousEventId = 0;
		foreach ($existingEvents as $existingEvent)
		{
			if (!$this->canFindIntersections($event, $existingEvent))
			{
				continue;
			}

			$currentEventId = (int)$existingEvent['PARENT_ID'];
			if ($currentEventId !== $previousEventId)
			{
				$previousEventId = $currentEventId;
				$currentIndex = 1;
			}

			try
			{
				[$timestampFrom, $timestampTo] = $this->getEventTimestamps($existingEvent);

				$this->timeline[$this->getTimelineKey($currentEventId, $currentIndex, false)] = $timestampFrom;
				$this->timeline[$this->getTimelineKey($currentEventId, $currentIndex, true)] = $timestampTo;
				$this->cachedEvents[$this->getCachedEventKey($currentEventId, $currentIndex)] = [
					'ID' => $currentEventId,
					'DATE_FROM' => $existingEvent['DATE_FROM'],
					'TZ_FROM' => $existingEvent['TZ_FROM'],
				];
				$currentIndex++;
			}
			catch (ObjectException $exception)
			{
			}
		}

		$currentIndex = 1;
		foreach ($eventsForCheck as $eventForCheck)
		{
			if (empty($eventForCheck['DATE_FROM']) || empty($eventForCheck['DATE_TO']))
			{
				continue;
			}
			[$timestampFrom, $timestampTo] = $this->getEventTimestamps($eventForCheck);

			$this->timeline[$this->getTimelineKey($this->checkEventId, $currentIndex, false)] = $timestampFrom;
			$this->timeline[$this->getTimelineKey($this->checkEventId, $currentIndex, true)] = $timestampTo;
			$this->cachedEvents[$this->getCachedEventKey($eventForCheck['ID'], $currentIndex)] = [
				'ID' => $eventForCheck['ID'],
				'DATE_FROM' => $eventForCheck['DATE_FROM'],
				'TZ_FROM' => $eventForCheck['TZ_FROM'],
			];
			$currentIndex++;
		}
	}

	/**
	 * @param array $event
	 * @param array $existingEvent
	 * @return bool
	 */
	private function canFindIntersections(array $event, array $existingEvent): bool
	{
		return
			($event['PARENT_ID'] ?? null) !== (int)($existingEvent['PARENT_ID'] ?? null)
			&& !empty($existingEvent['PARENT_ID'])
			&& !empty($existingEvent['DATE_FROM'])
			&& !empty($existingEvent['DATE_TO'])
			&& !empty($existingEvent['ID'])
		;
	}

	/**
	 * @param int $eventId
	 * @param int $repeatNumber
	 * @param bool $isEnd
	 * @return string
	 */
	private function getTimelineKey(int $eventId, int $repeatNumber, bool $isEnd): string
	{
		return $eventId . '_' . $repeatNumber . '_' . ($isEnd ? self::KEY_PART_END : self::KEY_PART_START);
	}

	/**
	 * @param string $key
	 * @return array
	 */
	private function parseTimelineKey(string $key): array
	{
		$res =  explode('_', $key);
		return [(int)$res[0], (int)$res[1], ($res[2] === self::KEY_PART_END)];
	}

	/**
	 * @param int $eventId
	 * @param int $repeatNumber
	 * @return string
	 */
	private function getCachedEventKey(int $eventId, int $repeatNumber): string
	{
		return $eventId . '_' . $repeatNumber;
	}

	/**
	 * @param array $event
	 * @return string
	 */
	private function getEventFormattedDate(array $event): string
	{
		$eventTimezone = null;
		if (!empty($event['TZ_FROM']))
		{
			$eventTimezone = new \DateTimeZone($event['TZ_FROM']);
		}
		$result = '';
		try
		{
			$result = \Bitrix\Calendar\Util::formatEventDate(
				new DateTime($event['DATE_FROM'], null, $eventTimezone)
			);
		}
		catch (ObjectException $e)
		{
		}

		return $result;
	}
}