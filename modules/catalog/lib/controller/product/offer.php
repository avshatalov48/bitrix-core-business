<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\ProductTable;

final class Offer extends Base
{
	protected const TYPE = ProductTable::TYPE_OFFER;

	protected function getAllowedProductTypes(): array
	{
		return [ProductTable::TYPE_OFFER, ProductTable::TYPE_FREE_OFFER];
	}
}