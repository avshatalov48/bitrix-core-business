<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatAddParams extends Base
{
	public function onBeforeAction(Event $event)
	{
		$arguments = $this->getAction()->getArguments();
		if (!isset($arguments['fields']))
		{
			$this->addError(new Error(
				'Parameter fields is required',
				Chat\ChatError::WRONG_PARAMETER
			));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}
