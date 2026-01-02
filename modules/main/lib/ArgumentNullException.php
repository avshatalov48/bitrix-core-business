<?php

namespace Bitrix\Main;

/**
 * Exception is thrown when "empty" value is passed to a function that does not accept it as a valid argument.
 */
class ArgumentNullException extends ArgumentException
{
	public function __construct($parameter, \Throwable $previous = null)
	{
		$message = "Argument '{$parameter}' is null or empty";
		parent::__construct($message, $parameter, $previous);
	}
}
