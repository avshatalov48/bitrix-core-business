<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format;

use Bitrix\Catalog\StoreTable;
use Bitrix\Iblock;
use Bitrix\Main\ORM\Entity;

class StoreFieldValidator extends Iblock\Public\Service\RestValidator\Format\BaseFieldValidator
{
	protected function getEntity(): Entity
	{
		return StoreTable::getEntity();
	}
}
