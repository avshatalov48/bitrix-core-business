<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;

class AutoJoinToChat extends Base
{
	public function onBeforeAction(Event $event)
	{
		$autoJoinFlag = $this->getAction()->getBinder()->getSourcesParametersToMap()[0]['autoJoin'] ?? 'N';
		$autoJoin = $autoJoinFlag === 'Y';

		if (!$autoJoin)
		{
			return null;
		}

		$chat = $this->getAction()->getArguments()['chat'] ?? null;

		if ($chat === null)
		{
			$message = $this->getAction()->getArguments()['message'] ?? null;
			if ($message instanceof Message)
			{
				$chat = $message->getChat();
			}
		}

		if ($chat instanceof CommentChat)
		{
			if ($chat->getParentChat()->getSelfRelation() === null)
			{
				return null;
			}

			if ($chat->checkAccess()->isSuccess())
			{
				$chat->join(false);
			}
		}

		return null;
	}
}