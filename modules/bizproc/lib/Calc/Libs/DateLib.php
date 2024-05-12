<?php

namespace Bitrix\Bizproc\Calc\Libs;

use Bitrix\Main;
use Bitrix\Bizproc;
use Bitrix\Bizproc\Calc\Arguments;
use Bitrix\Main\Localization\Loc;

class DateLib extends BaseLib
{
	private static $weekHolidays;
	private static $yearHolidays;
	private static $startWorkDay;
	private static $endWorkDay;
	private static $yearWorkdays;

	private const ONE_HOUR = 3600;
	private const TWELVE_HOURS = 12 * self::ONE_HOUR;

	function getFunctions(): array
	{
		return [
			'date' => [
				'args' => true,
				'func' => 'callDate',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_DATE_DESCRIPTION'),
			],
			'dateadd' => [
				'args' => true,
				'func' => 'callDateAdd',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_DATEADD_DESCRIPTION'),
			],
			'datediff' => [
				'args' => true,
				'func' => 'callDateDiff',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_DATEDIFF_DESCRIPTION'),
			],
			'addworkdays' => [
				'args' => true,
				'func' => 'callAddWorkDays',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_ADDWORKDAYS_DESCRIPTION'),
			],
			'workdateadd' => [
				'args' => true,
				'func' => 'callWorkDateAdd',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_WORKDATEADD_DESCRIPTION'),
			],
			'isworkday' => [
				'args' => true,
				'func' => 'callIsWorkDay',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_ISWORKDAY_DESCRIPTION'),
			],
			'isworktime' => [
				'args' => true,
				'func' => 'callIsWorkTime',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_ISWORKTIME_DESCRIPTION'),
			],
			'touserdate' => [
				'args' => true,
				'func' => 'callToUserDate',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_TOUSERDATE_DESCRIPTION'),
			],
			'getuserdateoffset' => [
				'args' => true,
				'func' => 'callGetUserDateOffset',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_GETUSERDATEOFFSET_DESCRIPTION'),
			],
			'strtotime' => [
				'args' => true,
				'func' => 'callStrtotime',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_STRTOTIME_DESCRIPTION'),
			],
			'locdate' => [
				'args' => true,
				'func' => 'callLocDate',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_LOCDATE_DESCRIPTION'),
			],
			'settime' => [
				'args' => true,
				'func' => 'callSetTime',
				'description' => Loc::getMessage('BIZPROC_CALC_FUNCTION_SETTIME_DESCRIPTION'),
			],
		];
	}

	public function callDateAdd(Arguments $args)
	{
		$date = $args->getFirstSingle();
		$offset = $this->getDateTimeOffset($date);
		$date = $this->makeTimestamp($date);
		$interval = $args->getSecondSingle();

		if ($date === false || ($interval && !is_scalar($interval)))
		{
			return null;
		}

		if (empty($interval))
		{
			return $date; // new Bizproc\BaseType\Value\DateTime($date, $offset);
		}

		// 1Y2M3D4H5I6S, -4 days 5 hours, 1month, 5h

		$interval = trim($interval);
		$bMinus = false;
		if (mb_substr($interval, 0, 1) === "-")
		{
			$interval = mb_substr($interval, 1);
			$bMinus = true;
		}

		static $arMap = [
			"y" => "YYYY",
			"year" => "YYYY",
			"years" => "YYYY",
			"m" => "MM",
			"month" => "MM",
			"months" => "MM",
			"d" => "DD",
			"day" => "DD",
			"days" => "DD",
			"h" => "HH",
			"hour" => "HH",
			"hours" => "HH",
			"i" => "MI",
			"min" => "MI",
			"minute" => "MI",
			"minutes" => "MI",
			"s" => "SS",
			"sec" => "SS",
			"second" => "SS",
			"seconds" => "SS",
		];

		$arInterval = [];
		while (preg_match('/\s*([\d]+)\s*([a-z]+)\s*/i', $interval, $match))
		{
			$match2 = mb_strtolower($match[2]);
			if (array_key_exists($match2, $arMap))
			{
				$arInterval[$arMap[$match2]] = ($bMinus ? -intval($match[1]) : intval($match[1]));
			}

			$p = mb_strpos($interval, $match[0]);
			$interval = mb_substr($interval, $p + mb_strlen($match[0]));
		}

		$date += $offset; // to server

		$newDate = AddToTimeStamp($arInterval, $date);

		$newDate -= $offset; // to user timezone

		return new Bizproc\BaseType\Value\DateTime($newDate, $offset);
	}

	public function callWorkDateAdd(Arguments $args)
	{
		$date = $args->getFirstSingle();
		$offset = $this->getDateTimeOffset($date);
		$paramInterval = $args->getSecondSingle();
		$user = $args->getThird();

		if ($user)
		{
			$userArgs = clone $args;
			$userArgs->setArgs([$user, $date]);

			$date = $this->callToUserDate($userArgs);
			$offset = $this->getDateTimeOffset($date);
		}

		$date = $this->makeTimestamp($date, true);

		if ($date === false || ($paramInterval && !is_scalar($paramInterval)))
		{
			return null;
		}

		if (empty($paramInterval) || !Main\Loader::includeModule('calendar'))
		{
			return $date;
		}

		$paramInterval = trim($paramInterval);
		$multiplier = 1;
		if (mb_substr($paramInterval, 0, 1) === "-")
		{
			$paramInterval = mb_substr($paramInterval, 1);
			$multiplier = -1;
		}

		$workDayInterval = $this->getWorkDayInterval();
		$intervalMap = [
			"d" => $workDayInterval,
			"day" => $workDayInterval,
			"days" => $workDayInterval,
			"h" => 3600,
			"hour" => 3600,
			"hours" => 3600,
			"i" => 60,
			"min" => 60,
			"minute" => 60,
			"minutes" => 60,
		];

		$interval = 0;
		while (preg_match('/\s*([\d]+)\s*([a-z]+)\s*/i', $paramInterval, $match))
		{
			$match2 = mb_strtolower($match[2]);
			if (array_key_exists($match2, $intervalMap))
			{
				$interval += intval($match[1]) * $intervalMap[$match2];
			}

			$p = mb_strpos($paramInterval, $match[0]);
			$paramInterval = mb_substr($paramInterval, $p + mb_strlen($match[0]));
		}

		if (date('H:i:s', $date) === '00:00:00')
		{
			//add start work day seconds
			$date += $this->getCalendarWorkTime()[0];
		}

		$date = $this->getNearestWorkTime($date, $multiplier);
		if ($interval)
		{
			$days = (int)floor($interval / $workDayInterval);
			$hours = $interval % $workDayInterval;

			$remainTimestamp = $this->getWorkDayRemainTimestamp($date, $multiplier);

			if ($days)
			{
				$date = $this->addWorkDay($date, $days * $multiplier);
			}

			if ($hours > $remainTimestamp)
			{
				$date += $multiplier < 0 ? -$remainTimestamp - 60 : $remainTimestamp + 60;
				$date = $this->getNearestWorkTime($date, $multiplier) + (($hours - $remainTimestamp) * $multiplier);
			}
			else
			{
				$date += $multiplier * $hours;
			}
		}

		$date -= $offset;

		return new Bizproc\BaseType\Value\DateTime($date, $offset);
	}

	public function callAddWorkDays(Arguments $args)
	{
		$date = $args->getFirstSingle();
		$offset = $this->getDateTimeOffset($date);
		$days = (int)$args->getSecond();

		if (($date = $this->makeTimestamp($date)) === false)
		{
			return null;
		}

		if ($days === 0 || !Main\Loader::includeModule('calendar'))
		{
			return $date;
		}

		$date = $this->addWorkDay($date, $days);

		return new Bizproc\BaseType\Value\DateTime($date, $offset);
	}

	public function callIsWorkDay(Arguments $args)
	{
		if (!Main\Loader::includeModule('calendar'))
		{
			return false;
		}

		$date = $args->getFirstSingle();
		$user = $args->getSecond();

		if ($user)
		{
			$userArgs = clone $args;
			$userArgs->setArgs([$user, $date]);

			$date = $this->callToUserDate($userArgs);
		}

		if (($date = $this->makeTimestamp($date, true)) === false)
		{
			return false;
		}

		return !$this->isHoliday($date);
	}

	public function callIsWorkTime(Arguments $args)
	{
		if (!Main\Loader::includeModule('calendar'))
		{
			return false;
		}

		$date = $args->getFirstSingle();
		$user = $args->getSecond();

		if ($user)
		{
			$userArgs = clone $args;
			$userArgs->setArgs([$user, $date]);

			$date = $this->callToUserDate($userArgs);
		}

		if (($date = $this->makeTimestamp($date, true)) === false)
		{
			return false;
		}

		return !$this->isHoliday($date) && $this->isWorkTime($date);
	}

	public function callDateDiff(Arguments $args)
	{
		$date1 = $args->getFirstSingle();
		$date2 = $args->getSecondSingle();
		$format = $args->getThird();

		if (!$date1 || !$date2 || !is_scalar($format))
		{
			return null;
		}

		$date1Formatted = $this->getDateTimeObject($date1);
		$date2Formatted = $this->getDateTimeObject($date2);

		if ($date1Formatted === false || $date2Formatted === false)
		{
			return null;
		}

		$interval = $date1Formatted->diff($date2Formatted);

		return $interval === false ? null : $interval->format($format);
	}

	public function callDate(Arguments $args)
	{
		$format = $args->getFirst();
		$date = $args->getSecondSingle();

		if (!$format || !is_string($format))
		{
			return null;
		}

		$ts = $date ? $this->makeTimestamp($date, true) : time();

		if (!$ts)
		{
			return null;
		}

		return date($format, $ts);
	}

	public function callToUserDate(Arguments $args)
	{
		$user = $args->getFirst();
		$date = $args->getSecondSingle();

		if (!$user)
		{
			return null;
		}

		if (!$date)
		{
			$date = time();
		}
		elseif (($date = $this->makeTimestamp($date)) === false)
		{
			return null;
		}

		$userId = \CBPHelper::extractFirstUser($user, $args->getParser()->getActivity()->getDocumentId());
		$offset = $userId ? \CTimeZone::GetOffset($userId, true) : 0;

		return new Bizproc\BaseType\Value\DateTime($date, $offset);
	}

	public function callGetUserDateOffset(Arguments $args)
	{
		$user = $args->getFirst();

		if (!$user)
		{
			return null;
		}

		$userId = \CBPHelper::extractFirstUser($user, $args->getParser()->getActivity()->getDocumentId());

		if (!$userId)
		{
			return 0;
		}

		return \CTimeZone::GetOffset($userId, true);
	}

	public function callStrtotime(Arguments $args)
	{
		$datetime = $args->getFirstSingle();
		$baseDate = $args->getSecondSingle();

		$baseTimestamp = $baseDate ? $this->makeTimestamp($baseDate, true) : time();

		if (!$baseTimestamp || !is_scalar($datetime))
		{
			return null;
		}

		$timestamp = strtotime($datetime, (int)$baseTimestamp);

		if ($timestamp === false)
		{
			return null;
		}

		return new Bizproc\BaseType\Value\DateTime($timestamp);
	}

	public function callLocDate(Arguments $args)
	{
		$format = $args->getFirst();
		$date = $args->getSecondSingle();

		if (!$format || !is_string($format))
		{
			return null;
		}

		$reformFormat = $this->frameSymbolsInDateFormat($format);
		$timestamp = $date ? $this->makeTimestamp($date, true) : time();

		if (!$timestamp)
		{
			return null;
		}

		$formattedDate = date($reformFormat, $timestamp);
		// return \FormatDate($format, $timestamp); - TODO
		if ($formattedDate === false)
		{
			return null;
		}

		return $this->replaceDateToLocDate($formattedDate, $reformFormat);
	}

	public function callSetTime(Arguments $args)
	{
		$date = $args->getFirstSingle();
		$currentOffset = $this->getDateTimeOffset($date);

		$hour = max(0, (int)$args->getSecondSingle());
		$minute = max(0, (int)$args->getThirdSingle());

		$timeOffset = $hour * self::ONE_HOUR + $minute * 60;
		$baseOffset = max(
			-1 * self::TWELVE_HOURS,
			min(self::TWELVE_HOURS, (int)$args->getArgSingle(3))
		);

		if ($baseOffset !== 0)
		{
			$timeOffset -= $baseOffset;
		}

		if (($date = $this->makeTimestamp($date, true)) === false)
		{
			return null;
		}

		$dateTime = Main\Type\DateTime::createFromTimestamp($date);
		$dateTime->setTime(0, 0, 0, 0);

		$date = $dateTime->getTimestamp() + $timeOffset;

		return new Bizproc\BaseType\Value\DateTime($date, $currentOffset);
	}

	private function makeTimestamp($date, $appendOffset = false)
	{
		if (!$date || (!is_scalar($date) && !is_object($date)))
		{
			return false;
		}

		//serialized date string
		if (is_string($date) && Bizproc\BaseType\Value\Date::isSerialized($date))
		{
			$date = new Bizproc\BaseType\Value\Date($date);
		}

		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return $date->getTimestamp() + ($appendOffset ? $date->getOffset() : 0);
		}

		if (intval($date) . "!" === $date . "!")
		{
			return $date;
		}

		if (($result = MakeTimeStamp($date, FORMAT_DATETIME)) === false)
		{
			if (($result = MakeTimeStamp($date, FORMAT_DATE)) === false)
			{
				if (($result = MakeTimeStamp($date, "YYYY-MM-DD HH:MI:SS")) === false)
				{
					$result = MakeTimeStamp($date, "YYYY-MM-DD");
				}
			}
		}
		return $result;
	}

	private function getWorkDayTimestamp($date)
	{
		return date('H', $date) * 3600 + date('i', $date) * 60;
	}

	private function getWorkDayRemainTimestamp($date, $multiplier = 1)
	{
		$dayTs = $this->getWorkDayTimestamp($date);
		[$startSeconds, $endSeconds] = $this->getCalendarWorkTime();
		return $multiplier < 0 ? $dayTs - $startSeconds : $endSeconds - $dayTs;
	}

	private function getWorkDayInterval()
	{
		[$startSeconds, $endSeconds] = $this->getCalendarWorkTime();
		return $endSeconds - $startSeconds;
	}

	private function isHoliday($date)
	{
		[$yearWorkdays] = $this->getCalendarWorkdays();
		[$weekHolidays, $yearHolidays] = $this->getCalendarHolidays();

		$dayOfYear = date('j.n', $date);
		if (in_array($dayOfYear, $yearWorkdays, true))
		{
			return false;
		}

		$dayOfWeek = date('w', $date);
		if (in_array($dayOfWeek, $weekHolidays))
		{
			return true;
		}

		$dayOfYear = date('j.n', $date);
		if (in_array($dayOfYear, $yearHolidays, true))
		{
			return true;
		}

		return false;
	}

	private function isWorkTime($date)
	{
		$dayTs = $this->getWorkDayTimestamp($date);
		[$startSeconds, $endSeconds] = $this->getCalendarWorkTime();
		return ($dayTs >= $startSeconds && $dayTs <= $endSeconds);
	}

	private function getNearestWorkTime($date, $multiplier = 1)
	{
		$reverse = $multiplier < 0;
		[$startSeconds, $endSeconds] = $this->getCalendarWorkTime();
		$dayTimeStamp = $this->getWorkDayTimestamp($date);

		if ($this->isHoliday($date))
		{
			$date -= $dayTimeStamp;
			$date += $reverse ? -86400 + $endSeconds : $startSeconds;
			$dayTimeStamp = $reverse ? $endSeconds : $startSeconds;
		}

		if (!$this->isWorkTime($date))
		{
			$date -= $dayTimeStamp;

			if ($dayTimeStamp < $startSeconds)
			{
				$date += $reverse ? -86400 + $endSeconds : $startSeconds;
			}
			else
			{
				$date += $reverse ? $endSeconds : 86400 + $startSeconds;
			}
		}

		if ($this->isHoliday($date))
		{
			$date = $this->addWorkDay($date, $reverse ? -1 : 1);
		}

		return $date;
	}

	private function addWorkDay($date, $days)
	{
		$delta = 86400;
		if ($days < 0)
		{
			$delta *= -1;
		}

		$days = abs($days);
		$iterations = 0;

		while ($days > 0 && $iterations < 1000)
		{
			++$iterations;
			$date += $delta;

			if ($this->isHoliday($date))
			{
				continue;
			}
			--$days;
		}

		return $date;
	}

	private function getCalendarHolidays()
	{
		if (static::$yearHolidays === null)
		{
			$calendarSettings = \CCalendar::GetSettings();
			$weekHolidays = [0, 6];
			$yearHolidays = [];

			if (isset($calendarSettings['week_holidays']))
			{
				$weekDays = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
				$weekHolidays = [];
				foreach ($calendarSettings['week_holidays'] as $day)
				{
					$weekHolidays[] = $weekDays[$day];
				}
			}

			if (isset($calendarSettings['year_holidays']))
			{
				foreach (explode(',', $calendarSettings['year_holidays']) as $yearHoliday)
				{
					$date = explode('.', trim($yearHoliday));
					if (count($date) == 2 && $date[0] && $date[1])
					{
						$yearHolidays[] = (int)$date[0] . '.' . (int)$date[1];
					}
				}
			}
			static::$weekHolidays = $weekHolidays;
			static::$yearHolidays = $yearHolidays;
		}

		return [static::$weekHolidays, static::$yearHolidays];
	}

	private function getCalendarWorkTime()
	{
		if (static::$startWorkDay === null)
		{
			$startSeconds = 0;
			$endSeconds = 24 * 3600 - 1;

			$calendarSettings = \CCalendar::GetSettings();
			if (!empty($calendarSettings['work_time_start']))
			{
				$time = explode('.', $calendarSettings['work_time_start']);
				$startSeconds = $time[0] * 3600;
				if (!empty($time[1]))
				{
					$startSeconds += $time[1] * 60;
				}
			}

			if (!empty($calendarSettings['work_time_end']))
			{
				$time = explode('.', $calendarSettings['work_time_end']);
				$endSeconds = $time[0] * 3600;
				if (!empty($time[1]))
				{
					$endSeconds += $time[1] * 60;
				}
			}
			static::$startWorkDay = $startSeconds;
			static::$endWorkDay = $endSeconds;
		}
		return [static::$startWorkDay, static::$endWorkDay];
	}

	private function getCalendarWorkdays()
	{
		if (static::$yearWorkdays === null)
		{
			$yearWorkdays = [];

			$calendarSettings = \CCalendar::GetSettings();
			$calendarYearWorkdays = $calendarSettings['year_workdays'] ?? '';

			foreach (explode(',', $calendarYearWorkdays) as $yearWorkday)
			{
				$date = explode('.', trim($yearWorkday));
				if (count($date) === 2 && $date[0] && $date[1])
				{
					$yearWorkdays[] = (int)$date[0] . '.' . (int)$date[1];
				}
			}

			static::$yearWorkdays = $yearWorkdays;
		}

		return [static::$yearWorkdays];
	}

	private function getDateTimeObject($date)
	{
		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return (new \DateTime())->setTimestamp($date->getTimestamp());
		}
		elseif (is_array($date))
		{
			$date = \CBPHelper::flatten($date);
		}

		if (!is_scalar($date))
		{
			return false;
		}

		$df = Main\Type\DateTime::getFormat();
		$df2 = Main\Type\Date::getFormat();
		$date1Formatted = \DateTime::createFromFormat($df, $date);
		if ($date1Formatted === false)
		{
			$date1Formatted = \DateTime::createFromFormat($df2, $date);
			if ($date1Formatted)
			{
				$date1Formatted->setTime(0, 0);
			}
		}
		return $date1Formatted;
	}

	private function getDateTimeOffset($date)
	{
		if ($date instanceof Bizproc\BaseType\Value\Date)
		{
			return $date->getOffset();
		}

		return 0;
	}

	private function frameSymbolsInDateFormat($format)
	{
		$complexSymbols = ['j F', 'd F', 'jS F'];
		$symbols = ['D', 'l', 'F', 'M', 'r'];

		$frameRule = [];
		foreach ($symbols as $symbol)
		{
			$frameRule[$symbol] = '#' . $symbol . '#';
			$frameRule['\\' . $symbol] = '\\' . $symbol;
		}
		foreach ($complexSymbols as $symbol)
		{
			$frameRule[$symbol] = substr($symbol, 0, -1) . '#' . $symbol[-1] . '_1#';
			$frameRule['\\' . $symbol] = '\\' . substr($symbol, 0, -1) . '#' . $symbol[-1] . '#';
		}

		return strtr($format, $frameRule);
	}

	private function frameNamesInFormattedDateRFC2822($formattedDate)
	{
		$matches = [];
		$pattern = "/#(\w{3}), \d{2} (\w{3}) \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4}#/";
		if (preg_match_all($pattern, $formattedDate, $matches))
		{
			foreach ($matches[0] as $key => $match)
			{
				$day = $matches[1][$key];
				$month = $matches[2][$key];

				$reformMatch = str_replace(
					[$day, $month],
					['#' . $day . '#', '#' . $month . '#'],
					$match
				);
				$reformMatch = substr($reformMatch, 1, -1);

				$formattedDate = str_replace($match, $reformMatch, $formattedDate);
			}
		}

		return $formattedDate;
	}

	private function replaceDateToLocDate($formattedDate, $format)
	{
		$lenShortName = 3;
		$dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		$monthNames = [
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December',
		];

		if (strpos($format, '#r#') !== false)
		{
			$formattedDate = $this->frameNamesInFormattedDateRFC2822($formattedDate);
		}

		$replacementRule = [];
		foreach (array_merge($dayNames, $monthNames) as $name)
		{
			$replacementRule['#' . $name . '#'] = Loc::getMessage(
				'BIZPROC_CALC_FUNCTION_LOCDATE_' . strtoupper($name)
			);
			$shortName = substr($name, 0, $lenShortName);
			$replacementRule['#' . $shortName . '#'] = Loc::getMessage(
				'BIZPROC_CALC_FUNCTION_LOCDATE_' . strtoupper($shortName) . '_SHORT'
			);
		}
		foreach ($monthNames as $monthName)
		{
			$replacementRule['#' . $monthName . '_1' . '#'] = Loc::getMessage(
				'BIZPROC_CALC_FUNCTION_LOCDATE_' . strtoupper($monthName) . '_1'
			);
		}

		return strtr($formattedDate, $replacementRule);
	}
}
