<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Message\Delete\DisappearService;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckDisappearingDuration extends Base
{
	public function onBeforeAction(Event $event)
	{
		$arguments = $this->getAction()->getArguments();
		$hours = $arguments['hours'];
		if (is_numeric($hours) && (int)$hours > -1 && in_array($hours, DisappearService::TIME_WHITELIST))
		{
			return null;
		}

		$this->addError(new ChatError(ChatError::WRONG_DISAPPEARING_DURATION, 'Wrong disappearing duration'));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}
}
