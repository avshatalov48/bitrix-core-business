// @flow

import {StackedBarChart} from "../charts/stacked-bar-chart";
import {StoreStackedChart} from "./store-stacked-chart";
import {TColumn, TSeries} from "../types/chart";
import {Loc} from "main.core";
import {TStockStore} from "./store-stock-chart";

export type TSaleStore = {
	name: string,
	sum_shipped: number,
	sum_arrived: number,
	sold_percent: number,
};


export class StoreSaleChart extends StoreStackedChart<TSaleStore>
{
	getChartLabel(): string | null
	{
		return super.getChartLabel();
	}

	isCommonChart(): boolean
	{
		return super.isCommonChart();
	}

	getChartSeries(): Array<TSeries>
	{
		return [
			{
				id: 'sum_shipped',
				color: '#6DA3E6',
				title: Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_TITLE'),
				getPopupContent: (storeData: TSaleStore) => {
					return {
						title: Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_TITLE'),
						content: `
						<div class="stacked-bar-chart-popup-info-multiple">
							<div class="stacked-bar-chart-popup-info-item" style="display: block">
								<div class="stacked-bar-chart-popup-info-subtitle">${Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_SUM')}</div>
								<div class="stacked-bar-chart-popup-info-value-box">
									<div id="chart-popup-template-sum" class="stacked-bar-chart-popup-info-value">${this.formatByCurrency(storeData.sum_shipped)}</div>
								</div>
							</div>
							<div class="stacked-bar-chart-popup-info-item">
								<div class="stacked-bar-chart-popup-info-subtitle">${Loc.getMessage('STORE_SALE_CHART_SUM_SHIPPED_SERIES_POPUP_SOLD_PERCENT')}</div>
								<div class="stacked-bar-chart-popup-info-value-box">
								<div class="stacked-bar-chart-popup-info-value">${storeData.sold_percent}%</div>
								</div>
							</div>
						</div>
						`,
					}
				},
			},
			{
				id: 'sum_arrived',
				color: '#42659B',
				title: Loc.getMessage('STORE_SALE_CHART_SUM_ARRIVED_SERIES_TITLE'),
				getPopupContent: (storeData: TSaleStore) => {
					return {
						title: Loc.getMessage('STORE_SALE_CHART_SUM_ARRIVED_SERIES_POPUP_TITLE'),
						content: `
							<div class="stacked-bar-chart-popup-info-item" style="display: block">
								<div class="stacked-bar-chart-popup-info-subtitle">${Loc.getMessage('STORE_SALE_CHART_SUM_ARRIVED_SERIES_POPUP_SUM')}</div>
								<div class="stacked-bar-chart-popup-info-value-box">
									<div id="chart-popup-template-sum" class="stacked-bar-chart-popup-info-value">${this.formatByCurrency(storeData.sum_arrived)}</div>
								</div>
							</div>
						`,
					}
				},
			},
		];
	}

	getChartColumns(columns: Array<TSaleStore>): Array<TColumn>
	{
		const stores = [];
		for (const storeId in columns)
		{
			stores.push(columns[storeId]);
		}

		return stores;
	}
}
