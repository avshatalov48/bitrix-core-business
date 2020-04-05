<?php

namespace Bitrix\Sale\Exchange\Internals;


use Bitrix\Main\Config\Option;

final class LoggerDiag extends Logger
{
	const END_TIME_OPTION = "exchange_debug_end_time";

	public static function isOn()
	{
		return time() < Option::get("sale", static::END_TIME_OPTION, 0);
	}

	public static function enable($endTime = 0)
	{
		Option::set("sale", static::END_TIME_OPTION, intval($endTime));
	}

	public static function disable()
	{
		Option::delete("sale", array("name" => static::END_TIME_OPTION));
	}

	public static function getEndTime()
	{
		return intval(Option::get("sale", static::END_TIME_OPTION, 0));
	}

	/**
	 * @param array $params
	 * @return \Bitrix\Main\Entity\AddResult|null
	 */
	static public function log(array $params)
	{
		$params['MESSAGE'] = static::isOn()? $params['MESSAGE']:null;
		$result = parent::log($params);

		return $result;
	}
}