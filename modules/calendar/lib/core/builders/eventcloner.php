<?php

namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Event\Properties\Location;
use Bitrix\Calendar\Core\Event\Properties\MeetingDescription;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Relations;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Main\ObjectException;

class EventCloner extends EventBuilder
{
	private Event $originalEvent;

	public function __construct(Event $originalEvent)
	{
		$this->originalEvent = $originalEvent;
	}

	/**
	 * @return int|null
	 */
	protected function getId(): ?int
	{
		return $this->originalEvent->getId();
	}

	/**
	 * @return int|null
	 */
	protected function getParentId(): ?int
	{
		return $this->originalEvent->getParentId();
	}

	/**
	 * @return string
	 */
	protected function getName(): string
	{
		return $this->originalEvent->getName() ?? '';
	}

	/**
	 * @return DateTimeZone
	 */
	protected function getStartTimezone(): ?DateTimeZone
	{
		return $this->originalEvent->getStartTimeZone()
			? new DateTimeZone(clone $this->originalEvent->getStartTimeZone()->getTimeZone())
			: null
			;
	}

	/**
	 * @return DateTimeZone
	 */
	protected function getEndTimezone(): ?DateTimeZone
	{
		return $this->originalEvent->getEndTimeZone()
			? new DateTimeZone(clone $this->originalEvent->getEndTimeZone()->getTimeZone())
			: null
			;
	}

	/**
	 * @return RecurringEventRules|null
	 *
	 * @throws ObjectException
	 */
	protected function getRecurringRule(): ?RecurringEventRules
	{
		if ($this->originalEvent->getRecurringRule())
		{
			$result = clone $this->originalEvent->getRecurringRule();
			if ($result->hasUntil())
			{
				$result->setUntil($this->cloneDate($result->getUntil()));
			}

			return $result;
		}

		return null;
	}

	/**
	 * @return Location|null
	 */
	protected function getLocation(): ?Location
	{
		return $this->originalEvent->getLocation() ? clone $this->originalEvent->getLocation() : null;
	}

	/**
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	protected function getStart(): Date
	{
		return $this->cloneDate($this->originalEvent->getStart());
	}

	/**
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	protected function getEnd(): Date
	{
		return $this->cloneDate($this->originalEvent->getEnd());
	}

	/**
	 * @return bool
	 */
	protected function getFullDay(): bool
	{
		return $this->originalEvent->isFullDayEvent();
	}

	/**
	 * @return AttendeeCollection|null
	 */
	protected function getAttendees(): ?AttendeeCollection
	{
		return $this->originalEvent->getAttendeesCollection();
	}

	/**
	 * @return RemindCollection
	 *
	 * @throws ObjectException
	 */
	protected function getReminders(): RemindCollection
	{
		$result = new RemindCollection();
		if ($this->originalEvent->getRemindCollection())
		{
			$result
				->setCollection($this->originalEvent->getRemindCollection()->getCollection())
				->setSingle($this->originalEvent->getRemindCollection()->isSingle())
			;
			if ($this->originalEvent->getRemindCollection()->getEventStart())
			{
				$result->setEventStart($this->cloneDate($this->originalEvent->getRemindCollection()->getEventStart()));
			}
			else if ($this->originalEvent->getStart())
			{
				$result->setEventStart($this->cloneDate($this->originalEvent->getStart()));
			}
		}

		return $result;
	}

	/**
	 * @return string|null
	 */
	protected function getDescription(): ?string
	{
		return $this->originalEvent->getDescription();
	}

	/**
	 * @return Section
	 */
	protected function getSection(): Section
	{
		return $this->originalEvent->getSection();
	}

	/**
	 * @return string|null
	 */
	protected function getColor(): ?string
	{
		return $this->originalEvent->getColor();
	}

	/**
	 * @return string|null
	 */
	protected function getTransparency(): ?string
	{
		return $this->originalEvent->getTransparent();
	}

	/**
	 * @return string|null
	 */
	protected function getImportance(): ?string
	{
		return $this->originalEvent->getImportance();
	}

	/**
	 * @return string|null
	 */
	protected function getAccessibility(): ?string
	{
		return $this->originalEvent->getAccessibility();
	}

	/**
	 * @return bool
	 */
	protected function getIsPrivate(): bool
	{
		return $this->originalEvent->isPrivate();
	}

	/**
	 * @return Role|null
	 */
	protected function getEventHost(): ?Role
	{
		return $this->cloneRole($this->originalEvent->getEventHost());
	}

	/**
	 * @return Role|null
	 */
	protected function getCreator(): ?Role
	{
		return $this->cloneRole($this->originalEvent->getCreator());
	}

	/**
	 * @return Role|null
	 */
	protected function getOwner(): ?Role
	{
		return $this->cloneRole($this->originalEvent->getOwner());
	}

	/**
	 * @return MeetingDescription|null
	 */
	protected function getMeetingDescription(): ?MeetingDescription
	{
		return $this->originalEvent->getMeetingDescription()
			? clone $this->originalEvent->getMeetingDescription()
			: null;
	}

	/**
	 * @return int
	 */
	protected function getVersion(): int
	{
		return $this->originalEvent->getVersion();
	}

	/**
	 * @return string|null
	 */
	protected function getCalendarType(): ?string
	{
		return $this->originalEvent->getCalendarType();
	}

	/**
	 * @return string|null
	 */
	protected function getSpecialLabel(): ?string
	{
		return $this->originalEvent->getSpecialLabel();
	}

	/**
	 * @return string|null
	 */
	protected function getUid(): ?string
	{
		return $this->originalEvent->getUid();
	}

	/**
	 * @return bool
	 */
	protected function isDeleted(): bool
	{
		return $this->originalEvent->isDeleted();
	}

	/**
	 * @return bool
	 */
	protected function isActive(): bool
	{
		return $this->originalEvent->isActive();
	}

	/**
	 * @return int|null
	 */
	protected function getRecurrenceId(): ?int
	{
		return $this->originalEvent->getRecurrenceId();
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getOriginalDate(): ?Date
	{
		return $this->cloneDate($this->originalEvent->getOriginalDateFrom());
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getDateCreate(): ?Date
	{
		return $this->cloneDate($this->originalEvent->getDateCreate());
	}

	/**
	 * @return Date|null
	 *
	 * @throws ObjectException
	 */
	protected function getDateModified(): ?Date
	{
		return $this->cloneDate($this->originalEvent->getDateModified());
	}

	/**
	 * @return ExcludedDatesCollection
	 */
	protected function getExcludedDate(): ExcludedDatesCollection
	{
		return clone $this->originalEvent->getExcludedDateCollection();
	}

	/**
	 * @return bool
	 */
	protected function isMeeting(): bool
	{
		return $this->originalEvent->isMeeting();
	}

	/**
	 * @return string|null
	 */
	protected function getMeetingStatus(): ?string
	{
		return $this->originalEvent->getMeetingStatus();
	}

	/**
	 * @return Relations|null
	 */
	protected function getRelations(): ?Relations
	{
		return $this->originalEvent->getRelations()
			? clone $this->originalEvent->getRelations()
			: null;
	}

	/**
	 * @param Date|null $date
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	private function cloneDate(?Date $date): ?Date
	{
		$format = 'YmdHisO';
		return $date
			? Date::createDateTimeFromFormat($date->format($format), $format)
			: null
			;
	}

	/**
	 * @param Role|null $role
	 *
	 * @return Role|null
	 */
	private function cloneRole(?Role $role): ?Role
	{
		return $role ?
			new Role($role->getRoleEntity())
			: null
			;
	}
}