<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\Public\Service\RestValidator\Trait;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Entity;

class PropertyFilterFieldValidator extends BaseFilterFieldValidator
{
	use Trait\OrmFilterTrait;
	use Trait\OrmFilterOperationTrait;

	protected function getEntity(): Entity
	{
		return PropertyTable::getEntity();
	}
}
