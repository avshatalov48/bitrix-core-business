<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Bitrix24\Limitation;

use Bitrix\Main\Config;

use Bitrix\Sender\Internals\Model;

/**
 * Class DailyLimit
 * @package Bitrix\Sender\Integration\Bitrix24\Limitation
 */
class DailyLimit
{
	/**	@var static $instance Instance */
	protected static $instance;

	/**
	 * Return true if installation is portal.
	 *
	 * @return static
	 */
	public static function instance()
	{
		if (!static::$instance)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		static $isRatingCalculated = false;
		if (!$isRatingCalculated)
		{
			$isRatingCalculated = true;
			Rating::calculate();
		}
	}

	/**
	 * Get current.
	 *
	 * @return integer
	 */
	public function getCurrent()
	{
		return Model\DailyCounterTable::getCurrentFieldValue('SENT_CNT');
	}

	/**
	 * Get limit.
	 *
	 * @return integer
	 */
	public function getLimit()
	{
		return intval(Config\Option::get("sender", "~mail_counter_limit_daily", 1000));
	}

	/**
	 * Set limit.
	 *
	 * @param int $limit Limit.
	 * @return void
	 */
	public function setLimit($limit)
	{
		Config\Option::set("sender", "~mail_counter_limit_daily", intval($limit));
	}

	/**
	 * Increment sent mails.
	 *
	 * @return void
	 */
	public static function increment()
	{
		Model\DailyCounterTable::incrementFieldValue('SENT_CNT');
	}

	/**
	 * Increment error mails.
	 *
	 * @return void
	 */
	public static function incrementError()
	{
		Model\DailyCounterTable::incrementFieldValue('ERROR_CNT');
	}
}