<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class ChangeCollabMemberRoleLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'CHANGE_COLLAB_MEMBER_ROLE';
	}

	public function setNewRole(string $newRole): self
	{
		return $this->setDataValue('newRole', $newRole);
	}

	public function setInitiator(int $initiator): self
	{
		return $this->setDataValue('initiator', $initiator);
	}
}
