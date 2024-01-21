<?php

namespace Bitrix\Catalog\Filter\DataProvider\EntitySettings;

use Bitrix\Catalog\Document\Type\StoreDocumentDeductTable;
use Bitrix\Main\Filter\EntitySettings;

class DeductDocumentSettings extends SpecificDocumentSettings
{
	protected static function getTableClass(): string
	{
		return StoreDocumentDeductTable::class;
	}
}
