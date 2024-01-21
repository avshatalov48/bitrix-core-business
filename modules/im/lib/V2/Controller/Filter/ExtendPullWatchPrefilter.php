<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat\OpenChat;
use Bitrix\Im\V2\Chat\OpenLineChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;

class ExtendPullWatchPrefilter extends Base
{
	public function onBeforeAction(Event $event)
	{
		$chat = $this->getAction()->getArguments()['chat'] ?? null;

		if ($chat === null)
		{
			$message = $this->getAction()->getArguments()['message'] ?? null;
			if ($message instanceof Message)
			{
				$chat = $message->getChat();
			}
		}

		if ($chat instanceof OpenChat || $chat instanceof OpenLineChat)
		{
			if ($chat->getSelfRelation() === null)
			{
				$chat->extendPullWatch();
			}
		}

		return null;
	}
}