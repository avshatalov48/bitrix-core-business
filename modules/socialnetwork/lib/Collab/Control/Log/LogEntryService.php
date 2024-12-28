<?php

namespace Bitrix\Socialnetwork\Collab\Control\Log;

use Bitrix\Socialnetwork\Collab\Internals\CollabLogTable;
use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;

class LogEntryService
{
	public function save(AbstractCollabLogEntry $logEntry): void
	{
		$fields = $logEntry->toArray();

		CollabLogTable::add($fields);
	}

	public function saveCollection(CollabLogEntryCollection $logEntryCollection): void
	{
		$rows = $logEntryCollection->toArray();

		if (empty($rows))
		{
			return;
		}

		CollabLogTable::addMulti($rows, true);
	}
}
