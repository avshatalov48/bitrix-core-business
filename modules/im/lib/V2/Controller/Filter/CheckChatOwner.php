<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class CheckChatOwner extends Base
{
	public function onBeforeAction(Event $event)
	{
		$currentUser = $this->getAction()->getCurrentUser();
		$currentUserId = (int)(isset($currentUser) ? $currentUser->getId() : null);

		if ($this->getAction()->getName() == 'setOwner')
		{

			$arguments = $this->getAction()->getArguments();
			/**
			 * @var $chat Chat
			 */
			$chat = $arguments['chat'];

			if (!isset($arguments['ownerId']))
			{
				$this->addError(new Error(
					'Parameter ownerId is required',
					Chat\ChatError::WRONG_PARAMETER
				));
				return new EventResult(EventResult::ERROR, null, null, $this);
			}

			if ($chat->getAuthorId() === $currentUserId)
			{
				return null;
			}

			$manageSettings = $chat->getManageSettings();
			if ($manageSettings === Chat::MANAGE_RIGHTS_MANAGERS)
			{
				$selfRelation = $chat->getSelfRelation();
				if ($selfRelation->getManager())
				{
					return null;
				}
			}
		}

		$this->addError(new Error(
			Chat\ChatError::ACCESS_DENIED
		));
		return new EventResult(EventResult::ERROR, null, null, $this);
	}
}
