<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class DeleteTaskLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'DELETE_TASK';
	}
}
