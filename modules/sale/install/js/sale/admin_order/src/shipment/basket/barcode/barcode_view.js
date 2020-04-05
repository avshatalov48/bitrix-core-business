import {Tag} from 'main.core';
import WidgetFabric from "./widgetfabric";
import Dialog from "./dialog";
import '../../../css/shipment/basket/barcode/barcode.css';

export default class BarcodeView
{
	static TYPE_BUTTON = 'button';
	static TYPE_LINK = 'link';
	static TYPE_INPUT = 'input';

	constructor(props)
	{
		this._basketId = props.basketId;
		this._product = props.product;
		this._index = props.index;
		this._orderId = props.orderId;
		this._type = props.type;
		this._useStoreControl = props.useStoreControl;

		this._dataFieldTemplate = props.dataFieldTemplate || '';

		this._itemNode = null;
		this._hiddensContainer = null;

		this._initialStoreId = 0;

		let barcodeInfo = [];

		if(this._product.BARCODE_INFO)
		{
			const stores = Object.keys(this._product.BARCODE_INFO);
			this._initialStoreId = stores[this._index - 1];

			if(this._initialStoreId)
			{
				barcodeInfo = this._product.BARCODE_INFO[this._initialStoreId];
			}
		}

		this._items = this._initItems(barcodeInfo);
	}

	_initItems(storeBarcodeInfo)
	{
		if(storeBarcodeInfo.length <= 0)
		{
			return [];
		}

		let result = [];

		if(this._isSupportedMarkingCode() || this._isBarcodeMulti())
		{
			storeBarcodeInfo.forEach((item) => {
				result.push({
					id: item.ID,
					barcode: item.BARCODE,
					markingCode: item.MARKING_CODE
				});
			});
		}
		else
		{
			let item = storeBarcodeInfo[0];

			result = [{
				id: item.ID,
				barcode: item.BARCODE,
				markingCode: item.MARKING_CODE
			}];
		}

		return result;
	}

	render()
	{
		this._itemNode = this._renderItemNode();
		this._hiddensContainer = Tag.render`<div></div>`;
		this._renderHiddens();
		return Tag.render`<div>${this._itemNode}${this._hiddensContainer}</div>`;
	}

	_renderItemNode()
	{
		let result = null;

		if(this._type === BarcodeView.TYPE_BUTTON)
		{
			result = Tag.render`<input type="button" value="${ BX.message('SALE_JS_ADMIN_ORDER_CONF_BARCODES')}" onclick="${this._onClick.bind(this)}">`;
		}
		else if(this._type === BarcodeView.TYPE_LINK)
		{
			result = Tag.render`<span style="cursor: pointer; border-bottom: 1px dashed;" onclick="${this._onClick.bind(this)}">${BX.message('SALE_JS_ADMIN_ORDER_CONF_BARCODE')}</span>`;
		}
		else if(this._type === BarcodeView.TYPE_INPUT)
		{
			let widget = this._createWidget(1);
			result = widget.render();
		}
		else
		{
			throw new Error('Wrong BarcodeView type');
		}

		return result;
	}

	_getActualBarcodesQuantity()
	{
		return this._items.length;
	}

	_getActualStoreId()
	{
		return this._initialStoreId;
	}

	_onClick()
	{
		let dialog = new Dialog({
			widget: this._createWidget(),
			productName: this._product.NAME,
			storeName: this._getStoreName(this._getActualStoreId()),
			columnsCount: this._getColumnsCount()
		});

		dialog.show();
	}

	_getColumnsCount()
	{
		return this._isSupportedMarkingCode() && this._useStoreControl ? 2 : 1;
	}

	_getStoreName(storeId)
	{
		if(this._product.STORES && Array.isArray(this._product.STORES))
		{
			let stores = this._product.STORES;

			for(let i = 0, l = stores.length; i < l; i++)
			{
				if(parseInt(stores[i].STORE_ID) === parseInt(storeId))
				{
					return stores[i].STORE_NAME;
				}
			}
		}

		return '';
	}

	_isBarcodeMulti()
	{
		return 	this._product.BARCODE_MULTI === 'Y';
	}

	_isSupportedMarkingCode()
	{
		return 	this._product.IS_SUPPORTED_MARKING_CODE === 'Y';
	}

	_createWidget(rowsCount)
	{
		return WidgetFabric.createWidget({
			items: this._items,
			rowsCount: rowsCount,
			orderId: this._orderId,
			basketId: this._basketId,
			readonly: true,
			useStoreControl: this._useStoreControl,
			storeId: this._getActualStoreId(),
			isBarcodeMulti: this._isBarcodeMulti(),
			isSupportedMarkingCode: this._isSupportedMarkingCode()
		});
	}

	_renderHiddens()
	{
		if(!this._dataFieldTemplate)
		{
			return;
		}

		this._hiddensContainer.innerHTML = '';
		let iterator = 0;

		this._items.forEach((item) => {
			this._hiddensContainer.appendChild(
				Tag.render`
					<div>					
						${this._createHiddenInput('VALUE', iterator, item.barcode)}
						${this._createHiddenInput('ID', iterator, item.id)}
						${this._createHiddenInput('MARKING_CODE', iterator, item.markingCode)}
					</div>`
			);

			iterator++;
		});
	}

	_createHiddenInput(dataType, iterator, value)
	{
		let strInput = this._dataFieldTemplate
		.replace('#ITERATOR#', iterator)
		.replace('#DATA_TYPE#', dataType)
		.replace('#DATA_TYPE_LOWER#', dataType.toLowerCase());

		let input = Tag.render`${strInput}`;
		input.setAttribute('value', value);
		return input;
	}
}