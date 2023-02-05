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

	public function logToDatabase(array $context)
	{
		$fields = [];
		if ($context['serviceName'])
		{
			$fields['TYPE'] = (string)$context['serviceName'];
			unset($context['serviceName']);
		}

		if ($context['userId'])
		{
			$fields['USER_ID'] = (int)$context['userId'];
			unset($context['userId']);
		}

		if ($context['loggerUuid'])
		{
			$fields['UUID'] = (string)$context['loggerUuid'];
			unset($context['loggerUuid']);
		}

		$fields['MESSAGE'] = var_export($context, true);

		CalendarLogTable::add($fields);
	}
}