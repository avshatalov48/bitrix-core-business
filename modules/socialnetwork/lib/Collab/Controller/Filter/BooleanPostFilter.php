<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Filter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;

class BooleanPostFilter extends Base
{
	public function onBeforeAction(Event $event)
	{
		$httpRequest = $this->getAction()->getController()->getRequest();
		if ($httpRequest && $httpRequest->isPost())
		{
			$httpRequest->addFilter(new BooleanFilter());
		}

		return null;
	}
}