<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Sender\Runtime;

use Bitrix\Main\Config\Option;

/**
 * Class Env
 * @package Bitrix\Sender\Runtime
 */
class Env
{
	/**
	 * Return true if jobs run at cron.

	 * @return bool
	 */
	public static function isSenderJobCron()
	{
		return Option::get("sender", "auto_method") === 'cron';
	}

	/**
	 * Return true if reiterated jobs run at cron.

	 * @return bool
	 */
	public static function isReiteratedJobCron()
	{
		return Option::get("sender", "reiterate_method") === 'cron';
	}

	/**
	 * Get execution timeout.

	 * @return int
	 */
	public static function getJobExecutionTimeout()
	{
		return self::isSenderJobCron() ? 0 : (int) Option::get('sender', 'interval');
	}

	/**
	 * Get execution item limit.

	 * @return int
	 */
	public static function getJobExecutionItemLimit()
	{
		if(self::isSenderJobCron())
		{
			return (int) Option::get('sender', 'max_emails_per_cron');
		}
		else
		{
			return (int) Option::get('sender', 'max_emails_per_hit');
		}
	}
}