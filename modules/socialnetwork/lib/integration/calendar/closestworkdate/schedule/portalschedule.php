<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Calendar\ClosestWorkDate\Schedule;

use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Integration\Calendar\Calendar;

class PortalSchedule implements ScheduleInterface
{
	protected const WEEK_DAYS_COUNT = 7;
	protected const MINUTES_IN_HOUR = 60;
	protected const SECONDS_IN_HOUR = 60 * 60;
	protected const SECONDS_IN_MINUTE = 60;
	protected const DAY_MAP = [
		'MO' => 1,
		'TU' => 2,
		'WE' => 3,
		'TH' => 4,
		'FR' => 5,
		'SA' => 6,
		'SU' => 0,
	];
	protected const DEFAULT_SETTINGS = [
		'HOURS' => [
			'START' => [
				'H' => 9,
				'M' => 0,
				'S' => 0,
			],
			'END' => [
				'H' => 19,
				'M' => 0,
				'S' => 0,
			],
		],
		'HOLIDAYS' => [],
		'WEEKEND' => ['SA', 'SU'],
		'WEEK_START' => 'MO',
	];

	protected array $calendarSettings;
	protected array $settings;

	protected array $weekend = [];
	protected array $holidays = [];
	protected array $workTime = [];
	protected int $startHour = 9;
	protected int $startMinute = 0;
	protected int $endHour = 19;
	protected int $endMinute = 0;

	public function __construct(?array $settings = null)
	{
		$this->settings = $settings ?? $this->getPortalSettings();

		$this->parseWeekend();
		$this->parseHolidays();
		$this->parseWorkHours();
		$this->parseWorkTime();
	}

	public function getShiftStart(?DateTime $date = null): DateTime
	{
		$date ??= new DateTime();

		return (clone $date)->setTime($this->startHour, $this->startMinute);
	}

	public function getShiftEnd(?DateTime $date = null): DateTime
	{
		$date ??= new DateTime();

		return (clone $date)->setTime($this->endHour, $this->endMinute);
	}

	public function getWorkDayDuration(?DateTime $date = null): int
	{
		$secondsInStartOfWorkDay = $this->startHour * static::SECONDS_IN_HOUR + $this->startMinute * self::SECONDS_IN_MINUTE;
		$secondsInEndOfWorkDay = $this->endHour * static::SECONDS_IN_HOUR + $this->endMinute * self::SECONDS_IN_MINUTE;

		return $secondsInEndOfWorkDay - $secondsInStartOfWorkDay;
	}

	public function isWorkTime(DateTime $date): bool
	{
		$start = $this->startHour * self::MINUTES_IN_HOUR + $this->startMinute;
		$end = $this->endHour * self::MINUTES_IN_HOUR + $this->endMinute;
		$now = (int)$date->format('H') * self::MINUTES_IN_HOUR + (int)$date->format('i');

		return $now >= $start && $now <= $end;
	}

	public function isWeekend(DateTime $date): bool
	{
		$weekday = (int)$date->format('w');

		if (isset($this->weekend[$weekday]))
		{
			return true;
		}

		$month = (int)$date->format('n');
		$day = (int)$date->format('j');

		return isset($this->holidays[$month . '_' . $day]);
	}

	protected function setSettings(array $settings): void
	{
		if (!empty($settings['HOURS']) && is_array($settings['HOURS']))
		{
			$hours = $settings['HOURS'];

			$this->startHour = (int)$hours['START']['H'];
			$this->startMinute = (int)$hours['START']['M'];
			$this->endHour = (int)$hours['END']['H'];
			$this->endMinute = (int)$hours['END']['M'];
		}

		$this->settings = $settings;

		$this->weekend = [];
		$this->parseWeekend();
		$this->parseHolidays();
		$this->parseWorkTime();
	}

	protected function getPortalSettings(): array
	{
		$settings = static::DEFAULT_SETTINGS;
		$calendarSettings = Calendar::getSettings();
		if(empty($calendarSettings))
		{
			return $settings;
		}

		if (is_array($calendarSettings['week_holidays']))
		{
			$settings['WEEKEND'] = $calendarSettings['week_holidays'];
		}

		if ((string)$calendarSettings['year_holidays'] !== '')
		{
			$holidays = explode(',', $calendarSettings['year_holidays']);
			if (is_array($holidays) && !empty($holidays))
			{
				foreach ($holidays as $day)
				{
					$day = trim($day);
					[$day, $month] = explode('.', $day);

					if ($day && $month)
					{
						$settings['HOLIDAYS'][] = ['M' => (int)$month, 'D' => (int)$day];
					}
				}
			}
		}

		$timeStart = explode('.', (string)$calendarSettings['work_time_start']);

		if (isset($timeStart[0]))
		{
			$settings['HOURS']['START']['H'] = (int)$timeStart[0];
		}
		if (isset($timeStart[1]))
		{
			$settings['HOURS']['START']['M'] = (int)$timeStart[1];
		}

		$timeEnd = explode('.', (string)$calendarSettings['work_time_end']);

		if (isset($timeEnd[0]))
		{
			$settings['HOURS']['END']['H'] = (int)$timeEnd[0];
		}
		if (isset($timeEnd[1]))
		{
			$settings['HOURS']['END']['M'] = (int)$timeEnd[1];
		}

		return $settings;
	}

	public function getSettings(): array
	{
		return $this->settings;
	}

	private function parseWeekend(): void
	{
		if(!empty($this->settings['WEEKEND']) && is_array($this->settings['WEEKEND']))
		{
			foreach ($this->settings['WEEKEND'] as $day)
			{
				$this->weekend[static::DAY_MAP[$day]] = true;
			}
		}

		if(count($this->weekend) === static::WEEK_DAYS_COUNT)
		{
			$this->weekend = [static::DAY_MAP['SA'] => true, static::DAY_MAP['SU'] => true];
		}
	}

	private function parseHolidays(): void
	{
		$holidays = $this->settings['HOLIDAYS'] ?? [];

		foreach ($holidays as $day)
		{
			$this->holidays[(int)$day['M'] . '_' . (int)$day['D']] = true;
		}
	}

	private function parseWorkHours(): void
	{
		$startHour = $this->settings['HOURS']['START']['H'] ?? null;

		if (isset($startHour))
		{
			$this->startHour = (int)$startHour;
		}

		$startMinute = $this->settings['HOURS']['START']['M'] ?? null;

		if (isset($startMinute))
		{
			$this->startMinute = (int)$startMinute;
		}

		$endHour = $this->settings['HOURS']['END']['H'] ?? null;

		if (isset($endHour))
		{
			$this->endHour = (int)$endHour;
		}

		$endMinute = $this->settings['HOURS']['END']['M'] ?? null;

		if (isset($endMinute))
		{
			$this->endMinute = (int)$endMinute;
		}
	}

	private function parseWorkTime(): void
	{
		$this->workTime = [
			[
				'start' => [
					'hours' => $this->startHour,
					'minutes' => $this->startMinute,
				],
				'end' => [
					'hours' => $this->endHour,
					'minutes' => $this->endMinute,
				],
			],
		];
	}
}
