<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service\ICloud;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use CDavICalendar;
use CDavICalendarComponent;
use CDavICalendarTimeZone;
use DateTime;
use DateTimeZone;
use Exception;

class CalendarDataBuilder
{
	private const WEEKDAY_MAP = [
		'SU' => 6,
		'MO' => 0,
		'TU' => 1,
		'WE' => 2,
		'TH' => 3,
		'FR' => 4,
		'SA' => 5,
	];

	private const PERIOD_MAP = [
		'M' => 'min',
		'H' => 'hour',
		'D' => 'day',
		'S' => 'min',
	];

	/**
	 * @throws SystemException
	 */
	public function __construct(private readonly CDavICalendar $calendar)
	{
		if (!self::isAvailable())
		{
			throw new SystemException('Module dav is not installed');
		}
	}

	public function build(CDavICalendarComponent $event): array
	{
		$calendarData = [
			'NAME' => $event->getPropertyValue('SUMMARY'),
			'VERSION' => $event->getPropertyValue('SEQUENCE'),
			'PROPERTY_LOCATION' => $event->getPropertyValue('LOCATION'),
			'DETAIL_TEXT' => $event->getPropertyValue('DESCRIPTION'),
			'DETAIL_TEXT_TYPE' => 'text',
			'TZ_FROM' => $event->getPropertyParameter('DTSTART', 'TZID'),
			'TZ_TO' => $event->getPropertyParameter('DTEND', 'TZID'),
			'XML_ID' => $event->getPropertyValue('UID'),
			'PROPERTY_CATEGORY' => $event->getPropertyValue('CATEGORIES'),
			'ORGANIZER' => $event->getPropertyValue('ORGANIZER'),
			'ORGANIZER_ENTITY' => $event->getProperties('ORGANIZER'),
			'ATTACH' => $event->getProperties('ATTACH'),
			'ATTENDEE' => $event->getProperties('ATTENDEE'),
			'URL' => $event->getPropertyValue('URL'),
		];

		$this
			->prepareDateFrom($event, $calendarData)
			->prepareDateTo($event, $calendarData)
			->prepareSkipTime($event, $calendarData)
			->prepareCreatedDate($event, $calendarData)
			->preparePriority($event, $calendarData)
			->prepareTransparency($event, $calendarData)
			->prepareAlarms($event, $calendarData)
			->prepareRecurrenceRule($event, $calendarData)
			->prepareRecurrenceId($event, $calendarData)
			->prepareExcludedDates($event, $calendarData)
		;

		return $calendarData;
	}

	private function prepareDateFrom(CDavICalendarComponent $event, array &$calendarData): self
	{
		$dateFrom = (string)$event->getPropertyValue('DTSTART');

		$calendarData['DATE_FROM'] = CDavICalendarTimeZone::getFormattedServerDateTime(
			$dateFrom,
			false,
			$this->calendar,
		);

		return $this;
	}

	private function prepareDateTo(CDavICalendarComponent $event, array &$calendarData): self
	{
		$dateTo = (string)$event->getPropertyValue('DTEND');

		$calendarData['DATE_TO'] = CDavICalendarTimeZone::getFormattedServerDateTime(
			$dateTo,
			false,
			$this->calendar,
		);

		return $this;
	}

	private function prepareSkipTime(CDavICalendarComponent $event, array &$calendarData): self
	{
		$dateFromType = $event->getPropertyParameter('DTSTART', 'VALUE');
		$dateToType = $event->getPropertyParameter('DTEND', 'VALUE');

		$skipTime = ($dateFromType === 'DATE') && ($dateToType === 'DATE');

		$calendarData['SKIP_TIME'] = $skipTime;

		return $this;
	}

	private function prepareCreatedDate(CDavICalendarComponent $event, array &$calendarData): self
	{
		$createdDate = (string)$event->getPropertyValue('CREATED');

		$calendarData['DATE_CREATE'] = CDavICalendarTimeZone::getFormattedServerDateTime($createdDate);

		return $this;
	}

	private function preparePriority(CDavICalendarComponent $event, array &$calendarData): self
	{
		$priority = (int)$event->getPropertyValue('PRIORITY');

		if (!$priority)
		{
			$calendarData['PROPERTY_IMPORTANCE'] = 'normal';

			return $this;
		}

		if ($priority <= 3)
		{
			$calendarData['PROPERTY_IMPORTANCE'] = 'high';

			return $this;
		}

		if ($priority <= 6)
		{
			$calendarData['PROPERTY_IMPORTANCE'] = 'normal';

			return $this;
		}

		$calendarData['PROPERTY_IMPORTANCE'] = 'low';

		return $this;
	}

	private function prepareTransparency(CDavICalendarComponent $event, array &$calendarData): self
	{
		$transparency = $event->getPropertyValue('TRANSP');

		if ($transparency === 'TRANSPARENT')
		{
			$calendarData['PROPERTY_ACCESSIBILITY'] = 'free';

			return $this;
		}

		$calendarData['PROPERTY_ACCESSIBILITY'] = 'busy';

		return $this;
	}

	private function prepareAlarms(CDavICalendarComponent $event, array &$calendarData): self
	{
		$alarms = $event->getComponents('VALARM');

		if (empty($alarms) || $event->getPropertyValue('X-MOZ-LASTACK') !== null)
		{
			return $this;
		}

		/** @var CDavICalendarComponent $alarm */
		foreach ($alarms as $alarm)
		{
			$action = $alarm->getPropertyValue('ACTION');

			if ($action === 'NONE')
			{
				continue;
			}

			$trigger = (string)$alarm->getPropertyValue('TRIGGER');

			if (
				preg_match('/^-PT(\d+)([HMD])$/i', $trigger, $matches)
				|| preg_match('/^PT(0+)(S)$/i', $trigger, $matches)
				|| preg_match('/^-P(\d+)(D)$/i', $trigger, $matches)
			)
			{
				$calendarData['PROPERTY_REMIND_SETTINGS'][] =
					$matches[1]
					. '_' . self::PERIOD_MAP[$matches[2]]
				;

				continue;
			}

			if (preg_match('/^(\d+)T(\d+)Z$/i', $trigger, $matches))
			{
				$calendarData['PROPERTY_REMIND_SETTINGS'][] =
					$matches[0]
					. '_' . 'date'
				;

				continue;
			}

			if (preg_match('/^PT(\d+)(H)$/i', $trigger, $matches))
			{
				$calendarData['PROPERTY_REMIND_SETTINGS'][] =
					$matches[1]
					. '_' . self::PERIOD_MAP[$matches[2]]
					. '_' . 'daybefore'
				;

				continue;
			}

			if (preg_match('/^-P(\d+)(D)T(\d+)(H)$/i', $trigger, $matches))
			{
				$calendarData['PROPERTY_REMIND_SETTINGS'][] =
					$matches[1]
					. '_' . self::PERIOD_MAP[$matches[2]]
					. '_' . $matches[3]
					. '_' . self::PERIOD_MAP[$matches[4]]
				;
			}
		}

		return $this;
	}

	// Recurrence rule structure: "RRULE:FREQ=WEEKLY;COUNT=5;INTERVAL=2;BYDAY=TU,SA"
	private function prepareRecurrenceRule(CDavICalendarComponent $event, array &$calendarData): self
	{
		$recurrenceRule = $event->getPropertyValueParsed('RRULE');

		if (!$recurrenceRule || !isset($recurrenceRule['FREQ']))
		{
			return $this;
		}

		$calendarData['PROPERTY_PERIOD_TYPE'] = $recurrenceRule['FREQ'];

		if ($calendarData['PROPERTY_PERIOD_TYPE'] === 'WEEKLY')
		{
			if (isset($recurrenceRule['BYDAY']))
			{
				$byDays = explode(',', $recurrenceRule['BYDAY']);
				$byDayResult = [];

				foreach ($byDays as $day)
				{
					$byDayResult[] = self::WEEKDAY_MAP[mb_strtoupper($day)];
				}

				$calendarData['PROPERTY_PERIOD_ADDITIONAL'] = implode(',', $byDayResult);
			}
			else
			{
				$calendarData['PROPERTY_PERIOD_ADDITIONAL'] = date(
						'w',
						MakeTimeStamp($calendarData['DATE_FROM']),
					) - 1;

				if ($calendarData['PROPERTY_PERIOD_ADDITIONAL'] < 0)
				{
					$calendarData['PROPERTY_PERIOD_ADDITIONAL'] = 6;
				}
			}
		}

		if (isset($recurrenceRule['COUNT']))
		{
			$calendarData['PROPERTY_RRULE_COUNT'] = $recurrenceRule['COUNT'];
		}
		elseif (isset($recurrenceRule['UNTIL']))
		{
			$calendarData['PROPERTY_PERIOD_UNTIL'] = CDavICalendarTimeZone::getFormattedServerDateTime(
				$recurrenceRule['UNTIL'],
				$event->getPropertyParameter('DTSTART', 'TZID'),
				$this->calendar,
			);
		}
		else
		{
			$calendarData['PROPERTY_PERIOD_UNTIL'] = date(
				\Bitrix\Main\Type\DateTime::getFormat(),
				mktime(0, 0, 0, 1, 1, 2038),
			);
		}

		$calendarData['PROPERTY_PERIOD_COUNT'] = $recurrenceRule['INTERVAL'] ?? 1;

		return $this;
	}

	private function prepareRecurrenceId(CDavICalendarComponent $event, array &$calendarData): self
	{
		$recurrenceId = $event->getPropertyValue('RECURRENCE-ID');

		if (!$recurrenceId)
		{
			return $this;
		}

		$calendarData['RECURRENCE_ID_DATE'] = CDavICalendarTimeZone::getFormattedServerDateTime(
			$recurrenceId,
			false,
			$this->calendar,
		);

		return $this;
	}

	private function prepareExcludedDates(CDavICalendarComponent $event, array &$calendarData): self
	{
		$excludedDates = $event->getProperties('EXDATE');

		if (empty($excludedDates))
		{
			return $this;
		}

		$formattedOriginalDate = (string)($calendarData['DATE_FROM'] ?? '');
		$originalTimezoneCode = (string)($calendarData['TZ_FROM'] ?? '');

		$calendarData['EXDATE'] = [];

		foreach ($excludedDates as $excludedDate)
		{
			$excludedDateValue = (string)$excludedDate->value();
			$date = CDavICalendarTimeZone::getFormattedServerDate($excludedDateValue);

			$excludedTimezoneCode = (string)$excludedDate->parameter('TZID');

			$shift = $this->getExcludedDateShift(
				$formattedOriginalDate,
				$originalTimezoneCode,
				$excludedTimezoneCode,
			);

			if ($shift)
			{
				$calendarData['EXDATE'][] = $this->shiftDate($date, $shift);
			}
			else
			{
				$calendarData['EXDATE'][] = $date;
			}
		}

		return $this;
	}

	private function getExcludedDateShift(
		string $formattedOriginalDate,
		string $originalTimezoneCode,
		string $excludedTimezoneCode,
	): int
	{
		// TODO: Remove static property
		static $originalDate;

		try
		{
			if (empty($originalDate))
			{
				$originalTimezone = new DateTimeZone($originalTimezoneCode);
				$originalDate = new DateTime($formattedOriginalDate, $originalTimezone);
			}

			$formattedOriginalDate = $originalDate->format('Ymd');

			$excludedTimezone = new DateTimeZone($excludedTimezoneCode);
			$excludedDate = (clone $originalDate)->setTimezone($excludedTimezone);

			$formattedExcludedDate = $excludedDate->format('Ymd');

			return $formattedOriginalDate <=> $formattedExcludedDate;
		}
		catch (Exception)
		{
			return 0;
		}
	}

	private function shiftDate(string $formattedDate, int $shift): string
	{
		$format = Date::getFormat();
		$date = DateTime::createFromFormat($format, $formattedDate);

		return $date->modify($shift . ' day')->format($format);
	}

	private static function isAvailable(): bool
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return Loader::includeModule('dav');
	}
}
