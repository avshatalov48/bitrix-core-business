<?

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main\Type\Date;


/**
 * Works with quarters
 * @package Bitrix\Main\UI\Filter
 */
class Quarter
{
	const Q1 = 1;
	const Q2 = 2;
	const Q3 = 3;
	const Q4 = 4;
	const Q1_START = 1;
	const Q1_END = 3;
	const Q2_START = 4;
	const Q2_END = 6;
	const Q3_START = 7;
	const Q3_END = 9;
	const Q4_START = 10;
	const Q4_END = 12;


	/**
	 * Gets current number of quarter
	 * @return int
	 */
	public static function getCurrent()
	{
		$date = new Date();
		return self::get($date);
	}


	/**
	 * Gets number of quarter by Bitrix\Main\Type\Date object
	 * @param Date $date
	 * @return int
	 */
	public static function get(Date $date)
	{
		$currentMonth = $date->format("n");
		$currentQuarter = self::Q1;

		if ($currentMonth >= self::Q2_START && $currentMonth <= self::Q2_END)
		{
			$currentQuarter = self::Q2;
		}

		if ($currentMonth >= self::Q3_START && $currentMonth <= self::Q3_END)
		{
			$currentQuarter = self::Q3;
		}

		if ($currentMonth >= self::Q4_START && $currentMonth <= self::Q4_END)
		{
			$currentQuarter = self::Q4;
		}

		return $currentQuarter;
	}


	/**
	 * Gets start date of quarter
	 * @param int $quarter
	 * @param int $year
	 * @return string
	 */
	public static function getStartDate($quarter, $year)
	{
		$startMonth = constant("self::Q".$quarter."_START");
		$startDate = Date::createFromTimestamp(mktime(0, 0, 0, $startMonth, 1, $year));
		$startDateString = $startDate->toString();

		return $startDateString;
	}


	/**
	 * Gets end date of quarter
	 * @param int $quarter
	 * @param int $year
	 * @return string
	 */
	public static function getEndDate($quarter, $year)
	{
		$date = Date::createFromTimestamp(\MakeTimeStamp(self::getStartDate($quarter, $year)));
		$date->add("3 months");
		return $date->toString();
	}
}