<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Component\Report\StoreChart\StoreChart;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!Loader::includeModule('catalog'))
{
	echo Loc::getMessage('STORE_SALE_CHART_NO_LOADED_CATALOG');
	die();
}

class CatalogReportStoreSaleChartComponent extends StoreChart
{

	protected function getChartId(): string
	{
		return 'store-sale-chart';
	}

	protected function getInnerChartData(): array
	{
		return $this->arParams['RESULT']['data']['chart'];
	}

	protected function fetchAndBuildStoreColumnData(array $stores): array
	{
		return array_map(static function($store) {
			return [
				'sum_shipped' => $store['SUM_SHIPPED'],
				'sum_arrived' => $store['SUM_ARRIVED'],
				'sold_percent' => StoreStockSale::computeSoldPercent($store['SUM_SHIPPED'] ?? 0, $store['SUM_ARRIVED'] ?? 0),
				'name' => $store['TITLE'],
			];
		}, $stores);
	}

	protected function buildLinkToSliderDetails(string $linkContent): string
	{
		return
			"<a 
			class=\"stores-sale-chart-label-link\"
			onclick=\"BX.Catalog.Report.StoreSaleChartManager.Instance.openDetailSlider()\"
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
