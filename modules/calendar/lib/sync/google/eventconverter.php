<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Entities\InstanceMap;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Util\EventDescription;

class EventConverter
{
	protected const MAX_COUNT_REMINDERS_FOR_SYNC = 5;

	/**
	 * @var InstanceMap|null
	 */
	protected ?InstanceMap $instanceCollection;
	/**
	 * @var Event
	 */
	private Event $originalEvent;
	/**
	 * @var EventConnection|null
	 */
	private ?EventConnection $eventConnection;

	/**
	 * @param Event $event
	 * @param EventConnection|null $eventConnection
	 * @param InstanceMap|null $instanceCollection
	 */
	public function __construct(
		Event $event,
		EventConnection $eventConnection = null,
		?InstanceMap $instanceCollection = null
	)
	{
		$this->originalEvent = $event;
		$this->eventConnection = $eventConnection;
		$this->instanceCollection = $instanceCollection;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function convertForCreate(): array
	{
		$event = [];

		$event['summary'] = $this->originalEvent->getName();
		$event['description'] = $this->prepareDescription($this->originalEvent);
		$event['transparency'] = $this->prepareAccessibility();
		$event = array_merge($event, $this->prepareDate());
		$event['reminders'] = $this->prepareReminders();
		$event['location'] = $this->prepareLocation();
		$event['visibility'] = $this->prepareVisibility();
		// $event['sequence'] = $this->originalEvent->getVersion() - Helper::VERSION_DIFFERENCE;

		if ($this->originalEvent->getUid() !== null)
		{
			$event['iCalUID'] = $this->originalEvent->getUid();
		}

		if ($this->originalEvent->isRecurrence())
		{
			$event['recurrence'] = $this->prepareRecurrenceRule();
		}

		return $event;
	}

	/**
	 * @param Event $event
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareDescription(Event $event): string
	{
		$description = \CCalendarEvent::ParseText((new EventDescription())->prepareForExport($event));

		return preg_replace("/<br>/i", "\r\n", $description);
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 * @throws BaseException
	 */
	public function convertForUpdate(): array
	{
		if ($this->eventConnection === null)
		{
			throw new BaseException('you should initialize eventConnection before update event');
		}

		$event = $this->convertForCreate();
		if ($this->eventConnection && $this->eventConnection->getVendorEventId() !== null)
		{
			$event['id'] = $this->eventConnection->getVendorEventId();

			if ($this->originalEvent->isInstance())
			{
				$event['recurringEventId'] = $this->eventConnection->getRecurrenceId();
			}
		}

		return $event;
	}

	/**
	 * @return array
	 * @throws BaseException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function convertForDeleteInstance(): array
	{
		$event = $this->convertForUpdate();
		$event['status'] = 'cancelled';

		return $event;
	}

	/**
	 * @return string
	 */
	public function prepareAccessibility(): string
	{
		return $this->originalEvent->getAccessibility() === 'busy'
			? 'opaque'
			: 'transparent'
		;
	}

	/**
	 * @return array
	 */
	public function prepareDate(): array
	{
		$date = [];

		if ($this->originalEvent->isFullDayEvent())
		{
			$date['start']['date'] = $this->originalEvent->getStart()->format('Y-m-d');
			$date['end']['date'] = $this->originalEvent->getEnd()->add(('+1 day'))->format('Y-m-d');
		}
		else
		{
			$date['start']['dateTime'] = $this->originalEvent->getStart()->format(Helper::DATE_TIME_FORMAT);
			$date['start']['timeZone'] = $this->originalEvent->getStartTimeZone()
				? $this->originalEvent->getStartTimeZone()->getTimeZone()->getName()
				: (new \DateTime())->getTimezone()->getName()
			;
			$date['end']['dateTime'] = $this->originalEvent->getEnd()->format(Helper::DATE_TIME_FORMAT);
			$date['end']['timeZone'] = $this->originalEvent->getEndTimeZone()
				? $this->originalEvent->getEndTimeZone()->getTimeZone()->getName()
				: (new \DateTime())->getTimezone()->getName()
			;
		}

		if ($this->originalEvent->getOriginalDateFrom() !== null)
		{
			if ($this->originalEvent->isFullDayEvent())
			{
				$event['originalStartTime']['date'] = $this->originalEvent->getOriginalDateFrom()->format(Helper::DATE_FORMAT);
			}
			else
			{
				$event['originalStartTime']['dateTime'] = $this->originalEvent->getOriginalDateFrom()->format(Helper::DATE_TIME_FORMAT);
			}
		}

		return $date;
	}

	/**
	 * @return array
	 */
	private function prepareReminders(): array
	{
		$reminders = [];
		$reminders['useDefault'] = false;

		$remindCollection = $this->originalEvent->getRemindCollection();
		if ($remindCollection && $remindCollection->count() > self::MAX_COUNT_REMINDERS_FOR_SYNC)
		{
			$remindCollection->sortFromStartEvent();
		}

		/** @var Remind $remind */
		foreach ($remindCollection as $remind)
		{
			if (!$remind->isBeforeEventStart())
			{
				continue;
			}

			$reminders['overrides'][] = [
				Remind::UNIT_MINUTES => $remind->getTimeBeforeStartInMinutes(),
				'method' => 'popup',
			];

			if (count($reminders['overrides']) >= self::MAX_COUNT_REMINDERS_FOR_SYNC)
			{
				break;
			}
		}

		return $reminders;
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareLocation(): string
	{
		if ($this->originalEvent->getLocation())
		{
			return \Bitrix\Calendar\Rooms\Util::getTextLocation((string)$this->originalEvent->getLocation());
		}

		return '';
	}

	/**
	 * @return string
	 */
	private function prepareVisibility(): string
	{
		return $this->originalEvent->isPrivate()
			? 'private'
			: 'public'
		;
	}

	/**
	 * @return array
	 */
	private function prepareRecurrenceRule(): array
	{
		$rule = [];

		if (!$this->originalEvent->isRecurrence())
		{
			return [];
		}

		$rule[] = $this->prepareDescriptionRecurrenceRule();

		if ($this->originalEvent->getExcludedDateCollection() !== null)
		{
			array_push($rule, ...$this->prepareExcludedDates());
		}

		return $rule;
	}

	/**
	 * @return string
	 */
	public function prepareDescriptionRecurrenceRule(): string
	{
		/** @var RecurringEventRules $eventRule */
		$eventRule = $this->originalEvent->getRecurringRule();
		$descriptionRule = 'RRULE:';
		$descriptionRule .= 'FREQ=' . $eventRule->getFrequency();
		$descriptionRule .= ';INTERVAL=' . $eventRule->getInterval();

		if ($eventRule->hasDay())
		{
			$descriptionRule .= ';BYDAY=' . implode(",", $eventRule->getByday());
		}

		if ($eventRule->hasCount())
		{
			$descriptionRule .= ';COUNT=' . $eventRule->getCount();
		}
		elseif ($eventRule->hasUntil())
		{
			$until = clone $eventRule->getUntil();
			if (!$this->originalEvent->isFullDayEvent() && !$eventRule->isUntilEndOfTime())
			{
				$until = $until->add('1 day')->sub('1 second');
			}
			$descriptionRule .= ';UNTIL=' . $until->format(Helper::DATE_TIME_FORMAT_WITH_UTC_TIMEZONE);
		}

		return $descriptionRule;
	}

	/**
	 * @return array
	 */
	public function prepareExcludedDates(): array
	{
		$rule = [];

		if (
			$this->originalEvent->getExcludedDateCollection()
			&& ($exdateCollection = $this->originalEvent->getExcludedDateCollection()->getDateCollectionNewerThanInterval())
		)
		{
			$originalDateList = [];
			if ($this->instanceCollection !== null)
			{
				/** @var SyncEvent $instance */
				foreach ($this->instanceCollection as $instance)
				{
					if ($originalDateFrom = $instance->getEvent()->getOriginalDateFrom())
					{
						$originalDateList[] = $originalDateFrom->format('Ymd');
					}
				}
			}

			if ($this->originalEvent->isFullDayEvent())
			{
				foreach ($exdateCollection as $exDate)
				{
					$date = $exDate->format('Ymd');
					if (!in_array($date, $originalDateList, true))
					{
						$rule[] = 'EXDATE;VALUE=DATE:' . $date;
					}
				}
			}
			else
			{
				$postfix = (clone $this->originalEvent->getStart())
					->setTimezone(new \DateTimeZone('UTC'))
					->format('\\THis\\Z')
				;
				/** @var Date $exDate */
				foreach ($exdateCollection as $exDate)
				{
					$date = $exDate->format('Ymd');
					if (!in_array($date, $originalDateList, true))
					{
						$rule[] = 'EXDATE;TZID=UTC:' . $date . $postfix;
					}
				}
			}
		}

		return $rule;
	}

	/**
	 * @return string[]
	 */
	public function convertForDelete(): array
	{
		return [
			'sendUpdates' => 'all',
		];
	}
}
