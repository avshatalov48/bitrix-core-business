<?php

namespace Bitrix\Catalog\Filter\DataProvider\EntitySettings;

use Bitrix\Catalog\Document\Type\StoreDocumentMovingTable;
use Bitrix\Main\Filter\EntitySettings;

class MovingDocumentSettings extends SpecificDocumentSettings
{
	protected static function getTableClass(): string
	{
		return StoreDocumentMovingTable::class;
	}
}
