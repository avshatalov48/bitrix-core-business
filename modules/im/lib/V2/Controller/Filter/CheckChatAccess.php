<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatAccess extends Base
{
	public function onBeforeAction(Event $event)
	{
		if (!$this->hasAccess())
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function hasAccess(): bool
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof Message)
			{
				return $argument->getChat()->hasAccess();
			}
			if ($argument instanceof MessageCollection)
			{
				return $this->hasAccessToMessages($argument);
			}
			if ($argument instanceof Chat)
			{
				return $argument->hasAccess();
			}
		}

		return true;
	}

	private function hasAccessToMessages(MessageCollection $messages): bool
	{
		$commonChatId = $messages->getCommonChatId();

		if ($commonChatId !== null)
		{
			return Chat::getInstance($commonChatId)->hasAccess();
		}

		foreach ($messages as $message)
		{
			if (!$message->getChat()->hasAccess())
			{
				return false;
			}
		}

		return true;
	}
}