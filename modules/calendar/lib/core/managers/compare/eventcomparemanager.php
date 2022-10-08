<?php

namespace Bitrix\Calendar\Core\Managers\Compare;

use Bitrix\Calendar\Core\Event\Event;

class EventCompareManager implements CompareManager
{
	/**
	 * @var array
	 */
	private array $differenceFields = [];
	/**
	 * @var array
	 */
	protected array $difference;
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
			'location' => $event->getLocation() ? (string) $event->getLocation()->toString() : null,
			'attendees' => (string) $event->getAttendeesCollection(),
			'description' => $event->getDescription(),
			'ownerId' => $event->getOwner() ? $event->getOwner()->getId() : null,
			'creatorId' => $event->getCreator() ? $event->getCreator()->getId(): null,
			'hostId' => $event->getEventHost() ? $event->getEventHost()->getId() : null,
			'meetingDescription' => $event->getMeetingDescription() ? (string)$event->getMeetingDescription() : null,
			'accessibility' => (string)$event->getAccessibility(),
			'transparent' => (string)$event->getTransparent(),
			'isPrivate' => (string)$event->getIsPrivate(),
			'importance' => (string)$event->getImportance(),
			'eventType' => (string)$event->getEventType(),
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
		return [
			'name',
			'attendees',
			'start',
			'end',
			'location',
			'recurringRule',
			'creatorId',
			'hostId',
			'excludedDates'
		];
	}

	/**
	 * @return string[]
	 */
	private function getCompareFields(): array
	{
		return [
			'name',
			'recurringRule',
			'start',
			'end',
			'startTimezone',
			'endTimezone',
			'location',
			'attendees',
			'description',
			'ownerId',
			'creatorId',
			'accessibility',
			'transparent',
			'isPrivate',
			'importance',
			'eventType',
			'isFullDay',
			'color',
			'section',
			'uid',
			'isActive',
			'deleted',
			'originalDateFrom',
//			'excludedDates',
//			'version',
//			'recurrenceId',
//			'dateCreate',
//			'dateModified',
//			'reminds',
//			'hostId',
//			'meetingDescription',
		];
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
			if ($this->eventValueForCompare[$field] !== $this->originalValueForCompare[$field])
			{
				$this->differenceFields[$field] = $this->eventValueForCompare[$field];
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
				$this->differenceFields['excludedDates'] = $excludedDatesManager->getDiffCollection()->toString(';');
			}
		}
	}

	/**
	 * @return void
	 */
	protected function compareReminders(): void
	{
		$remindersCompareManager = RemindCompareManager::createInstance(
			$this->event->getRemindCollection(),
			$this->originalEvent->getRemindCollection()
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
		return $this->difference;
	}
}
