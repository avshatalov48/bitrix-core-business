<?php

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main;

class DateTimeFactory
{
	/**
	 * Create DateTime with 00:00:00 today time in user timezone
	 * @return DateTime
	 */
	public static function createToday(): DateTime
	{
		$dateInUserTimezone = new Main\Type\DateTime();
		$dateInUserTimezone
			->toUserTime()
			->setTime(0, 0, 0)
		;

		return new DateTime($dateInUserTimezone->getTimestamp());
	}

	/**
	 * Create DateTime with 00:00:00 time and current week monday date in user timezone
	 * @return DateTime
	 */
	public static function createCurrentWeekMonday(): DateTime
	{
		$date = self::getMonday();

		return new DateTime($date->getTimestamp());
	}

	/**
	 * Create DateTime with 00:00:00 time and next week monday date in user timezone
	 * @return DateTime
	 */
	public static function createNextWeekMonday(): DateTime
	{
		$date = self::getMonday();
		$date->add('+1 week');

		return new DateTime($date->getTimestamp());
	}

	/**
	 * Create DateTime with 00:00:00 time and previous week monday date in user timezone
	 * @return DateTime
	 */
	public static function createLastWeekMonday(): DateTime
	{
		$date = self::getMonday();
		$date->add('-1 week');

		return new DateTime($date->getTimestamp());
	}

	/**
	 * Create DateTime with 00:00:00 time and first day of current month date in user timezone
	 * @return DateTime
	 */
	public static function createFirstDayOfCurrentMonth(): DateTime
	{
		$date = self::getFirstDayOfMonth(strtotime('first day of this month'));

		return new DateTime($date->getTimestamp());
	}

	/**
	 * Create DateTime with 00:00:00 time and first day of next month date in user timezone
	 * @return DateTime
	 */
	public static function createFirstDayOfNextMonth()
	{
		$date = self::getFirstDayOfMonth(strtotime('first day of this month'));
		$date->add('+1 month');

		return new DateTime($date->getTimestamp());
	}

	/**
	 * Create DateTime with 00:00:00 time and first day of previous month date in user timezone
	 * @return DateTime
	 */
	public static function createFirstDayOfLastMonth()
	{
		$date = self::getFirstDayOfMonth(strtotime('first day of this month'));
		$date->add('-1 month');

		return new DateTime($date->getTimestamp());
	}

	private static function getMonday(): Main\Type\DateTime
	{
		$timestamp = strtotime('monday this week');

		$date = Main\Type\DateTime::createFromTimestamp($timestamp);
		$date->setTime(0, 0, 0);

		$todayDate = new Main\Type\DateTime();
		$todayDate->setTime(0, 0, 0);

		if ($date->format('Y-m-d') === $todayDate->format('Y-m-d')) // is it monday today?
		{
			$todayInUserTimezone = new Main\Type\DateTime();;
			$todayInUserTimezone->toUserTime();

			if ($todayInUserTimezone->format('N') == '7') // if user date is still previous saturday
			{
				$date->add('-1 week');
			}
		}

		return $date;
	}

	private static function getFirstDayOfMonth(): Main\Type\DateTime
	{
		$timestamp = strtotime('first day of this month');

		$date = Main\Type\DateTime::createFromTimestamp($timestamp);
		$date->setTime(0, 0, 0);

		$todayDate = new Main\Type\DateTime();
		$todayDate->setTime(0, 0, 0);

		if ($date->format('Y-m-d') === $todayDate->format('Y-m-d')) // is first day of month today?
		{
			$todayInUserTimezone = new Main\Type\DateTime();
			$todayInUserTimezone->toUserTime();

			if ((int)($todayInUserTimezone->format('j')) >= 28) // if user date is still previous month
			{
				$date->add('-1 month');
			}
		}

		return $date;
	}
}
