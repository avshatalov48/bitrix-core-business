<?php


namespace Bitrix\Sale\Exchange\Internals;


use Bitrix\Main\Config\Option;
use Bitrix\Main\NotImplementedException;

class LoggerDiagBase extends Logger
{
	static protected function getNameOptionEndTime()
	{
		throw new NotImplementedException('The method getNameOptionEndTime is not implemented.');
	}

	static protected function getNameOptionIntervalDayOption()
	{
		throw new NotImplementedException('The method getNameOptionIntervalDayOption is not implemented.');
	}

	public static function isOn()
	{
		return time() < Option::get("sale", static::getNameOptionEndTime(), 0);
	}

	public static function enable($endTime = 0)
	{
		Option::set("sale", static::getNameOptionEndTime(), intval($endTime));
	}

	public static function disable()
	{
		Option::delete("sale", array("name" => static::getNameOptionEndTime()));
	}

	public static function getEndTime()
	{
		return intval(Option::get("sale", static::getNameOptionEndTime(), 0));
	}

	/**
	 * @return int
	 */
	static public function getInterval()
	{
		$interval = Option::get('sale', static::getNameOptionIntervalDayOption(), 1);
		return intval($interval)>0 ? $interval:1;
	}
}