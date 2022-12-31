// @flow

import {Loc, Event, Tag} from 'main.core';
import type {TChartProps, TColumn, TSeries} from "../types/chart";
import {Popup} from "main.popup";
import './stacked-bar-chart.css';

type TProps = {
	isPopupEnabled: boolean,
	chartProps: TChartProps,
	onChartClick?: ((column: TColumn, series: TSeries) => void),
	onSeriesLabelLoad?: ((chartLegendList: Array) => void),
};

export class StackedBarChart
{
	#chartId: string;
	#chart: am4charts.XYChart;
	#chartPopup: Popup | null;
	#isChartCommon: boolean;
	#onChartClick: Function | null;

	#onSeriesLabelLoadHandler: Function | null;
	#legendIsPrepared: boolean = false;

	#axes = {
		categoryAxis: am4charts.CategoryAxis,
		valueAxis: am4charts.ValueAxis,
	};

	#seriesList: Array<[TSeries, am4charts.ColumnSeries]> = [];
	#seriesLoadedLen: number = 0;

	#isPopupEnabled: boolean;

	constructor(props: TProps)
	{
		this.#isPopupEnabled = props?.isPopupEnabled;
		this.#chartPopup = null;
		this.#onChartClick = props.onChartClick;
		this.#onSeriesLabelLoadHandler = props?.onSeriesLabelLoad;
		this.#initializeChart(props.chartProps);
	}

	#initializeChart(chartProps: TChartProps): void
	{
		window.am4core.useTheme(am4themes_animated);

		this.#chartId = chartProps.id;
		this.#chart = am4core.create(this.#chartId, am4charts.XYChart);
		this.#chart.data = [];

		this.#chart.zoomOutButton.readerTitle = Loc.getMessage('STORE_CHART_ZOOMOUT_TITLE');

		this.#chart.legend = new am4charts.Legend();
		this.#chart.legend.position = 'bottom';

		this.#initAxes();
		this.#initColumnsData(chartProps.columns);
		this.#initSeries(chartProps.seriesList);

		if (chartProps.isCommonChart)
		{
			this.#axes.categoryAxis.renderer.labels.template.html = chartProps.label;
		}

		this.#chart.events.on("inited", () => {
			this.#onChartLoaded();
		});
	}

	#initColumnsTitle(): void
	{
		this.#chart.data.forEach((columnData) => {
			this.#axes.categoryAxis.dataItemsByCategory.getKey(columnData['id']).text = columnData['name'];
		});
	}

	#initAxes(): void
	{
		this.#axes.categoryAxis = this.#chart.xAxes.push(new am4charts.CategoryAxis());
		this.#axes.categoryAxis.dataFields.category = 'id';
		this.#axes.categoryAxis.renderer.grid.template.opacity = 0;

		this.#axes.valueAxis = this.#chart.yAxes.push(new am4charts.ValueAxis());
		this.#axes.valueAxis.min = 0;
		this.#axes.valueAxis.renderer.grid.template.opacity = 0;
		this.#axes.valueAxis.renderer.ticks.template.strokeOpacity = 0.5;
		this.#axes.valueAxis.renderer.ticks.template.length = 10;
		this.#axes.valueAxis.renderer.line.strokeOpacity = 0.5;
		this.#axes.valueAxis.renderer.baseGrid.disabled = true;
		this.#axes.valueAxis.renderer.minGridDistance = 40;
		this.#axes.valueAxis.calculateTotals = true;

		// some space needed for the total label
		this.#axes.categoryAxis.renderer.labels.template.marginRight = 40;
	}

	#initColumnsData(columnsData: Array<TColumn>): void
	{
		this.#chart.data = columnsData.map((columnData) => {
			columnData.id = columnData.id ? columnData.id : BX.util.getRandomString(4);
			return columnData;
		});
	}

	#initSeries(seriesList: Array<TSeries>): void
	{
		const emptySeries = StackedBarChart.createSeries({
			id: 5,
			title: 'empty',
			color: '#ffffff',
		});
		emptySeries.hiddenInLegend = true;
		emptySeries.maskBullets = false;
		this.#chart.series.push(emptySeries);

		seriesList.sort((firstSeries: TSeries, secondSeries: TSeries) => {
			if (!firstSeries.weight)
			{
				return -1;
			}

			if (firstSeries.weight < secondSeries.weight)
			{
				return 1;
			}
			else if (firstSeries.weight > secondSeries.weight)
			{
				return -1;
			}

			return 0;
		});

		seriesList.forEach((seriesData: TSeries) => {
			const seriesObject = this.#chart.series.push(StackedBarChart.createSeries(seriesData));
			this.#seriesList[seriesData.id] = [
				seriesData,
				seriesObject,
			];
			this.#seriesLoadedLen++;

			seriesObject.events.on("inited", (eventObject: Object) => {
				this.#onSeriesLoaded(eventObject.target, seriesData.id);
			}, this);
		});
	}

	#onSeriesLoaded(event, seriesId: string): void
	{
		if (this.#chart.legend.labels.values.length === this.#seriesLoadedLen)
		{
			this.#prepareChartLegend()
		}

		if (this.#isPopupEnabled)
		{
			this.#bindPopupEvents(event, seriesId);
		}
	}

	#prepareChartLegend(): void
	{
		if (this.#legendIsPrepared)
		{
			return;
		}
		else
		{
			this.#legendIsPrepared = true;
		}

		if (this.#onSeriesLabelLoadHandler instanceof Function)
		{
			this.#onSeriesLabelLoadHandler(this.#chart.legend.labels.values);
		}
	}

	#onChartLoaded(): void
	{
		if (!this.#isChartCommon)
		{
			this.#initColumnsTitle();
		}
		else
		{
			this.#axes.categoryAxis.dataItemsByCategory.getKey('0').text = '';
		}
	}

	#bindPopupEvents(seriesObject: Object, seriesId: string): void
	{
		for (let i = 0; i < seriesObject.columns.length; i++)
		{
			const [series,] = this.#seriesList[seriesId];
			const column = {
				columnObject: seriesObject.columns.getIndex(i),
				columnSeries: series,
				storedData: this.#chart.data[i],
			};

			Event.bind(column.columnObject.group.node, 'mouseover', this.#onStuckMouseOver.bind(this, column));
			Event.bind(column.columnObject.group.node, 'mouseout', this.#onStuckMouseOut.bind(this, column));

			if (this.#onChartClick)
			{
				column.columnObject.group.node.style.cursor = 'pointer';
				Event.bind(column.columnObject.group.node, 'click', () => this.#onChartClick(column.storedData, series));
			}
		}
	}

	#onStuckMouseOver(column: Object): void
	{
		let popupContent = column.columnSeries?.getPopupContent(column.storedData);
		if (popupContent && !this.#chartPopup)
		{
			let popupTitle = column.columnSeries.title;
			if (typeof popupContent === 'object')
			{
				popupTitle = popupContent.title ? popupContent.title : popupTitle;
				popupContent = popupContent.content;
			}

			this.#chartPopup = new Popup(
				`stacked-bar-chart-popup-${BX.util.getRandomString(4)}`,
				column.columnObject.group.node,
				{
					content: StackedBarChart.formPopupContent(column.columnSeries.color, popupTitle, popupContent),
					bindOptions: {
						position: "top"
					},
					offsetLeft: 30,
					offsetTop: -1,
					noAllPaddings: true,
					autoHide: false,
					draggable: {restrict: false},
					cacheable: false,
				}
			);
			this.#chartPopup.show();
		}
	}

	#onStuckMouseOut(column: Object): void
	{
		if (this.#chartPopup)
		{
			this.#chartPopup.close();
			this.#chartPopup.destroy();
			this.#chartPopup = null;
		}
	}

	static formPopupContent(color: string, title: string, innerContent: string): HTMLElement
	{
		return Tag.render`
			<div id="chart-popup-template" class="stacked-bar-chart-popup" style="border-color: ${color};">
				<div class="stacked-bar-chart-popup-head">
					<div id="chart-popup-template-title" class="stacked-bar-chart-popup-title">${title}</div>
				</div>
				<div class="stacked-bar-chart-popup-main">
					<div class="stacked-bar-chart-popup-info">
						${innerContent}
					</div>
				</div>
			</div>
		`;
	}

	static createSeries(seriesData: TSeries): am4charts.ColumnSeries
	{
		const series = new am4charts.ColumnSeries();
		series.dataFields.valueY = seriesData.id;
		series.dataFields.categoryX = 'id';
		series.stacked = true;
		series.name = seriesData.title;
		series.fill = am4core.color(seriesData.color);
		series.stroke = am4core.color('#ffffff');

		return series;
	}
}
