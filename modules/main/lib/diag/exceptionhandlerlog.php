<?php

namespace Bitrix\Main\Diag;

use Psr\Log\LogLevel;

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
		static $types = [
			self::UNCAUGHT_EXCEPTION => 'UNCAUGHT_EXCEPTION',
			self::CAUGHT_EXCEPTION => 'CAUGHT_EXCEPTION',
			self::IGNORED_ERROR => 'IGNORED_ERROR',
			self::LOW_PRIORITY_ERROR => 'LOW_PRIORITY_ERROR',
			self::ASSERTION => 'ASSERTION',
			self::FATAL => 'FATAL',
		];

		if (isset($types[$logType]))
		{
			return $types[$logType];
		}

		return 'UNKNOWN';
	}

	public static function logTypeToLevel($logType)
	{
		static $types = [
			self::UNCAUGHT_EXCEPTION => LogLevel::ERROR,
			self::CAUGHT_EXCEPTION => LogLevel::ERROR,
			self::IGNORED_ERROR => LogLevel::ERROR,
			self::LOW_PRIORITY_ERROR => LogLevel::WARNING,
			self::ASSERTION => LogLevel::CRITICAL,
			self::FATAL => LogLevel::CRITICAL,
		];

		if (isset($types[$logType]))
		{
			return $types[$logType];
		}

		return LogLevel::INFO;
	}

	abstract public function write($exception, $logType);

	abstract public function initialize(array $options);
}
