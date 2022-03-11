<?php

namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main\Type\Date;
use Bitrix\Rest\LogTable;
use Bitrix\Main\Diag\Logger;

/**
 * Class DataBaseLogger
 * @package Bitrix\Rest\Tools\Diagnostics
 */
class DataBaseLogger extends Logger
{
	protected function logMessage(string $level, string $message)
	{
		if (LoggerManager::getInstance()->isActive())
		{
			LogTable::add(
				[
					'CLIENT_ID' => Date::createFromTimestamp(time()),
					'PASSWORD_ID' => 0,
					'SCOPE' => '',
					'METHOD' => 'logger:' . $level,
					'REQUEST_METHOD' => '',
					'REQUEST_URI' => '',
					'REQUEST_AUTH' => '',
					'REQUEST_DATA' => '',
					'RESPONSE_STATUS' => \CHTTP::getLastStatus(),
					'RESPONSE_DATA' => $message,
				]
			);
		}
	}
}