<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format;

use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Iblock\Public\Service\RestValidator;
use Bitrix\Main\ORM\Entity;

class DocumentFilterFieldValidator extends RestValidator\Format\BaseFilterFieldValidator
{
	use RestValidator\Trait\OrmFieldTrait;
	use RestValidator\Trait\OrmFilterOperationTrait;

	protected function getEntity(): Entity
	{
		return StoreDocumentTable::getEntity();
	}
}
