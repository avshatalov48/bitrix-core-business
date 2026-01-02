<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;

class AutoJoinToChat extends Base
{
	public function onBeforeAction(Event $event)
	{
		$chat = $this->getChat();
		if ($chat === null)
		{
			return null;
		}

		if (!$chat->canUserAutoJoin())
		{
			return null;
		}

		$chat->join(false);
	}

	private function getChat(): ?Chat
	{
		$chat = $this->getAction()->getArguments()['chat'] ?? null;

		if ($chat instanceof Chat)
		{
			return $chat;
		}

		$message = $this->getAction()->getArguments()['message'] ?? null;
		if ($message instanceof Message)
		{
			return $message->getChat();
		}

		return null;
	}
}
