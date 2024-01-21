<?php

namespace Bitrix\Catalog\Document\Type;

use Bitrix\Catalog\StoreDocumentTable;

class StoreDocumentUndoReserveTable extends StoreDocumentSpecificTable
{
	public static function getType(): string
	{
		return StoreDocumentTable::TYPE_UNDO_RESERVE;
	}
}
