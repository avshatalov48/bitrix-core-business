<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;

class EventDateResponse
{
	/**
	 * @param string|null $date The date, in the format "yyyy-mm-dd", if this is an all-day event
	 * @param string|null $dateTime The time, as a combined date-time value (formatted according to RFC3339). A time zone offset is required unless a time zone is explicitly specified in timeZone
	 * @param string|null $timeZone The time zone in which the time is specified. (Formatted as an IANA Time Zone Database name, e.g. "Europe/Zurich".) For recurring events this field is required and specifies the time zone in which the recurrence is expanded. For single events this field is optional and indicates a custom time zone for the event start/end
	 *
	 * @throws DtoValidationException
	 */
	public function __construct(
		public readonly ?string $date,
		public readonly ?string $dateTime,
		public readonly ?string $timeZone
	)
	{
		if (empty($this->date) && empty($this->dateTime))
		{
			throw new DtoValidationException('Field "date" or "dateTime" should be not empty');
		}

		if (!empty($this->date) && !empty($this->dateTime))
		{
			throw new DtoValidationException('Field "date" or "dateTime" should not be filled in a same time');
		}
	}

	public function isAllDay(): bool
	{
		return !empty($this->date);
	}

	public function toArray(): array
	{
		$data = [];

		if ($this->date)
		{
			$data['date'] = $this->date;
		}

		if ($this->dateTime)
		{
			$data['dateTime'] = $this->dateTime;
		}

		if ($this->timeZone)
		{
			$data['timeZone'] = $this->timeZone;
		}

		return $data;
	}
}
