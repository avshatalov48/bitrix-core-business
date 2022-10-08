<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Internals\CalendarLogTable;
use Bitrix\Main;
use Exception;

class DatabaseLogger extends Main\Diag\Logger
{

	/**
	 * @param string $level
	 * @param string $message
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function logMessage(string $level, string $message)
	{
		CalendarLogTable::add([
			'MESSAGE' => $message,
		]);
	}
}