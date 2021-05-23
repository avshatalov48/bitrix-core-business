<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\Type\Date,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Config\Option;

/**
 * Class Agent
 * @package Bitrix\Sale\Internals\Analytics
 * @internal
 */
abstract class Agent
{
	private const LAST_SEND_DATE = '~last_send_date_';
	private const LAST_ATTEMPT_DATE = '~last_attempt_date_';

	/**
	 * @return string
	 */
	abstract protected static function getProviderCode(): string;

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function send(): void
	{
		/** @var Provider $provider */
		$provider = Factory::create(static::getProviderCode());

		$sender = new Sender($provider);
		if ($sender->sendForPeriod(static::getDateFrom(), static::getDateTo()))
		{
			$nextExecutionAgentDate = static::getSuccessNextExecutionAgentDate();
			static::onSuccessfullySent();
		}
		else
		{
			$nextExecutionAgentDate = static::getFailureNextExecutionAgentDate();
		}

		static::createAgent($nextExecutionAgentDate);
	}

	/**
	 * @param DateTime $nextExecutionAgentDate
	 */
	protected static function createAgent(DateTime $nextExecutionAgentDate): void
	{
		\CAgent::Add([
			'NAME' => '\\'.static::class.'::send();',
			'MODULE_ID' => 'sale',
			'ACTIVE' => 'Y',
			'AGENT_INTERVAL' => 86400,
			'IS_PERIOD' => 'Y',
			'NEXT_EXEC' => $nextExecutionAgentDate,
		]);
	}

	/**
	 * @return DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function getSuccessNextExecutionAgentDate(): DateTime
	{
		$date = new \DateTime();
		$currentMonth = $date->format('n');

		$date->modify('+1 week');
		$modifiedMonth = $date->format('n');

		if ($modifiedMonth > $currentMonth)
		{
			$nextDate = $date->modify('first day of '.$date->format('F'))->format(DateTime::getFormat());
		}
		else
		{
			$nextDate = $date->format(DateTime::getFormat());
		}

		return new DateTime($nextDate);
	}

	/**
	 * @return DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function getFailureNextExecutionAgentDate(): DateTime
	{
		$date = new \DateTime();
		return new DateTime($date->modify('+1 hour')->format(DateTime::getFormat()));
	}

	/**
	 * @return DateTime
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function getDateFrom(): DateTime
	{
		$optionName = self::LAST_SEND_DATE.static::getProviderCode();

		$date = Option::get('sale', $optionName, null);
		if ($date && Date::isCorrect($date))
		{
			return new DateTime($date);
		}

		$date = (new \DateTime())->modify('first day of this month midnight')->format(DateTime::getFormat());
		$date = new DateTime($date);

		Option::set('sale', $optionName, $date);
		return $date;
	}

	/**
	 * @return DateTime
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function getDateTo(): DateTime
	{
		$optionName = self::LAST_ATTEMPT_DATE.static::getProviderCode();

		$date = Option::get('sale', $optionName, null);
		if ($date && Date::isCorrect($date))
		{
			return new DateTime($date);
		}

		$dateFrom = static::getDateFrom();
		$fromMonth = $dateFrom->format('n');

		$dateTo = new \DateTime();
		$toMonth = $dateTo->format('n');

		if ($toMonth > $fromMonth)
		{
			$dateTo = (new \DateTime())->modify('first day of this month midnight');
		}

		$date = new DateTime($dateTo->format(DateTime::getFormat()));

		Option::set('sale', $optionName, $date);
		return $date;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function onSuccessfullySent(): void
	{
		$dateTo = static::getDateTo();
		Option::set('sale', self::LAST_SEND_DATE.static::getProviderCode(), $dateTo);
		Option::delete('sale', ['name' => self::LAST_ATTEMPT_DATE.static::getProviderCode()]);
	}

	/**
	 * @param \DateTime $dateTime
	 * @return DateTime
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected static function toBitrixDate(\DateTime $dateTime): DateTime
	{
		return new DateTime($dateTime->format(DateTime::getFormat()));
	}
}
