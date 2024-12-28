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

/**
 * Exception is thrown when database returns an error on query execution.
 */
class SqlQueryException extends SqlException
{
	/** @var string */
	protected $query = "";

	/**
	 * @param string $message Application message.
	 * @param string $databaseMessage Database reason.
	 * @param string $query Sql query text.
	 * @param \Throwable | null $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = "", $databaseMessage = "", $query = "", \Throwable $previous = null)
	{
		parent::__construct($message, $databaseMessage, $previous);
		$this->query = $query;
	}

	/**
	 * Returns text of the sql query.
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}
}

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

/**
 * Exception is thrown when database returns the duplicate entry error on query execution.
 */
class DuplicateEntryException extends SqlQueryException
{
}
