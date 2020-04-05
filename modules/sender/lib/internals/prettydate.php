<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class PrettyDate
 * @package Bitrix\Sender\Internals
 */
class PrettyDate
{
	/**
	 * Get datetime format.
	 *
	 * @return string
	 */
	public static function getDateTimeFormat()
	{
		$isAmPm = IsAmPmMode(true);
		switch ($isAmPm)
		{
			case AM_PM_LOWER:
				return Loc::getMessage('SENDER_PRETTY_DATE_FORMAT_DATETIME_PM_LOWER');

			case AM_PM_UPPER:
				return Loc::getMessage('SENDER_PRETTY_DATE_FORMAT_DATETIME_PM_UPPER');
		}

		return Loc::getMessage('SENDER_PRETTY_DATE_FORMAT_DATETIME');
	}

	/**
	 * Get date format.
	 *
	 * @return string
	 */
	public static function getDateFormat()
	{
		return Loc::getMessage('SENDER_PRETTY_DATE_FORMAT_DATE');
	}

	/**
	 * Format datetime.
	 *
	 * @param DateTime|null $date Date.
	 * @return string
	 */
	public static function formatDateTime(DateTime $date = null)
	{
		$date = $date ?: new DateTime();
		return \FormatDate(self::getDateTimeFormat(), $date);
	}

	/**
	 * Format date.
	 *
	 * @param Date|null $date Date.
	 * @return string
	 */
	public static function formatDate(Date $date = null)
	{
		$date = $date ?: new Date();
		return \FormatDate(self::getDateFormat(), DateTime::createFromTimestamp($date->getTimestamp()));
	}
}