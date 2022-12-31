//@flow

import {Reflection} from "main.core";

type Options = {
	productListSliderUrl: string,
	productListSliderFilter: Object,
	gridId: string,
};

class StoreSaleGrid
{
	#productListSliderUrl: string;
	#productListSliderFilter: Object;

	constructor(options: Options)
	{
		this.#productListSliderUrl = options.productListSliderUrl;
		this.#productListSliderFilter = options.productListSliderFilter;
	}

	openStoreProductListGrid(storeId: number)
	{
		BX.SidePanel.Instance.open(`${this.#productListSliderUrl}?storeId=${storeId}`,
			{
				requestMethod: "post",
				requestParams: {
					filter: this.#productListSliderFilter,
					openedFromReport: true,
				},
				cacheable: false,
			});
	}
}

Reflection.namespace('BX.Catalog.Report.StoreSale').StoreGrid = StoreSaleGrid;
