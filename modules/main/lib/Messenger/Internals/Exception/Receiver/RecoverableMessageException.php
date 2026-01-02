<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Receiver;

use Bitrix\Main\Messenger\Internals\Exception\RuntimeException;

/**
 * If something goes wrong while handling a message but the message should be retried anyway,
 * a handler can throw this exception
 */
class RecoverableMessageException extends RuntimeException
{
	public function __construct(
		string $message = '',
		int $code = 0,
		?\Throwable $previous = null,
		private readonly ?int $retryDelay = null
	)
	{
		parent::__construct($message, $code, $previous);
	}

	public function getRetryDelay(): ?int
	{
		return $this->retryDelay;
	}
}
