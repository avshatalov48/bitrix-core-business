<?php

namespace Bitrix\Catalog\Integration\Report\Filter;


class StoreStockFilter extends BaseFilter
{
	protected static function getStoreFilterContext(): string
	{
		return 'report_store_stock_filter_stores';
	}

	protected static function getProductFilterContext(): string
	{
		return 'report_store_stock_filter_products';
	}
}
