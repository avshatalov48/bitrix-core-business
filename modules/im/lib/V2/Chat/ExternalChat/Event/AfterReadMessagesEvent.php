<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;
use Bitrix\Im\V2\MessageCollection;

class AfterReadMessagesEvent extends ChatEvent
{
	public function __construct(ExternalChat $chat, MessageCollection $messages, int $readerId)
	{
		$parameters = ['messages' => $messages, 'readerId' => $readerId];

		parent::__construct($chat, $parameters);
	}

	protected function getActionName(): string
	{
		return 'AfterReadMessages';
	}

	public function getMessages(): MessageCollection
	{
		return $this->parameters['messages'];
	}

	public function getReaderId(): int
	{
		return $this->parameters['readerId'];
	}
}
