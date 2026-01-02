<?php

namespace Bitrix\Im\V2\Chat\ExternalChat\Event;

use Bitrix\Im\V2\Chat\ExternalChat;
use Bitrix\Im\V2\Chat\ExternalChat\Event;

abstract class ChatEvent extends Event
{
	public function __construct(ExternalChat $chat, array $parameters = [])
	{
		$parameters['chat'] = $chat;

		parent::__construct($chat->getEntityType(), $parameters);
	}

	public function getChat(): ExternalChat
	{
		return $this->parameters['chat'];
	}
}