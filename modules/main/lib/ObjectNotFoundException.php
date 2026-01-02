<?php

namespace Bitrix\Main;

/**
 * Exception is thrown when an object is not present.
 */
class ObjectNotFoundException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 510, '', 0, $previous);
	}
}
