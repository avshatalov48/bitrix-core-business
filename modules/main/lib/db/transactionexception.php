<?php

namespace Bitrix\Main\DB;

/**
 * Special exception for transactions handling.
 */
class TransactionException extends SqlException
{
	/**
	 * @param string $message Application message.
	 * @param \Throwable | null $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = '', \Throwable $previous = null)
	{
		parent::__construct($message, '', $previous);
	}
}
