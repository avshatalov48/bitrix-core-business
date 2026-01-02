<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\PropertyTable;

class SimpleNoFilePropertyValueValidator extends SimplePropertyValueValidator
{
	protected function getPropertyFilter(int $iblockId): array
	{
		$filter = parent::getPropertyFilter($iblockId);
		$filter['!=PROPERTY_TYPE'] = PropertyTable::TYPE_FILE;

		return $filter;
	}
}
