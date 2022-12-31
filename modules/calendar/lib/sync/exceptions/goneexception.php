<?php

namespace Bitrix\Calendar\Sync\Exceptions;

class GoneException extends ApiException
{
	public function __construct($message = "", $code = 410, $file = "", $line = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $file, $line, $previous);
	}
}