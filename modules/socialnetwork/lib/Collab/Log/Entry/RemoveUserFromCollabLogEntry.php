<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class RemoveUserFromCollabLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'REMOVE_USER_FROM_COLLAB';
	}

	public function setRole(string $role): self
	{
		return $this->setDataValue('role', $role);
	}

	public function setInitiator(int $initiator): self
	{
		return $this->setDataValue('initiator', $initiator);
	}
}
