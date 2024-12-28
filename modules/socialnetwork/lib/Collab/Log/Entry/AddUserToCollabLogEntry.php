<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class AddUserToCollabLogEntry extends AbstractCollabLogEntry
{
	public static function getEventType(): string
	{
		return 'ADD_USER_TO_COLLAB';
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
