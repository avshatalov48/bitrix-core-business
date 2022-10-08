<?php

namespace Bitrix\Calendar\Core\Handlers;

use Bitrix\Calendar\Core;

class UpdateMasterExdateHandler extends HandlerBase
{
	public function __invoke(Core\Event\Event $event)
	{
		return (new Core\Mappers\Event())->patch($event, ['EXDATE']);
	}
}
