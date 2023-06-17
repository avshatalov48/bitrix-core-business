<?php

namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
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
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\EO_User;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;

abstract class EventBuilder implements Builder
{
	private Event $event;

	public function setBaseEvent(Event $event): self
	{
		$this->event = $event;

		return $this;
	}

	/**
	 * @return Event
	 *
	 */
	public function build(): Event
	{
		return $this->getBaseEvent()
			->setId($this->getId())
			->setParentId($this->getParentId())
			->setName($this->getName())
			->setRecurringRule($this->getRecurringRule())
			->setLocation($this->getLocation())
			->setStart($this->getStart())
			->setStartTimeZone($this->getStartTimezone())
			->setEnd($this->getEnd())
			->setEndTimeZone($this->getEndTimezone())
			->setIsFullDay($this->getFullDay())
			->setAttendeesCollection($this->getAttendees())
			->setRemindCollection($this->getReminders())
			->setSection($this->getSection())
			->setDescription($this->getDescription())
			->setColor($this->getColor())
			->setTransparent($this->getTransparency())
			->setAccessibility($this->getAccessibility())
			->setImportance($this->getImportance())
			->setIsPrivate($this->getIsPrivate())
			->setEventHost($this->getEventHost())
			->setCreator($this->getCreator())
			->setOwner($this->getOwner())
			->setMeetingDescription($this->getMeetingDescription())
			->setVersion($this->getVersion())
			->setCalendarType($this->getCalendarType())
			->setSpecialLabel($this->getSpecialLabel())
			->setUid($this->getUid())
			->setIsActive($this->isActive())
			->setIsDeleted($this->isDeleted())
			->setRecurrenceId($this->getRecurrenceId())
			->setDateCreate($this->getDateCreate())
			->setDateModified($this->getDateModified())
			->setOriginalDateFrom($this->getOriginalDate())
			->setExcludedDateCollection($this->getExcludedDate())
			->setIsMeeting($this->isMeeting())
			->setMeetingStatus($this->getMeetingStatus())
			->setOriginalDateFrom($this->getOriginalDate())
			->setRelations($this->getRelations())
		;
	}

	/**
	 * @return Event
	 */
	protected function getBaseEvent(): Event
	{
		if (empty($this->event))
		{
			$this->event = new Event();
		}
		return $this->event;
	}

	/**
	 * @param array|string|null $ruleData
	 *
	 * @return RecurringEventRules|null
	 *
	 * @throws ObjectException
	 */
	protected function prepareRecurringRule($ruleData = null): ?RecurringEventRules
	{
		if (empty($ruleData))
		{
			return null;
		}

		if (is_string($ruleData))
		{
			$ruleData = \CCalendarEvent::ParseRRULE($ruleData);
		}

		if (
			isset($ruleData['FREQ'])
			&& $ruleData['FREQ'] !== 'NONE'
		)
		{
			$rule = new RecurringEventRules($ruleData['FREQ']);

			if (isset($ruleData['COUNT']))
			{
				$rule->setCount((int)$ruleData['COUNT']);
			}

			if (is_string($ruleData['UNTIL'] ?? null))
			{
				$ruleData['UNTIL'] = \CCalendarEvent::convertDateToCulture($ruleData['UNTIL']);
				$rule->setUntil(new Date(Util::getDateObject($ruleData['UNTIL'])));
			}

			if (isset($ruleData['INTERVAL']))
			{
				$rule->setInterval((int)$ruleData['INTERVAL']);
			}

			if (!empty($ruleData['BYDAY']) && $ruleData['FREQ'] === RecurringEventRules::FREQUENCY_WEEKLY)
			{
				if (
					is_string($ruleData['BYDAY'])
				)
				{
					$rule->setByDay(explode(",", $ruleData['BYDAY']));
				}
				elseif (
					is_array($ruleData['BYDAY'])
				)
				{
					$rule->setByDay($ruleData['BYDAY']);
				}
			}

			return $rule;
		}

		return null;
	}

	/**
	 * @param array|string|null $locationData
	 *
	 * @return Location|null
	 */
	protected function prepareLocation($locationData = ''): ?Location
	{
		if (!$locationData)
		{
			return null;
		}

		if (is_array($locationData) && isset($locationData['NEW']))
		{
			$location = new Location($locationData['NEW']);
			if (isset($locationData['OLD']))
			{
				$location->setOriginalLocation($locationData['OLD']);
			}

			return $location;
		}

		if (is_string($locationData))
		{
			return new Location($locationData);
		}

		return null;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function prepareEventHost(int $hostId = null): ?Role
	{
		return $this->prepareUserInstance($hostId);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function prepareUserInstance(int $userId): ?Role
	{
		try
		{
			return Helper::getUserRole($userId);
		}
		catch (BaseException $e)
		{}

		return null;
	}

	/**
	 * @param int $userId
	 *
	 * @return EO_User|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getUserEntityObject(int $userId): ?EO_User
	{
		return User::$users[$userId] = UserTable::query()
			->setSelect(['*'])
			->whereIn('ID', $userId)
			->exec()
			->fetchObject()
		;
	}

	protected function fillAttendeeCollection(AttendeeCollection $collection, $hostEventId)
	{
		// TODO: implement method
	}

	/**
	 * @return int|null
	 */
	abstract protected function getId(): ?int;

	/**
	 * @return int|null
	 */
	abstract protected function getParentId(): ?int;

	/**
	 * @return string
	 */
	abstract protected function getName(): string;

	/**
	 * @return DateTimeZone
	 */
	abstract protected function getStartTimezone(): ?DateTimeZone;

	/**
	 * @return DateTimeZone
	 */
	abstract protected function getEndTimezone(): ?DateTimeZone;

	/**
	 * @return RecurringEventRules|null
	 */
	abstract protected function getRecurringRule(): ?RecurringEventRules;

	/**
	 * @return Location|null
	 */
	abstract protected function getLocation(): ?Location;

	/**
	 * @return Date
	 */
	abstract protected function getStart(): Date;

	/**
	 * @return Date
	 */
	abstract protected function getEnd(): Date;

	/**
	 * @return bool
	 */
	abstract protected function getFullDay(): bool;

	/**
	 * @return AttendeeCollection|null
	 */
	abstract protected function getAttendees(): ?AttendeeCollection;

	/**
	 * @return RemindCollection
	 */
	abstract protected function getReminders(): RemindCollection;

	/**
	 * @return string|null
	 */
	abstract protected function getDescription(): ?string;

	/**
	 * @return Section
	 */
	abstract protected function getSection(): Section;

	/**
	 * @return string|null
	 */
	abstract protected function getColor(): ?string;

	/**
	 * @return string|null
	 */
	abstract protected function getTransparency(): ?string;

	/**
	 * @return string|null
	 */
	abstract protected function getImportance(): ?string;

	/**
	 * @return string|null
	 */
	abstract protected function getAccessibility(): ?string;

	/**
	 * @return bool
	 */
	abstract protected function getIsPrivate(): bool;

	/**
	 * @return Role|null
	 */
	abstract protected function getEventHost(): ?Role;

	/**
	 * @return Role|null
	 */
	abstract protected function getCreator(): ?Role;

	/**
	 * @return Role|null
	 */
	abstract protected function getOwner(): ?Role;

	/**
	 * @return MeetingDescription|null
	 */
	abstract protected function getMeetingDescription(): ?MeetingDescription;

	/**
	 * @return int
	 */
	abstract protected function getVersion(): int;

	/**
	 * @return string|null
	 */
	abstract protected function getCalendarType(): ?string;

	/**
	 * @return string|null
	 */
	abstract protected function getSpecialLabel(): ?string;

	/**
	 * @return string|null
	 */
	abstract protected function getUid(): ?string;

	/**
	 * @return bool
	 */
	abstract protected function isDeleted(): bool;

	/**
	 * @return bool
	 */
	abstract protected function isActive(): bool;

	/**
	 * @return int|null
	 */
	abstract protected function getRecurrenceId(): ?int;

	/**
	 * @return Date|null
	 */
	abstract protected function getOriginalDate(): ?Date;

	/**
	 * @return Date|null
	 */
	abstract protected function getDateCreate(): ?Date;

	/**
	 * @return Date|null
	 */
	abstract protected function getDateModified(): ?Date;

	/**
	 * @return ExcludedDatesCollection
	 */
	abstract protected function getExcludedDate(): ExcludedDatesCollection;

	/**
	 * @return bool
	 */
	abstract protected function isMeeting(): bool;

	/**
	 * @return string|null
	 */
	abstract protected function getMeetingStatus(): ?string;

	/**
	 * @return Relations|null
	 */
	abstract protected function getRelations(): ?Relations;

	/**
	 * @param $meeting
	 *
	 * @return MeetingDescription|null
	 */
	protected function prepareMeetingDescription($meeting = null): ?MeetingDescription
	{
		if (!isset($meeting))
		{
			return null;
		}

		$meeting = is_string($meeting)
			? unserialize($meeting, ['allowed_classes' => false])
			: $meeting;

		if ($meeting && !empty($meeting['HOST_NAME']))
		{
			return (new MeetingDescription())
				->setAllowInvite((bool)($meeting['ALLOW_INVITE'] ?? null))
				->setReInvite((bool)($meeting['REINVITE'] ?? null))
				->setHideGuests((bool)($meeting['HIDE_GUESTS'] ?? null))
				->setHostName($meeting['HOST_NAME'])
				->setIsNotify((bool)($meeting['NOTIFY'] ?? null))
				->setMeetingCreator((int)($meeting['MEETING_CREATOR'] ?? null))
				->setLanguageId($meeting['LANGUAGE_ID'] ?? null)
				->setMailFrom($meeting['MAIL_FROM'] ?? null)
				->setChatId($meeting['CHAT_ID'] ?? null)
			;
		}

		return null;
	}

	protected function prepareRelations($relations): ?Relations
	{
		if (!isset($relations))
		{
			return null;
		}

		$relations = is_string($relations)
			? unserialize($relations, ['allowed_classes' => false])
			: $relations
		;

		if ($relations && !empty($relations['COMMENT_XML_ID']))
		{
			return (new Relations($relations['COMMENT_XML_ID']));
		}

		return null;
	}

	/**
	 * @param string $dates
	 *
	 * @return ExcludedDatesCollection
	 * @throws ObjectException
	 */
	protected function prepareExcludedDates(string $dates = ''): ExcludedDatesCollection
	{
		if (empty($dates))
		{
			return new ExcludedDatesCollection();
		}

		$collection = new ExcludedDatesCollection();
		foreach (explode(";", $dates) as $exDate)
		{
			$collection->add($this->createDateForRecurrence($exDate));
		}

		return $collection;
	}

	/**
	 * @param string $date
	 *
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	protected function createDateForRecurrence(string $date): Date
	{
		if ($date[2] === '.' && $date[5] === '.')
		{
			return Date::createDateFromFormat(
				$date,
				ExcludedDatesCollection::EXCLUDED_DATE_FORMAT
			);
		}

		return new Date(Util::getDateObject($date));
	}
}
