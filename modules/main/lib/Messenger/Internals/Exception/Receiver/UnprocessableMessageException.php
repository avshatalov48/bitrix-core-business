<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception\Receiver;

use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\Messenger\Internals\Exception\LogicException;

class UnprocessableMessageException extends LogicException
{
	private MessageInterface $messengerMessage;

	public function __construct(
		MessageInterface $messengerMessage,
		string $message = '',
		int $code = 0,
		?\Throwable $previous = null
	)
	{
		parent::__construct($message, $code, $previous);

		$this->messengerMessage = $messengerMessage;
	}

	public function getMessengerMessage(): MessageInterface
	{
		return $this->messengerMessage;
	}
}
