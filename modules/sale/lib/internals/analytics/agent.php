<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class Agent
 * @package Bitrix\Sale\Internals\Analytics
 * @internal
 */
class Agent
{
	/** @var Main\Type\DateTime $date */
	private static $date;

	/**
	 * Sends data
	 *
	 * @return void
	 */
	public static function send(): void
	{
		self::$date = new Main\Type\DateTime();

		foreach (self::getProviders() as $provider)
		{
			$providerCode = $provider::getCode();
			$payload = Storage::getPayloadByCode($providerCode, self::$date);

			$sender = new Sender($providerCode, $payload);
			if ($sender->send())
			{
				static::onSuccessfullySent($providerCode, self::$date);
			}
		}

		static::createAgent(static::getNextExecutionAgentDate());
	}

	/**
	 * @param Main\Type\DateTime $nextExecutionAgentDate
	 * @return void
	 */
	protected static function createAgent(Main\Type\DateTime $nextExecutionAgentDate): void
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
	 * @return Main\Type\DateTime
	 */
	protected static function getNextExecutionAgentDate(): Main\Type\DateTime
	{
		$date = new \DateTime();
		$currentMonth = $date->format('n');

		$date->modify('+1 day');
		$modifiedMonth = $date->format('n');

		if ($modifiedMonth > $currentMonth)
		{
			$nextDate =
				$date
					->modify('first day of '.$date->format('F'))
					->format(Main\Type\DateTime::getFormat())
			;
		}
		else
		{
			$nextDate = $date->format(Main\Type\DateTime::getFormat());
		}

		return new Main\Type\DateTime($nextDate);
	}

	/**
	 * @return void
	 */
	protected static function onSuccessfullySent(string $providerCode, Main\Type\DateTime $dateTo): void
	{
		Storage::clean($providerCode, $dateTo);
	}

	/**
	 * @return Provider[]
	 */
	private static function getProviders(): array
	{
		return [
			Sale\PaySystem\Internals\Analytics\Provider::class,
			Sale\Delivery\Internals\Analytics\Provider::class,
			Sale\Cashbox\Internals\Analytics\Provider::class,
			Sale\Internals\Analytics\Events\Provider::class,
		];
	}
}
