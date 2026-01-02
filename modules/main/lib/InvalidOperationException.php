<?php

namespace Bitrix\Main;

/**
 * Exception is thrown when a method call is invalid for current state of object.
 */
class InvalidOperationException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 160, '', 0, $previous);
	}
}
