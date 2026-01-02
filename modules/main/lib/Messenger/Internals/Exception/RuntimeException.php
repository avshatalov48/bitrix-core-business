<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception;

use Bitrix\Main\SystemException;

class RuntimeException extends SystemException implements ExceptionInterface
{
	public function __construct($message = '', $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, previous: $previous);
	}
}
