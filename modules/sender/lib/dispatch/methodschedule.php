<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

class MethodSchedule implements iMethod
{
	/** @var integer[] $monthsOfYear Months. */
	protected $monthsOfYear = array();

	/** @var integer[] $daysOfMonth Days. */
	protected $daysOfMonth = array();

	/** @var integer[] $daysOfWeek Week days. */
	protected $daysOfWeek = array();

	/** @var integer $hours Hour. */
	protected $hours = 0;

	/** @var integer $minutes Minutes. */
	protected $minutes = 0;

	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;
	}

	/**
	 * Set days of month.
	 *
	 * @param integer[] $days Days.
	 * @return $this
	 */
	public function setDaysOfMonth(array $days)
	{
		$this->daysOfMonth = $days;
		return $this;
	}

	/**
	 * Set days of week.
	 *
	 * @param integer[] $days Days.
	 * @return $this
	 */
	public function setDaysOfWeek(array $days)
	{
		$this->daysOfWeek = $days;
		return $this;
	}

	/**
	 * Set month of year.
	 *
	 * @param integer[] $months Months.
	 * @return $this
	 */
	public function setMonthsOfYear(array $months)
	{
		$this->monthsOfYear = $months;
		return $this;
	}

	/**
	 * Set time.
	 *
	 * @param string $hours Hours.
	 * @param string $minutes Minutes.
	 * @return $this
	 */
	public function setTime($hours, $minutes)
	{
		$this->hours = $hours;
		$this->minutes = $minutes;
		return $this;
	}

	private function getDateTimeByData(array $months = [], array $days = [])
	{
		if (empty($months))
		{
			for ($i = 1; $i <= 12; $i++)
			{
				$months[] = $i;
			}
		}
		if (empty($days))
		{
			for ($i = 1; $i <= 31; $i++)
			{
				$days[] = $i;
			}
		}
		foreach ([false, true] as $nextYear)
		{
			foreach ($months as $month)
			{
				foreach ($days as $day)
				{
					$date = $this->getDateTime($month, $day, $nextYear);
					if ($this->checkDateTime($date))
					{
						return $date;
					}
				}
			}
		}

		return null;
	}

	private function checkDateTime(DateTime $date = null)
	{
		static $current = null;
		if ($current === null)
		{
			$current = time();
		}

		if (!$date)
		{
			return false;
		}

		return $current < $date->getTimestamp();
	}

	private function getDateTime($month = null, $day = null, $nextYear = false)
	{
		if ($month === null && $day === null)
		{
			$date = new DateTime();
		}
		else
		{
			$date = DateTime::createFromTimestamp(
				mktime(0, 0, 0, $month, $day, date('Y') + ($nextYear ? 1 : 0))
			);
		}

		return $date->setTime((int) $this->hours, (int) $this->minutes);
	}

	/**
	 * Get next date.
	 *
	 * @return DateTime
	 * @throws NotImplementedException
	 */
	public function getNextDate()
	{
		if (empty($this->daysOfWeek) && empty($this->monthsOfYear) && empty($this->daysOfMonth))
		{
			return null;
		}

		if (!empty($this->monthsOfYear) || !empty($this->daysOfMonth))
		{
			$date = $this->getDateTimeByData($this->monthsOfYear, $this->daysOfMonth);
		}
		else
		{
			$date = $this->getDateTime();
			for($i = 0; $i < 7; $i++)
			{
				if ($i > 0)
				{
					$date->add("+1 days");
				}

				if (!$this->checkDateTime($date))
				{
					continue;
				}

				$day = (int) date('w', $date->getTimestamp());
				$day = $day === 0 ? 7 : $day;
				if (in_array($day, $this->daysOfWeek))
				{
					break;
				}
			}
		}

		return $date;
	}

	/**
	 * Apply method.
	 *
	 * @return void
	 */
	public function apply()
	{
		$this->letter->set('MONTHS_OF_YEAR', implode(',', $this->monthsOfYear));
		$this->letter->set('DAYS_OF_MONTH', implode(',', $this->daysOfMonth));
		$this->letter->set('DAYS_OF_WEEK', implode(',', $this->daysOfWeek));
		$this->letter->set('TIMES_OF_DAY', $this->hours ? ($this->hours . ':' . $this->minutes) : null);
		$this->letter->set('REITERATE', 'Y');
		$this->letter->set('AUTO_SEND_TIME', $this->getNextDate());
		$this->letter->save();
		$this->letter->getState()->wait($this);

	}

	/**
	 * Revoke method.
	 *
	 * @return void
	 */
	public function revoke()
	{
		$this->letter->set('MONTHS_OF_YEAR', null);
		$this->letter->set('DAYS_OF_MONTH', null);
		$this->letter->set('DAYS_OF_WEEK', null);
		$this->letter->set('TIMES_OF_DAY', null);
		$this->letter->set('REITERATE', 'N');
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return Method::SCHEDULE;
	}

	/**
	 * Parse days of month.
	 *
	 * @param string $daysOfMonth Days of month.
	 * @return array|null
	 */
	public static function parseDaysOfMonth($daysOfMonth)
	{
		$result = [];
		if ($daysOfMonth <> '')
		{
			$days = explode(",", $daysOfMonth);
			$found = [];
			foreach ($days as $day)
			{
				$day = trim($day);
				if (preg_match("/^(\d{1,2})$/", $day, $found))
				{
					if (intval($found[1]) < 1 || intval($found[1]) > 31)
					{
						return [];
					}
					else
					{
						$result[] = intval($found[1]);
					}
				}
				elseif (preg_match("/^(\d{1,2})-(\d{1,2})$/", $day, $found))
				{
					if (intval($found[1]) < 1 || intval($found[1]) > 31 || intval($found[2]) < 1 || intval($found[2]) > 31 || intval($found[1]) >= intval($found[2]))
					{
						return [];
					}
					else
					{
						for ($i = intval($found[1]); $i <= intval($found[2]); $i++)
						{
							$result[] = intval($i);
						}
					}
				}
				else
				{
					return [];
				}
			}
		}
		else
		{
			return [];
		}

		return $result;
	}

	/**
	 * Parse days of week.
	 *
	 * @param string $daysOfWeek Days of week.
	 * @return array|null
	 */
	public static function parseDaysOfWeek($daysOfWeek)
	{
		if($daysOfWeek == '')
		{
			return [];
		}

		$result = [];
		$days = explode(",", $daysOfWeek);
		foreach($days as $day)
		{
			$day = trim($day);
			$found = [];
			if(
				preg_match("/^(\d)$/", $day, $found)
				&& $found[1] >= 1
				&& $found[1] <= 7
			)
			{
				$result[]=intval($found[1]);
			}
			else
			{
				return [];
			}
		}

		return $result;
	}

	/**
	 * Parse months of year.
	 *
	 * @param string $monthsOfYear Months of year.
	 * @return array|null
	 */
	public static function parseMonthsOfYear($monthsOfYear)
	{
		if($monthsOfYear == '')
		{
			return [];
		}

		$result = [];
		$months = explode(",", $monthsOfYear);
		foreach($months as $month)
		{
			$month = trim($month);
			$found = [];
			if(
				preg_match("/^(\d{1,2})$/", $month, $found)
				&& $found[1] >= 1
				&& $found[1] <= 12
			)
			{
				$result[]=intval($found[1]);
			}
			else
			{
				return [];
			}
		}

		return $result;
	}

	/**
	 * Parse times of day.
	 *
	 * @param string $time Time.
	 * @return array|null
	 */
	public static function parseTimesOfDay($time)
	{
		if($time == '')
		{
			return null;
		}

		$time = trim($time);
		$found = [];
		if(
			preg_match("/^(\d{1,2}):(\d{1,2})$/", $time, $found)
			&& $found[1] <= 23
			&& $found[2] <= 59
		)
		{
			return [$found[1], $found[2]];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get time list.
	 *
	 * @return array
	 */
	public static function getTimeList()
	{
		$list = [];
		$timesOfDayHours = ['00', '30'];
		for ($hour = 0; $hour < 24; $hour++)
		{
			$hourPrint = str_pad($hour, 2, "0", STR_PAD_LEFT);
			foreach ($timesOfDayHours as $timePartHour)
			{
				$list[] = $hourPrint . ":" . $timePartHour;
			}
		}

		return $list;
	}
}