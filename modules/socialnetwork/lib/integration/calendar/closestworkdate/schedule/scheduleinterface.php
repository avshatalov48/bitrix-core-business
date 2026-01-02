<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\Schedule;

use Bitrix\Main\Type\DateTime;

interface ScheduleInterface
{
	public function getShiftStart(?DateTime $date = null): DateTime;

	public function getShiftEnd(?DateTime $date = null): DateTime;

	public function getWorkDayDuration(?DateTime $date = null): int;

	public function isWorkTime(DateTime $date): bool;

	public function isWeekend(DateTime $date): bool;
}
