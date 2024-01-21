<?php

namespace Bitrix\Catalog\Filter\DataProvider\EntitySettings;

use Bitrix\Catalog\Document\Type\StoreDocumentArrivalTable;
use Bitrix\Main\Filter\EntitySettings;

class ArrivalDocumentSettings extends SpecificDocumentSettings
{
	protected static function getTableClass(): string
	{
		return StoreDocumentArrivalTable::class;
	}
}
