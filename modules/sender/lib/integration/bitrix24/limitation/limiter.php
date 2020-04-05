<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Bitrix24\Limitation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bitrix24\MailCounter as B24MailCounter;

use Bitrix\Sender\Integration\Bitrix24\Service;
use Bitrix\Sender\Transport;

Loc::loadMessages(__FILE__);

/**
 * Class Limiter
 * @package Bitrix\Sender\Integration\Bitrix24\Limitation
 */
class Limiter
{
	/** @var  Transport\iLimiter[] $limiters */
	protected static $limiters;

	/**
	 * Return true if installation is portal.
	 *
	 * @return Transport\iLimiter[]
	 */
	public static function getList()
	{
		if (!Service::isCloud())
		{
			return array();
		}

		if (empty(self::$limiters))
		{
			self::$limiters = array(
				self::getMonthly(),
				self::getDaily(),
			);
		}

		return self::$limiters;
	}

	/**
	 * Get monthly limiter percentage.
	 *
	 * @return int
	 */
	public static function getMonthlyLimitPercentage()
	{
		$value = (int) Option::get('sender', '~mail_month_limit_percent', 90);
		$value = $value < 10 ? 10 : $value;
		$value = $value > 100 ? 100 : $value;

		return $value;
	}

	/**
	 * Set monthly limiter percentage.
	 *
	 * @param int $value Value.
	 * @return void
	 */
	public static function setMonthlyLimitPercentage($value)
	{
		$value = (int) $value;
		$value = $value < 10 ? 10 : $value;
		$value = $value > 100 ? 100 : $value;

		Option::set('sender', '~mail_month_limit_percent', $value);
	}

	/**
	 * Return monthly limiter.
	 *
	 * @return Transport\CountLimiter
	 */
	public static function getMonthly()
	{
		$counter = new B24MailCounter();
		return Transport\CountLimiter::create()
			->withName('mail_per_month')
			->withLimit($counter->getLimit())
			->withUnit("1 " . Transport\iLimiter::MONTHS)
			->withCurrent(
				function () use ($counter)
				{
					return $counter->getMonthly();
				}
			)
			->setParameter('setupUri', 'javascript: BX.Sender.B24License.showMailLimitPopup();')
			->setParameter(
				'globalHelpUri',
				Loc::getMessage('SENDER_INTEGRATION_BITRIX24_LIMITER_MONTH_HELP_URL') ?: Loc::getMessage('SENDER_INTEGRATION_BITRIX24_LIMITER_DAILY_HELP_URL')
			)
			->setParameter('percentage', self::getMonthlyLimitPercentage());
	}

	/**
	 * Return daily limiter.
	 *
	 * @return Transport\iLimiter
	 */
	protected static function getDaily()
	{
		$counter = new DailyLimit();
		return Transport\CountLimiter::create()
			->withName('mail_per_day')
			->withLimit($counter->getLimit())
			->withUnit("1 " . Transport\iLimiter::DAYS)
			->withCurrent(
				function () use ($counter)
				{
					return $counter->getCurrent();
				}
			)
			->setParameter('setupUri', Loc::getMessage('SENDER_INTEGRATION_BITRIX24_LIMITER_DAILY_HELP_URL'))
			->setParameter('setupCaption', Loc::getMessage('SENDER_INTEGRATION_BITRIX24_LIMITER_DAILY_DETAILS'));
	}
}