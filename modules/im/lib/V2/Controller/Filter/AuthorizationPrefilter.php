<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;

class AuthorizationPrefilter extends Base
{
	public function onBeforeAction(Event $event)
	{
		global $USER;
		if(!$USER->IsAuthorized())
		{
			$USER->LoginByCookies();
		}
	}
}