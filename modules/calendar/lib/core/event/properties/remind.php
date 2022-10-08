<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use Bitrix\Calendar\Core\Base\BaseProperty;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Event\Tools\PropertyException;

class Remind extends BaseProperty
{
	public const UNIT_SECONDS = 'seconds';
	public const UNIT_MINUTES = 'minutes';
	public const UNIT_HOURS = 'hour';
	public const UNIT_DAYS = 'day';
	public const UNIT_WEEKS = 'weeks';
	public const UNIT_MONTHS = 'months';
	public const UNIT_YEARS = 'years';
	public const UNIT_DATES = 'date';
	public const UNIT_DAY_BEFORE = 'daybefore';
	protected const MINUTE_PER_DAY = 1440;

	/**
	 * @var string
	 */
	private string $method;
	/**
	 * @var int|null
	 */
	private ?int $time = null;
	/**
	 * @var string|null
	 */
	private ?string $units = null;
	/**
	 * @var Date|null
	 */
	private ?Date $specificTime = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $eventStart = null;
	/**
	 * @var int|null
	 */
	private ?int $daysBefore = null;

	public function __construct(string $method = 'popup')
	{
		$this->method = $method;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return [
			'method' => $this->method,
			'time' => $this->time,
			'units' => $this->units,
			'specificTime' => $this->specificTime ?? null,
			'eventStart' => $this->eventStart ?? null,
			'daysBefore' => $this->daysBefore ?? null
		];
	}

	/**
	 * @return string
	 * @throws PropertyException
	 */
	public function toString(): string
	{
		if ($this->specificTime || $this->eventStart)
		{
			return (string)$this->getSpecificTime();
		}

		return '';
	}

	/**
	 * @param int $time
	 * @param string $units
	 * @return $this
	 */
	public function setTimeBeforeEvent(int $time = 15, string $units = self::UNIT_MINUTES): Remind
	{
		$this->time = $time;
		$this->units = $units;

		return $this;
	}

	/**
	 * @param Date $specificTime
	 * @return Remind
	 */
	public function setSpecificTime(Date $specificTime): Remind
	{
		$this->specificTime = $specificTime;

		return $this;
	}

	public function setDaysBefore(int $daysBefore): Remind
	{
		$this->daysBefore = $daysBefore;

		return $this;
	}

	/**
	 * @return Date
	 * @throws PropertyException
	 */
	public function getSpecificTime(): Date
	{
		if (
			$this->specificTime === null
			&& $this->time !== null
			&& $this->units
			&& $this->eventStart !== null
		)
		{
			return (clone $this->eventStart)->sub("{$this->time} {$this->units}");
		}

		if ($this->specificTime)
		{
			return $this->specificTime;
		}

		throw new PropertyException('It is impossible to perform this operation. You should set property $eventStart or $specificTime');
	}

	/**
	 * @param Date $eventStart
	 * @return Remind
	 */
	public function setEventStart(Date $eventStart): Remind
	{
		$this->eventStart = $eventStart;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBeforeEventStart(): bool
	{
		if ($this->checkSpecificTime())
		{
			try
			{
				return $this->getSpecificTime()->getTimestamp() <= $this->eventStart->getTimestamp();
			}
			catch (PropertyException $e)
			{
				return false;
			}
		}

		return false;
	}

	/**
	 * @param int $dayBefore
	 * @param int $minute
	 * @return int
	 */
	public static function calculateDayBeforeToMinute(int $dayBefore, int $minute): int
	{
		return ($dayBefore * self::MINUTE_PER_DAY) - $minute;
	}

	/**
	 * @return bool
	 */
	private function checkSpecificTime(): bool
	{
		return $this->specificTime || $this->eventStart;
	}

	/**
	 * @return int
	 */
	public function getTimeBeforeStartInMinutes(): int
	{
		if (!$this->checkSpecificTime())
		{
			return 0;
		}

		try
		{
			$delta = $this->eventStart->getTimestamp() - $this->getSpecificTime()->getTimestamp();

			return $delta / 60;
		}
		catch (PropertyException $e)
		{
			return 0;
		}
	}

	/**
	 * @return int
	 */
	public function getRank(): int
	{
		$rank = 0;
		if (!empty($this->daysBefore))
		{
			$rank = 100;
		}
		elseif (!empty($this->specificTime))
		{
			$rank = 10;
		}
		elseif(!empty($this->units))
		{
			$rankMap = [
				self::UNIT_SECONDS => 1,
				self::UNIT_MINUTES => 2,
				self::UNIT_HOURS => 3,
				self::UNIT_DAYS => 4,
				self::UNIT_WEEKS => 5,
				self::UNIT_MONTHS => 6,
				self::UNIT_YEARS => 7,
			];
			$rank = $rankMap[$this->units] ?? 0;
		}

		return $rank;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * @return ?int
	 */
	public function getTime(): ?int
	{
		return $this->time ?? null;
	}

	/**
	 * @return ?string
	 */
	public function getUnits(): ?string
	{
		return $this->units ?? null;
	}

	/**
	 * @return ?int
	 */
	public function getDaysBefore(): ?int
	{
		return $this->daysBefore ?? null;
	}

	/**
	 * @return ?Date Clone of date event start
	 */
	public function getEventStart(): ?Date
	{
		return isset($this->eventStart)
			? (clone $this->eventStart)
			: null
			;
	}

	/**
	 * @return int
	 *
	 * @throws PropertyException
	 */
	public function getTimeOffset(): int
	{
		$time = $this->getSpecificTime();
		return 60 * intval($time->format('H'))
			+ intval($time->format('i'));
	}

	/**
	 * @return bool
	 */
	public function isSimpleType(): bool
	{
		return !empty($this->units);
	}
}
