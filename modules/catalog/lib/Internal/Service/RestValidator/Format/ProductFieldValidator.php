<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format;

use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock;
use Bitrix\Main\ORM\Entity;

class ProductFieldValidator extends Iblock\Public\Service\RestValidator\Format\BaseFieldValidator
{
	protected function getEntity(): Entity
	{
		return ProductTable::getEntity();
	}
}
