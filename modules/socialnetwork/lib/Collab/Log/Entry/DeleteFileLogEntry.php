<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;
use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class DeleteFileLogEntry extends AbstractCollabLogEntry
{
	private int $fileId;

	static public function getEventType(): string
	{
		return 'DELETE_FILE';
	}

	public function setFileId(int $fileId): static
	{
		$this->fileId = $fileId;

		return $this;
	}

	public function toArray(): array
	{
		$result = parent::toArray();

		$result['ENTITY_TYPE'] = EntityType::File->value;
		$result['ENTITY_ID'] = $this->fileId;

		return $result;
	}
}
