<?php

namespace Bitrix\Calendar\Core\Event\Properties;


use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\Property;
use Bitrix\Calendar\Sync\Google\Helper;

class RecurringEventRules implements Property
{
	public const FREQUENCY = [
		'daily' => 'DAILY',
		'weekly' => 'WEEKLY',
		'monthly' => 'MONTHLY',
		'yearly' => 'YEARLY',
	];
	public const FREQUENCY_WEEKLY = 'WEEKLY';
	public const FREQUENCY_DAILY = 'DAILY';
	public const FREQUENCY_MONTHLY = 'MONTHLY';
	public const FREQUENCY_YEARLY = 'YEARLY';

	/**
	 * @var string
	 */
	private string $frequency;
	/**
	 * @var int|null
	 */
	private ?int $count = null;
	/**
	 * @var Date|null
	 */
	private ?Date $until = null;
	/**
	 * @var mixed|string
	 */
	private $dateFormat;
	/**
	 * @var int
	 */
	private int $interval;
	/**
	 * @var array|null
	 */
	private ?array $byDay = [];

	/**
	 * @param string $frequency
	 * @param int $interval
	 */
	public function __construct(string $frequency, int $interval = 1)
	{
		$this->frequency = $frequency;
		$this->interval = $interval;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		$fields = [];

		$fields['frequency'] = $this->frequency;

		if ($this->count > 0)
		{
			$fields['count'] = $this->count;
		}

		if (isset($this->until))
		{
			$fields['until'] = $this->until->format($this->dateFormat);
		}

		if (isset($this->interval))
		{
			$fields['interval'] = $this->interval;
		}

		if (isset($this->byDay))
		{
			$fields['byday'] = $this->byDay;
		}

		return $fields;
	}

	/**
	 * @param Date $until
	 * @param string|null $dateFormat
	 * @return $this
	 */
	public function setUntil(Date $until, string $dateFormat = null): RecurringEventRules
	{
		$this->until = $until;
		$this->dateFormat = $dateFormat;

		return $this;
	}

	/**
	 * @param int $count
	 * @return $this
	 */
	public function setCount(int $count): RecurringEventRules
	{
		$this->count = $count;

		return $this;
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		$result = "FREQ={$this->frequency}";

		if ($this->count > 0)
		{
			$result .= ",COUNT={$this->count}";
		}

		if (isset($this->until))
		{
			$result .= ",UNTIL={$this->until}";
		}

		if (isset($this->interval))
		{
			$result .= ",INTERVAL={$this->interval}";
		}

		if (isset($this->byDay) && $this->frequency === self::FREQUENCY_WEEKLY)
		{
			$byDayString = implode(',',$this->byDay);
			$result .= ",BYDAY={$byDayString}";
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		$result = ['FREQ' => $this->frequency];
		if ($this->count > 0)
		{
			$result['COUNT'] = $this->count;
		}

		if (isset($this->until))
		{
			$result['UNTIL'] = $this->until->toString();
		}

		if (isset($this->interval))
		{
			$result ['INTERVAL'] = $this->interval;
		}

		if (isset($this->byDay) && $this->frequency === self::FREQUENCY_WEEKLY)
		{
			$result['BYDAY'] = $this->byDay;
		}

		return $result;
	}

	/**
	 * @param int $interval
	 * @return $this
	 */
	public function setInterval(int $interval): RecurringEventRules
	{
		$this->interval = $interval;

		return $this;
	}

	/**
	 * @param array $byDay
	 *
	 * @return $this
	 */
	public function setByDay(array $byDay): RecurringEventRules
	{
		$this->byDay = $byDay;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFrequency(): string
	{
		return $this->frequency;
	}

	/**
	 * @return ?int
	 */
	public function getCount(): ?int
	{
		return $this->count;
	}

	/**
	 * @return ?Date
	 */
	public function getUntil(): ?Date
	{
		return $this->until;
	}

	/**
	 * @return int
	 */
	public function getInterval(): int
	{
		return $this->interval;
	}

	/**
	 * @return string[]
	 */
	public function getByday(): array
	{
		return $this->byDay;
	}

	/**
	 * @return bool
	 */
	public function hasDay(): bool
	{
		return ($this->frequency === self::FREQUENCY_WEEKLY)
			&& $this->byDay;
	}

	/**
	 * @return bool
	 */
	public function hasCount(): bool
	{
		return (bool)$this->count;
	}

	/**
	 * @return bool
	 */
	public function hasUntil(): bool
	{
		return $this->until !== null;
	}

	/**
	 * @return bool
	 */
	public function isUntilEndOfTime(): bool
	{
		if ($this->until === null)
		{
			return false;
		}

		return $this->until->format('d.m.Y') === Helper::END_OF_TIME;
	}

	/**
	 * @param string $frequency
	 * @return RecurringEventRules
	 */
	public function setFrequency(string $frequency): RecurringEventRules
	{
		$this->frequency = $frequency;

		return $this;
	}
}
