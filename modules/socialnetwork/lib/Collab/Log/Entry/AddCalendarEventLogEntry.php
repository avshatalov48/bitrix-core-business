<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class AddCalendarEventLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'ADD_CALENDAR_EVENT';
	}
}
