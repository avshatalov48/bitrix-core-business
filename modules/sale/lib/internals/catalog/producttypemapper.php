<?php

namespace Bitrix\Sale\Internals\Catalog;

use Bitrix\Sale;
use Bitrix\Catalog;

class ProductTypeMapper
{
	public static function getType(int $catalogType): ?int
	{
		if ($catalogType === Catalog\ProductTable::TYPE_SET)
		{
			return Sale\BasketItem::TYPE_SET;
		}

		if ($catalogType === Catalog\ProductTable::TYPE_SERVICE)
		{
			return Sale\BasketItem::TYPE_SERVICE;
		}

		return null;
	}

	public static function getCatalogType(?int $saleType): ?int
	{
		if ($saleType === Sale\BasketItem::TYPE_SET)
		{
			return Catalog\ProductTable::TYPE_SET;
		}

		if ($saleType === Sale\BasketItem::TYPE_SERVICE)
		{
			return Catalog\ProductTable::TYPE_SERVICE;
		}

		return null;
	}
}
