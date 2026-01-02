<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format;

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\Public\Service\RestValidator as IblockRestValidator;
use Bitrix\Main\ORM\Entity;

class ProductFilterFieldValidator extends IblockRestValidator\Format\BaseFilterFieldValidator
{
	use IblockRestValidator\Trait\OrmFieldTrait;
	use IblockRestValidator\Trait\IblockFilterOperationTrait;

	protected function getEntity(): Entity
	{
		return ProductTable::getEntity();
	}
}
