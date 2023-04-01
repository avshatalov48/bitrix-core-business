<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Component\ReportStoreList;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogReportStoreSaleGridComponent extends ReportStoreList
{
	protected function getGridColumns(): array
	{
		return [
			[
				'id' => 'TITLE',
				'name' => Loc::getMessage('STORE_SALE_REPORT_GRID_TITLE_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 210,
			],
			[
				'id' => 'STARTING_QUANTITY',
				'name' => Loc::getMessage('STORE_SALE_REPORT_GRID_STARTING_QUANTITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_REPORT_GRID_STARTING_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'RECEIVED_QUANTITY',
				'name' => Loc::getMessage('STORE_SALE_REPORT_GRID_RECEIVED_QUANTITY_COLUMN_MSGVER_1'),
				'hint' => Loc::getMessage('STORE_SALE_REPORT_GRID_RECEIVED_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 140,
			],
			[
				'id' => 'AMOUNT_SOLD',
				'name' => Loc::getMessage('STORE_SALE_REPORT_GRID_AMOUNT_SOLD_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_REPORT_GRID_AMOUNT_SOLD_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 140,
			],
			[
				'id' => 'AMOUNT_SUM',
				'name' => Loc::getMessage('STORE_SALE_REPORT_GRID_AMOUNT_SUM_COLUMN'),
				'hint' => Loc::getMessage('STORE_SALE_REPORT_GRID_AMOUNT_SUM_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
		];
	}

	protected function getReportProductGridComponentName(): string
	{
		return 'bitrix:catalog.report.store_sale.products.grid';
	}

	protected function getTotalFields(): array
	{
		return [
			'STARTING_QUANTITY',
			'RECEIVED_QUANTITY',
			'AMOUNT_SUM',
			'AMOUNT_SOLD',
		];
	}

	protected function getGridId(): string
	{
		return 'catalog_report_store_sale_grid';
	}
}
