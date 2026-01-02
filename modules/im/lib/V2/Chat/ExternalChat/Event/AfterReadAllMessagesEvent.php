<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;

class AfterReadAllMessagesEvent extends ChatEvent
{
	public function __construct(ExternalChat $chat, int $readerId)
	{
		$parameters = ['readerId' => $readerId];

		parent::__construct($chat, $parameters);
	}

	protected function getActionName(): string
	{
		return 'AfterReadAllMessages';
	}

	public function getReaderId(): int
	{
		return $this->parameters['readerId'];
	}
}
