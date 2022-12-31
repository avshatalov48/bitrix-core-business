<?php

namespace Bitrix\Calendar\Sync\Office365\Converter;

use Bitrix\Calendar\Core\Base;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\Location;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Sync\Util\EventDescription;
use Bitrix\Main\Type;
use CCalendar;

class EventConverter
{
	public const ACCESSIBILITY_IMPORT_MAP = [
		'busy' => 'busy',
		'free' => 'free',
		'workingElsewhere' => 'absent',
		'tentative' => 'quest',
		'oof' => 'absent', // Away
	];
	public const ACCESSIBILITY_EXPORT_MAP = [
		'busy' => 'busy',
		'free' => 'free',
		'quest' => 'tentative',
		'absent' => 'oof', // Away
	];

	public function __construct()
	{
	}

	/**
	 * @param Event $event
	 *
	 * @return EventDto
	 */
	public function eventToDto(Event $event): EventDto
	{
		$endDate = $event->isFullDayEvent()
			? (clone $event->getEnd())->add('1 day')
			: $event->getEnd();

		$data = [
			'subject' => $event->getName(),
			'body' =>  [
				'contentType' => 'HTML',
				'content' => $this->prepareDescription($event),
			],
			'isAllDay' => $event->isFullDayEvent(),
			'start' =>  [
				'dateTime' => $event->getStart()->getDate()->format(Helper::TIME_FORMAT_LONG),
				'timeZone' => $this->prepareTimeZone(
					$event->getStart(),
					$event->getStartTimeZone(),
					$event->isFullDayEvent()
				),
			],
			'end' =>  [
				'dateTime' => $endDate->getDate()->format(Helper::TIME_FORMAT_LONG),
				'timeZone' => $this->prepareTimeZone(
					$endDate,
					$event->getEndTimeZone(),
					$event->isFullDayEvent()
				),
			],
			'isCancelled' => $event->isDeleted(),
			'location' => [
				'displayName' => $this->prepareLocation($event->getLocation()),
			],
		];

		if ($event->isRecurrence())
		{
			$data['recurrence'] = $this->prepareRecurringForDto($event->getRecurringRule(), $event->getStart()->getDate());
		}

		if ($event->getRemindCollection())
		{
			$this->prepareReminders($event, $data);
		}

		if ($accessibility = $this->convertAccessibility($event->getAccessibility()))
		{
			$data['showAs'] = $accessibility;
		}

		return new EventDto($data);
	}

	/**
	 * @param string|null $ourValue
	 *
	 * @return string|null
	 */
	private function convertAccessibility(?string $ourValue): ?string
	{
		return self::ACCESSIBILITY_EXPORT_MAP[$ourValue] ?? null;
	}

	/**
	 * @param RecurringEventRules $rule
	 * @param Type\Date $startDate
	 *
	 * @return array|array[]
	 */
	private function prepareRecurringForDto(RecurringEventRules $rule, Type\Date $startDate): array
	{
		$result = [
			'pattern' => [
				'interval' => $rule->getInterval(),
			],
			'range' => [],
		];
		$dayMap = [
			'MO' => 'monday',
			'TU' => 'tuesday',
			'WE' => 'wednesday',
			'TH' => 'thursday',
			'FR' => 'friday',
			'SA' => 'saturday',
			'SU' => 'sunday',
		];
		if ($rule->getFrequency() === 'WEEKLY')
		{
			$result['pattern']['type'] = 'weekly';

			$firstDayOfWeek = \COption::GetOptionString('calendar', 'week_start', 'MO');
			$result['pattern']['firstDayOfWeek'] = $dayMap[$firstDayOfWeek];

			if ($rule->getByday())
			{
				$result['pattern']['daysOfWeek'] = array_map(function ($val) use ($dayMap) {
					return $dayMap[$val] ?? '';
				}, array_values($rule->getByday()));
			}
		}
		elseif ($rule->getFrequency() === 'DAILY')
		{
			$result['pattern']['type'] = 'daily';
		}
		elseif ($rule->getFrequency() === 'MONTHLY')
		{
			$result['pattern']['type'] = 'absoluteMonthly';
			$result['pattern']['interval'] = $rule->getInterval();
			$result['pattern']['dayOfMonth'] = (int) $startDate->format('d');

		}
		elseif ($rule->getFrequency() === 'YEARLY')
		{
			$result['pattern']['type'] = 'absoluteYearly';
			$result['pattern']['dayOfMonth'] = (int) $startDate->format('d');
			$result['pattern']['month'] = (int) $startDate->format('m');
		}
		$result['range']['startDate'] = $startDate->format('Y-m-d');

		if ($rule->getCount())
		{
			$result['range']['type'] = 'numbered';
			$result['range']['numberOfOccurrences'] = $rule->getCount();
			$result['range']['endDate'] = '0001-01-01';
		}
		elseif ($rule->getUntil())
		{
			$result['range']['type'] = 'endDate';
			$result['range']['endDate'] = $rule->getUntil()->format('Y-m-d');
		}
		else
		{
			$result['range']['type'] = 'noEnd';
			$result['range']['endDate'] = $this->getFarFarAwayDate();
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function getFarFarAwayDate(): string
	{
		return '01.01.2038';
	}

	/**
	 * @param RemindCollection $remindCollection
	 * @param array $data
	 *
	 * @return void
	 */
	private function prepareReminders(Event $event, array &$data): void
	{
		$remindCollection = $event->getRemindCollection();
		$delta = null;

		/** @var Remind $remind */
		foreach ($remindCollection as $remind)
		{
			if (!$remind->isBeforeEventStart() && !$event->isFullDayEvent())
			{
				continue;
			}

			$newDelta = $remind->getTimeBeforeStartInMinutes();

			if ($newDelta < -1440)
			{
				continue;
			}

			$delta = $delta === null ? $newDelta : min($delta, $newDelta);
		}

		if ($delta !== null)
		{
			$delta = (int)$delta;
			$data['isReminderOn'] = true;
			$data['reminderMinutesBeforeStart'] = $delta;
		}
		else
		{
			$data['isReminderOn'] = false;
			$data['reminderMinutesBeforeStart'] = 0;
		}

	}

	/**
	 * @param Location|null $location
	 *
	 * @return mixed|string|null
	 */
	private function prepareLocation(?Location $location)
	{
		if ($location)
		{
			return CCalendar::GetTextLocation($location->getActualLocation());
		}

		return null;
	}

	/**
	 * @param Base\Date $date
	 * @param Base\DateTimeZone|null $timeZone
	 * @param bool $fullDay
	 *
	 * @return string
	 */
	private function prepareTimeZone(
		Base\Date $date,
		?Base\DateTimeZone $timeZone,
		bool $fullDay
	): string
	{
		if ($timeZone)
		{
			return $timeZone->getTimeZone()->getName();
		}

		if (!$fullDay)
		{
			$coreDate = $date->getDate();
			if ($coreDate instanceof Type\DateTime)
			{
				return $coreDate->getTimeZone()->getName();
			}
		}

		return $this->getDefaultTimezone();
	}

	/**
	 * @return string
	 */
	private function getDefaultTimezone(): string
	{
		return 'UTC';
	}

	/**
	 * @param Event $event
	 *
	 * @return string
	 */
	private function prepareDescription(Event $event): string
	{
		$description = (new EventDescription())->prepareForExport($event);

		return $description ? \CCalendarEvent::ParseText($this->parseImagesInDescription($description)) : '';
	}

	/**
	 * @param string $description
	 *
	 * @return string
	 */
	private function parseImagesInDescription(string $description): string
	{
		return preg_replace(
			"#\[img]((cid):[.\\-_:a-z0-9@]+)*\[/img]#is".BX_UTF_PCRE_MODIFIER,
			"<img src='\\1'>",
			$description
		);
	}
}
