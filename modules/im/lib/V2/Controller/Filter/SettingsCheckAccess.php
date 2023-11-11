<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Settings\SettingsError;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class SettingsCheckAccess extends Base
{
	public function onBeforeAction(Event $event)
	{
		$userId = (int)$this->getAction()->getArguments()['userId'];
		$currentUserId = User::getCurrent()->getId();
		if (
			$userId !== $currentUserId
			&& !User::getCurrent()->isAdmin()
		)
		{
			$this->addError(new SettingsError(SettingsError::ACCESS_DENIED));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}