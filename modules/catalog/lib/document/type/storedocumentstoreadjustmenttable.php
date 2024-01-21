<?php

namespace Bitrix\Catalog\Document\Type;

use Bitrix\Catalog\StoreDocumentTable;

class StoreDocumentStoreAdjustmentTable extends StoreDocumentSpecificTable
{
	public static function getType(): string
	{
		return StoreDocumentTable::TYPE_STORE_ADJUSTMENT;
	}
}
