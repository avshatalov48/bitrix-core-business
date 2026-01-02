<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\Public\Service\RestValidator\Trait;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Entity;

class PropertyScalarFilterValidator extends BaseFilterFieldValidator
{
	use Trait\IblockFilterOperationTrait;
	use Trait\OrmFieldTrait;

	protected function getEntity(): Entity
	{
		return PropertyTable::getEntity();
	}

	protected function getUnprocessedFields(): array
	{
		return [
			'SORT',
			'DEFAULT_VALUE',
			'ROW_COUNT',
			'COL_COUNT',
			'LIST_TYPE',
			'FILE_TYPE',
			'MULTIPLE_CNT',
			'WITH_DESCRIPTION',
			'USER_TYPE_SETTINGS',
			'HINT',
		];
	}

	protected function init(): void
	{
		$fields = $this->getProcessedFields();
		$this->setFields($fields);
		$this->setScalarFields($fields);
	}
}
