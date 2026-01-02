<?php

namespace Bitrix\Bizproc\UI\Helpers;

use Bitrix\Main\Localization\Loc;

class DurationFormatter
{
	public const SECONDS = 60;
	public const HALF_SECONDS = self::SECONDS / 2;
	public const MINUTES = 60;
	public const HALF_MINUTES = self::MINUTES / 2;
	public const HOURS = 24;
	public const HALF_HOURS = self::HOURS / 2;
	public const DAYS = 31;
	public const HALF_DAYS = 15;
	public const MONTHS = 12;
	public const HALF_MONTHS = self::MONTHS / 2;
	public const DAYS_YEAR = 365;

	public static function roundTimeInSeconds(int $timeInSeconds, int $meaningUnit = 1): int
	{
		$checkUnits = static function (array $units) use ($meaningUnit) {
			return count(array_filter($units)) <= $meaningUnit;
		};

		$roundUnitFunction = [
			'self::removeOrRoundSeconds',
			'self::removeOrRoundMinutes',
			'self::removeOrRoundHours',
			'self::removeOrRoundDays',
			'self::removeOrRoundMonths',
		];

		$unitMod = self::getUnitMod($timeInSeconds);
		for ($i = -1; $i <= 4; $i++)
		{
			if (isset($roundUnitFunction[$i]))
			{
				$unitMod = call_user_func($roundUnitFunction[$i], $unitMod);;
			}

			if ($checkUnits($unitMod))
			{
				break;
			}
		}

		return self::getFromUnitMod($unitMod);
	}

	private static function removeOrRoundSeconds(array $unitMod): array
	{
		$seconds = $unitMod['s'];
		$minutes = $unitMod['i'];

		if (($minutes !== 0 && $seconds >= self::HALF_SECONDS) || ($minutes === 0 && $seconds === self::SECONDS))
		{
			++$minutes;
		}

		$unitMod['s'] = 0;
		$unitMod['i'] = $minutes;

		return $unitMod;
	}

	private static function removeOrRoundMinutes(array $unitMod): array
	{
		$minutes = $unitMod['i'];
		$hours = $unitMod['H'];

		if (($hours !== 0 && $minutes >= self::HALF_MINUTES) || ($hours === 0 && $minutes === self::MINUTES))
		{
			++$hours;
		}

		$unitMod['i'] = 0;
		$unitMod['H'] = $hours;

		return $unitMod;
	}

	private static function removeOrRoundHours(array $unitMod): array
	{
		$hours = $unitMod['H'];
		$days = $unitMod['d'];

		if (($days !== 0 && $hours >= self::HALF_HOURS) || ($days === 0 && $hours === self::HOURS))
		{
			++$days;
		}

		$unitMod['H'] = 0;
		$unitMod['d'] = $days;

		return $unitMod;
	}

	private static function removeOrRoundDays(array $unitMod): array
	{
		$days = $unitMod['d'];
		$months = $unitMod['m'];

		if (($months !== 0 && $days >= self::HALF_DAYS) || ($months === 0 && $days === self::DAYS))
		{
			++$months;
		}

		$unitMod['d'] = 0;
		$unitMod['m'] = $months;

		return $unitMod;
	}

	private static function removeOrRoundMonths(array $unitMod): array
	{
		$months = $unitMod['m'];
		$years = $unitMod['Y'];

		if (($years !== 0 && $months >= self::HALF_MONTHS) || ($years === 0 && $months === self::MONTHS))
		{
			++$years;
		}

		$unitMod['m'] = 0;
		$unitMod['Y'] = $years;

		return $unitMod;
	}

	public static function getFromUnitMod(array $unitMod): int
	{
		return (
			($unitMod['s'] ?? 0)
			+ self::getSecondsFromMinutes($unitMod['i'] ?? 0)
			+ self::getSecondsFromHours($unitMod['H'] ?? 0)
			+ self::getSecondsFromDays($unitMod['d'] ?? 0)
			+ self::getSecondsFromMonths($unitMod['m'] ?? 0)
			+ self::getSecondsFromYears($unitMod['Y'] ?? 0)
		);
	}

	public static function roundUpTimeInSeconds(int $timeInSeconds): int
	{
		if ($timeInSeconds <= self::SECONDS)
		{
			return $timeInSeconds;
		}

		$unitMod = self::getUnitMod($timeInSeconds);

		$years = $unitMod['Y'];
		$months = $unitMod['m'];
		$days = $unitMod['d'];
		$hours = $unitMod['H'];
		$minutes = $unitMod['i'];
		$seconds = $unitMod['s'];

		if ($years !== 0)
		{
			if ($seconds || $minutes || $hours || $days || $months)
			{
				++$years;
			}

			return self::getSecondsFromYears($years);
		}

		if ($months !== 0)
		{
			if ($seconds || $minutes || $hours || $days)
			{
				++$months;
			}

			return $months === self::MONTHS ? self::getSecondsFromYears(1) : self::getSecondsFromMonths($months);
		}

		if ($days !== 0)
		{
			if ($seconds || $minutes || $hours)
			{
				++$days;
			}

			return $days === self::DAYS ? self::getSecondsFromMonths(1) : self::getSecondsFromDays($days);
		}

		if ($hours !== 0)
		{
			if ($seconds || $minutes)
			{
				++$hours;
			}

			return $hours === self::HOURS ? self::getSecondsFromDays(1) : self::getSecondsFromHours($hours);
		}

		if ($minutes !== 0)
		{
			if ($seconds)
			{
				++$minutes;
			}

			return $minutes === self::MINUTES ? self::getSecondsFromHours(1) : self::getSecondsFromMinutes($minutes);
		}

		return $seconds;
	}

	public static function getUnitMod(int $seconds): array
	{
		$minutes = $seconds >= self::SECONDS ? (int)($seconds / self::SECONDS) : 0;
		$hours = $minutes >= self::MINUTES ? (int)($minutes / self::MINUTES) : 0;
		$days = $hours >= self::HOURS ? (int)($hours / self::HOURS) : 0;
		$months = $days >= self::DAYS ? (int)($days / self::DAYS) : 0;
		$years = $months >= self::MONTHS ? (int)($months / self::MONTHS) : 0;

		return [
			's' => $seconds % self::SECONDS,
			'i' => $minutes % self::MINUTES,
			'H' => $hours % self::HOURS,
			'd' => $days % self::DAYS,
			'm' => $months % self::MONTHS,
			'Y' => $years,
		];
	}

	public static function getSecondsFromMinutes(int $minutes): int
	{
		return $minutes * self::SECONDS;
	}

	public static function getSecondsFromHours(int $hours): int
	{
		return $hours * self::MINUTES * self::SECONDS;
	}

	public static function getSecondsFromDays(int $days): int
	{
		return $days * self::HOURS * self::MINUTES * self::SECONDS;
	}

	public static function getSecondsFromMonths(int $months): int
	{
		return $months * self::DAYS * self::HOURS * self::MINUTES * self::SECONDS;
	}

	public static function getSecondsFromYears(int $years): int
	{
		return $years * self::MONTHS * self::DAYS * self::HOURS * self::MINUTES * self::SECONDS;
	}

	public static function format(int $duration): ?string
	{
		if ($duration === 0)
		{
			return Loc::getMessage('BIZPROC_UI_DURATION_FORMATTER_DURATION_ZERO');
		}

		$valuedMods = array_filter(self::getUnitMod($duration));
		$targetFormat = array_key_last($valuedMods);
		if ($targetFormat)
		{
			$formats = [
				'Y' => 'Ydiff',
				'm' => 'mdiff',
				'd' => 'ddiff',
				'H' => 'Hdiff',
				'i' => 'idiff',
				's' => 'sdiff',
			];

			return \FormatDate($formats[$targetFormat], time() - $duration);
		}

		return null;
	}
}
