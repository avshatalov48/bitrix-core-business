<?php

namespace Bitrix\Calendar\Core\Managers\Duration;

use Bitrix\Calendar\Core\Base\Date;

class DurationManager
{
	private const MINUTE_LENGTH = 60;
	private const HOUR_LENGTH = 60 * self::MINUTE_LENGTH;
	private const DAY_LENGTH = 24 * self::HOUR_LENGTH;

	private Date $start;
	private Date $end;

	public function __construct(Date $start, Date $end)
	{
		$this->start = $start;
		$this->end = $end;
	}

	/**
	 * Example: "1 day 8 hours 13 minutes"
	 * @return string
	 */
	public function getFormattedDuration(): string
	{
		$duration = $this->getDuration();
		if (!$this->isPositiveDuration())
		{
			$result = FormatDate('idiff', time(), time());
		}

		if (empty($result))
		{
			$result = '';
			if ($duration >= self::DAY_LENGTH)
			{
				$result .= FormatDate('ddiff', time(), $duration + time()) . " ";
			}
			$duration = $duration % self::DAY_LENGTH;
			if ($duration >= self::HOUR_LENGTH)
			{
				$result .= FormatDate('Hdiff', time(), $duration + time()) . " ";
			}
			$duration = $duration % self::HOUR_LENGTH;
			if ($duration >= self::MINUTE_LENGTH)
			{
				$result .= FormatDate('idiff', time(), $duration + time());
			}
		}

		return trim($result);
	}

	/**
	 * returns true if duration is longer than 1 minute, false otherwise
	 * @return bool
	 */
	public function isPositiveDuration(): bool
	{
		return $this->getDuration() >= self::MINUTE_LENGTH;
	}

	/**
	 * returns difference between start and end (in seconds)
	 * @return int
	 */
	public function getDuration(): int
	{
		return $this->calculateDuration($this->start->getTimestamp(), $this->end->getTimestamp());
	}

	/**
	 * @param int $start
	 * @param int $end
	 * @return int
	 */
	private function calculateDuration(int $start, int $end): int
	{
		return $end - $start;
	}

	/**
	 * @param Date $start
	 * @param Date $end
	 * @return bool
	 */
	public function areDurationsEqual(Date $start, Date $end): bool
	{
		return $this->getDuration() === $this->calculateDuration($start->getTimestamp(), $end->getTimestamp());
	}
}