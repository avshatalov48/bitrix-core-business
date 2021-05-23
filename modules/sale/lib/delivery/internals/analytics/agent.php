<?php

namespace Bitrix\Sale\Delivery\Internals\Analytics;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\Analytics;

/**
 * Class Agent
 * @package Bitrix\Sale\Delivery\Internals\Analytics
 * @internal
 */
final class Agent extends Analytics\Agent
{
	/**
	 * @inheritDoc
	 */
	protected static function getProviderCode(): string
	{
		return Provider::getCode();
	}

	/**
	 * @inheritDoc
	 */
	protected static function getSuccessNextExecutionAgentDate(): DateTime
	{
		return static::toBitrixDate(
			(new \DateTime())
				->modify('first day of next month')
				->setTime(6, 0)
		);
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDateFrom(): DateTime
	{
		return static::toBitrixDate(
			(new \DateTime())->modify('first day of last month midnight')
		);
	}

	/**
	 * @inheritDoc
	 */
	protected static function getDateTo(): DateTime
	{
		return static::toBitrixDate(
			(new \DateTime())->modify('first day of this month midnight')
		);
	}

	/**
	 * @inheritDoc
	 */
	protected static function onSuccessfullySent(): void
	{
	}
}
