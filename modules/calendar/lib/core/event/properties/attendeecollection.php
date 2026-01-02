<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use ArrayIterator;
use Bitrix\Calendar\Core\Base\PropertyCollection;
use Bitrix\Calendar\Core\Role\Helper;


class AttendeeCollection extends PropertyCollection
{
	/** @var array $attendeesIdCollection*/
	private array $attendeesIdCollection = [];
	/** @var array $attendeesCodesCollection*/
	private array $attendeesCodesCollection = [];

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return array_merge(parent::getFields(), [
			'attendeesIdCollection' => $this->attendeesIdCollection,
			'attendeesCodesCollection' => $this->attendeesCodesCollection,
		]);
	}

	/**
	 * @return ArrayIterator
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getIterator(): ArrayIterator
	{
		if (!$this->collection && $this->attendeesIdCollection)
		{
			$this->loadAttendees();
		}

		return parent::getIterator();
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		if (!$this->collection && $this->attendeesIdCollection)
		{
			return count($this->attendeesIdCollection);
		}

		if (!$this->collection && $this->attendeesCodesCollection)
		{
			return count($this->attendeesCodesCollection);
		}

		return parent::count();
	}

	/**
	 * @param array $attendeesId
	 * @return $this
	 */
	public function setAttendeesId(array $attendeesId): AttendeeCollection
	{
		$this->attendeesIdCollection = $attendeesId;

		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getAttendeesIdCollection(): array
	{
		return $this->attendeesIdCollection;
	}

	public function hasAttendeeId(int $userId): bool
	{
		return in_array($userId, $this->attendeesIdCollection, true);
	}

	public function setAttendeesCodes(array $attendeesCodes): AttendeeCollection
	{
		$this->attendeesCodesCollection = $attendeesCodes;

		return $this;
	}

	public function getAttendeesCodes(): array
	{
		return $this->attendeesCodesCollection;
	}

	public function hasAttendeeCode(int $userId): bool
	{
		$userCode = 'U' . $userId;

		return in_array($userCode, $this->attendeesCodesCollection, true);
	}

	public function addAttendee(int $userId): void
	{
		if (!$this->hasAttendeeId($userId))
		{
			$this->attendeesIdCollection[$userId] = $userId;
		}

		if (!$this->hasAttendeeCode($userId))
		{
			$this->attendeesCodesCollection[] = 'U' . $userId;
		}
	}

	/**
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function loadAttendees(): void
	{
		foreach ($this->attendeesIdCollection as $attendee)
		{
			if (!$attendee)
			{
				continue;
			}

			if ((int)$attendee)
			{
				$this->add(
					Helper::getUserRole((int)$attendee)
				);
			}
		}
	}

	/**
	 * @param $separator
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function toString($separator = ', '): string
	{
		if (!$this->collection)
		{
			$this->loadAttendees();
		}

		return parent::toString($separator);
	}
}
