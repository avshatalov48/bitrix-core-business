<?php
namespace Bitrix\Main\Type;

use Bitrix\Main;
use Bitrix\Main\Context;

class DateTime extends Date
{
	/** @var bool */
	protected $userTimeEnabled = true;

	/**
	 * @param string $time String representation of datetime.
	 * @param string $format PHP datetime format. If not specified, the format is got from the current culture.
	 * @param \DateTimeZone $timezone Optional timezone object.
	 *
	 * @throws Main\ObjectException
	 */
	public function __construct($time = null, $format = null, \DateTimeZone $timezone = null)
	{
		if ($timezone === null)
		{
			$this->value = new \DateTime();
		}
		else
		{
			$this->value = new \DateTime(null, $timezone);
		}

		if ($time !== null && $time !== "")
		{
			if ($format === null)
			{
				$format = static::getFormat();
			}

			$parsedValue = $this->parse($format, $time);

			if($parsedValue === false)
			{
				throw new Main\ObjectException("Incorrect date/time: ".$time);
			}

			if(isset($parsedValue["timestamp"]))
			{
				$this->value->setTimestamp($parsedValue["timestamp"]);
			}
			else
			{
				if(isset($parsedValue["zone_type"]) && $parsedValue["zone_type"] == 1)
				{
					if(isset($parsedValue["zone"]) && $parsedValue["zone"] <> 0)
					{
						$this->setTimeZone(new \DateTimeZone(static::secondsToOffset($parsedValue["zone"])));
					}
				}

				$microseconds = 0;
				if($parsedValue['fraction'] > 0)
				{
					$microseconds = intval($parsedValue['fraction'] * 1000000);
				}

				$this->value->setDate($parsedValue['year'], $parsedValue['month'], $parsedValue['day']);
				$this->value->setTime($parsedValue['hour'], $parsedValue['minute'], $parsedValue['second'], $microseconds);
  			}
		}
	}

	public static function secondsToOffset($seconds)
	{
		$absSeconds = abs($seconds);
		$hours = sprintf("%02d", floor($absSeconds / 3600));
		$minutes = gmdate("i", $absSeconds % 3600);
		return ($seconds < 0? "-" : "+").$hours.$minutes;
	}

	/**
	 * Converts date to string, using Culture and global timezone settings.
	 *
	 * @param Context\Culture $culture Culture contains datetime format.
	 *
	 * @return string
	 */
	public function toString(Context\Culture $culture = null)
	{
		if(\CTimeZone::Enabled() && $this->userTimeEnabled)
		{
			$userTime = clone $this;
			$userTime->toUserTime();

			$format = static::getFormat($culture);
			return $userTime->format($format);
		}
		else
		{
			return parent::toString($culture);
		}
	}

	/**
	 * Returns timezone object.
	 *
	 * @return \DateTimeZone
	 */
	public function getTimeZone()
	{
		return $this->value->getTimezone();
	}

	/**
	 * Sets timezone object.
	 *
	 * @param \DateTimeZone $timezone Timezone object.
	 *
	 * @return DateTime
	 */
	public function setTimeZone(\DateTimeZone $timezone)
	{
		$this->value->setTimezone($timezone);
		return $this;
	}

	/**
	 * Sets default timezone.
	 *
	 * @return DateTime
	 */
	public function setDefaultTimeZone()
	{
		$time = new \DateTime();
		$this->setTimezone($time->getTimezone());
		return $this;
	}

	/**
	 * @param int $hour Hour value.
	 * @param int $minute Minute value.
	 * @param int $second Second value.
	 * @param int $microseconds Microseconds value.
	 *
	 * @return DateTime
	 */
	public function setTime($hour, $minute, $second = 0, $microseconds = 0)
	{
		$this->value->setTime($hour, $minute, $second, $microseconds);
		return $this;
	}

	/**
	 * Changes time from server time to user time using global timezone settings.
	 *
	 * @return DateTime
	 */
	public function toUserTime()
	{
		//first, move to server timezone
		$this->setDefaultTimeZone();

		//second, adjust time according global timezone offset
		static $diff = null;
		if($diff === null)
		{
			$diff = \CTimeZone::GetOffset();
		}
		if($diff <> 0)
		{
			$this->add(($diff < 0? "-":"")."PT".abs($diff)."S");
		}
		return $this;
	}

	/**
	 * Creates DateTime object from local user time using global timezone settings and default culture.
	 *
	 * @param string $timeString Full or short formatted time.
	 *
	 * @return DateTime
	 */
	public static function createFromUserTime($timeString)
	{
		/** @var DateTime $time */
		try
		{
			//try full datetime format
			$time = new static($timeString);
		}
		catch(Main\ObjectException $e)
		{
			//try short date format
			$time = new static($timeString, Date::getFormat());
			$time->setTime(0, 0, 0);
		}

		if(\CTimeZone::Enabled())
		{
			static $diff = null;
			if($diff === null)
			{
				$diff = \CTimeZone::GetOffset();
			}
			if($diff <> 0)
			{
				$time->add(($diff > 0? "-":"")."PT".abs($diff)."S");
			}
		}
		return $time;
	}

	/**
	 * Returns long (including time) date culture format.
	 *
	 * @param Context\Culture $culture Culture.
	 *
	 * @return string
	 */
	protected static function getCultureFormat(Context\Culture $culture = null)
	{
		if($culture)
		{
			return $culture->getDateTimeFormat();
		}
		return "DD.MM.YYYY HH:MI:SS";
	}

	/**
	 * Creates DateTime object from PHP \DateTime object.
	 *
	 * @param \DateTime $datetime Source object.
	 *
	 * @return static
	 */
	public static function createFromPhp(\DateTime $datetime)
	{
		$d = new static();
		$d->value = clone $datetime;
		return $d;
	}

	/**
	 * Creates DateTime object from Unix timestamp.
	 *
	 * @param int $timestamp Source timestamp.
	 *
	 * @return static
	 */
	public static function createFromTimestamp($timestamp)
	{
		$d = new static();
		$d->value->setTimestamp($timestamp);
		return $d;
	}

	/**
	 * Creates DateTime object from string.
	 * NULL will be returned on failure.
	 * @param string $timeString Full formatted time.
	 * @param string $format PHP datetime format. If not specified, the format is got from the current culture.
	 * @return DateTime|null
	 */
	public static function tryParse($timeString, $format = null)
	{
		if($timeString === '')
		{
			return null;
		}

		if ($format === null)
		{
			$format = static::getFormat();
		}

		try
		{
			$time = new DateTime($timeString, $format);
		}
		catch(Main\ObjectException $e)
		{
			$time = null;
		}
		return $time;
	}

	/**
	 * @return bool
	 */
	public function isUserTimeEnabled()
	{
		return $this->userTimeEnabled;
	}

	/**
	 * @return $this
	 */
	public function disableUserTime()
	{
		$this->userTimeEnabled = false;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function enableUserTime()
	{
		$this->userTimeEnabled = true;

		return $this;
	}
}
