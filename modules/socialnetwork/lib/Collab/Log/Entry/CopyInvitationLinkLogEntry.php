<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class CopyInvitationLinkLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'COPY_INVITATION_LINK';
	}
}
