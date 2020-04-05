<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\PaySystemErrLogTable;

class ErrorLog
{
	const DEBUG_MODE = false;

	/**
	 * @param array $fields
	 * @throws \Exception
	 */
	public static function add(array $fields)
	{
		$fields = self::prepareParams($fields);

		if (self::DEBUG_MODE)
			self::addToFile($fields);
		else
			self::addToDb($fields);
	}

	/**
	 * @param array $fields
	 * @return bool|int
	 */
	private static function addToFile(array $fields)
	{
		return file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/payment_log.txt', self::convertArrayToString($fields), FILE_APPEND);
	}

	/**
	 * @param array $fields
	 * @return bool
	 */
	private static function addToDb(array $fields)
	{
		$fields['DATE_INSERT'] = new DateTime();
		$result = PaySystemErrLogTable::add($fields);

		return $result->isSuccess();
	}

	/**
	 * @param $fields
	 * @return string
	 */
	private static function convertArrayToString($fields)
	{
		$result = '';
		foreach ($fields as $key => $value)
			$result .= $key." : ".$value."\n";

		return $result."\n";
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	private static function prepareParams(array $fields)
	{
		$fields['DATE_INSERT'] = new DateTime();

		if (is_array($fields['MESSAGE']))
			$fields['MESSAGE'] = self::convertArrayToString($fields['MESSAGE']);

		return $fields;
	}
}