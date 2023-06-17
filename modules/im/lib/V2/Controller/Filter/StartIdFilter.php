<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class StartIdFilter extends Base
{
	public function onBeforeAction(Event $event)
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof Message)
			{
				if (!$argument->getId())
				{
					return null;
				}

				if ($argument->getId() < $argument->getChat()->getStartId())
				{
					$this->addError(new Message\MessageError(Message\MessageError::MESSAGE_ACCESS_ERROR));

					return new EventResult(EventResult::ERROR, null, null, $this);
				}
			}

			if ($argument instanceof MessageCollection)
			{
				$messages = $argument;
				$this->filterMessagesByStartId($messages);
				if ($messages->count() === 0)
				{
					$this->addError(new Message\MessageError(Message\MessageError::MESSAGE_ACCESS_ERROR));

					return new EventResult(EventResult::ERROR, null, null, $this);
				}
			}
		}

		return null;
	}

	private function filterMessagesByStartId(MessageCollection $messages): void
	{
		foreach ($messages as $message)
		{
			if ($message->getMessageId() && $message->getMessageId() < $message->getChat()->getStartId())
			{
				unset($messages[$message->getMessageId()]);
			}
		}
	}
}