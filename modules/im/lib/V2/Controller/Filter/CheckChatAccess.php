<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatAccess extends Base
{
	public function onBeforeAction(Event $event)
	{
		$checkResult = $this->checkAccess();
		if (!$checkResult->isSuccess())
		{
			$this->addError($checkResult->getErrors()[0] ?? new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function checkAccess(): Result
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof Message)
			{
				return $argument->checkAccess();
			}
			if ($argument instanceof MessageCollection)
			{
				return $this->checkAccessToMessages($argument);
			}
			if ($argument instanceof Chat)
			{
				return $argument->checkAccess();
			}
		}

		return new Result();
	}

	private function checkAccessToMessages(MessageCollection $messages): Result
	{
		foreach ($messages as $message)
		{
			$checkResult = $message->checkAccess();

			if (!$checkResult->isSuccess())
			{
				return $checkResult;
			}
		}

		return new Result();
	}
}