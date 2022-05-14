<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogReportStoreStockGridComponent extends CBitrixComponent
{
	private const GRID_ID = 'catalog_report_store_stock_grid';

	public function executeComponent()
	{
		if (!self::checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('STORE_STOCK_REPORT_GRID_NO_READ_RIGHTS_ERROR');
			$this->includeComponentTemplate();
			return;
		}

		$this->arResult['GRID'] = $this->prepareResult();

		$this->includeComponentTemplate();
	}

	private function prepareResult()
	{
		$providerData = $this->arParams['RESULT']['data']['items'];
		$overallData = $this->arParams['RESULT']['data']['overall'];

		$result = [];

		$result['GRID_ID'] = self::GRID_ID;

		$result['COLUMNS'] = [
			[
				'id' => 'TITLE',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_TITLE_COLUMN'),
				'sort' => false,
				'default' => true,
			],
			[
				'id' => 'AMOUNT_SUM',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_AMOUNT_SUM_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 220,
			],
			[
				'id' => 'QUANTITY_RESERVED_SUM',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_QUANTITY_RESERVED_SUM_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 220,
			],
			[
				'id' => 'QUANTITY',
				'name' => Loc::getMessage('STORE_STOCK_REPORT_GRID_QUANTITY_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 220,
			],
		];

		$result['ROWS'] = [];

		if (!empty($providerData))
		{
			foreach($providerData as $storeId => $item)
			{
				$result['ROWS'][] = [
					'id' => $storeId,
					'data' => $item,
					'columns' => $this->prepareItemColumn($item),
				];
			}

			$result['ROWS'][] = $this->prepareOverallTotalRow($overallData);
		}

		$result['SHOW_PAGINATION'] = false;
		$result['SHOW_NAVIGATION_PANEL'] = false;
		$result['SHOW_PAGESIZE'] = false;
		$result['SHOW_ROW_CHECKBOXES'] = false;
		$result['SHOW_CHECK_ALL_CHECKBOXES'] = false;
		$result['SHOW_ACTION_PANEL'] = false;
		$result['HANDLE_RESPONSE_ERRORS'] = true;
		$result['SHOW_GRID_SETTINGS_MENU'] = false;

		return $result;
	}

	private function prepareItemColumn(array $item): array
	{
		$column = $item;

		$column['TITLE'] = $this->prepareTitleViewForColumn($column);

		foreach (['AMOUNT_SUM', 'QUANTITY_RESERVED_SUM', 'QUANTITY'] as $totalField)
		{
			$column[$totalField] = $this->prepareTotalField($column['TOTALS'], $totalField);
		}

		unset($column['TOTALS']);

		return $column;
	}

	private function prepareTotalField(array $totals, string $field): string
	{
		if (empty($totals))
		{
			return 0;
		}

		$result = '';
		foreach ($totals as $measureId => $total)
		{
			$result .= $this->formatNumberWithMeasure($total[$field], (int)$measureId);
			$result .= '<br>';
		}

		return $result;
	}

	private function formatNumberWithMeasure($number, int $measureId)
	{
		return Loc::getMessage(
			'STORE_STOCK_REPORT_MEASURE_TEMPLATE',
			[
				'#NUMBER#' => $number,
				'#MEASURE_SYMBOL#' => $this->getMeasureSymbol($measureId),
			]
		);
	}

	private function prepareTitleViewForColumn(array $column): string
	{
		if (!isset($column['TITLE'], $column['STORE_ID']))
		{
			return '';
		}

		if ($column['TITLE'])
		{
			$column['TITLE'] = htmlspecialcharsbx($column['TITLE']);
		}
		else
		{
			$column['TITLE'] = Loc::getMessage('STORE_STOCK_REPORT_EMPTY_STORE_NAME');
		}

		$storeId = (int)$column['STORE_ID'];

		$productGridPath = CComponentEngine::makeComponentPath('bitrix:catalog.report.store_stock.products.grid');
		$productGridPath = getLocalPath('components'.$productGridPath.'/slider.php');
		$productGridPath .= '?storeId=' . $storeId;
		$title = '<a class="store-report-link" onclick="BX.SidePanel.Instance.open(\'' . $productGridPath . '\', {cacheable: false});">' . $column['TITLE'] . '</a>';

		return $title;
	}

	private function prepareOverallTotalRow(array $overallData): array
	{
		$overallColumns = [];
		$overallColumns['TITLE'] = Loc::getMessage('STORE_STOCK_REPORT_GRID_OVERALL_TOTAL');

		foreach (['AMOUNT_SUM', 'QUANTITY_RESERVED_SUM', 'QUANTITY'] as $totalField)
		{
			$overallColumns[$totalField] = $this->prepareTotalField($overallData, $totalField);
		}

		return [
			'id' => 'overallTotal',
			'data' => $overallData,
			'columns' => $overallColumns,
		];
	}

	private function getMeasureSymbol(int $measureId): string
	{
		return htmlspecialcharsbx($this->getMeasures()[$measureId]['SYMBOL']);
	}

	private function getMeasures(): array
	{
		static $measures = [];

		if (empty($measures))
		{
			$measuresResult = \CCatalogMeasure::getList();
			while ($measure = $measuresResult->Fetch())
			{
				$measures[$measure['ID']] = $measure;
			}
		}

		return $measures;
	}

	private static function checkDocumentReadRights(): bool
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->canDoOperation('catalog_read');
	}
}
