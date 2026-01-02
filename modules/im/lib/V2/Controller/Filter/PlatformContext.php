<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\Bot;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;
use Bitrix\Rest\SessionAuth\Auth;
use CRestServer;

class PlatformContext extends Base
{
	public function onBeforeAction(Event $event)
	{
		$server = $this->getAction()->getArguments()['restServer'] ?? null;

		if ($server instanceof CRestServer && $server->getAuthType() !== Auth::AUTH_TYPE)
		{
			$context = $this->getAction()->getController()->getRequest()->getValues()['platformContext'] ?? null;

			if (is_string($context))
			{
				Bot::setPlatformContext($context);
			}
		}

		return null;
	}
}
