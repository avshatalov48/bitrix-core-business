<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Im\V2\ActionUuid;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Event;

class ActionUuidHandler extends Base
{
	public function onBeforeAction(Event $event)
	{
		$actionUuid = $this->getAction()->getBinder()->getSourcesParametersToMap()[0]['actionUuid'] ?? null;

		if (isset($actionUuid))
		{
			ActionUuid::getInstance()->setValue($actionUuid);
		}

		return null;
	}
}