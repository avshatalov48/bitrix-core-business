<?php

namespace Bitrix\Catalog\Integration\Report\Handler\StoreStock;

use \Bitrix\Catalog\Integration\Report\Handler\BaseHandler;
use Bitrix\Catalog\Integration\Report\Handler\Chart\ChartStoreInfo;
use Bitrix\Catalog\Integration\Report\Handler\Chart\StoreInfoCombiner\StoreWithProductsInfoCombiner;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Currency\CurrencyManager;

final class ChartHandler extends BaseHandler
{
	public function prepare()
	{
		return [
			'filter' => $this->getFormattedFilter(),
			'chart' => $this->getChartData(),
		];
	}

	public function getChartData(): array
	{
		$filterParams = $this->getFormattedFilter();

		$chartStoreInfo = new ChartStoreInfo(new StoreWithProductsInfoCombiner());
		$chartStoreInfo->accumulate('SUM_STORED', ...StoreStockSale::getReservedData($filterParams));

		if
		(
			isset($filterParams['STORES'])
			&& count($filterParams['STORES']) <= self::MAX_CHART_COLUMNS_COUNT
		)
		{
			$chartData = [
				'data' => $chartStoreInfo->getCalculatedColumns(),
				'storesInfo' => [
					'storeCount' => $chartStoreInfo->getStoresCount(),
					'cropStoreNamesList' => '',
				],
				'isCommonChart' => false,
			];
		}
		else
		{
			$combinedData = $chartStoreInfo->getCombinedCalculatedColumn(self::MAX_STORES_LIST_CHARS);
			$chartData = [
				'data' => [$combinedData],
				'storesInfo' => [
					'storeCount' => $chartStoreInfo->getStoresCount(),
					'cropStoreNamesList' => $combinedData['TITLE'],
				],
				'isCommonChart' => true,
			];
		}

		$chartData['currency'] = CurrencyManager::getBaseCurrency();
		$chartData['sliderUrl'] = static::formChartSliderUrl('bitrix:catalog.report.store_stock.salechart.stores.grid', $filterParams);

		return $chartData;
	}
}
