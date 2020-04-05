import {Tag} from 'main.core';
import Dialog from "./dialog";
import BarcodeView from "./barcode_view";
import WidgetFabric from "./widgetfabric";

export default class BarcodeEdit extends BarcodeView
{
	constructor(props)
	{
		super(props);

		this._dataFieldTemplate = props.dataFieldTemplate;
		this._useStoreControl = props.useStoreControl;

		//We need some actual information from form fields
		this._getActualBarcodeQuantityMethod = props.getActualBarcodeQuantityMethod;
		this._getActualStoreIdByIndexMethod = props.getActualStoreIdByIndexMethod;
	}

	_getActualBarcodesQuantity()
	{
		let result = 1;

		if(this._isBarcodeMulti() || this._isSupportedMarkingCode())
		{
			result = this._getActualBarcodeQuantityMethod(this._basketId, this._index);
		}

		return result;
	}

	_getActualStoreId()
	{
		return this._getActualStoreIdByIndexMethod(this._basketId, this._index);
	}

	_onClick()
	{
		let dialog = new Dialog({
			widget: this._createWidget(this._getActualBarcodesQuantity()),
			productName: this._product.NAME,
			storeName: this._getStoreName(this._getActualStoreId()),
			onClose: this._onDialogClose.bind(this),
			columnsCount: this._getColumnsCount()
		});

		dialog.show();
	}

	_createWidget(rowsCount)
	{
		let widget = WidgetFabric.createWidget({
			items: this._items,
			rowsCount: rowsCount,
			orderId: this._orderId,
			basketId: this._basketId,
			readonly: false,
			useStoreControl: this._useStoreControl,
			storeId: this._getActualStoreId(),
			isBarcodeMulti: this._isBarcodeMulti(),
			isSupportedMarkingCode: this._isSupportedMarkingCode()
		});

		widget.onChangeSubscribe(this._onWidgetChanged.bind(this));
		return widget;
	}


	_onWidgetChanged(event)
	{
		let widget = event.data;
		this._getWidgetData(widget);
	}

	_getWidgetData(widget)
	{
		this._items = [];

		widget.getItemsData().forEach((itemData) => {
			this._items.push({
				id: itemData.id,
				barcode: itemData.barcode.value,
				markingCode: itemData.markingCode.value
			});
		});

		this._renderHiddens(this._items);
	}

	_onDialogClose(widget)
	{
		this._getWidgetData(widget);
	}
}