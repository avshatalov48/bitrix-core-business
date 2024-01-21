<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

abstract class ReportStoreProfitList extends \CBitrixComponent
{
	abstract protected function getGridColumns(): array;

	abstract protected function getReportProductGridComponentName(): string;

	abstract protected function getTotalFields(): array;

	abstract protected function getGridId(): string;

	public function executeComponent()
	{
		if (!$this->checkErrors())
		{
			return;
		}

		$this->fillResult();

		$this->includeComponentTemplate();
	}

	protected function checkErrors(): bool
	{
		if (!Loader::includeModule('catalog') || !self::checkDocumentReadRights())
		{
			$this->arResult['ERROR_MESSAGES'][] = Loc::getMessage('CATALOG_REPORT_PROFIT_LIST_NO_READ_RIGHTS_ERROR');
			$this->includeComponentTemplate();

			return false;
		}

		return true;
	}

	protected function fillResult(): void
	{
		$this->arResult['GRID'] = $this->getGridData();
		$this->arResult['GRID_FILTER'] = $this->getGridFilter();
		$this->arResult['PRODUCT_LIST_SLIDER_URL'] = $this->getProductListComponentUrl();
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
			$result['ROWS'][] = $this->prepareTotalLinkRow();
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
			$column[$totalField] = $this->formatValue($totalField, $column['TOTALS'][$totalField]);
		}

		unset($column['TOTALS']);

		return $column;
	}

	private function prepareOverallTotalRow(array $overallData): array
	{
		$overallColumns = [];
		$overallColumns['TITLE'] = Loc::getMessage('CATALOG_REPORT_PROFIT_LIST_OVERALL_TOTAL');

		foreach ($this->getTotalFields() as $totalField)
		{
			$overallColumns[$totalField] = $this->formatValue($totalField, $overallData[$totalField]);
		}

		return [
			'id' => 'overallTotal',
			'data' => $overallData,
			'columns' => $overallColumns,
		];
	}

	private function prepareTotalLinkRow(): array
	{
		return [
			'id' => 'totalLink',
			'columns' => [
				'TITLE' => Loc::getMessage('CATALOG_REPORT_PROFIT_LIST_OPEN_SLIDER_ALL')
			],
		];
	}

	protected function formatValue(string $fieldName, $value): ?string
	{
		return (string)$value;
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
			$title = Loc::getMessage('CATALOG_REPORT_PROFIT_LIST_EMPTY_STORE_NAME');
		}

		return $title;
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
