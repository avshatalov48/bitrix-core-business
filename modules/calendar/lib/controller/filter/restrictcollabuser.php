<?php

namespace Bitrix\Calendar\Controller\Filter;

use Bitrix\Calendar\Util;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class RestrictCollabUser extends Base
{
	public function onBeforeAction(Event $event)
	{
		$currentUser = CurrentUser::get();

		if (Util::isCollabUser($currentUser->getId()))
		{
			$this->addError(new Error('access denied'));

			return new EventResult(type: EventResult::ERROR, handler: $this);
		}

		return null;
	}
}
