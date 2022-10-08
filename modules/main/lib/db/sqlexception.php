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
	 * @param \Exception | null $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = "", $databaseMessage = "", \Exception $previous = null)
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
	 * @param \Exception | null $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = "", $databaseMessage = "", $query = "", \Exception $previous = null)
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
	 * @param \Exception | null $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = '', \Exception $previous = null)
	{
		parent::__construct($message, '', $previous);
	}
}
