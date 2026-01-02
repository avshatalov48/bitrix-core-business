<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format;

use Bitrix\Catalog\StoreTable;
use Bitrix\Iblock\Public\Service\RestValidator;
use Bitrix\Main\ORM\Entity;

class StoreFilterFieldValidator extends RestValidator\Format\BaseFilterFieldValidator
{
	use RestValidator\Trait\OrmFieldTrait;
	use RestValidator\Trait\OrmFilterOperationTrait;

	protected function getEntity(): Entity
	{
		return StoreTable::getEntity();
	}
}
