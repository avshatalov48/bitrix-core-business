// @flow

import "currency";
import {Reflection, Tag} from "main.core";

type TProps = {
	chartProps: Object,
	widgetId: string,
	boardId: string,
	detailSliderUrl: string | null,
};

class StoreSaleChartManager
{
	#storeSaleChart: BX.Catalog.StoreSaleChart;
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
		this.#storeSaleChart = new BX.Catalog.StoreSaleChart(props.chartProps);
		this.updateWidgetTitle();
	}

	updateWidgetTitle()
	{

		BX.clean(this.#widget.layout.titleContainer);

		this.#widget.layout.titleContainer.appendChild(Tag.render`
		<div class="chart-header">
			<div>${this.#widget.config.title}</div>
			${this.#storeSaleChart.getHelpdeskButton('16863272')}
		</div>
		`);

		this.#widget.layout.titleContainer.style.width = '100%';
	}

	openDetailSlider()
	{
		BX.SidePanel.Instance.open(this.detailSliderUrl, {
			cacheable: false,
			allowChangeTitle: false,
			allowChangeHistory: false,
		});
	}
}

Reflection.namespace('BX.Catalog.Report').StoreSaleChartManager = StoreSaleChartManager;