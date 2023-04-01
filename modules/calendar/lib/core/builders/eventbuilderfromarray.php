<?php

namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Event;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Calendar\Core\Base\Date;
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
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use DateTime;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');

class EventBuilderFromArray extends EventBuilder
{
	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * @param array $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $fields;
		$this->prepareRecurrenceRuleField();
	}

	private function prepareRecurrenceRuleField(): void
	{
		if (!empty($this->fields['RRULE']) && is_string($this->fields['RRULE']))
		{
			$result = [];
			foreach (explode(';', $this->fields['RRULE']) as $item) {
				if (!empty($item))
				{
					[$key, $value] = explode('=', $item);
					$result[$key] = $value;
				}
			}
			$this->fields['RRULE'] = $result;
		}
	}

	/**
	 * @return string
	 */
	protected function getName(): string
	{
		return $this->fields['NAME'] ?? '';
	}

	/**
	 * @return DateTimeZone
	 */
	protected function getStartTimezone(): ?DateTimeZone
	{
		if (!isset($this->fields['TZ_FROM']))
		{
			return null;
		}

		return new DateTimeZone(Util::prepareTimezone($this->fields['TZ_FROM']));
	}

	/**
	 * @return DateTimeZone
	 */
	protected function getEndTimezone(): ?DateTimeZone
	{
		if (!isset($this->fields['TZ_TO']))
		{
			return null;
		}

		return new DateTimeZone(Util::prepareTimezone($this->fields['TZ_TO']));
	}

	/**
	 * @return RecurringEventRules|null
	 *
	 * @throws ObjectException
	 */
	protected function getRecurringRule(): ?RecurringEventRules
	{
		if (!empty($this->fields['RRULE']))
		{
			if (is_string($this->fields['RRULE']))
			{
				$this->fields['RRULE'] = \CCalendarEvent::convertDateToCulture($this->fields['RRULE']);
			}
			elseif (is_array($this->fields['RRULE']) && !empty($this->fields['RRULE']['UNTIL']))
			{
				$this->fields['RRULE']['UNTIL'] = \CCalendarEvent::convertDateToCulture($this->fields['RRULE']['UNTIL']);
			}

			return $this->prepareRecurringRule($this->fields['RRULE']);
		}
		else
		{
			return null;
		}
	}

	/**
	 * @return Location|null
	 */
	protected function getLocation(): ?Location
	{
		return $this->prepareLocation($this->fields['LOCATION'] ?? null);
	}

	/**
	 * @return Date
	 * @throws ObjectException
	 */
	protected function getStart(): Date
	{
		return new Date(
			Util::getDateObject(
				$this->fields['DATE_FROM'] ?? null,
				$this->isFullDay(),
                $this->fields['TZ_FROM'] ?? null
			)
		);
	}

	/**
	 * @return Date
	 * @throws ObjectException
	 */
	protected function getEnd(): Date
	{
		return new Date(
			Util::getDateObject(
				$this->fields['DATE_TO'] ?? null,
                $this->isFullDay(),
                $this->fields['TZ_TO'] ?? null,
			)
		);
	}

	private function isFullDay(): bool
	{
		return (isset($this->fields['SKIP_TIME']) && $this->fields['SKIP_TIME'] === 'Y')
			||	(isset($this->fields['DT_SKIP_TIME']) && $this->fields['DT_SKIP_TIME'] === 'Y');
	}

	/**
	 * @return Date|null
	 * @throws ObjectException
	 */
	protected function getOriginalDate(): ?Date
	{
		if (!isset($this->fields['ORIGINAL_DATE_FROM']))
		{
			return null;
		}

		return new Date(Util::getDateObject(
			$this->fields['ORIGINAL_DATE_FROM'],
			($this->fields['SKIP_TIME'] ?? null) === 'Y' || ($this->fields['DT_SKIP_TIME'] ?? null) === 'Y',
			$this->fields['TZ_FROM'] ?? null
		));
	}

	/**
	 * @return bool
	 */
	protected function getFullDay(): bool
	{
		return $this->isFullDay();
	}

	/**
	 * @return AttendeeCollection|null
	 */
	protected function getAttendees(): ?AttendeeCollection
	{
		$collection = new AttendeeCollection();

		if (isset($this->fields['ATTENDEES_CODES']))
		{
			if (is_string($this->fields['ATTENDEES_CODES']))
			{
				$collection->setAttendeesCodes(explode(',', $this->fields['ATTENDEES_CODES']));
			}
			else if (is_array($this->fields['ATTENDEES_CODES']))
			{
				$collection->setAttendeesCodes($this->fields['ATTENDEES_CODES']);
			}
		}

		if (isset($this->fields['ATTENDEES']) && is_array($this->fields['ATTENDEES']))
		{
			$collection->setAttendeesId($this->fields['ATTENDEES']);
		}
		else
		{
			$collection->setAttendeesId([(int)$this->fields['OWNER_ID']]);
		}


		return $collection;
	}

	/**
	 * @return RemindCollection
	 * @throws ObjectException
	 */
	protected function getReminders(): RemindCollection
	{
		if (isset($this->fields['REMIND']) && is_string($this->fields['REMIND']))
		{
			$this->fields['REMIND'] = unserialize($this->fields['REMIND'], ['allowed_classes' => false]);
		}

		if (!isset($this->fields['REMIND']) || !is_array($this->fields['REMIND']))
		{
			return new RemindCollection();
		}

		$eventStart = $this->getStart();

		$collection = new RemindCollection();
		$collection->setEventStart($eventStart);

		foreach ($this->fields['REMIND'] as $remind)
		{
			if ($remind['type'] === Event\Tools\Dictionary::REMIND_UNIT['date'])
			{
				$collection->add((new Event\Properties\Remind())
					->setSpecificTime(
						new Date(
							Util::getDateObject(
								$remind['value'],
								false,
								$this->fields['TZ_FROM']
							)
						)
					)
					->setEventStart($eventStart)
				);
			}
			elseif ($remind['type'] === Event\Properties\Remind::UNIT_DAY_BEFORE)
			{
				$collection->add((new Event\Properties\Remind())
					->setEventStart($eventStart)
					->setSpecificTime(
						(new Date(
							Util::getDateObject(
								$eventStart->toString(),
								false,
								$this->fields['TZ_FROM'])
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
						?? Event\Properties\Remind::UNIT_MINUTES
					)
					->setEventStart($eventStart)
				);
			}
		}

		return $collection;
	}

	/**
	 * @return string|null
	 */
	protected function getDescription(): ?string
	{
		return $this->fields['DESCRIPTION'] ?? null;
	}

	/**
	 * @return Section
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getSection(): Section
	{
		$sectionId = $this->fields['SECTION_ID'] ??
			(is_array($this->fields['SECTIONS'])
				? (int)$this->fields['SECTIONS'][0]
				: null
			);

		if ($sectionId)
		{
			return (new \Bitrix\Calendar\Core\Mappers\Section())->getMap([
				'=ID' => $sectionId
			])->fetch();
		}

		throw new BuilderException('it is impossible to find the section');
	}

	/**
	 * @return string|null
	 */
	protected function getColor(): ?string
	{
		return $this->fields['COLOR'] ?? null;
	}

	/**
	 * @return string|null
	 */
	protected function getTransparency(): ?string
	{
		return $this->fields['TRANSPARENT'] ?? null;
	}

	/**
	 * @return string|null
	 */
	protected function getImportance(): ?string
	{
		return $this->fields['IMPORTANCE'] ?? null;
	}

	/**
	 * @return string|null
	 */
	protected function getAccessibility(): ?string
	{
		return $this->fields['ACCESSIBILITY'] ?? null;
	}

	/**
	 * @return bool
	 */
	protected function getIsPrivate(): bool
	{
		return $this->fields['PRIVATE_EVENT'] ?? false;
	}

	/**
	 * @return Role|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getEventHost(): ?Role
	{
		if (empty($this->fields['MEETING_HOST']))
		{
			return null;
		}

		try
		{
			return Helper::getUserRole($this->fields['MEETING_HOST']);
		}
		catch (BaseException $exception)
		{
			return null;
		}
	}

	/**
	 * @return Role|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getCreator(): ?Role
	{
		if (empty($this->fields['CREATED_BY']))
		{
			return null;
		}

		try
		{
			return Helper::getUserRole($this->fields['CREATED_BY']);
		}
		catch (BaseException $exception)
		{
			return null;
		}
	}

	/**
	 * @return Role|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOwner(): ?Role
	{
		if (empty($this->fields['OWNER_ID']))
		{
			return null;
		}
		if (empty($this->fields['CAL_TYPE']))
		{
			$this->fields['CAL_TYPE'] = User::TYPE;
		}

		try
		{
			return Helper::getRole($this->fields['OWNER_ID'], $this->fields['CAL_TYPE']);
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
		return $this->prepareMeetingDescription($this->fields['MEETING'] ?? null);
	}

	/**
	 * @return int
	 */
	protected function getVersion(): int
	{
		return (int)($this->fields['VERSION'] ?? null);
	}

	/**
	 * @return string|null
	 */
	protected function getCalendarType(): ?string
	{
		return $this->fields['CAL_TYPE'] ?? null;
	}

	/**
	 * @return string|null
	 */
	protected function getUid(): ?string
	{
		return $this->fields['DAV_XML_ID'] ?? null;
	}

	/**
	 * @return bool
	 */
	protected function isDeleted(): bool
	{
		return isset($this->fields['DELETED']) && $this->fields['DELETED'] === 'Y';
	}

	/**
	 * @return bool
	 */
	protected function isActive(): bool
	{
		return isset($this->fields['ACTIVE']) && $this->fields['ACTIVE'] === 'Y';
	}

	/**
	 * @return int|null
	 */
	protected function getRecurrenceId(): ?int
	{
		return $this->fields['RECURRENCE_ID'] ?? null;
	}

	/**
	 * @return Date|null
	 * @throws ObjectException
	 */
	protected function getDateCreate(): ?Date
	{
		if (!isset($this->fields['DATE_CREATE']))
		{
			return null;
		}

		return new Date(Util::getDateObject(
			$this->fields['DATE_CREATE'],
			false,
			(new DateTime())->getTimezone()->getName()
		));
	}

	/**
	 * @return Date|null
	 * @throws ObjectException
	 */
	protected function getDateModified(): ?Date
	{
		if (!isset($this->fields['TIMESTAMP_X']))
		{
			return null;
		}

		return new Date(Util::getDateObject(
			$this->fields['TIMESTAMP_X'],
			false,
			(new DateTime())->getTimezone()->getName()
		));
	}

	/**
	 * @return Event\Properties\ExcludedDatesCollection
	 * @throws ObjectException
	 */
	protected function getExcludedDate(): Event\Properties\ExcludedDatesCollection
	{
		if (empty($this->fields['EXDATE']))
		{
			return new Event\Properties\ExcludedDatesCollection();
		}

		$collection = new Event\Properties\ExcludedDatesCollection();
		if (is_string($this->fields['EXDATE']))
		{
			foreach (explode(";", $this->fields['EXDATE']) as $exDate)
			{
				$collection->add($this->createDateForRecurrence($exDate));
			}
		}

		else if (is_array($this->fields['EXDATE']))
		{
			foreach ($this->fields['EXDATE'] as $exDate)
			{
				$collection->add($this->createDateForRecurrence($exDate));
			}
		}

		return $collection;
	}

	/**
	 * @return int|null
	 */
	protected function getId(): ?int
	{
		return $this->fields['ID'] ?? null;
	}

	/**
	 * @return int|null
	 */
	protected function getParentId(): ?int
	{
		return $this->fields['PARENT_ID'] ?? null;
	}

	/**
	 * @return bool
	 */
	protected function isMeeting(): bool
	{
		return (bool)($this->fields['IS_MEETING'] ?? null);
	}

	/**
	 * @return string|null
	 */
	protected function getMeetingStatus(): ?string
	{
		return $this->fields['MEETING_STATUS'] ?? null;
	}

	/**
	 * @return Relations|null
	 */
	protected function getRelations(): ?Relations
	{
		return $this->prepareRelations($this->fields['RELATIONS'] ?? null);
	}

	/**
	 * @return string|null
	 */
	protected function getSpecialLabel(): ?string
	{
		return $this->fields['EVENT_TYPE'] ?? null;
	}
}