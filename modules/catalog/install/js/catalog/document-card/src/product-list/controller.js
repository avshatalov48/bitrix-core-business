import {Loc} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events'
import DocumentCard from "../card/card";

export default class ProductListController extends BX.UI.EntityEditorController
{
	constructor(id, settings)
	{
		super();
		this.initialize(id, settings);
		this._setProductListHandler = this.handleSetProductList.bind(this);
		this._tabShowHandler = this.onTabShow.bind(this);

		this._editorControlChangeHandler = this.onEditorControlChange.bind(this);

		this._currencyId = this._model.getField('CURRENCY', '');

		EventEmitter.subscribe(this._editor, 'onControlChanged', this.onEditorControlChange.bind(this));
		EventEmitter.subscribe('DocumentProductListController', this._setProductListHandler);
		EventEmitter.subscribe('onEntityDetailsTabShow', this._tabShowHandler);
	}

	handleSetProductList(event)
	{
		const productList = event.getData()[0];
		this.setProductList(productList);
		EventEmitter.unsubscribe('DocumentProductListController', this._setProductListHandler);
	}

	reinitializeProductList()
	{
		if (this.productList)
		{
			this.productList.reloadGrid(false);
		}
	}

	onTabShow(event: BaseEvent)
	{
		const [tab] = event.getData();
		if (tab.id === 'tab_products' && this.productList)
		{
			this.productList.handleOnTabShow();
			EventEmitter.unsubscribe('onEntityDetailsTabShow', this._tabShowHandler);
			EventEmitter.emit('onDocumentProductListTabShow', this);
		}
	}

	innerCancel()
	{
		this.rollback();
		if (this.productList)
		{
			this.productList.onInnerCancel();
		}

		this._currencyId = this._model.getField('CURRENCY');

		if (this.productList)
		{
			this.productList.changeCurrencyId(this._currencyId);
		}

		this._isChanged = false;
	}

	getCurrencyId = function()
	{
		return this._currencyId;
	}

	setProductList(productList)
	{
		if (this.productList === productList)
		{
			return;
		}

		if (this.productList)
		{
			this.productList.destroy();
		}

		this.productList = productList;

		if (this.productList)
		{
			this.productList.setController(this);
			this.productList.setForm(this._editor.getFormElement());

			if (this.productList.getCurrencyId() !== this.getCurrencyId())
			{
				this.productList.changeCurrencyId(this.getCurrencyId());
			}

			this._prevProductCount = this._curProductCount = this.productList.getProductCount();
		}
	}

	onAfterSave()
	{
		super.onAfterSave();
		if (this.productList)
		{
			this.productList.removeFormFields();
		}

		this._editor._toolPanel.showViewModeButtons();
	}

	productChange(disableSaveButton = false)
	{
		disableSaveButton = disableSaveButton ?? false;
		this.markAsChanged();

		if (disableSaveButton)
		{
			this.disableSaveButton();
		}

		EventEmitter.emit('onDocumentProductChange', this.productList.getProductsFields());
	}

	onBeforeSubmit()
	{
		if (this.productList && (this.isChanged() || this._editor.isNew()))
		{
			this.productList.compileProductData();
		}
	}

	enableSaveButton()
	{
		if (this._editor?._toolPanel)
		{
			this._editor._toolPanel.enableSaveButton();
		}
	}

	disableSaveButton()
	{
		if (this._editor?._toolPanel)
		{
			this._editor._toolPanel.disableSaveButton();
		}
	}

	onEditorControlChange(event: BaseEvent)
	{
		const [field, params] = event.getData();
		if (field instanceof BX.UI.EntityEditorMoney && params?.fieldName === 'CURRENCY')
		{
			this._currencyId = params?.fieldValue;

			if (this.productList && this._currencyId)
			{
				this.productList.changeCurrencyId(this._currencyId);
				this.markAsChanged();
			}
		}
	}

	setTotal(totalData)
	{
		this._model.setField(
			'FORMATTED_TOTAL',
			BX.Currency.currencyFormat(totalData.totalCost, this.getCurrencyId(), false),
		);

		this._model.setField(
			'FORMATTED_TOTAL_WITH_CURRENCY',
			BX.Currency.currencyFormat(totalData.totalCost, this.getCurrencyId(), true),
		);

		this._model.setField(
			'TOTAL',
			totalData.totalCost,
		);

		this._editor.getControlById('TOTAL_WITH_CURRENCY').refreshLayout();
	}

	validateProductList()
	{
		let errorsArray = this.productList.validate();
		if (errorsArray.length > 0)
		{
			this._editor._toolPanel.addError(errorsArray[0]);
			EventEmitter.emit('onProductsCheckFailed', errorsArray);
			return false;
		}

		return true;
	}
}
