<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class CreateCollabLogEntry extends AbstractCollabLogEntry
{
	static public function getEventType(): string
	{
		return 'CREATE_COLLAB';
	}

	public function setDescription(string $description): self
	{
		return $this->setDataValue('description', $description);
	}
}
