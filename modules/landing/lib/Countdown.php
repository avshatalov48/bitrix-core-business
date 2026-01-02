<?php

namespace Bitrix\Landing;

class Countdown
{
	public static function getTimestamp(): int
	{
		$currentTime = time();

		$currentYear = (int)date('Y', $currentTime);
		$currentMonth = (int)date('m', $currentTime);
		$currentDay = (int)date('d', $currentTime);

		$targetMonth = 4;
		$targetDay = 12;

		if ($currentMonth > $targetMonth || ($currentMonth === $targetMonth && $currentDay >= $targetDay))
		{
			$targetYear = $currentYear + 1;
		}
		else
		{
			$targetYear = $currentYear;
		}

		$time = '10:00:00';

		return strtotime("$targetYear-$targetMonth-$targetDay $time") * 1000;
	}
}