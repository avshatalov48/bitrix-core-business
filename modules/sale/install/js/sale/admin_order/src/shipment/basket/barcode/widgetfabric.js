import {Widget} from 'sale.barcode';

export default class WidgetFabric
{
	static createWidget(props)
	{
		let items = props.items.slice(0, props.rowsCount);

		return new Widget({
			rowData: WidgetFabric._createBarcodeWidgetRows(items, props.isSupportedMarkingCode),
			headData: WidgetFabric._createBarcodeWidgetHead(props.isSupportedMarkingCode, props.useStoreControl),
			rowsCount: props.rowsCount,
			orderId: props.orderId,
			basketId: props.basketId,
			storeId: props.storeId,
			isBarcodeMulti: props.isBarcodeMulti,
			readonly: props.readonly,
		});
	}

	static _createBarcodeWidgetHead(isSupportedMarkingCode, useStoreControl)
	{
		let result = {};

		if(useStoreControl)
		{
			result['barcode'] = {title: BX.message('SALE_JS_ADMIN_ORDER_CONF_BARCODE')};
		}

		if(isSupportedMarkingCode)
		{
			result['markingCode'] = {title: BX.message('SALE_JS_ADMIN_ORDER_CONF_MARKING_CODE')};
		}

		return result;
	}

	static _createBarcodeWidgetRows(items, isSupportedMarkingCode)
	{
		let result = [];

		items.forEach((item) => {
			let itemData = {id: item.id};

			itemData.barcode = item.barcode;

			if(isSupportedMarkingCode)
			{
				itemData.markingCode = item.markingCode;
			}

			result.push(itemData);
		});

		return result;
	}
}