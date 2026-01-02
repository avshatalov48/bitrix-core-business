<?php

declare(strict_types=1);

namespace Bitrix\Main\Repository\Exception;

use Bitrix\Main\Error;
use Bitrix\Main\SystemException;

class PersistenceException extends SystemException
{
	/**
	 * @param string $message
	 * @param \Throwable|null $previous
	 * @param Error[] $errors
	 */
	public function __construct(string $message = '', \Throwable $previous = null, private readonly array $errors = [])
	{
		parent::__construct($message, previous: $previous);
	}

	/**
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}
