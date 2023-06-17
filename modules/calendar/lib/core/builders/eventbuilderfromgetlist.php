<?php

namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class EventBuilderFromGetList extends EventBuilderFromArray
{
	/**
	 * @return Event\Properties\RecurringEventRules|null
	 * @throws ObjectException
	 */
	protected function getRecurringRule(): ?Event\Properties\RecurringEventRules
	{
		if (
			isset($this->fields['RRULE'])
			&& isset($this->fields['RRULE']['FREQ'])
			&& $this->fields['RRULE']['FREQ'] !== 'NONE'
		)
		{
			$rule = new Event\Properties\RecurringEventRules($this->fields['RRULE']['FREQ']);

			if (isset($this->fields['RRULE']['COUNT']))
			{
				$rule->setCount((int)$this->fields['RRULE']['COUNT']);
			}

			if (is_string($this->fields['RRULE']['UNTIL']))
			{
				$rule->setUntil($this->createDateForRecurrence($this->fields['RRULE']['UNTIL']));
			}

			if (isset($this->fields['RRULE']['INTERVAL']))
			{
				$rule->setInterval((int)$this->fields['RRULE']['INTERVAL']);
			}

			if (
				is_string($this->fields['RRULE']['BYDAY'])
				&& $this->fields['RRULE']['FREQ'] === Event\Properties\RecurringEventRules::FREQUENCY_WEEKLY
			)
			{
				$rule->setByDay(explode(",", $this->fields['RRULE']['BYDAY']));
			}
			elseif (
				is_array($this->fields['RRULE']['BYDAY'])
				&& $this->fields['RRULE']['FREQ'] === Event\Properties\RecurringEventRules::FREQUENCY_WEEKLY
			)
			{
				$rule->setByDay($this->fields['RRULE']['BYDAY']);
			}

			return $rule;
		}

		return null;
	}

	/**
	 * @return Event\Properties\Location|null
	 */
	protected function getLocation(): ?Event\Properties\Location
	{
		if (is_array($this->fields['LOCATION']) && isset($this->fields['LOCATION']['NEW']))
		{
			$location = new Event\Properties\Location($this->fields['NEW']);
			if (isset($this->fields['LOCATION']['OLD']))
			{
				$location->setOriginalLocation($this->fields['LOCATION']['OLD']);
			}

			return $location;
		}

		if (is_string($this->fields['LOCATION']))
		{
			return new Event\Properties\Location($this->fields['LOCATION']);
		}

		return null;
	}

	/**
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	protected function getStart(): Date
	{
		return new Date(
			Util::getDateObject((is_object($this->fields['DATE_FROM'])
					? $this->fields['DATE_FROM']->format('d.m.y H:i:s')
					: $this->fields['DATE_FROM']),
				$this->fields['DT_SKIP_TIME'] === 'Y',
				$this->fields['TZ_FROM'] ?? null)
		);
	}

	/**
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	protected function getEnd(): Date
	{
		return new Date(
			Util::getDateObject((is_object($this->fields['DATE_TO'])
				? $this->fields['DATE_TO']->format('d.m.y H:i:s')
				: $this->fields['DATE_TO']),
			$this->fields['DT_SKIP_TIME'] === 'Y',
			$this->fields['TZ_TO'] ?? null)
		);
	}

	/**
	 * @return bool
	 */
	protected function getFullDay(): bool
	{
		return isset($this->fields['DT_SKIP_TIME']) && $this->fields['DT_SKIP_TIME'] === 'Y';
	}

	/**
	 * @return AttendeeCollection|null
	 */
	protected function getAttendees(): ?AttendeeCollection
	{
		$collection = new AttendeeCollection();

		if (is_string($this->fields['ATTENDEES_CODES']))
		{
			$collection->setAttendeesCodes(explode(',', $this->fields['ATTENDEES_CODES']));
		}
		else if (is_array($this->fields['ATTENDEE_LIST']))
		{
			$collection->setAttendeesId(array_column($this->fields['ATTENDEE_LIST'], 'id'));
		}
		else
		{
			$collection->setAttendeesId([(int)$this->fields['OWNER_ID']]);
		}


		return $collection;
	}

//	/**
//	 * @return Event\Properties\RemindCollection|null
//	 */
//	protected function getReminders(): Event\Properties\RemindCollection
//	{
//		if (!is_array($this->fields['REMIND']))
//		{
//			return new Event\Properties\RemindCollection();
//		}
//
//		$collection = new Event\Properties\RemindCollection();
////		foreach ($this->fields['REMIND'] as $remind)
////		{
//			$collection->add((new Event\Properties\Remind())->setTimeBeforeEvent());
////		}
//
//		return $collection;
//	}

	/**
	 * @return Section|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getSection(): Section
	{
		if (
			isset($this->fields['SECTION_ID'])
			&& $sectionDM = SectionTable::getById($this->fields['SECTION_ID'])->fetchObject()
		)
		{
			return (new SectionBuilderFromDataManager($sectionDM))->build();
		}

		throw new BuilderException('it is impossible to find the section');
	}
}
