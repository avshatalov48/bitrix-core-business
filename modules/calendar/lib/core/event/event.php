<?php

namespace Bitrix\Calendar\Core\Event;

use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Event\Properties\Location;
use Bitrix\Calendar\Core\Event\Properties\MeetingDescription;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Relations;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Main\Text\Emoji;

class Event implements EntityInterface
{
	private const MIN_MEETING_PARTICIPANT = 2;

	/**
	 * @var int|null
	 */
	protected ?int $id = null;
	/**
	 * @var string|null
	 */
	protected ?string $name = null;
	/**
	 * @var RecurringEventRules|null
	 */
	protected ?RecurringEventRules $recurringRule = null;
	/**
	 * @var Location|null
	 */
	protected ?Location $location = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $start = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $end = null;
	/**
	 * @var bool
	 */
	protected bool $isFullDay = true;
	/**
	 * @var AttendeeCollection|null
	 */
	protected ?AttendeeCollection $attendeeCollection = null;
	/**
	 * @var Properties\RemindCollection|null
	 */
	protected ?Properties\RemindCollection $remindCollection = null;
	/**
	 * @var string|null
	 */
	protected ?string $description = null;
	/**
	 * @var string|null
	 */
	protected ?string $color = null;
	/**
	 * @var string|null
	 */
	protected ?string $transparent  = null;
	/**
	 * @var Section|null
	 */
	protected ?Section $section  = null;
	/**
	 * @var string|null
	 */
	protected ?string $importance  = null;
	/**
	 * @var string|null
	 */
	protected ?string $accessibility = 'busy';
	/**
	 * @var bool
	 */
	protected bool $isPrivate = false;
	/**
	 * @var Role|null
	 */
	protected ?Role $eventHost = null;
	/**
	 * @var Role|null
	 */
	protected ?Role $creator = null;
	/**
	 * @var Role|null
	 */
	protected ?Role $owner = null;
	/**
	 * @var ?MeetingDescription
	 */
	protected ?MeetingDescription $meetingDescription = null;
	/**
	 * @var int|null
	 */
	protected ?int $version = 0;
	/**
	 * it's actually a special label|tag
	 * @var string|null
	 */
	protected ?string $eventType = null;
	/**
	 * field CAL_TYPE
	 * @var string|null
	 */
	protected ?string $calType = null;
	/**
	 * @var string|null
	 */
	protected ?string $uid = null;
	/**
	 * @var bool
	 */
	protected bool $isActive = true;
	/**
	 * @var bool
	 */
	protected bool $isDeleted = false;
	/**
	 * @var int|null
	 */
	protected ?int $recurrenceId = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $originalDateFrom = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $dateCreate = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $dateModified = null;
	/**
	 * @var ExcludedDatesCollection|null
	 */
	protected ?ExcludedDatesCollection $excludedDateCollection = null;
	/**
	 * @var DateTimeZone|null
	 */
	protected ?DateTimeZone $startTimeZone = null;
	/**
	 * @var DateTimeZone|null
	 */
	protected ?DateTimeZone $endTimeZone = null;
	/**
	 * @var int|null
	 */
	private ?int $parentId = null;
	/**
	 * @var bool
	 */
	private bool $isMeeting = false;
	/**
	 * @var string|null
	 */
	private ?string $meetingStatus = null;
	/**
	 * @var Relations|null
	 */
	private ?Relations $relations = null;

	/**
	 * @param Builder $builder
	 * @return Event
	 */
	public static function fromBuilder(Builder $builder): Event
	{
		return $builder->build();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name ?? '';
	}

	/**
	 * @param string|null $name
	 * @return Event
	 */
	public function setName(?string $name): Event
	{
		$this->name = $name ? Emoji::decode($name) : $name;

		return $this;
	}

	/**
	 * @param RecurringEventRules|null $recurringRule
	 * @return $this
	 */
	public function setRecurringRule(?RecurringEventRules $recurringRule): Event
	{
		$this->recurringRule = $recurringRule;

		return $this;
	}

	/**
	 * @param Location|null $location
	 * @return $this
	 */
	public function setLocation(?Location $location): Event
	{
		$this->location = $location;

		return $this;
	}

	/**
	 * @param Date|null $start
	 *
	 * @return $this
	 */
	public function setStart(?Date $start): Event
	{
		$this->start = $start;

		return $this;
	}

	/**
	 * @param DateTimeZone|null $timezone
	 *
	 * @return Event
	 */
	public function setStartTimeZone(?DateTimeZone $timezone): Event
	{
		$this->startTimeZone = $timezone;

		return $this;
	}

	/**
	 * @param DateTimeZone|null $timezone
	 *
	 * @return $this
	 */
	public function setEndTimeZone(?DateTimeZone $timezone): Event
	{
		$this->endTimeZone = $timezone;
		return $this;
	}

	/**
	 * @param Date|null $end
	 *
	 * @return $this
	 */
	public function setEnd(?Date $end): Event
	{
		$this->end = $end;

		return $this;
	}

	/**
	 * @param AttendeeCollection|null $collection
	 * @return $this
	 */
	public function setAttendeesCollection(?AttendeeCollection $collection): Event
	{
		$this->attendeeCollection = $collection;

		return $this;
	}

	/**
	 * @param bool $isFullDay
	 * @return Event
	 */
	public function setIsFullDay(bool $isFullDay): Event
	{
		$this->isFullDay = $isFullDay;

		return $this;
	}

	/**
	 * @param Properties\RemindCollection|null $remindCollection
	 *
	 * @return Event
	 */
	public function setRemindCollection(?Properties\RemindCollection $remindCollection): Event
	{
		$this->remindCollection = $remindCollection;

		return $this;
	}

	/**
	 * @param Section|null $section
	 * @return $this
	 */
	public function setSection(?Section $section): Event
	{
		$this->section = $section;

		return $this;
	}

	/**
	 * @param string|null $description
	 * @return $this
	 */
	public function setDescription(?string $description): Event
	{
		$this->description = $description ? Emoji::decode($description) : $description;

		return $this;
	}

	/**
	 * @param string|null $color
	 * @return $this
	 */
	public function setColor(?string $color): Event
	{
		$this->color = $color;

		return $this;
	}

	/**
	 * @param string|null $accessibility
	 * @return $this
	 */
	public function setAccessibility(?string $accessibility): Event
	{
		$this->accessibility = $accessibility;

		return $this;
	}

	/**
	 * @param string|null $importance
	 * @return Event
	 */
	public function setImportance(?string $importance): Event
	{
		$this->importance = $importance;

		return $this;
	}

	/**
	 * @param bool $isPrivate
	 * @return $this
	 */
	public function setIsPrivate(bool $isPrivate): Event
	{
		$this->isPrivate = $isPrivate;

		return $this;
	}

	/**
	 * @param Role|null $eventHost
	 * @return $this
	 */
	public function setEventHost(?Role $eventHost): Event
	{
		$this->eventHost = $eventHost;

		return $this;
	}

	/**
	 * @param Role|null $creator
	 * @return $this
	 */
	public function setCreator(?Role $creator): Event
	{
		$this->creator = $creator;

		return $this;
	}

	/**
	 * @param Role|null $owner
	 * @return $this
	 */
	public function setOwner(?Role $owner): Event
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * @param int $version
	 * @return Event
	 */
	public function setVersion(int $version): Event
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @param Date|null $originalDateFrom
	 * @return Event
	 */
	public function setOriginalDateFrom(?Date $originalDateFrom): Event
	{
		$this->originalDateFrom = $originalDateFrom;

		return $this;
	}

	/**
	 * @param int|null $recurrenceId
	 * @return Event
	 */
	public function setRecurrenceId(?int $recurrenceId): Event
	{
		$this->recurrenceId = $recurrenceId;

		return $this;
	}

	/**
	 * @param Date|null $dateCreate
	 * @return Event
	 */
	public function setDateCreate(?Date $dateCreate): Event
	{
		$this->dateCreate = $dateCreate;

		return $this;
	}

	/**
	 * @param Date|null $dateModified
	 * @return Event
	 */
	public function setDateModified(?Date $dateModified): Event
	{
		$this->dateModified = $dateModified;

		return $this;
	}

	/**
	 * @param bool $isActive
	 * @return Event
	 */
	public function setIsActive(bool $isActive): Event
	{
		$this->isActive = $isActive;

		return $this;
	}

	/**
	 * @param bool $isDeleted
	 * @return Event
	 */
	public function setIsDeleted(bool $isDeleted): Event
	{
		$this->isDeleted = $isDeleted;

		return $this;
	}

	/**
	 * @param string|null $uid
	 * @return $this
	 */
	public function setUid(?string $uid): Event
	{
		$this->uid = $uid;

		return $this;
	}

	/**
	 * @param string|null $transparent
	 * @return Event
	 */
	public function setTransparent(?string $transparent): Event
	{
		$this->transparent = $transparent;

		return $this;
	}

	/**
	 * @param ExcludedDatesCollection|null $excludedDateCollection
	 * @return Event
	 */
	public function setExcludedDateCollection(?ExcludedDatesCollection $excludedDateCollection): Event
	{
		$this->excludedDateCollection = $excludedDateCollection;

		return $this;
	}

	/**
	 * @param string|null $eventType
	 *
	 * @return $this
	 *
	 * @deprecated use setSpecialLabel() and setCalendarType();
	 */
	public function setEventType(?string $eventType): Event
	{
		$this->eventType = $eventType;

		return $this;
	}

	/**
	 * @param string|null $label
	 *
	 * @return $this
	 */
	public function setSpecialLabel(?string $label): Event
	{
		$this->eventType = $label;

		return $this;
	}

	/**
	 * @param MeetingDescription|null $meetingDescription
	 *
	 * @return Event
	 */
	public function setMeetingDescription(?MeetingDescription $meetingDescription): Event
	{
		$this->meetingDescription = $meetingDescription;

		return $this;
	}

	/**
	 * @param Relations|null $relations
	 *
	 * @return $this
	 */
	public function setRelations(?Relations $relations): Event
	{
		$this->relations = $relations;

		return $this;
	}


	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return RecurringEventRules|null
	 */
	public function getRecurringRule(): ?RecurringEventRules
	{
		return $this->recurringRule;
	}

	/**
	 * @return Location|null
	 */
	public function getLocation(): ?Location
	{
		return $this->location;
	}

	/**
	 * @return Date
	 */
	public function getStart(): Date
	{
		return $this->start;
	}

	/**
	 * @return DateTimeZone|null
	 */
	public function getStartTimeZone(): ?DateTimeZone
	{
		return $this->startTimeZone;
	}

	/**
	 * @return DateTimeZone|null
	 */
	public function getEndTimeZone(): ?DateTimeZone
	{
		return $this->endTimeZone;
	}

	/**
	 * @return Date
	 */
	public function getEnd(): Date
	{
		return $this->end;
	}

	/**
	 * @return AttendeeCollection
	 */
	public function getAttendeesCollection(): ?AttendeeCollection
	{
		if (is_null($this->attendeeCollection))
		{
			$this->initAttendeesCollection();
		}
		return $this->attendeeCollection;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return Role|null
	 */
	public function getOwner(): ?Role
	{
		return $this->owner;
	}

	/**
	 * @return Role|null
	 */
	public function getCreator(): ?Role
	{
		return $this->creator;
	}

	/**
	 * @return Role|null
	 */
	public function getEventHost(): ?Role
	{
		return $this->eventHost;
	}

	/**
	 * @return bool
	 */
	public function getIsPrivate(): bool
	{
		return $this->isPrivate;
	}

	/**
	 * @return bool
	 */
	public function isPrivate(): bool
	{
		return $this->isPrivate;
	}

	/**
	 * @return string|null
	 */
	public function getAccessibility(): ?string
	{
		return $this->accessibility;
	}

	/**
	 * @return string|null
	 */
	public function getImportance(): ?string
	{
		return $this->importance;
	}

	/**
	 * @return Section
	 */
	public function getSection(): Section
	{
		if ($this->section === null)
		{
			$this->initSection();
		}

		return $this->section;
	}

	/**
	 * @return string|null
	 */
	public function getColor(): ?string
	{
		return $this->color;
	}

	/**
	 * @return string|null
	 */
	public function getTransparent(): ?string
	{
		return $this->transparent;
	}

	/**
	 * @return MeetingDescription
	 */
	public function getMeetingDescription(): ?MeetingDescription
	{
		return $this->meetingDescription;
	}

	/**
	 * @return Relations|null
	 */
	public function getRelations(): ?Relations
	{
		return $this->relations;
	}

	/**
	 * @return int
	 */
	public function getVersion(): int
	{
		return $this->version;
	}

	/**
	 * @return string|null
	 *
	 * @deprecated use $this->getSpecialLabel() and $this->getCalendarType()
	 */
	public function getEventType(): ?string
	{
		return $this->eventType;
	}

	/**
	 * @return string|null
	 */
	public function getSpecialLabel(): ?string
	{
		return $this->eventType;
	}

	/**
	 * @return string|null
	 */
	public function getUid(): ?string
	{
		return $this->uid;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->isActive;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->isDeleted;
	}

	/**
	 * @return bool
	 */
	public function isInstance(): bool
	{
		return $this->recurrenceId && $this->originalDateFrom;
	}

	/**
	 * @return int|null
	 */
	public function getRecurrenceId(): ?int
	{
		return $this->recurrenceId;
	}

	/**
	 * @return Date|null
	 */
	public function getOriginalDateFrom(): ?Date
	{
		return $this->originalDateFrom;
	}

	/**
	 * @return Date|null
	 */
	public function getDateCreate(): ?Date
	{
		return $this->dateCreate;
	}

	/**
	 * @return Date|null
	 */
	public function getDateModified(): ?Date
	{
		return $this->dateModified;
	}

	/**
	 * @return ExcludedDatesCollection
	 */
	public function getExcludedDateCollection(): ?ExcludedDatesCollection
	{
		return $this->excludedDateCollection;
	}

	/**
	 * @return bool
	 */
	public function isFullDayEvent(): bool
	{
		return $this->isFullDay;
	}

	/**
	 * @return bool
	 */
	public function isRecurrence(): bool
	{
		return $this->recurringRule && $this->recurringRule->getFrequency();
	}

	/**
	 * @return bool
	 */
	public function isSingle(): bool
	{
		return !$this->isInstance() && !$this->isRecurrence();
	}

	/**
	 * @return bool
	 */
	public function isBaseEvent(): bool
	{
		return ($this->id === $this->parentId) && ($this->id !== null);
	}

	/**
	 * @return Properties\RemindCollection|null
	 */
	public function getRemindCollection(): ?Properties\RemindCollection
	{
		return $this->remindCollection;
	}

	/**
	 * @return bool
	 */
	public function isMeeting(): bool
	{
		if ($this->isMeeting === false)
		{
			return $this->attendeeCollection
				&& count($this->attendeeCollection) >= self::MIN_MEETING_PARTICIPANT;
		}

		return true;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @param int|null $id
	 * @return $this
	 */
	public function setId(?int $id): Event
	{
		$this->id = $id;

		return $this;
	}

	private function initAttendeesCollection(): void
	{
		// TODO: need to implement a logic
		$this->attendeeCollection = new AttendeeCollection();
	}

	private function initSection(): void
	{
		// TODO: need to implement a logic
		$this->section = new Section();
	}

	/**
	 * @param int|null $parentId
	 * @return $this
	 */
	public function setParentId(?int $parentId): Event
	{
		$this->parentId = $parentId;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getParentId(): ?int
	{
		return $this->parentId;
	}

	/**
	 * @param bool $isMeeting
	 * @return $this
	 */
	public function setIsMeeting(bool $isMeeting): Event
	{
		$this->isMeeting = $isMeeting;

		return $this;
	}

	/**
	 * @param string|null $meetingStatus
	 * @return $this
	 */
	public function setMeetingStatus(?string $meetingStatus): Event
	{
		$this->meetingStatus = $meetingStatus;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getMeetingStatus(): ?string
	{
		return $this->meetingStatus;
	}

	/**
	 * @return bool
	 */
	public function isNew(): bool
	{
		return $this->id === 0 || $this->id === null;
	}

	/**
	 * @return $this
	 */
	public function upVersion(): self
	{
		$this->version++;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCalendarType(): ?string
	{
		return $this->calType;
	}

	/**
	 * @param string|null $calendarType
	 *
	 * @return $this
	 */
	public function setCalendarType(?string $calendarType): self
	{
		$this->calType = $calendarType;
		return $this;
	}
}