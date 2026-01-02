<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;
use Bitrix\Im\V2\Message;

class AfterUpdateMessageEvent extends ChatEvent
{
	public function __construct(ExternalChat $chat, Message $message)
	{
		$parameters = ['message' => $message];

		parent::__construct($chat, $parameters);
	}

	protected function getActionName(): string
	{
		return 'AfterUpdateMessage';
	}

	public function getMessage(): Message
	{
		return $this->parameters['message'];
	}
}
