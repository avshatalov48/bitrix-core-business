<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Receiver;

use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Internals\Exception\MessageBoxAwareExceptionTrait;
use Bitrix\Main\Messenger\Internals\Exception\MessageBoxAwareExceptionInterface;
use Bitrix\Main\Messenger\Internals\Exception\RuntimeException;

class ProcessingException extends RuntimeException implements MessageBoxAwareExceptionInterface
{
	use MessageBoxAwareExceptionTrait;

	public function __construct(
		MessageBox $messageBox,
		string $message = '',
		int $code = 0,
		?\Throwable $previous = null
	)
	{
		parent::__construct($message, $code, $previous);

		$this->messageBox = $messageBox;
	}
}
