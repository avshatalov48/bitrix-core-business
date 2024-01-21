<?php

namespace Bitrix\Catalog\Filter\DataProvider\EntitySettings;

use Bitrix\Catalog\Document\Type\StoreDocumentStoreAdjustmentTable;
use Bitrix\Main\Filter\EntitySettings;

class StoreAdjustmentDocumentSettings extends SpecificDocumentSettings
{
	protected static function getTableClass(): string
	{
		return StoreDocumentStoreAdjustmentTable::class;
	}
}
