<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Calendar\Core\Base\BaseException;

class LockException extends BaseException
{
	public function __construct($message = "", $code = 423, $file = "", $line = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $file, $line, $previous);
	}
}