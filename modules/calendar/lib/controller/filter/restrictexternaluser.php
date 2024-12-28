<?php

namespace Bitrix\Calendar\Controller\Filter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

final class RestrictExternalUser extends Base
{
	public function onBeforeAction(Event $event)
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			$this->addError(new Error('Access denied'));

			return new EventResult(type: EventResult::ERROR, handler: $this);
		}

		return null;
	}
}
