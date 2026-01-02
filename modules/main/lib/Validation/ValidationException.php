<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation;

use Bitrix\Main\Error;
use Bitrix\Main\SystemException;
use Throwable;

class ValidationException extends SystemException
{
	/**
	 * @param Error[] $validationErrors
	 * @param string $message
	 * @param Throwable|null $previous
	 */
	public function __construct(
		private readonly array $validationErrors,
		string $message = 'Data has validation errors',
		Throwable $previous = null
	)
	{
		parent::__construct($message, previous: $previous);
	}

	/**
	 * @return Error[]
	 */
	public function getValidationErrors(): array
	{
		return $this->validationErrors;
	}
}