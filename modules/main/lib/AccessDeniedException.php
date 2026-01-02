<?php

namespace Bitrix\Main;

/**
 * Exception is thrown when access is denied
 */
class AccessDeniedException extends SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct(($message ?: 'Access denied.'), 403, '', 0, $previous);
	}
}
