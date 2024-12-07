<?php

namespace Bitrix\MessageService\Queue\Event\Result;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\EventResult;
use Bitrix\MessageService\Sender\Result\SendMessage;

final class BeforeSendMessageFromQueueEventResult extends EventResult
{
	public ?SendMessage $sendMessage;
	public ?ErrorCollection $errors;

	private function __construct()
	{
		parent::__construct(self::ERROR);
	}

	public static function createWithSendMessage(SendMessage $message): self
	{
		$self = new static();
		$self->sendMessage = $message;

		return $self;
	}

	public static function createWithErrors(Error ...$errors): self
	{
		$self = new static();
		$self->errors = new ErrorCollection($errors);

		return $self;
	}
}
