<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Cashbox\Internals\CashboxErrLogTable;

/**
 * Class Logger
 * @package Bitrix\Sale\Cashbox
 */
class Logger
{
	/* trace levels */
	protected const TRACE_LEVEL_ERROR = 1;
	protected const TRACE_LEVEL_WARNING = 2;
	protected const TRACE_LEVEL_DEBUG = 3;

	/**
	 * @param string $message
	 * @param null $cashboxId
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function addError(string $message, $cashboxId = null): void
	{
		self::addToLog($message, $cashboxId, static::TRACE_LEVEL_ERROR);
	}

	/**
	 * @param string $message
	 * @param null $cashboxId
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function addWarning(string $message, $cashboxId = null): void
	{
		self::addToLog($message, $cashboxId, static::TRACE_LEVEL_WARNING);
	}

	/**
	 * @param string $message
	 * @param null $cashboxId
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function addDebugInfo(string $message, $cashboxId = null): void
	{
		self::addToLog($message, $cashboxId, static::TRACE_LEVEL_DEBUG);
	}

	/**
	 * @param string $message
	 * @return bool
	 */
	private static function validateMessage(string $message): bool
	{
		return $message !== '';
	}

	/**
	 * @param int $messageLevel
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function checkMessageLevel(int $messageLevel): bool
	{
		return $messageLevel <= self::getLevel();
	}

	/**
	 * @param string $message
	 * @param $cashboxId
	 * @param $messageLevel
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function addToLog(string $message, $cashboxId, $messageLevel): void
	{
		if (self::checkMessageLevel($messageLevel) && self::validateMessage($message))
		{
			$data = [
				'CASHBOX_ID' => $cashboxId,
				'MESSAGE' => $message,
				'DATE_INSERT' => new DateTime()
			];

			CashboxErrLogTable::add($data);
		}
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function getLevel(): int
	{
		return (int)Option::get('sale', 'cashbox_log_level', static::TRACE_LEVEL_ERROR);
	}
}