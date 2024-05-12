<?php

namespace Bitrix\Calendar\Internals\Log;

use Throwable;

class Logger
{
	private const DEFAULT_MARKER = 'DEBUG_CALENDAR';
	private const MODULE_ID = 'calendar';

	public function __construct(private string $marker = self::DEFAULT_MARKER)
	{
	}

	public function log(mixed $data, int $traceDepth = 6): void
	{
		if ($data instanceof Throwable)
		{
			$data = $data->getMessage();
		}
		elseif (!is_scalar($data))
		{
			$data = var_export($data, true);
		}

		$message = [$this->marker];
		$message[] = $data;
		$message = implode("\n", $message);

		AddMessage2Log($message, static::MODULE_ID, $traceDepth);
	}
}