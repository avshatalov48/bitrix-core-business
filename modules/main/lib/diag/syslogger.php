<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Diag;

class SysLogger extends Logger
{
	protected $prefix;
	protected $flags;
	protected $facility;
	protected $connected = false;

	public function __construct(string $prefix = '', int $flags = LOG_ODELAY, int $facility = LOG_USER)
	{
		$this->prefix = $prefix;
		$this->flags = $flags;
		$this->facility = $facility;
	}

	protected function connect()
	{
		if (!$this->connected)
		{
			openlog($this->prefix, $this->flags, $this->facility);

			$this->connected = true;
		}
	}

	protected function logMessage(string $level, string $message)
	{
		$this->connect();

		syslog(static::$supportedLevels[$level], $message);
	}

	/**
	 * Converts syslog priority to LogLevel.
	 * @param int $priority
	 * @return string
	 */
	public static function priorityToLevel(int $priority)
	{
		static $levels = null;

		if ($levels === null)
		{
			$levels = array_flip(static::$supportedLevels);
		}

		return $levels[$priority] ?? \Psr\Log\LogLevel::WARNING;
	}
}
