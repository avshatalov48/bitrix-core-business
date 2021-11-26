<?php

namespace Bitrix\Main\Diag;

abstract class ExceptionHandlerLog
{
	const UNCAUGHT_EXCEPTION = 0;
	const CAUGHT_EXCEPTION = 1;
	const IGNORED_ERROR = 2;
	const LOW_PRIORITY_ERROR = 3;
	const ASSERTION = 4;
	const FATAL = 5;

	public static function logTypeToString($logType)
	{
		switch ($logType)
		{
			case 0:
				return 'UNCAUGHT_EXCEPTION';
			case 1:
				return 'CAUGHT_EXCEPTION';
			case 2:
				return 'IGNORED_ERROR';
			case 3:
				return 'LOW_PRIORITY_ERROR';
			case 4:
				return 'ASSERTION';
			case 5:
				return 'FATAL';
			default:
				return 'UNKNOWN';
		}
	}

	abstract public function write($exception, $logType);

	abstract public function initialize(array $options);
}
