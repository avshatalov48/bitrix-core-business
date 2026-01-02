<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\Schedule\PortalSchedule;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\Schedule\ScheduleInterface;
use Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\Trait\ClosestWorkDateTrait;

class ClosestWorkDate
{
	use ClosestWorkDateTrait;

	private ScheduleInterface $schedule;
	private const SECONDS_PER_DAY = 60 * 60 * 24;

	public function __construct(?ScheduleInterface $schedule = null)
	{
		$this->schedule = $schedule ?? new PortalSchedule();
	}

	/**
	 * @return DateTime in user time
	 */
	public function get(int $offsetInSeconds, ?DateTime $userTimeDate = null): DateTime
	{
		$userTimeDate = $userTimeDate ?? (new DateTime())->toUserTime();

		$possibleDate = clone $userTimeDate;
		$possibleDate = $possibleDate->disableUserTime();

		if ($offsetInSeconds <= 0)
		{
			return $possibleDate;
		}

		while ($offsetInSeconds >= self::SECONDS_PER_DAY)
		{
			$possibleDate->add('1 day');
			$possibleDate = $this->getClosestWorkDate($possibleDate);

			$offsetInSeconds -= self::SECONDS_PER_DAY;
		}

		$possibleDate->add($offsetInSeconds . ' seconds');

		return $this->getClosestWorkDate($possibleDate);
	}
}
