<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatCanPost extends Base
{
	public function onBeforeAction(Event $event)
	{
		$arguments = $this->getAction()->getArguments();
		$arguments['rightsLevel'] = (new Converter(Converter::TO_UPPER))->process($arguments['rightsLevel'] ?? '');
		$this->getAction()->setArguments($arguments);
		if (in_array(
			$arguments['rightsLevel'],
			[Chat::MANAGE_RIGHTS_NONE, Chat::MANAGE_RIGHTS_MEMBER, Chat::MANAGE_RIGHTS_MANAGERS, Chat::MANAGE_RIGHTS_OWNER],
			true
		))
		{
			return null;
		}

		$this->addError(new ChatError(
			ChatError::WRONG_PARAMETER
		));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}
}
