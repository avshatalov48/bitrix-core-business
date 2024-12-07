<?php
namespace Bitrix\Main\DB;

/**
 * Class ConnectionException used to indicate errors during database connection process.
 *
 * @see \Bitrix\Main\DB\ConnectionException::__construct
 * @package Bitrix\Main\DB
 */
class ConnectionException extends Exception
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
