// @flow

import {TSeries, TColumn, TChartProps} from "../types/chart";
import {StackedBarChart} from "../charts/stacked-bar-chart";
import {Extension, Tag, Loc} from "main.core";
import {CurrencyCore} from "currency.currency-core";
import './store-stacked-chart.css';


export type TStackedChartProps<S> = {
	id: string,
	detailSliderUrl: string | null,
	stores: Array<S>,
	isPopupEnabled: boolean,
	label: string | null,
	isCommonChart: boolean,
	currency: {
		id: string,
		symbol: string,
	} | null,
	onChartClick: ((column: TColumn, series: TSeries) => void) | null,
};

export class StoreStackedChart<StoreData>
{
	#chart: StackedBarChart;
	properties: TStackedChartProps<StoreData>;

	#currency: {
		id: string,
		symbol: string,
		format: Object,
	}

	constructor(props: TStackedChartProps<StoreData>)
	{
		if (new.target === StoreStackedChart)
		{
			throw new Error('StoreStackedChart cannot be directly instantiated');
		}

		this.properties = props;
		this.loadCurrency();
		this.#chart = new StackedBarChart({
			chartProps: this.getFormedChartProps(),
			isPopupEnabled: this.properties.isPopupEnabled,
			onChartClick: this.properties.onChartClick,
			onSeriesLabelLoad: this.onChartLabelLoadHandler.bind(this),
		});
	}

	loadCurrency(): void
	{
		if (this.properties.currency)
		{
			this.#currency = this.properties.currency;
		}
		else
		{
			const extensionSettingsCollection = Extension.getSettings('catalog.store-chart');
			this.#currency = {
				id: extensionSettingsCollection.get('currency'),
				symbol: extensionSettingsCollection.get('currencySymbol'),
				format: extensionSettingsCollection.get('currencyFormat'),
			};
		}

		CurrencyCore.setCurrencyFormat(this.#currency.id, this.#currency.format);
	}

	getCurrency(): {id: string, symbol: string}
	{
		return this.#currency;
	}

	formatByCurrency(value: number): string
	{
		return CurrencyCore.currencyFormat(value, this.getCurrency().id, true);
	}

	getFormedChartProps(): TChartProps
	{
		return {
			id: this.properties.id,
			label: this.getChartLabel(),
			isCommonChart: this.isCommonChart(),
			seriesList: this.getChartSeries(),
			columns: this.getChartColumns(this.properties.stores),
		};
	}

	getHelpdeskButton(code: string): HTMLElement
	{
		return Tag.render`
			<div onclick='top.BX.Helper.show("redirect=detail&code=${code}")' class="how-it-works-guide-link">${Loc.getMessage('STORE_CHART_HINT_TITLE')}</div>
		`;
	}

	getChartLabel(): string | null
	{
		return this.properties.label;
	}

	isCommonChart(): boolean
	{
		return this.properties.isCommonChart;
	}

	getChartSeries(): Array<TSeries>
	{
		return [];
	}

	getChartColumns(columns: Array<StoreData>): Array<TColumn>
	{
		return [];
	}

	onChartLabelLoadHandler(legendValues: Array): void
	{
		const currencyPostfix = ', ' + this.#currency.symbol;
		for (let i = 0;  i < legendValues.length; i++)
		{
			legendValues[i].fill = am4core.color("#000000");
			legendValues[i].html = legendValues[i].currentText + currencyPostfix;
		}
	}

	getChart(): StackedBarChart
	{
		return this.#chart;
	}
}
