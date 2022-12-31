<?php

namespace Bitrix\Catalog\Access\Rule;

use \Bitrix\Catalog\Access\Permission\PermissionDictionary;

class DealProductReserveRule extends VariableRule
{
	protected function loadAvailableValues(): array
	{
		return array_column(PermissionDictionary::getDealCategoryVariables(), 'id');
	}
}