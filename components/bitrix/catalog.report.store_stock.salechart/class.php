<?php

use Bitrix\Catalog\Component\Report\StoreChart\StoreChart;
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

if (!Loader::includeModule('catalog'))
{
	echo Loc::getMessage('STORE_STOCK_SALECHART_NO_LOADED_CATALOG');
	die();
}

final class CatalogReportStoreStockSaleChartComponent extends StoreChart
{

	protected function getChartId(): string
	{
		return 'store-stock-chart';
	}

	protected function getInnerChartData(): array
	{
		return $this->arParams['RESULT']['data']['chart'];
	}

	protected function fetchAndBuildStoreColumnData(array $stores): array
	{
		return array_map(static function($store) {
			return [
				'sum_stored' => $store['SUM_STORED'],
				'name' => $store['TITLE'],
			];
		}, $stores);
	}

	protected function buildLinkToSliderDetails(string $linkContent): string
	{
		return
			"<a 
			class=\"stores-stock-chart-label-link\"
			onclick=\"BX.Catalog.Report.StoreStockChartManager.Instance.openDetailSlider()\"
		 >
		 {$linkContent}
		 </a>"
			;
	}

	protected function initializeAdditionalData(): void
	{
		/** @var Bitrix\Report\VisualConstructor\Entity\Widget $widget */
		$widget = $this->arParams['WIDGET'];

		$this->arResult['chartData']['widgetId'] = $widget->getGId();
		$this->arResult['chartData']['boardId'] = $widget->getBoardId();;
	}
}

