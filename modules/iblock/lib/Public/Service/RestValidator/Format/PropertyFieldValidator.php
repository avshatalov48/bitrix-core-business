<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Entity;

class PropertyFieldValidator extends BaseFieldValidator
{
	protected function getEntity(): Entity
	{
		return PropertyTable::getEntity();
	}

	protected function getUnprocessedFields(): array
	{
		return [
			'DEFAULT_VALUE',
			'USER_TYPE_SETTINGS',
			'USER_TYPE_SETTINGS_LIST'
		];
	}
}
