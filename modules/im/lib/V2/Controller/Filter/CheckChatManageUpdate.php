<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatManageUpdate extends Base
{

	public function onBeforeAction(Event $event)
	{
		$arguments = $this->getAction()->getArguments();
		$arguments['rightsLevel'] = (new Converter(Converter::TO_UPPER))->process($arguments['rightsLevel'] ?? '');
		$this->getAction()->setArguments($arguments);
		$rightsLevel = $arguments['rightsLevel'];
		$actionName = $this->getAction()->getName();
		if (in_array($actionName, ['setManageUsers', 'setManageUI'], true))
		{
			if (in_array(
				$rightsLevel,
				[Chat::MANAGE_RIGHTS_ALL, Chat::MANAGE_RIGHTS_MANAGERS, Chat::MANAGE_RIGHTS_OWNER],
				true
			))
			{
				return null;
			}
		}

		if ($actionName === 'setManageSettings')
		{
			if (in_array(
				$rightsLevel,
				[Chat::MANAGE_RIGHTS_MANAGERS, Chat::MANAGE_RIGHTS_OWNER],
				true
			))
			{
				return null;
			}
		}

		$this->addError(new Error(
			Chat\ChatError::WRONG_PARAMETER
		));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}
}
