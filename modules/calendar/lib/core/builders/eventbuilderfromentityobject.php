<?php


namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Event;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Core\Event\Properties\Location;
use Bitrix\Calendar\Core\Event\Properties\MeetingDescription;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Relations;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;
use Bitrix\Calendar\Core\Event\Tools\UidGenerator;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CCalendarEvent;

class EventBuilderFromEntityObject extends EventBuilder
{
	/**
	 * @var EO_Event
	 */
	private EO_Event $event;

	/**
	 * @param EO_Event $event
	 */
	public function __construct(EO_Event $event)
	{
		$this->event = $event;
	}

	/**
	 * @return int|null
	 */
	protected function getId(): ?int
	{
		return $this->event->getId();
	}

	protected function getParentId(): ?int
	{
		return $this->event->getParentId();
	}

	protected function getName(): string
	{
		return $this->event->getName();
	}

	protected function getStartTimezone(): ?DateTimeZone
	{
		if (!$this->event->getTzFrom())
		{
			return null;
		}

		return new DateTimeZone(Util::prepareTimezone($this->event->getTzFrom()));
	}

	protected function getEndTimezone(): ?DateTimeZone
	{
		if (!$this->event->getTzTo())
		{
			return null;
		}

		return new DateTimeZone(Util::prepareTimezone($this->event->getTzTo()));
	}

	/**
	 * @return RecurringEventRules|null
	 * @throws ObjectException
	 */
	protected function getRecurringRule(): ?RecurringEventRules
	{
		$rule = CCalendarEvent::convertDateToCulture($this->event->getRrule());
		return $this->prepareRecurringRule(
			CCalendarEvent::ParseRRULE($rule)
		);
	}

	protected function getLocation(): ?Location
	{
		return $this->prepareLocation($this->event->getLocation());
	}

	/**
	 * @throws ObjectException
	 */
	protected function getStart(): Date
	{
		return new Date(Util::getDateObject(
            $this->event->getDateFrom()
                ? $this->event->getDateFrom()->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME))
                : null
            ,
			false,
			$this->getStartTimezone() ? $this->getStartTimezone()->getTimeZone()->getName() : null
		));
	}

	/**
	 * @throws ObjectException
	 */
	protected function getEnd(): Date
	{
		return new Date(Util::getDateObject(
            $this->event->getDateTo()
                ? $this->event->getDateTo()->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME))
                : null
            ,
			false,
			$this->getEndTimezone() ? $this->getEndTimezone()->getTimeZone()->getName() : null
		));
	}

	/**
	 * @return Date|null
	 * @throws ObjectException
	 */
	protected function getOriginalDate(): ?Date
	{
		if (empty($this->event->getOriginalDateFrom()))
		{
			return null;
		}

		return new Date(Util::getDateObject(
			$this->event->getOriginalDateFrom()->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME)),
			false,
			$this->getStartTimezone() ? $this->getStartTimezone()->getTimeZone()->getName() : null
		));
	}


	protected function getFullDay(): bool
	{
		return $this->event->getDtSkipTime();
	}

	protected function getAttendees(): ?AttendeeCollection
	{
		$collection = new AttendeeCollection();
		if (is_string($this->event->getAttendeesCodes()))
		{
			$collection->setAttendeesCodes(explode(',', $this->event->getAttendeesCodes()));
		}
		else
		{
			$collection->setAttendeesId([$this->event->getOwnerId()]);
		}

		return $collection;
	}

	/**
	 * @return RemindCollection
	 * @throws ObjectException
	 */
	protected function getReminders(): RemindCollection
	{
		$remindField = $this->event->getRemind();
		if (is_string($remindField))
		{
			$remindField = unserialize($remindField, ['allowed_classes' => false]);
		}

		if (!is_array($remindField))
		{
			return new RemindCollection();
		}

		$eventStart = $this->getStart();

		$collection = new RemindCollection();
		$collection->setEventStart($eventStart);
		foreach ($remindField as $remind)
		{
			if ($remind['type'] === Event\Tools\Dictionary::REMIND_UNIT['date'])
			{
				$collection->add((new Event\Properties\Remind())
					->setSpecificTime(
						new Date(Util::getDateObject(
							$remind['value'],
							false,
							$this->getStartTimezone()
						))
					)
					->setEventStart($eventStart)
				);
			}
			elseif ($remind['type'] === Event\Properties\Remind::UNIT_DAY_BEFORE)
			{
				$collection->add((new Event\Properties\Remind())
					->setEventStart($eventStart)
					->setSpecificTime(
						(new Date(Util::getDateObject(
							$eventStart->toString(),
							false,
							$this->getStartTimezone())
						))
						->resetTime()
						->sub("{$remind['before']} days")
						->add("{$remind['time']} minutes")
					)
					->setDaysBefore($remind['before'])
				);
			}
			else
			{
				$collection->add((new Event\Properties\Remind())
					->setTimeBeforeEvent(
						$remind['count'],
						Event\Tools\Dictionary::REMIND_UNIT[$remind['type']]
					)
					->setEventStart($eventStart)
				);
			}
		}

		return $collection;
	}

	protected function getDescription(): ?string
	{
		return $this->event->getDescription();
	}

	/**
	 * @return Section|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ObjectException
	 */
	protected function getSection(): Section
	{
		if ($this->event->getSectionId())
		{
			/** @var Factory $mapper */
			$mapper = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

			return $mapper->getSection()->getById($this->event->getSectionId());
		}

		throw new ObjectException('Section ID not found');
	}

	protected function getColor(): ?string
	{
		return $this->event->getColor();
	}

	protected function getTransparency(): ?string
	{
		// TODO: what to do here?
		return '';
	}

	protected function getImportance(): ?string
	{
		return $this->event->getImportance();
	}

	protected function getAccessibility(): ?string
	{
		return $this->event->getAccessibility();
	}

	protected function getIsPrivate(): bool
	{
		return (bool) $this->event->getPrivateEvent();
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function getEventHost(): ?Role
	{
		if (!$this->event->getMeetingHost())
		{
			return null;
		}
		try
		{
			return Helper::getUserRole($this->event->getMeetingHost());
		}
		catch (BaseException $exception)
		{
			return null;
		}
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function getCreator(): ?Role
	{
		if (!$this->event->getCreatedBy())
		{
			return null;
		}
		try
		{
			return Helper::getUserRole($this->event->getCreatedBy());
		}
		catch (BaseException $exception)
		{
			return null;
		}
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function getOwner(): ?Role
	{
		if (!$this->event->getOwnerId())
		{
			return null;
		}
		try
		{
			return Helper::getRole($this->event->getOwnerId(), $this->event->getCalType());
		}
		catch (BaseException $exception)
		{
			return null;
		}
	}

	/**
	 * @return MeetingDescription|null
	 */
	protected function getMeetingDescription(): ?MeetingDescription
	{
		return $this->prepareMeetingDescription($this->event->getMeeting());
	}

	protected function getVersion(): int
	{
		return (int)$this->event->getVersion();
	}

	/**
	 * @return string|null
	 */
	protected function getCalendarType(): ?string
	{
		return $this->event->getCalType();
	}

	/**
	 * @throws ObjectException
	 */
	protected function getUid(): ?string
	{
		$uid = $this->event->getDavXmlId();
		if ($uid == $this->event->getId())
		{
			$uid = UidGenerator::createInstance()
				->setPortalName(Util::getServerName())
				->setDate(new Date(Util::getDateObject(
					$this->event->getDateFrom()->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME)),
					false,
					$this->getStartTimezone() ? $this->getStartTimezone()->getTimeZone()->getName() : null
				)))
				->setUserId((int)$this->event->getOwnerId())
				->getUidWithDate()
			;
		}

		return $uid;
	}

	protected function isDeleted(): bool
	{
		return $this->event->getDeleted();
	}

	protected function isActive(): bool
	{
		return $this->event->getActive();
	}

	protected function getRecurrenceId(): ?int
	{
		return $this->event->getRecurrenceId();
	}

	protected function getDateCreate(): ?Date
	{
		if (empty($this->event->getDateCreate()))
		{
			return null;
		}

		return new Date($this->event->getDateCreate());
	}

	protected function getDateModified(): ?Date
	{
		if (empty($this->event->getTimestampX()))
		{
			return null;
		}

		return new Date($this->event->getTimestampX());
	}

	/**
	 * @throws ObjectException
	 */
	protected function getExcludedDate(): ExcludedDatesCollection
	{
		return $this->prepareExcludedDates($this->event->getExdate());
	}

	protected function isMeeting(): bool
	{
		return (bool)$this->event->getIsMeeting();
	}

	protected function getMeetingStatus(): ?string
	{
		return $this->event->getMeetingStatus();
	}

	protected function getRelations(): ?Relations
	{
		return $this->prepareRelations($this->event->getRelations());
	}

	/**
	 * @return string|null
	 */
	protected function getSpecialLabel(): ?string
	{
		return $this->event->getEventType();
	}
}
