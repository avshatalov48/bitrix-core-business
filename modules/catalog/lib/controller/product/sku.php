<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\ProductTable;

final class Sku extends Base
{
	protected const TYPE = ProductTable::TYPE_SKU;
	protected const LIST = 'UNITS';

	protected function getServiceListName(): string
	{
		return self::LIST;
	}

	protected function getAllowedProductTypes(): array
	{
		return [ProductTable::TYPE_SKU, ProductTable::TYPE_EMPTY_SKU];
	}

	public function addAction($fields): ?array
	{
		$fields['TYPE'] = ProductTable::TYPE_EMPTY_SKU;
		$result = parent::addAction($fields);

		return $this->fillKeyResponse($result);
	}
}