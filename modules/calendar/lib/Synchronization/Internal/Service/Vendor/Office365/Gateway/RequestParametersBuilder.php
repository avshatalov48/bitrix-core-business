<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Gateway;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\Internal\Repository\EventRepository;
use Bitrix\Calendar\Sync\Office365\Converter\EventConverter;
use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Sync\Util\EventDescription;
use Bitrix\Calendar\Synchronization\Internal\Exception\Repository\RepositoryReadException;
use Bitrix\Main\Type\DateTime;
use CCalendar;
use CCalendarEvent;
use COption;

class RequestParametersBuilder
{
	private const WEEK_START_OPTION = 'week_start';
	private const DEFAULT_WEEK_START = 'MO';
	private const DAY_MAP = [
		'MO' => 'monday',
		'TU' => 'tuesday',
		'WE' => 'wednesday',
		'TH' => 'thursday',
		'FR' => 'friday',
		'SA' => 'saturday',
		'SU' => 'sunday',
	];

	public function __construct(
		private readonly EventDescription $eventDescription,
		private readonly EventRepository $eventRepository,
	)
	{
	}

	public function build(Event $event): array
	{
		$parameters = [
			'subject' => $event->getName(),
			'isAllDay' => $event->isFullDayEvent(),
			'isCancelled' => $event->isDeleted(),
		];

		$this
			->prepareBody($parameters, $event)
			->prepareStartDate($parameters, $event)
			->prepareEndDate($parameters, $event)
			->prepareLocation($parameters, $event)
			->prepareRecurrence($parameters, $event)
			->prepareReminders($parameters, $event)
			->prepareAccessibility($parameters, $event)
		;

		return array_filter(
			$parameters,
			static fn ($parameter) => $parameter !== [] && $parameter !== null,
		);
	}

	private function prepareBody(array &$parameters, Event $event): self
	{
		if ($content = $this->eventDescription->prepareForExport($event))
		{
			$content = $this->parseImagesInDescription($content);

			$content = CCalendarEvent::ParseText($content);
		}
		else
		{
			$content = '';
		}

		$parameters['body'] = [
			'contentType' => 'HTML',
			'content' => $content,
		];

		return $this;
	}

	private function prepareStartDate(array &$parameters, Event $event): self
	{
		$parameters['start'] =  [
			'dateTime' => $event->getStart()->getDate()->format(Helper::TIME_FORMAT_LONG),
			'timeZone' => $this->prepareTimeZone(
				$event->getStart(),
				$event->getStartTimeZone(),
				$event->isFullDayEvent(),
			),
		];

		return $this;
	}

	private function prepareEndDate(array &$parameters, Event $event): self
	{
		$endDate =
			$event->isFullDayEvent()
				? (clone $event->getEnd())->add('1 day')
				: $event->getEnd()
		;

		$parameters['end'] = [
			'dateTime' => $endDate->getDate()->format(Helper::TIME_FORMAT_LONG),
			'timeZone' => $this->prepareTimeZone(
				$endDate,
				$event->getEndTimeZone(),
				$event->isFullDayEvent(),
			),
		];

		return $this;
	}

	private function getDefaultTimezone(): string
	{
		return 'UTC';
	}

	private function prepareLocation(array &$parameters, Event $event): self
	{
		if ($location = $event->getLocation())
		{
			$displayName = CCalendar::GetTextLocation($location->getActualLocation());
		}
		else
		{
			$displayName = null;
		}

		$parameters['location'] = ['displayName' => $displayName];

		return $this;
	}

	private function prepareRecurrence(array &$parameters, Event $event): self
	{
		if (!$event->isRecurrence())
		{
			return $this;
		}

		/** @var RecurringEventRules $rule */
		$rule = $event->getRecurringRule();

		$startDate = $event->getStart()->getDate();

		$recurrenceParameters = [
			'pattern' => [
				'interval' => $rule->getInterval(),
			],
			'range' => [],
		];

		$recurringFrequency = $rule->getFrequency();

		switch ($recurringFrequency)
		{
			case 'DAILY':
				$recurrenceParameters['pattern']['type'] = 'daily';

				break;
			case 'WEEKLY':
				$recurrenceParameters['pattern']['type'] = 'weekly';

				$firstDayOfWeek = COption::GetOptionString('calendar', self::WEEK_START_OPTION, self::DEFAULT_WEEK_START);

				$recurrenceParameters['pattern']['firstDayOfWeek'] = self::DAY_MAP[$firstDayOfWeek];

				if ($byDay = $rule->getByday())
				{
					$recurrenceParameters['pattern']['daysOfWeek'] = array_map(
						static fn ($val) => self::DAY_MAP[$val] ?? '',
						array_values($byDay),
					);
				}

				break;
			case 'MONTHLY':
				$recurrenceParameters['pattern']['type'] = 'absoluteMonthly';

				$recurrenceParameters['pattern']['dayOfMonth'] = (int)$startDate->format('d');

				$recurrenceParameters['pattern']['interval'] = $rule->getInterval();

				break;
			case 'YEARLY':
				$recurrenceParameters['pattern']['type'] = 'absoluteYearly';

				$recurrenceParameters['pattern']['dayOfMonth'] = (int)$startDate->format('d');

				$recurrenceParameters['pattern']['month'] = (int)$startDate->format('m');

				break;
		}

		$recurrenceParameters['range']['startDate'] = $startDate->format('Y-m-d');

		if ($numberOfOccurrences = $rule->getCount())
		{
			$recurrenceParameters['range']['type'] = 'numbered';

			$recurrenceParameters['range']['numberOfOccurrences'] = $numberOfOccurrences;

			$recurrenceParameters['range']['endDate'] = '0001-01-01';
		}
		elseif ($untilDate = $rule->getUntil())
		{
			$recurrenceParameters['range']['type'] = 'endDate';

			try
			{
				$instances = $this->eventRepository->getEventInstances($event);
			}
			catch (RepositoryReadException)
			{
				$instances = [];
			}

			foreach ($instances as $instance)
			{
				$dateFrom = $instance->getOriginalDateFrom() ?: $instance->getStart();

				// Office 365 does not support instances later than until date of recurrence. Simulated this behavior
				if ($untilDate->getTimestamp() < $dateFrom->getTimestamp())
				{
					$untilDate = clone $dateFrom;
				}
			}

			$recurrenceParameters['range']['endDate'] = $untilDate->format('Y-m-d');
		}
		else
		{
			$recurrenceParameters['range']['type'] = 'noEnd';

			$recurrenceParameters['range']['endDate'] = $this->getFarAwayDate();
		}

		$parameters['recurrence'] = $recurrenceParameters;

		return $this;
	}

	private function prepareReminders(array &$parameters, Event $event): self
	{
		$remindCollection = $event->getRemindCollection();

		if (!$remindCollection)
		{
			return $this;
		}

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

			$parameters['isReminderOn'] = true;
			$parameters['reminderMinutesBeforeStart'] = $delta;
		}
		else
		{
			$parameters['isReminderOn'] = false;
			$parameters['reminderMinutesBeforeStart'] = 0;
		}

		return $this;
	}

	private function prepareAccessibility(array &$parameters, Event $event): self
	{
		if ($accessibility = $this->convertAccessibility($event->getAccessibility()))
		{
			$parameters['showAs'] = $accessibility;
		}

		return $this;
	}

	private function parseImagesInDescription(string $description): string
	{
		return preg_replace(
			"#\[img]((cid):[.\\-_:a-z0-9@]+)*\[/img]#isu",
			"<img src='\\1'>",
			$description,
		);
	}

	private function prepareTimeZone(
		Date $date,
		?DateTimeZone $timeZone,
		bool $fullDay,
	): string
	{
		if ($timeZone)
		{
			return $timeZone->getTimeZone()->getName();
		}

		if (!$fullDay)
		{
			$coreDate = $date->getDate();

			if ($coreDate instanceof DateTime)
			{
				return $coreDate->getTimeZone()->getName();
			}
		}

		return $this->getDefaultTimezone();
	}

	private function getFarAwayDate(): string
	{
		return '01.01.2038';
	}

	private function convertAccessibility(?string $accessibility): ?string
	{
		return EventConverter::ACCESSIBILITY_EXPORT_MAP[$accessibility] ?? null;
	}
}
