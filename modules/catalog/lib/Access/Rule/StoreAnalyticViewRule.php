<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Access\Model\StoreDocumentElement;
use \Bitrix\Catalog\Access\Permission\PermissionDictionary;

class StoreAnalyticViewRule extends VariableRule
{
	protected function loadAvailableValues(): array
	{
		return array_column(PermissionDictionary::getStoreAnalyticVariables(), 'id');
	}
}
