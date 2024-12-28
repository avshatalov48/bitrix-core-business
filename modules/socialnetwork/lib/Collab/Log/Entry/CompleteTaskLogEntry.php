<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class CompleteTaskLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'COMPLETE_TASK';
	}
}
