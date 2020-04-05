import {Tag} from 'main.core';
import '../../../css/shipment/basket/barcode/dialog.css';

export default class Dialog
{
	constructor(props)
	{
		this._onClose = props.onClose || null;
		this._columnsCount = props.columnsCount;

		this._dialog = this._create(
			props.widget,
			props.productName,
			props.storeName
		);
	}

	show()
	{
		this._dialog.Show();
		this._dialog.adjustSizeEx();
	}

	_getWidth()
	{
		return this._columnsCount === 1 ? 280 : 400;
	}

	_createStoreRow(storeName)
	{
		let result = '';

		if(storeName.length > 0)
		{
			result = Tag.render`<div class="sale-shipment-basket-barcodes-dialog-store-name">${BX.util.htmlspecialchars(storeName)}</div>`;
		}

		return result;
	}

	_create(widget, productName, storeName)
	{
		let content = Tag.render`
			<div class="sale-shipment-basket-barcodes-dialog">
				<div class="sale-shipment-basket-barcodes-dialog-product-name">${BX.util.htmlspecialchars(productName)}</div>
				${this._createStoreRow(storeName)}
				${widget.render()}
			</div>`;

		let dialog =  new BX.CDialog({
			'content': content,
			'title': BX.message('SALE_JS_ADMIN_ORDER_CONF_INPUT_BARCODES'),
			'width': this._getWidth(),
			'height': 400,
			'resizable': false,
			'buttons': [
				new BX.CWindowButton({
					'title': BX.message('SALE_JS_ADMIN_ORDER_CONF_CLOSE'),
					'action': () =>	{

						if(this._onClose)
						{
							this._onClose(widget);
						}

						BX.WindowManager.Get().Close();
					},
					className: 'btnCloseBarcodeDialog'
				})
			]
		});

		//fully remove dialog and content after it will be closed
		BX.addCustomEvent(dialog, 'onWindowClose', function(dialog) {
			dialog.DIV.parentNode.removeChild(dialog.DIV);
		});

		return dialog;
	}
}
