<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\PaySystemErrLogTable;

/**
 * Class Logger
 * @package Bitrix\Sale\PaySystem
 */
class Logger
{
	const E_ALL = 0;
	const E_ERROR = 1;

	/**
	 * @param $error
	 * @throws ArgumentTypeException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function addError($error)
	{
		if (!is_string($error))
		{
			throw new ArgumentTypeException('$error', 'string');
		}

		if ($error === '')
		{
			return;
		}

		if (self::getLevel() > static::E_ERROR)
		{
			return;
		}

		PaySystemErrLogTable::add([
			'DATE_INSERT' => new DateTime(),
			'MESSAGE' => $error,
		]);
	}

	/**
	 * @param $debugInfo
	 * @throws ArgumentTypeException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function addDebugInfo($debugInfo)
	{
		if (!is_string($debugInfo))
		{
			throw new ArgumentTypeException('$debugInfo', 'string');
		}

		if (self::getLevel() !== static::E_ALL)
		{
			return;
		}

		PaySystemErrLogTable::add([
			'DATE_INSERT' => new DateTime(),
			'MESSAGE' => $debugInfo,
		]);
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function getLevel()
	{
		return (int)Option::get('sale', 'pay_system_log_level', static::E_ERROR);
	}

	/**
	 * @param array $fields
	 * @throws \Exception
	 *
	 * @deprecated Use \Bitrix\Sale\PaySystem\Logger::addError instead
	 */
	public static function add(array $fields)
	{
		trigger_error(
			'Method will be deleted in one of the future releases. Use \Bitrix\Sale\PaySystem\Logger::addError instead',
			E_USER_WARNING
		);

		if (isset($fields['ACTION']))
		{
			unset($fields['ACTION']);
		}

		if (isset($fields['MESSAGE'])
			&& is_array($fields['MESSAGE'])
		)
		{
			$fields['MESSAGE'] = implode("\n", $fields['MESSAGE']);
		}

		$fields['DATE_INSERT'] = new DateTime();

		PaySystemErrLogTable::add($fields);
	}
}