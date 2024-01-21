<?php

use Bitrix\Catalog\Component\ReportStoreProfitList;
use Bitrix\Catalog\Config\State;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogReportStoreProfitGridComponent extends ReportStoreProfitList
{
	public function executeComponent()
	{
		if (!$this->checkErrors())
		{
			return;
		}

		if (!State::isProductBatchMethodSelected())
		{
			$this->includeComponentTemplate('configure');

			return;
		}

		$this->fillResult();

		$this->includeComponentTemplate();
	}

	protected function getGridColumns(): array
	{
		return [
			[
				'id' => 'TITLE',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_TITLE_COLUMN'),
				'sort' => false,
				'default' => true,
				'width' => 350,
				'resizeable' => false,
				'sticked' => true,
			],
			[
				'id' => 'STARTING_QUANTITY',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_STARTING_QUANTITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_STARTING_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'RECEIVED_QUANTITY',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_RECEIVED_QUANTITY_COLUMN_MSGVER_1'),
				'hint' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_RECEIVED_QUANTITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'TOTAL_SOLD',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_TOTAL_SOLD_COLUMN'),
				'hint' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_TOTAL_SOLD_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'TOTAL_COST_PRICE',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_TOTAL_COST_PRICE_COLUMN'),
				'hint' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_TOTAL_COST_PRICE_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 250,
			],
			[
				'id' => 'PROFIT',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_PROFIT_COLUMN'),
				'hint' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_PROFIT_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
			[
				'id' => 'PROFITABILITY',
				'name' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_PROFITABILITY_COLUMN'),
				'hint' => Loc::getMessage('STORE_PROFIT_REPORT_GRID_PROFITABILITY_COLUMN_HINT'),
				'sort' => false,
				'default' => true,
				'width' => 200,
			],
		];
	}

	protected function getReportProductGridComponentName(): string
	{
		return 'bitrix:catalog.report.store_profit.products.grid';
	}

	protected function getTotalFields(): array
	{
		return [
			'STARTING_QUANTITY',
			'RECEIVED_QUANTITY',
			'TOTAL_SOLD',
			'TOTAL_COST_PRICE',
			'PROFIT',
			'PROFITABILITY',
		];
	}

	protected function formatValue(string $fieldName, $value): ?string
	{
		if ($this->isMoneyField($fieldName))
		{
			return $this->prepareMoneyField((float)$value);
		}

		if ($this->isMeasureField($fieldName))
		{
			return $this->prepareMeasureField($value);
		}

		if ($fieldName === 'PROFITABILITY')
		{
			if (is_null($value))
			{
				return '-';
			}

			$value = (float)$value;

			return "$value%";
		}

		return parent::formatValue($fieldName, $value);
	}

	private function prepareMoneyField(float $fieldValue): ?string
	{
		static $baseCurrency = null;
		if (empty($baseCurrency) && Loader::includeModule('currency'))
		{
			$baseCurrency = CurrencyManager::getBaseCurrency();
		}

		if ($baseCurrency)
		{
			return \CCurrencyLang::CurrencyFormat($fieldValue, $baseCurrency);
		}

		return $fieldValue;
	}

	private function prepareMeasureField($fieldValue = null): ?string
	{
		if (!is_array($fieldValue) || empty($fieldValue))
		{
			$defaultMeasureId = $this->getDefaultMeasureId();

			return $defaultMeasureId ? $this->formatNumberWithMeasure(0, $defaultMeasureId) : '';
		}

		$result = '';
		foreach ($fieldValue as $measureId => $total)
		{
			$result .= $this->formatNumberWithMeasure((float)$total, (int)$measureId);
			$result .= '<br>';
		}

		return $result;
	}

	private function formatNumberWithMeasure(float $number, int $measureId): ?string
	{
		return Loc::getMessage(
			'STORE_PROFIT_REPORT_GRID_MEASURE_TEMPLATE',
			[
				'#NUMBER#' => $number,
				'#MEASURE_SYMBOL#' => $this->getMeasureSymbol($measureId),
			]
		);
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

	private function getDefaultMeasureId(): ?string
	{
		$defaultMeasure = \CCatalogMeasure::getDefaultMeasure(true) ?? [];

		return $defaultMeasure['ID'] ?? null;
	}

	private function isMeasureField(string $fieldName): bool
	{
		return in_array($fieldName, ['STARTING_QUANTITY', 'RECEIVED_QUANTITY'], true);
	}

	private function isMoneyField(string $fieldName): bool
	{
		return in_array($fieldName, ['TOTAL_SOLD', 'TOTAL_COST_PRICE', 'PROFIT'], true);
	}

	protected function getGridId(): string
	{
		return 'catalog_report_store_profit_grid';
	}
}
