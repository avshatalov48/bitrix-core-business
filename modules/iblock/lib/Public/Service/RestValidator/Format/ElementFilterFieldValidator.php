<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\Public\Service\RestValidator\Trait;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ORM\Entity;

class ElementFilterFieldValidator extends BaseFilterFieldValidator
{
	use Trait\OrmFilterTrait;
	use Trait\IblockFilterOperationTrait;

	protected function getEntity(): Entity
	{
		return ElementTable::getEntity();
	}

	protected function getProcessedScalarFields(): array
	{
		return [
			'IBLOCK_ID',
		];
	}
}
