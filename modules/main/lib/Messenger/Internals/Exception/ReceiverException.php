<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception;

class ReceiverException extends RuntimeException
{
	protected array $exceptions;

	public function __construct(
		string $message = '',
		int $code = 0,
		\Throwable $previous = null,
		array $exceptions = []
	)
	{
		parent::__construct($message, $code, $previous);

		$this->exceptions = $exceptions;
	}

	public function getList(): array
	{
		return $this->exceptions;
	}
}
