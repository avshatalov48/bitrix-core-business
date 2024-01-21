<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

abstract class ReportStoreList extends \CBitrixComponent
{
	abstract protected function getGridColumns(): array;

	abstract protected function getReportProductGridComponentName(): string;

	abstract protected function getTotalFields(): array;

	abstract protected function getGridId(): string;

	public function executeComponent()
	{
		if (!Loader::includeModule('catalog') || !self::checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_REPORT_STORE_LIST_NO_READ_RIGHTS_ERROR');
			$this->includeComponentTemplate();

			return;
		}

		$this->arResult['GRID'] = $this->getGridData();
		$this->arResult['GRID_FILTER'] = $this->getGridFilter();
		$this->arResult['PRODUCT_LIST_SLIDER_URL'] = $this->getProductListComponentUrl();

		$this->includeComponentTemplate();
	}

	private function getGridData(): array
	{
		$result = [
			'GRID_ID' => $this->getGridId(),
			'COLUMNS' => $this->getGridColumns(),
			'ROWS' => [],
		];

		if (
			isset($this->arParams['RESULT']['data']['stub'])
			&& is_array($this->arParams['RESULT']['data']['stub'])
		)
		{
			$result['STUB'] = $this->arParams['RESULT']['data']['stub'];

			return $result;
		}

		$providerData = $this->arParams['RESULT']['data']['items'];
		$overallData = $this->arParams['RESULT']['data']['overall'];

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
		$result['ALLOW_STICKED_COLUMNS'] = true;

		return $result;
	}

	private function prepareItemColumn(array $item): array
	{
		$column = $item;

		$column['TITLE'] = $this->prepareTitleViewForColumn($column);
		if (isset($column['STORE_ID']))
		{
			$column['STORE_ID'] = (int)$column['STORE_ID'];
		}

		foreach ($this->getTotalFields() as $totalField)
		{
			$column[$totalField] = $this->prepareTotalField($column['TOTALS'], $totalField);
		}

		unset($column['TOTALS']);

		return $column;
	}

	private function prepareOverallTotalRow(array $overallData): array
	{
		$overallColumns = [];
		$overallColumns['TITLE'] = Loc::getMessage('CATALOG_REPORT_STORE_LIST_OVERALL_TOTAL');

		foreach ($this->getTotalFields() as $totalField)
		{
			$overallColumns[$totalField] = $this->prepareTotalField($overallData, $totalField);
		}

		return [
			'id' => 'overallTotal',
			'data' => $overallData,
			'columns' => $overallColumns,
		];
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
			'CATALOG_REPORT_STORE_LIST_MEASURE_TEMPLATE',
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
			$title = htmlspecialcharsbx($column['TITLE']);
		}
		else
		{
			$title = Loc::getMessage('CATALOG_REPORT_STORE_LIST_EMPTY_STORE_NAME');
		}

		return $title;
	}

	private function getMeasureSymbol(int $measureId): string
	{
		$measure = $this->getMeasures()[$measureId] ?? null;

		return $measure !== null ? htmlspecialcharsbx($measure['SYMBOL']) : '';
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

	private function getGridFilter(): array
	{
		return $this->arParams['RESULT']['data']['filter'];
	}

	protected function getProductListComponentUrl(): string
	{
		$productGridPath = \CComponentEngine::makeComponentPath($this->getReportProductGridComponentName());

		return getLocalPath('components' . $productGridPath . '/slider.php');
	}

	private static function checkDocumentReadRights(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}
}
