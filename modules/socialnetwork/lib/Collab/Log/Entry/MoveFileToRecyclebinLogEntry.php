<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class MoveFileToRecyclebinLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'MOVE_FILE_TO_RECYCLEBIN';
	}
}
