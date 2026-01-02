<?php

declare(strict_types=1);

namespace Bitrix\Main\Command\Exception;

use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationException;
use Throwable;

class CommandValidationException extends ValidationException
{
	/**
	 * @param Error[] $validationErrors
	 * @param string $message
	 * @param Throwable|null $previous
	 */
	public function __construct(
		array $validationErrors,
		string $message = 'Command has validation errors',
		Throwable $previous = null
	)
	{
		parent::__construct($validationErrors, $message, $previous);
	}
}
