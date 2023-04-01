<?php

namespace Bitrix\Calendar\Core\Managers\Compare;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;

class EventCompareManager implements CompareManager
{
	public const SIGNIFICANT_FIELDS = [
		'name' => 'name',
		'attendees' => 'attendees',
		'start' => 'start',
		'end' => 'end',
		'location' => 'location',
		'recurringRule' => 'recurringRule',
		'creatorId' => 'creatorId',
		'hostId' => 'hostId',
		'excludedDates' => 'excludedDates',
	];

	public const COMPARE_FIELDS = [
		'name' => 'name',
		'recurringRule' => 'recurringRule',
		'start' => 'start',
		'end' => 'end',
		'startTimezone' => 'startTimezone',
		'endTimezone' => 'endTimezone',
		'location' => 'location',
		'attendees' => 'attendees',
		'description' => 'description',
		'ownerId' => 'ownerId',
		'creatorId' => 'creatorId',
		'accessibility' => 'accessibility',
		'transparent' => 'transparent',
		'isPrivate' => 'isPrivate',
		'importance' => 'importance',
		'calendarType' => 'calendarType',
		'specialLabel' => 'specialLabel',
		'isFullDay' => 'isFullDay',
		'color' => 'color',
		'section' => 'section',
		'uid' => 'uid',
		'isActive' => 'isActive',
		'deleted' => 'deleted',
		'originalDateFrom' => 'originalDateFrom',
		//			'excludedDates' => 'excludedDates',
		//			'version' => 'version',
		//			'recurrenceId' => 'recurrenceId',
		//			'dateCreate' => 'dateCreate',
		//			'dateModified' => 'dateModified',
		//			'reminds' => 'reminds',
		//			'hostId' => 'hostId',
		//			'meetingDescription' => 'meetingDescription',
	];

	/**
	 * @var array
	 */
	private array $differenceFields = [];
	/**
	 * @var array
	 */
	protected array $difference = [];
	/**
	 * @var array
	 */
	protected array $eventValueForCompare;
	/**
	 * @var array
	 */
	protected array $originalValueForCompare;
	/**
	 * @var string[]
	 */
	protected array $significantFields;
	/**
	 * @var Event
	 */
	protected Event $event;
	/**
	 * @var Event
	 */
	protected Event $originalEvent;

	/**
	 * @param Event $event
	 * @param Event $originalEvent
	 */
	public function __construct(Event $event, Event $originalEvent)
	{
		$this->event = $event;
		$this->originalEvent = $originalEvent;

		$this->compare();
	}

	/**
	 * @return $this
	 */
	public function compare(): EventCompareManager
	{
		$this->eventValueForCompare = $this->transformValuesForCompare($this->event);
		$this->originalValueForCompare = $this->transformValuesForCompare($this->originalEvent);
		$this->compareFields();

		$this->significantFields = array_intersect($this->getDifferenceKeyFields(), $this->getSignificantFields());

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasDifferenceFields(): bool
	{
		return (bool)$this->differenceFields;
	}

	/**
	 * @return bool
	 */
	public function hasSignificantFields(): bool
	{
		return (bool)$this->significantFields;
	}

	/**
	 * @return string[]
	 */
	public function getSignificantChanges(): array
	{
		return $this->significantFields;
	}

	/**
	 * @return string[]
	 */
	private function getFields(): array
	{
		return [
			'NAME',
			'DESCRIPTION',
			'RRULE',
			'EXDATE',
			'DATE_FROM',
			'DATE_TO',
			'LOCATION',
			'SECTION',
		];
	}

	/**
	 * @return array
	 */
	public function getDifferenceKeyFields(): array
	{
		return array_keys($this->differenceFields);
	}

	/**
	 * @return array
	 */
	public function getDifferenceFieldsWithOriginalValue(): array
	{
		return [
			'event' => $this->differenceFields,
			'originalEvent' => array_intersect_key($this->originalValueForCompare, $this->differenceFields),
		];
	}

	/**
	 * @param Event $event
	 * @return array
	 */
	private function transformValuesForCompare(Event $event): array
	{
		return [
			'name' => $event->getName(),
			'recurringRule' => $event->isRecurrence() ? $event->getRecurringRule()->toString() : null,
			'start' => (string)$event->getStart(),
			'end' => (string)$event->getEnd(),
			'startTimeZone' => $event->getStartTimeZone() ? (string)$event->getStartTimeZone() : null,
			'endTimeZone' => $event->getEndTimeZone() ? (string)$event->getEndTimeZone() : null,
			'location' => $event->getLocation() ? (string) $event->getLocation()->toString() : '',
			'attendees' => (string) $event->getAttendeesCollection(),
			'description' => trim($event->getDescription()),
			'ownerId' => $event->getOwner() ? $event->getOwner()->getId() : null,
			'creatorId' => $event->getCreator() ? $event->getCreator()->getId(): null,
			'hostId' => $event->getEventHost() ? $event->getEventHost()->getId() : null,
			'meetingDescription' => $event->getMeetingDescription() ? (string)$event->getMeetingDescription() : null,
			'accessibility' => (string)$event->getAccessibility(),
			'transparent' => (string)$event->getTransparent(),
			'isPrivate' => (string)$event->getIsPrivate(),
			'importance' => (string)$event->getImportance(),
			'calendarType' => (string)$event->getCalendarType(),
			'specialLabel' => (string)$event->getSpecialLabel(),
			'excludedDates' => (string)$event->getExcludedDateCollection(),
			'isFullDay' => $event->isFullDayEvent(),
			'color' => (string)$event->getColor(),
			'section' => $event->getSection()->getId(),
			'version' => $event->getVersion(),
			'uid' => (string)$event->getUid(),
			'isActive' => $event->isActive(),
			'deleted' => $event->isDeleted(),
			'recurrenceId' => $event->getRecurrenceId(),
			'originalDateFrom' => $event->getOriginalDateFrom(),
			'dateCreate' => (string)$event->getDateCreate(),
			'dateModified' => (string)$event->getDateModified(),
			'reminds' => (string)$event->getRemindCollection(),
		];
	}

	/**
	 * @return string[]
	 */
	private function getSignificantFields(): array
	{
		return self::SIGNIFICANT_FIELDS;
	}

	/**
	 * @return string[]
	 */
	private function getCompareFields(): array
	{
		return self::COMPARE_FIELDS;
	}

	/**
	 * @return void
	 */
	private function compareFields(): void
	{
		$this->compareHandler();
		$this->compareExcludedDates();
		$this->compareReminders();
	}

	/**
	 * @return void
	 */
	private function compareHandler(): void
	{
		foreach ($this->getCompareFields() as $field)
		{
			if (($this->eventValueForCompare[$field] ?? null) != ($this->originalValueForCompare[$field] ?? null))
			{
				$this->differenceFields[$field] = $this->eventValueForCompare[$field] ?? null;
			}
		}
	}

	/**
	 * @return void
	 */
	protected function compareExcludedDates(): void
	{
		if ($this->originalEvent->getExcludedDateCollection() || $this->event->getExcludedDateCollection())
		{
			$excludedDatesManager = ExcludedDateCompareManager::createInstance(
				$this->originalEvent->getExcludedDateCollection(),
				$this->event->getExcludedDateCollection()
			);
			if (!$excludedDatesManager->isEqual())
			{
				$this->differenceFields['excludedDates'] = $excludedDatesManager
					->getDiffCollection()->toString(';');
			}
		}
	}

	/**
	 * @return void
	 */
	protected function compareReminders(): void
	{
		$remindersCompareManager = RemindCompareManager::createInstance(
			$this->event->getRemindCollection() ?? new RemindCollection([]),
			$this->originalEvent->getRemindCollection() ?? new RemindCollection([])
		);
		if (!$remindersCompareManager->isEqual())
		{
			$this->differenceFields['reminds'] = $remindersCompareManager->getDiffCollection()->toString(';');
		}
	}

	/**
	 * @return array
	 */
	public function getDiff(): array
	{
		return $this->differenceFields;
	}
}
