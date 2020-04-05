import {Tag, Event} from 'main.core';
import BarcodeItem from "./items/barcode";
import MarkingCodeItem from "./items/markingcode";
import './../css/widget/items/barcode.css';

export default class Widget
{
	static COLUMN_TYPE_BARCODE = 'barcode';
	static COLUMN_TYPE_MARKING_CODE = 'markingCode';

	constructor(props)
	{
		this._headData = props.headData;
		this._orderId = props.orderId;
		this._basketId = props.basketId;
		this._storeId = props.storeId;
		this._isBarcodeMulti = props.isBarcodeMulti;
		this._readonly = props.readonly;

		this._items = this.createItems(props.rowData, props.rowsCount);
		this._eventEmitter = new Event.EventEmitter();
	}

	get orderId()
	{
		return this._orderId;
	}

	get basketId()
	{
		return this._basketId;
	}

	get storeId()
	{
		return this._storeId;
	}

	createItems(data, count)
	{
		let items = [];

		data.forEach((rowData) => {
			items.push(this.createItemsRow(rowData));
		});

		if(data.length < count)
		{
			for (let i = 0, l = count - data.length; i < l; i++)
			{
				items.push(this.createEmptyRow());
			}
		}

		return items;
	}

	createEmptyRow()
	{
		let result = {id: 0};

		if(this.isBarcodeNeeded())
		{
			let barcodeItem = new BarcodeItem({});
			barcodeItem.onChangeSubscribe(this.onBarcodeItemChange.bind(this));
			result[Widget.COLUMN_TYPE_BARCODE] = barcodeItem;
		}

		if(this.isMarkingCodeNeeded)
		{
			let markingCodeItem = new MarkingCodeItem({});
			markingCodeItem.onChangeSubscribe(this.onMarkingCodeItemChange.bind(this));
			result[Widget.COLUMN_TYPE_MARKING_CODE] = markingCodeItem;
		}

		return result;
	}

	onBarcodeItemChange(event)
	{
		let barcodeItem = event.data.value;

		this.isBarcodeExist(barcodeItem.value)
		.then((result) => {
			barcodeItem.isExist = result;

			if(!this._isBarcodeMulti)
			{
				this.synchronizeBarcodes(barcodeItem.value, barcodeItem.isExist);
			}

			this.onChange();

		})
		.catch((data) => {
			BX.debug(data);
		});
	}

	onMarkingCodeItemChange()
	{
		this.onChange();
	}

	onChange()
	{
		this._eventEmitter.emit('onChange', this);
	}

	onChangeSubscribe(callback)
	{
		this._eventEmitter.subscribe('onChange', callback);
	}

	synchronizeBarcodes(value, isExist)
	{
		this._items.forEach((item) => {
			if(item[Widget.COLUMN_TYPE_BARCODE])
			{
				item[Widget.COLUMN_TYPE_BARCODE].value = value;
				item[Widget.COLUMN_TYPE_BARCODE].isExist = isExist;
			}
		});
	}

	isBarcodeExist(barcode)
	{
		if(barcode.length > 0)
		{
			let storeId = this._isBarcodeMulti ? this.storeId : 0;

			 return BX.Sale.Barcode.Checker.isBarcodeExist(
				barcode, this.basketId, this.orderId, storeId
			);
		}
		else
		{
			return new Promise((resolve) => {resolve(null);});
		}
	}

	createItemsRow(rowData)
	{
		let result = {id: rowData.id};

		if(this.isBarcodeNeeded())
		{
			let barcodeItem = new BarcodeItem({
				id: rowData.id,
				value: rowData.barcode,
				widget: this,
				readonly: this._readonly
			});

			barcodeItem.onChangeSubscribe(this.onBarcodeItemChange.bind(this));
			result[Widget.COLUMN_TYPE_BARCODE] = barcodeItem;
		}

		if(this.isMarkingCodeNeeded())
		{
			let markingCodeItem = new MarkingCodeItem({
				id: rowData.id,
				value: rowData.markingCode,
				readonly: this._readonly
			});

			markingCodeItem.onChangeSubscribe(this.onMarkingCodeItemChange.bind(this));
			result[Widget.COLUMN_TYPE_MARKING_CODE] = markingCodeItem;
		}

		return result;
	}

	isBarcodeNeeded()
	{
		return (typeof this._headData[Widget.COLUMN_TYPE_BARCODE] !== 'undefined');
	}

	isMarkingCodeNeeded()
	{
		return (typeof this._headData[Widget.COLUMN_TYPE_MARKING_CODE] !== 'undefined');
	}

	createTh(type)
	{
		let th = document.createElement('th');
		th.innerHTML = this._headData[type].title;
		return th;
	}

	render()
	{
		let tableNode = Tag.render`<table></table>`;
		let headRow = tableNode.insertRow();

		if(this.isBarcodeNeeded())
		{
			headRow.appendChild(
				this.createTh(Widget.COLUMN_TYPE_BARCODE)
			);
		}

		if(this.isMarkingCodeNeeded())
		{
			headRow.appendChild(
				this.createTh(Widget.COLUMN_TYPE_MARKING_CODE)
			);
		}

		this._items.forEach((row) => {
			let tableRow = tableNode.insertRow(-1);

			if(this.isBarcodeNeeded())
			{
				let cell = 	tableRow.insertCell();
				cell.appendChild(row[Widget.COLUMN_TYPE_BARCODE].render());
			}

			if(this.isMarkingCodeNeeded())
			{
				let cell = 	tableRow.insertCell();
				cell.appendChild(row[Widget.COLUMN_TYPE_MARKING_CODE].render());
			}
		});

		return tableNode;
	}

	getItemsData()
	{
		let result = [];

		this._items.forEach((item) => {
			result.push({
				id: item.id,
				barcode: {
					value: item[Widget.COLUMN_TYPE_BARCODE] ? item[Widget.COLUMN_TYPE_BARCODE].value : '',
					isExist: item[Widget.COLUMN_TYPE_BARCODE] ? item[Widget.COLUMN_TYPE_BARCODE].isExist : false
				},
				markingCode: {
					value: item[Widget.COLUMN_TYPE_MARKING_CODE] ? item[Widget.COLUMN_TYPE_MARKING_CODE].value : ''
				}
			});
		});

		return result;
	}
}