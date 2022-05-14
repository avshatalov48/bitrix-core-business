<?php

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

final class CatalogReportStoreStockSaleChartComponent
	extends CBitrixComponent
	implements Errorable
{
	use ErrorableImplementation;

	public function executeComponent()
	{
		if ($this->checkModules())
		{
			$this->prepareWidgetData();
			$this->initializeChart();
			$this->includeComponentTemplate();
		}

		if ($this->hasErrors())
		{
			$this->showErrors();
		}

	}

	private function prepareWidgetData()
	{
		/** @var Bitrix\Report\VisualConstructor\Entity\Widget $widget */
		$widget = $this->arParams['WIDGET'];

		$this->arResult['widgetId'] = $widget->getGId();
		$this->arResult['boardId'] = $widget->getBoardId();
	}

	private function checkModules(): bool
	{
		$validateResult = true;

		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new Error('Module "catalog" is not installed.');
			$validateResult = false;
		}

		if (!Loader::includeModule('sale'))
		{
			$this->errorCollection[] = new Error('Module "sale" is not installed.');
			$validateResult = false;
		}

		if (!Loader::includeModule('currency'))
		{
			$this->errorCollection[] = new Error('Module "sale" is not installed.');
			$validateResult = false;
		}


		return $validateResult;
	}

	private function initializeChart(): void
	{
		$this->arResult['chartData'] = $this->arParams['RESULT']['data']['chart'];

		$this->arResult['chartData']['enablePopup'] = true;

		if ($this->arResult['chartData']['isOneColumn'])
		{
			if ((int)$this->arResult['chartData']['storesInfo']['storeCount'] > 0)
			{
				$this->arResult['chartData']['chartLabel'] = $this->formChartLabel($this->arResult['chartData']);
			}
			else
			{
				$this->arResult['chartData']['chartLabel'] = '';
				$this->arResult['chartData']['enablePopup'] = false;
			}
		}
		$currency = $this->arResult['chartData']['currency'];
		$this->arResult['chartData']['currencySymbol'] = $this->getCurrencySymbol($currency);
	}

	private function formChartLabel(array $chartData): string
	{
		$storesInfo = $chartData['storesInfo'];
		$storesList = htmlspecialcharsbx($storesInfo['cropStoreNamesList']);

		$totalLinkContent = Loc::getMessage(
			'STORE_STOCK_CHART_STORES_TOTAL',
			['#TOTAL_NUMBER#' => $storesInfo['storeCount']]
		);

		$totalLink = $totalLinkContent;
		if (isset($chartData['sliderUrl']))
		{
			$totalLink =
				"<a 
				class=\"stores-stock-chart-label-link\"
				onclick=\"BX.Catalog.Report.StoreStock.StoreStockSaleChart.Instance.openStoreStockChartGridSlider()\"
			 >
			 {$totalLinkContent}
			 </a>"
			;
		}

		return Loc::getMessage(
			'STORE_STOCK_CHART_STORES_LIST_TEMPLATE',
			[
				'#STORES_LIST#' => $storesList,
				'#STORES_TOTAL_LINK#' => $totalLink,
			]
		);
	}

	private function getCurrencySymbol(string $currency): string
	{
		if (CurrencyManager::isCurrencyExist($currency))
		{
			return CurrencyManager::getSymbolList()[$currency];
		}
		else
		{
			$this->errorCollection[] = new Error("Currency {$currency} is not exist");
		}

		return '';
	}

	/**
	 * Show all errors from errorCollection
	 */
	protected function showErrors(): void
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
	}
}
