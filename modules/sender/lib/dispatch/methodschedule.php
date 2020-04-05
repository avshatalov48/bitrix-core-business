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
	/** @var integer[] $dateTime Date. */
	protected $daysOfMonth = array();

	/** @var integer[] $dateTime Date. */
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
	 * Set days of month.
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

	/**
	 * Get next date.
	 *
	 * @return DateTime
	 * @throws NotImplementedException
	 */
	public function getNextDate()
	{
		$currentDate = new DateTime();
		$date = new DateTime();
		$date->setTime((int) $this->hours, (int) $this->minutes);
		if (empty($this->daysOfWeek))
		{
			return null;
		}
		for($i = 0; $i < 7; $i++)
		{
			if ($i > 0)
			{
				$date->add("+1 days");
			}

			if ($currentDate->getTimestamp() > $date->getTimestamp())
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

		return $date;
	}

	/**
	 * Apply method.
	 *
	 * @return void
	 */
	public function apply()
	{
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
		if (strlen($daysOfMonth) > 0)
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
		if(strlen($daysOfWeek) <= 0)
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
	 * Parse times of day.
	 *
	 * @param string $time Time.
	 * @return array|null
	 */
	public static function parseTimesOfDay($time)
	{
		if(strlen($time) <= 0)
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