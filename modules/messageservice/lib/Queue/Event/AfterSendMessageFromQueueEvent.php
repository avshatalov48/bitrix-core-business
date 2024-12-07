<?php

namespace Bitrix\MessageService\Queue\Event;

use Bitrix\Main\Event;
use Bitrix\MessageService\Sender\Result\SendMessage;

final class AfterSendMessageFromQueueEvent extends Event
{
	public const TYPE = 'OnAfterSendMessageFromQueue';

	public function __construct(array $messageFields, SendMessage $sendResult)
	{
		parent::__construct(
			'messageservice',
			self::TYPE,
			[
				'message' => $messageFields,
				'sendResult' => $sendResult,
			]
		);
	}

	public function sendAlias(string $aliasEventName): void
	{
		$event = new Event(
			$this->getModuleId(),
			$aliasEventName,
			$this->getParameters(),
		);
		$event->send();
	}
}
