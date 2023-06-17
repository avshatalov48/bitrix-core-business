<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class SameChatMessageFilter extends Base
{

	public function onBeforeAction(Event $event)
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof MessageCollection)
			{
				$commonChatId = $argument->getCommonChatId();

				if ($commonChatId === null)
				{
					$this->addError(new Message\MessageError(Message\MessageError::DIFFERENT_CHAT_ERROR));

					return new EventResult(EventResult::ERROR, null, null, $this);
				}

				$this->filterMessageByChatId($argument, $commonChatId);
			}
		}

		return null;
	}

	private function filterMessageByChatId(MessageCollection $messages, int $chatId): void
	{
		foreach ($messages as $message)
		{
			if ($message->getChatId() !== $chatId)
			{
				unset($messages[$message->getMessageId()]);
			}
		}
	}

}