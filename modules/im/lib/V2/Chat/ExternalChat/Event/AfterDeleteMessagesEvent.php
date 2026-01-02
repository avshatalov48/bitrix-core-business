<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;
use Bitrix\Im\V2\Message\Delete\DeletionMode;
use Bitrix\Im\V2\MessageCollection;

class AfterDeleteMessagesEvent extends ChatEvent
{
	public function __construct(ExternalChat $chat, MessageCollection $messages, DeletionMode $deletionMode)
	{
		$parameters = ['messages' => $messages, 'deletionMode' => $deletionMode];

		parent::__construct($chat, $parameters);
	}

	protected function getActionName(): string
	{
		return 'AfterDeleteMessages';
	}

	public function getMessages(): MessageCollection
	{
		return $this->parameters['messages'];
	}

	public function getDeletionMode(): DeletionMode
	{
		return $this->parameters['deletionMode'];
	}
}
