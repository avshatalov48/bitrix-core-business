<?php

namespace Bitrix\Main;

/**
 * Exception is thrown when operation is not supported.
 */
class NotSupportedException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 150, '', 0, $previous);
	}
}
