<?php

namespace Bitrix\Catalog\Document\Type;

use Bitrix\Catalog\StoreDocumentTable;

class StoreDocumentDeductTable extends StoreDocumentSpecificTable
{
	public static function getType(): string
	{
		return StoreDocumentTable::TYPE_DEDUCT;
	}
}
