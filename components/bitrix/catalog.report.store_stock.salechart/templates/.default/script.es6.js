// @flow

import "currency";
import {Loc, Reflection, Tag} from "main.core";
import {StoreStockChart} from "catalog.store-chart";

type TProps = {
	chartProps: Object,
	widgetId: string,
	boardId: string,
	detailSliderUrl: string | null,
};

class StoreStockChartManager
{
	#storeStockChart: BX.Catalog.StoreStockChart;
	detailSliderUrl: string | null;

	#boardId: string;
	#board: Object;
	#widgetId: string;
	#widget: Object;

	constructor(props: TProps)
	{
		if (props.chartProps.detailSliderUrl)
		{
			this.detailSliderUrl = props.chartProps.detailSliderUrl;
			props.chartProps.onChartClick = this.openDetailSlider.bind(this);
		}
		this.#widgetId = props.widgetId;
		this.#boardId = props.boardId;
		this.#board = BX.VisualConstructor.BoardRepository.getBoard(this.#boardId);
		this.#widget = this.#board.dashboard.getWidget(this.#widgetId);
		this.#storeStockChart = new BX.Catalog.StoreStockChart(props.chartProps);
		this.updateWidgetTitle();
	}

	openDetailSlider()
	{
		BX.SidePanel.Instance.open(this.detailSliderUrl, {
			cacheable: false,
			allowChangeTitle: false,
			allowChangeHistory: false,
		});
	}

	updateWidgetTitle()
	{

		BX.clean(this.#widget.layout.titleContainer);

		this.#widget.layout.titleContainer.appendChild(Tag.render`
		<div class="chart-header">
			<div>${this.#widget.config.title}</div>
			${this.#storeStockChart.getHelpdeskButton('15503856')}
		</div>
		`);

		this.#widget.layout.titleContainer.style.width = '100%';
	}
}

Reflection.namespace('BX.Catalog.Report').StoreStockChartManager = StoreStockChartManager;