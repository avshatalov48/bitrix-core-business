<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format;

use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Iblock;
use Bitrix\Main\ORM\Entity;

class DocumentFieldValidator extends Iblock\Public\Service\RestValidator\Format\BaseFieldValidator
{
	protected function getEntity(): Entity
	{
		return StoreDocumentTable::getEntity();
	}
}
