<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Helper\UI;

use Bitrix\Main\Context;

class DateTime
{
	public static function getDateValue(?\Bitrix\Main\Type\DateTime $value = null): string
	{
		if ($value === null)
		{
			return '';
		}

		$timestamp = static::getDateTimestamp($value);
		$format = static::getHumanDateTimeFormat($timestamp);

		return static::formatDateTime($timestamp, $format);
	}

	/**
	 * Converts timestamp into the time string in site format without any additional decorations
	 * @param $stamp
	 * @param bool|string $format in php notation
	 * @return string
	 */
	protected static function formatDateTime($stamp, ?string $format = null): string
	{
		$simple = false;

		// accept also FORMAT_DATE and FORMAT_DATETIME as ones of the legal formats
		if (
			(defined('FORMAT_DATE') && $format === FORMAT_DATE)
			|| (defined('FORMAT_DATETIME') && $format === FORMAT_DATETIME))
		{
			$format = \CDatabase::dateFormatToPHP($format);
			$simple = true;
		}

		$default = static::getDateTimeFormat();
		if ($format === false)
		{
			$format = $default;
			$simple = true;
		}

		if ($simple)
		{
			// its a simple format, we can use a simpler function
			return date($format, $stamp);
		}

		return FormatDate($format, $stamp);
	}

	protected static function getDateTimeFormat()
	{
		$format = (defined('FORMAT_DATETIME') ?  FORMAT_DATETIME : \CSite::getDateFormat());

		return \CDatabase::dateFormatToPHP($format);
	}

	/**
	 * @param \Bitrix\Main\Type\DateTime $date
	 * @return int
	 */
	protected static function getDateTimestamp(\Bitrix\Main\Type\DateTime $date): int
	{
		$timestamp = MakeTimeStamp($date);

		if ($timestamp === false)
		{
			$timestamp = strtotime($date);
			if ($timestamp !== false)
			{
				$timestamp += \CTimeZone::getOffset() - \Bitrix\Main\Type\DateTime::createFromTimestamp($timestamp)->getSecondGmt();
			}
		}

		return $timestamp;
	}

	protected static function getHumanDateTimeFormat(int $timestamp): string
	{
		$dateFormat = static::getHumanDateFormat($timestamp);
		$timeFormat = static::getHumanTimeFormat($timestamp);

		return $dateFormat . ($timeFormat ? ", {$timeFormat}" : '');
	}

	protected static function getHumanDateFormat(int $timestamp): string
	{
		$culture = Context::getCurrent()->getCulture();

		if (date('Y') !== date('Y', $timestamp))
		{
			return $culture->getLongDateFormat();
		}

		return $culture->getDayMonthFormat();
	}

	protected static function getHumanTimeFormat(int $timestamp): string
	{
		$timeFormat = '';
		$culture = Context::getCurrent()->getCulture();

		if (date('Hi', $timestamp) > 0)
		{
			$timeFormat = $culture->getShortTimeFormat();
		}

		return $timeFormat;
	}

}
