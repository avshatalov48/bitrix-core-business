<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Component\ReportStoreList;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogReportStoreStockGridComponent extends ReportStoreList
{
	protected function getGridColumns(): array
	{
		return [
			[
				'id' => 'TITLE',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_TITLE_COLUMN'),
				'sort' => false,
				'default' => true,
			],
			[
				'id' => 'AMOUNT_SUM',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_AMOUNT_SUM_COLUMN'),
				'hint' => Loc::getMessage('STORE_STOCK_REPORT_GRID_AMOUNT_SUM_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'QUANTITY_RESERVED_SUM',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_QUANTITY_RESERVED_SUM_COLUMN'),
				'hint' => Loc::getMessage('STORE_STOCK_REPORT_GRID_QUANTITY_RESERVED_SUM_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'QUANTITY',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_QUANTITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_STOCK_REPORT_GRID_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
		];
	}

	protected function getReportProductGridComponentName(): string
	{
		return 'bitrix:catalog.report.store_stock.products.grid';
	}

	protected function getTotalFields(): array
	{
		return [
			'AMOUNT_SUM',
			'QUANTITY_RESERVED_SUM',
			'QUANTITY',
		];
	}

	protected function getGridId(): string
	{
		return 'catalog_report_store_stock_grid';
	}
}
