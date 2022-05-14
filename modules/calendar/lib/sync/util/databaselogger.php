<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Internals\CalendarLogTable;

class DatabaseLogger extends \Bitrix\Main\Diag\Logger
{

	protected function logMessage(string $level, string $message)
	{
		CalendarLogTable::add([
			'MESSAGE' => $message,
		]);
	}
}