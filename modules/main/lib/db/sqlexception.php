<?php

namespace Bitrix\Main\DB;

/**
 * Exception is thrown when database returns an error.
 */
class SqlException extends Exception
{
	/**
	 * @param string $message Application message.
	 * @param string $databaseMessage Database reason.
	 * @param \Throwable | null $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = "", $databaseMessage = "", \Throwable $previous = null)
	{
		parent::__construct($message, $databaseMessage, $previous);
	}
}
