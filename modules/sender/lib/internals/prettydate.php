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
		$lang = Application::getInstance()->getContext()->getLanguage();
		$format = $lang === 'de' ? 'j. F' : 'j F';
		$format .= ', ' . ($isAmPm === AM_PM_LOWER? "g:i a" : ($isAmPm === AM_PM_UPPER? "g:i A" : "H:i"));
		return $format;
	}

	/**
	 * Get date format.
	 *
	 * @return string
	 */
	public static function getDateFormat()
	{
		$lang = Application::getInstance()->getContext()->getLanguage();
		return $lang === 'de' ? 'j. F' : 'j F';
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
	 * @param DateTime|null $date Date.
	 * @return string
	 */
	public static function formatDate(DateTime $date = null)
	{
		$date = $date ?: new DateTime();
		return \FormatDate(self::getDateFormat(), $date);
	}
}