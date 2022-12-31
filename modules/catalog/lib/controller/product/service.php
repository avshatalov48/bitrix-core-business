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
}