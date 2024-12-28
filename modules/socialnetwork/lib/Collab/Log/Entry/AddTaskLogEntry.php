<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class AddTaskLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'ADD_TASK';
	}
}
