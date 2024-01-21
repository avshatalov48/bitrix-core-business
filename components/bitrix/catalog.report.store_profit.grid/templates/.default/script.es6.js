// @flow

import { Loc, Reflection, Tag, Uri } from 'main.core';

type Options = {
	productListSliderUrl: string,
	productListSliderFilter: Object,
	gridId: string,
};

class StoreProfitGrid
{
	#productListSliderUrl: string;
	#productListSliderFilter: Object;

	constructor(options: Options)
	{
		this.#productListSliderUrl = options.productListSliderUrl;
		this.#productListSliderFilter = options.productListSliderFilter;

		this.updateWidgetTitleInterval = setInterval(this.updateWidgetTitle.bind(this), 50);
	}

	openStoreProductListGrid(storeId: ?number)
	{
		const url = Uri.addParam(this.#productListSliderUrl, { storeId });
		BX.SidePanel.Instance.open(
			url,
			{
				requestMethod: 'post',
				requestParams: {
					filter: this.#productListSliderFilter,
					openedFromReport: true,
				},
				cacheable: false,
			},
		);
	}

	updateWidgetTitle()
	{
		const dashboardElement = document.querySelector('.amcharts-main-div')?.parentElement?.parentElement?.parentElement?.parentElement;
		if (!dashboardElement)
		{
			return;
		}

		const titleElement = dashboardElement.querySelector('.report-visualconstructor-dashboard-widget-head-wrapper > .report-visualconstructor-dashboard-widget-title-container');
		document.querySelector('.report-visualconstructor-dashboard-widget-head-wrapper').appendChild(Tag.render`
			<div class="chart-header">
				${titleElement}
				<div onclick='top.BX.Helper.show("redirect=detail&code=18502626")' class="how-it-works-guide-link">${Loc.getMessage('STORE_CHART_HINT_TITLE')}</div>
			</div>
		`);
		clearInterval(this.updateWidgetTitleInterval);
	}
}

Reflection.namespace('BX.Catalog.Report.StoreProfit').StoreGrid = StoreProfitGrid;
