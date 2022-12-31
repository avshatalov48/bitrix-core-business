<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Access\Model\StoreDocumentElement;
use \Bitrix\Catalog\Access\Permission\PermissionDictionary;

class StoreViewRule extends VariableRule
{
	protected function loadAvailableValues(): array
	{
		return array_column(PermissionDictionary::getStoreVariables(), 'id');
	}

	protected function check($params): bool
	{
		$item = $params['item'] ?? null;
		if ($item instanceof StoreDocumentElement)
		{
			$params['value'] = $item->getStoreIds();
		}

		return parent::check($params);
	}
}
