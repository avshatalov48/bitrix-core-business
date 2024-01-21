<?php

namespace Bitrix\Catalog\Filter\DataProvider\EntitySettings;

use Bitrix\Catalog\Document\Type\StoreDocumentArrivalTable;
use Bitrix\Main\Filter\EntitySettings;

abstract class SpecificDocumentSettings extends EntitySettings
{
	public function getEntityTypeName()
	{
		return $this->getUserFieldEntityID();
	}

	abstract protected static function getTableClass(): string;

	public function getUserFieldEntityID()
	{
		return static::getTableClass()::getUfId();
	}
}
