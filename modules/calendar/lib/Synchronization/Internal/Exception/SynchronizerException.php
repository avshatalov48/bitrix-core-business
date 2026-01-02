<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Exception;

class SynchronizerException extends Exception
{
	public function __construct(
		string $message,
		int $code = 0,
		\Throwable $previous = null,
		private bool $isRecoverable = true,
	)
	{
		parent::__construct($message, $code, $previous);

		if ($previous instanceof ApiException)
		{
			if ($previous->getCode() >= 500 && $previous->getCode() < 600)
			{
				$this->isRecoverable = true;
			}
		}
	}

	public function isRecoverable(): bool
	{
		return $this->isRecoverable;
	}
}
