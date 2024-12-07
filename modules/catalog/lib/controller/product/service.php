<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\ProductTable;

final class Service extends Base
{
	protected const TYPE = ProductTable::TYPE_SERVICE;

	protected function getAllowedProductTypes(): array
	{
		return [ProductTable::TYPE_SERVICE];
	}

	protected function prepareFieldsForAdd(array $fields): ?array
	{
		$fields = parent::prepareFieldsForAdd($fields);
		if ($fields === null)
		{
			return null;
		}

		$fields['TYPE'] = ProductTable::TYPE_SERVICE;

		return $fields;
	}
}
