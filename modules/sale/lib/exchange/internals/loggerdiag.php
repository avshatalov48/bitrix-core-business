<?php

namespace Bitrix\Sale\Exchange\Internals;


class LoggerDiag extends LoggerDiagBase
{
	static protected function getNameOptionEndTime()
	{
		return "exchange_debug_end_time";
	}

	static protected function getNameOptionIntervalDayOption()
	{
		return "SALE_EXCHANGE_DEBUG_INTERVAL_DAY";
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