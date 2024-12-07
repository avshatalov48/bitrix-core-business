<?php

namespace Bitrix\Main\Type;

use Bitrix\Main;
use Bitrix\Main\Context;

class Date
{
	/** @var \DateTime */
	protected $value;

	/**
	 * @param string | null $date String representation of date.
	 * @param string | null $format PHP date format. If not specified, the format is got from the current culture.
	 *
	 * @throws Main\ObjectException
	 */
	public function __construct($date = null, $format = null)
	{
		$this->value = new \DateTime();
		if ($date !== null && $date !== "")
		{
			if ($format === null)
			{
				$format = static::getFormat();
			}

			$parsedValue = $this->parse($format, $date);

			if ($parsedValue === false)
			{
				throw new Main\ObjectException("Incorrect date: " . $date);
			}

			if (isset($parsedValue["timestamp"]))
			{
				$this->value->setTimestamp($parsedValue["timestamp"]);
			}
			else
			{
				$this->value->setDate($parsedValue['year'], $parsedValue['month'], $parsedValue['day']);
			}
		}
		$this->value->setTime(0, 0);
	}

	/**
	 * @param string $format
	 * @param string $time
	 * @return array|bool
	 */
	protected function parse($format, $time)
	{
		$parsedValue = date_parse_from_format($format, $time);

		//Ignore errors when format is longer than date
		//or date string is longer than format
		if ($parsedValue['error_count'] > 1)
		{
			$error = current($parsedValue['errors']);

			if ($error === 'A two digit second could not be found')
			{
				//possibly missed seconds with am/pm format
				$timestamp = strtotime($time);

				if ($timestamp === false)
				{
					return false;
				}

				return [
					"timestamp" => $timestamp,
				];
			}
			if ($error !== 'Trailing data' && $error !== 'Data missing')
			{
				return false;
			}
		}

		if (isset($parsedValue["relative"]["second"]) && $parsedValue["relative"]["second"] <> 0)
		{
			return [
				"timestamp" => $parsedValue["relative"]["second"],
			];
		}

		//normalize values
		if ($parsedValue['month'] === false)
		{
			$parsedValue['month'] = 1;
		}
		if ($parsedValue['day'] === false)
		{
			$parsedValue['day'] = 1;
		}

		return $parsedValue;
	}

	/**
	 * Formats date value to string.
	 *
	 * @param string $format PHP date format.
	 *
	 * @return string
	 */
	public function format($format)
	{
		return $this->value->format($format);
	}

	/**
	 * Produces the copy of the object.
	 *
	 * @return void
	 */
	public function __clone()
	{
		$this->value = clone $this->value;
	}

	/**
	 * Performs dates arithmetic.
	 *
	 * Each duration period is represented by an integer value followed by a period
	 * designator. If the duration contains time elements, that portion of the
	 * specification is preceded by the letter T.
	 * Period Designators: Y - years, M - months, D - days, W - weeks, H - hours,
	 * M - minutes, S - seconds.
	 * Examples: two days - 2D, two seconds - T2S, six years and five minutes - 6YT5M.
	 * The unit types must be entered from the largest scale unit on the left to the
	 * smallest scale unit on the right.
	 * Use first "-" char for negative periods.
	 * OR
	 * Relative period.
	 * Examples: "+5 weeks", "12 day", "-7 weekdays", '3 months - 5 days'
	 *
	 * @param string $interval Time interval to add.
	 *
	 * @return $this
	 */
	public function add($interval)
	{
		$i = $this->tryToCreateIntervalByDesignators($interval);
		if ($i == null)
		{
			$i = \DateInterval::createFromDateString($interval);
		}

		if ($i instanceof \DateInterval)
		{
			$this->value->add($i);
		}

		return $this;
	}

	/**
	 * Sets the current date of the DateTime object to a different date.
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 *
	 * @return $this
	 */
	public function setDate($year, $month, $day)
	{
		$this->value->setDate($year, $month, $day);

		return $this;
	}

	private function tryToCreateIntervalByDesignators($interval)
	{
		if (!is_string($interval) || str_contains($interval, ' '))
		{
			return null;
		}

		$i = null;
		try
		{
			$intervalTmp = strtoupper($interval);
			$isNegative = false;
			$firstChar = substr($intervalTmp, 0, 1);
			if ($firstChar === "-")
			{
				$isNegative = true;
				$intervalTmp = substr($intervalTmp, 1);
				$firstChar = substr($intervalTmp, 0, 1);
			}

			if ($firstChar !== "P")
			{
				$intervalTmp = "P" . $intervalTmp;
			}
			$i = new \DateInterval($intervalTmp);
			if ($isNegative)
			{
				$i->invert = 1;
			}
		}
		catch (\Exception)
		{
		}

		return $i;
	}

	/**
	 * Returns Unix timestamp from date.
	 *
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->value->getTimestamp();
	}

	/**
	 * Returns difference between dates.
	 *
	 * @param Date $time
	 * @return \DateInterval
	 */
	public function getDiff(Date $time)
	{
		return $this->value->diff($time->value);
	}

	/**
	 * Converts a date to the string.
	 *
	 * @param Context\Culture | null $culture Culture contains date format.
	 *
	 * @return string
	 */
	public function toString(Context\Culture $culture = null)
	{
		$format = static::getFormat($culture);
		return $this->format($format);
	}

	/**
	 * Converts a date to the string with default culture format setting.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns a date format from the culture in the php format.
	 *
	 * @param Context\Culture | null $culture Optional culture.
	 *
	 * @return string
	 */
	public static function getFormat(Context\Culture $culture = null)
	{
		static $defaultCulture = null;

		if ($culture === null)
		{
			if ($defaultCulture === null)
			{
				$context = Context::getCurrent();
				if ($context)
				{
					$defaultCulture = $context->getCulture();
				}
			}
			$culture = $defaultCulture;
		}

		$format = static::getCultureFormat($culture);

		return static::convertFormatToPhp($format);
	}

	/**
	 * Returns short date culture format.
	 *
	 * @param Context\Culture | null $culture Culture.
	 *
	 * @return string
	 */
	protected static function getCultureFormat(Context\Culture $culture = null)
	{
		if ($culture)
		{
			return $culture->getDateFormat();
		}
		return "DD.MM.YYYY";
	}

	/**
	 * Converts date format from culture to php format.
	 *
	 * @param string $format Format string.
	 *
	 * @return string
	 */
	public static function convertFormatToPhp($format)
	{
		static $from = [
			"YYYY", // 1999
			"MMMM", // January - December
			"MM", // 01 - 12
			"DD", // 01 - 31
			"TT", // AM - PM
			"T", // am - pm
			"MI", // 00 - 59
			"SS", // 00 - 59
		];
		static $to = [
			"Y", // 1999
			"F", // January - December
			"m", // 01 - 12
			"d", // 01 - 31
			"A", // AM - PM
			"a", // am - pm
			"i", // 00 - 59
			"s", // 00 - 59
		];

		$format = str_replace($from, $to, $format);

		$tempFormat = $format;
		$format = str_replace("HH", "H", $format); // 00 - 24
		if ($tempFormat === $format)
		{
			$format = str_replace("H", "h", $format); // 01 - 12
		}

		$tempFormat = $format;
		$format = str_replace("GG", "G", $format); // 0 - 24
		if ($tempFormat === $format)
		{
			$format = str_replace("G", "g", $format); // 1 - 12
		}

		return $format;
	}

	/**
	 * Checks the string for correct date (by trying to create Date object).
	 *
	 * @param string $time String representation of date.
	 * @param string $format PHP date format. If not specified, the format is got from the current culture.
	 *
	 * @return bool
	 */
	public static function isCorrect($time, $format = null)
	{
		if (empty($time))
		{
			return false;
		}

		$result = true;

		try
		{
			new static($time, $format);
		}
		catch (Main\ObjectException)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Creates Date object from PHP \DateTime object.
	 *
	 * @param \DateTime $datetime Source object.
	 *
	 * @return static
	 */
	public static function createFromPhp(\DateTime $datetime)
	{
		$d = new static();
		$d->value = clone $datetime;
		$d->value->setTime(0, 0);
		return $d;
	}

	/**
	 * Creates Date object from Unix timestamp.
	 *
	 * @param int $timestamp Source timestamp.
	 *
	 * @return static
	 */
	public static function createFromTimestamp($timestamp)
	{
		$d = new static();
		$d->value->setTimestamp($timestamp);
		$d->value->setTime(0, 0);
		return $d;
	}

	/**
	 * Creates Date object from Text (return array of result object)
	 * Examples: "end of next week", "tomorrow morning", "friday 25.10"
	 *
	 * @param string $text
	 * @return DateTime|null
	 */
	public static function createFromText($text)
	{
		$result = Main\Text\DateConverter::decode($text);
		if (empty($result))
		{
			return null;
		}

		return $result[0]->getDate();
	}
}
