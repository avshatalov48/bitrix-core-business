<?php

namespace Bitrix\Socialnetwork\Collab\Log\Entry;

use Bitrix\Socialnetwork\Collab\Log\AbstractCollabLogEntry;

class UpdateCollabLogEntry extends AbstractCollabLogEntry
{
	public const PERMISSION_FIELD_PREFIX = 'permission';

	static public function getEventType(): string
	{
		return 'UPDATE_COLLAB';
	}

	public function setFieldName(string $fieldName): self
	{
		return $this->setDataValue('fieldName', $fieldName);
	}

	public function setPreviousValue(mixed $value): self
	{
		return $this->setDataValue('previousValue', $value);
	}

	public function setCurrentValue(mixed $value): self
	{
		return $this->setDataValue('currentValue', $value);
	}
}
