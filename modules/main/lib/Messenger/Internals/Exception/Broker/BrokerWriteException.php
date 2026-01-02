<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Broker;

use Bitrix\Main\Messenger\Entity\MessageBox;
use Bitrix\Main\Messenger\Internals\Exception\MessageBoxAwareExceptionInterface;
use Bitrix\Main\Messenger\Internals\Exception\MessageBoxAwareExceptionTrait;
use Bitrix\Main\Messenger\Internals\Exception\RuntimeException;

class BrokerWriteException extends RuntimeException implements MessageBoxAwareExceptionInterface
{
	use MessageBoxAwareExceptionTrait;

	public function __construct(
		MessageBox $messageBox,
		?\Throwable $previous = null,
		string $message = '',
		int $code = 0,
	)
	{
		parent::__construct($message, $code, $previous);

		$this->messageBox = $messageBox;

		$this->message = $this->buildActualMessage($this->messageBox);
	}

	protected function buildActualMessage(MessageBox $messageBox): string
	{
		return $this->message;
	}
}
