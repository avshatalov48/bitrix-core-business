<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\Type\Date,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Config\Option,
	Bitrix\Main\SystemException;

/**
 * Class Agent
 * @package Bitrix\Sale\Internals\Analytics
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
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function send(): void
	{
		/** @var Provider $provider */
		$provider = Factory::create(static::getProviderCode());
		if ($provider === null)
		{
			throw new SystemException('Failed to create provider');
		}

		$sender = new Sender($provider);
		if ($sender->sendForPeriod(self::getLastSendDate(), self::getLastAttemptDate()))
		{
			$nextExecutionAgentDate = self::getSuccessNextExecutionAgentDate();
			self::updateDate();
		}
		else
		{
			$nextExecutionAgentDate = self::getFailureNextExecutionAgentDate();
		}

		self::createAgent($nextExecutionAgentDate);
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
	private static function getSuccessNextExecutionAgentDate(): DateTime
	{
		$date = new \DateTime();
		$currentMonth = $date->format('n');
		$date->modify('+1 week')->format(DateTime::getFormat());
		$modifiedMonth = $date->format('n');

		if ($modifiedMonth > $currentMonth)
		{
			$nextDate = $date->modify('first day of next month')->format(DateTime::getFormat());
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
	private static function getFailureNextExecutionAgentDate(): DateTime
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
	private static function getLastSendDate(): DateTime
	{
		$optionName = self::LAST_SEND_DATE.static::getProviderCode();

		$date = Option::get('sale', $optionName, null);
		if ($date)
		{
			return new DateTime($date);
		}

		$date = (new \DateTime())->modify('first day of this month')->format(Date::getFormat().' 00:00:00');
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
	private static function getLastAttemptDate(): DateTime
	{
		$optionName = self::LAST_ATTEMPT_DATE.static::getProviderCode();

		$date = Option::get('sale', $optionName, null);
		if ($date)
		{
			return new DateTime($date);
		}

		$date = (new \DateTime())->format(DateTime::getFormat());
		$date = new DateTime($date);

		Option::set('sale', $optionName, $date);
		return $date;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function updateDate(): void
	{
		$lastAttemptDate = self::getLastAttemptDate();
		Option::set('sale', self::LAST_SEND_DATE.static::getProviderCode(), $lastAttemptDate);
		Option::delete('sale', ['name' => self::LAST_ATTEMPT_DATE.static::getProviderCode()]);
	}
}
