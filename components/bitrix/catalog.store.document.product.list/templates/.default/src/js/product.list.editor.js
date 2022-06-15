import {ajax, Cache, Dom, Event, Reflection, Runtime, Text, Type, Loc} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Row} from './product.list.row';
import {PageEventsManager} from './page.events.manager';
import SettingsPopup from './settings.button';
import {CurrencyCore} from 'currency.currency-core';
import {ProductSelector} from 'catalog.product-selector';
import HintPopup from './hint.popup';
import ProductListController from "catalog.document-card";
import {ProductModel} from "catalog.product-model";

const GRID_TEMPLATE_ROW = 'template_0';
const DEFAULT_PRECISION: number = 2;

export class Editor
{
	id: ?string;
	settings: Object;
	controller: ?ProductListController;
	container: ?HTMLElement;
	form: ?HTMLElement
	products: Row[] = [];
	productsWasInitiated = false;
	pageEventsManager: PageEventsManager;
	cache = new Cache.MemoryCache();

	actions = {
		productChange: 'productChange',
		productListChanged: 'productListChanged',
		updateListField: 'listField',
		updateTotal: 'total'
	};

	updateFieldForList = null;

	productRowAddHandler = this.handleProductRowAdd.bind(this);
	productRowCreateHandler = this.handleProductRowCreate.bind(this);
	showBarcodeSettingsPopupHandler = this.handleShowBarcodeSettingsPopup.bind(this);
	showSettingsPopupHandler = this.handleShowSettingsPopup.bind(this);

	onSaveHandler = this.handleOnSave.bind(this);
	onEditorSubmit = this.handleEditorSubmit.bind(this);
	onBeforeGridRequestHandler = this.handleOnBeforeGridRequest.bind(this);
	onGridUpdatedHandler = this.handleOnGridUpdated.bind(this);
	onGridRowMovedHandler = this.handleOnGridRowMoved.bind(this);
	onBeforeProductChangeHandler = this.handleOnBeforeProductChange.bind(this);
	onProductChangeHandler = this.handleOnProductChange.bind(this);
	onProductClearHandler = this.handleOnProductClear.bind(this);
	dropdownChangeHandler = this.handleDropdownChange.bind(this);
	onScanEmitHandler = this.handleMobileScanEvent.bind(this);

	changeProductFieldHandler = this.handleFieldChange.bind(this);
	updateTotalDataDelayedHandler = Runtime.debounce(this.updateTotalDataDelayed, 100, this);

	constructor(id)
	{
		this.setId(id);
	}

	init(config = {})
	{
		this.setSettings(config);

		this.scannerToken = this.scannerToken ?? Text.getRandom(16);
		if (this.canEdit())
		{
			this.addFirstRowIfEmpty();
			this.enableEdit();
		}

		this.initForm();
		this.initProducts();
		this.initGridData();
		this.paintColumns();

		EventEmitter.emit( 'DocumentProductListController', [this]);

		this.#initSupportCustomRowActions();
		this.subscribeDomEvents();
		this.subscribeCustomEvents();
	}

	subscribeDomEvents()
	{
		const container = this.getContainer();

		if (Type.isElementNode(container))
		{
			container.querySelectorAll('[data-role="product-list-add-button"]').forEach((addButton) => {
				Event.bind(
					addButton,
					'click',
					this.productRowAddHandler
				);
			});

			container.querySelectorAll('[data-role="product-list-create-button"]').forEach((addButton) => {
				Event.bind(
					addButton,
					'click',
					this.productRowCreateHandler
				);
			});

			container.querySelectorAll('[data-role="product-list-settings-button"]').forEach((configButton) => {
				Event.bind(
					configButton,
					'click',
					this.showSettingsPopupHandler
				);
			});

			container.querySelectorAll('[data-role="product-list-barcode-settings-button"]').forEach((configButton) => {
				Event.bind(
					configButton,
					'click',
					this.showBarcodeSettingsPopupHandler
				);
			});
		}
	}

	unsubscribeDomEvents()
	{
		const container = this.getContainer();

		if (Type.isElementNode(container))
		{
			container.querySelectorAll('[data-role="product-list-select-button"]').forEach((selectButton) => {
				Event.unbind(
					selectButton,
					'click',
					this.productSelectionPopupHandler
				);
			});

			container.querySelectorAll('[data-role="product-list-add-button"]').forEach((createButton) => {
				Event.unbind(
					createButton,
					'click',
					this.productRowCreateHandler
				);
			});

			container.querySelectorAll('[data-role="product-list-barcode-settings-button"]').forEach((addButton) => {
				Event.unbind(
					addButton,
					'click',
					this.productRowAddHandler
				);
			});

			container.querySelectorAll('[data-role="product-list-settings-button"]').forEach((configButton) => {
				Event.unbind(
					configButton,
					'click',
					this.showSettingsPopupHandler
				);
			});
		}
	}

	subscribeCustomEvents()
	{
		EventEmitter.subscribe('BX.UI.EntityEditor:onSave', this.onSaveHandler);
		EventEmitter.subscribe('BX.UI.EntityEditorAjax:onSubmit', this.onEditorSubmit);
		EventEmitter.subscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
		EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
		EventEmitter.subscribe('Grid::rowMoved', this.onGridRowMovedHandler);
		EventEmitter.subscribe('BX.Catalog.ProductSelector:onBeforeChange', this.onBeforeProductChangeHandler);
		EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChangeHandler);
		EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClearHandler);
		EventEmitter.subscribe('Dropdown::change', this.dropdownChangeHandler);
		EventEmitter.subscribe('BarcodeScanner::onScanEmit', this.onScanEmitHandler);
	}

	unsubscribeCustomEvents()
	{
		EventEmitter.unsubscribe('BX.UI.EntityEditor:onSave', this.onSaveHandler);
		EventEmitter.unsubscribe('BX.UI.EntityEditorAjax:onSubmit', this.onEditorSubmit);
		EventEmitter.unsubscribe('Grid::beforeRequest', this.onBeforeGridRequestHandler);
		EventEmitter.unsubscribe('Grid::updated', this.onGridUpdatedHandler);
		EventEmitter.unsubscribe('Grid::rowMoved', this.onGridRowMovedHandler);
		EventEmitter.unsubscribe('BX.Catalog.ProductSelector:onBeforeChange', this.onBeforeProductChangeHandler);
		EventEmitter.unsubscribe('BX.Catalog.ProductSelector:onChange', this.onProductChangeHandler);
		EventEmitter.unsubscribe('BX.Catalog.ProductSelector:onClear', this.onProductClearHandler);
		EventEmitter.unsubscribe('Dropdown::change', this.dropdownChangeHandler);
		EventEmitter.unsubscribe('BarcodeScanner::onScanEmit', this.onScanEmitHandler);
	}

	handleMobileScanEvent(event: BaseEvent): void
	{
		const params = event.getData();
		if (this.scannerToken !== params.id || !Type.isStringFilled(params.barcode))
		{
			return;
		}

		for (let product of this.products)
		{
			if (product.getBarcodeSelector()?.searchInput?.getNameInput() === document.activeElement)
			{
				product.getBarcodeSelector().searchInput?.applyScannerData(params.barcode);

				return;
			}
		}

		for (let product of this.products)
		{
			if (
				product.getBarcodeSelector()?.searchInput?.getNameInput().value === ''
				&& product.getSelector()?.searchInput?.getNameInput().value === ''
			)
			{
				product.getBarcodeSelector().searchInput?.applyScannerData(params.barcode);

				return;
			}
		}

		const newRowId = this.addProductRow();
		this.getProductById(newRowId)?.getBarcodeSelector().searchInput?.applyScannerData(params.barcode);
	}

	#initSupportCustomRowActions()
	{
		this.getGrid()._clickOnRowActionsButton = () => {};
	}

	selectProductInRow(id: string, productId: number): void
	{
		if (!Type.isStringFilled(id) || Text.toNumber(productId) <= 0)
		{
			return;
		}

		requestAnimationFrame(() => {
			this
				.getProductSelector(id)
				?.onProductSelect(productId)
			;
		});
	}

	handleOnSave(event: BaseEvent)
	{
		const notification = ProductModel.getLastActiveSaveNotification();
		if (notification)
		{
			notification.close();
		}

		const items = [];

		this.products.forEach((product) => {
			const item = {
				fields: {...product.fields},
				rowId: product.fields.ROW_ID
			};
			items.push(item);
		});

		this.setSettingValue('items', items);
	}

	handleEditorSubmit(event: BaseEvent)
	{
	}

	onInnerCancel()
	{
		this.reloadGrid(false);
	}

	changeActivePanelButtons(panelCode: 'top' | 'bottom'): HTMLElement
	{
		const container = this.getContainer();
		const activePanel = container.querySelector('.catalog-document-product-list-add-block-' + panelCode);
		if (Type.isDomNode(activePanel))
		{
			Dom.removeClass(activePanel, 'catalog-document-product-list-add-block-hidden');
			Dom.addClass(activePanel, 'catalog-document-product-list-add-block-active');
		}

		const hiddenPanelCode = (panelCode === 'top') ? 'bottom' : 'top';
		const removePanel = container.querySelector('.catalog-document-product-list-add-block-' + hiddenPanelCode);
		if (Type.isDomNode(removePanel))
		{
			Dom.addClass(removePanel, 'catalog-document-product-list-add-block-hidden');
			Dom.removeClass(removePanel, 'catalog-document-product-list-add-block-active');
		}

		return activePanel;
	}

	reloadGrid(useProductsFromRequest: boolean = true, isInternalChanging: ?boolean = null): void
	{
		if (isInternalChanging === null)
		{
			isInternalChanging = !useProductsFromRequest;
		}

		this.getGrid().reloadTable(
			'POST',
			{useProductsFromRequest},
			() => this.actionUpdateTotalData({isInternalChanging})
		);
	}

	/*
		keep in mind different actions for this handler:
		- native reload by grid actions (columns settings, etc)		- products from request
		- rollback													- products from db			this.reloadGrid(false)
	 */
	handleOnBeforeGridRequest(event: BaseEvent)
	{
		const [grid, eventArgs] = event.getCompatData();

		if (!grid || !grid.parent || grid.parent.getId() !== this.getGridId())
		{
			return;
		}

		// reload by native grid actions (columns settings, etc), otherwise by this.reloadGrid()
		const isNativeAction = !('useProductsFromRequest' in eventArgs.data);
		const useProductsFromRequest = isNativeAction ? true : eventArgs.data.useProductsFromRequest;

		eventArgs.url = this.getReloadUrl();
		eventArgs.method = 'POST';
		eventArgs.sessid = BX.bitrix_sessid();
		eventArgs.data = {
			...eventArgs.data,
			useProductsFromRequest,
			signedParameters: this.getSignedParameters(),
			products: useProductsFromRequest ? this.getProductsFields() : null,
		};

		let isDeletingRequest = false;
		if (eventArgs.data['action_button_' + eventArgs.gridId] === 'delete')
		{
			isDeletingRequest = true;
		}

		this.clearEditor();

		if (isNativeAction)
		{
			EventEmitter.subscribeOnce('Grid::updated', (event) => {
				const [grid] = event.getCompatData();

				if (!grid || grid.getId() !== this.getGridId())
				{
					return;
				}

				this.actionUpdateTotalData({isInternalChanging: false});
				if (isDeletingRequest)
				{
					this.executeActions([
						{type: this.actions.productListChanged},
					]);
				}
			});
		}
	}

	handleOnGridUpdated(event: BaseEvent)
	{
		const [grid] = event.getCompatData();

		if (!grid || grid.getId() !== this.getGridId())
		{
			return;
		}

		this.getSettingsPopup().updateCheckboxState();
	}

	handleOnGridRowMoved(event: BaseEvent)
	{
		const [ids, , grid] = event.getCompatData();

		if (!grid || grid.getId() !== this.getGridId())
		{
			return;
		}

		const changed = this.resortProductsByIds(ids);
		if (changed)
		{
			this.refreshSortFields();
			this.numerateRows();
			this.executeActions([{type: this.actions.productListChanged}]);
		}
	}

	initPageEventsManager(): void
	{
		const componentId = this.getSettingValue('componentId');
		this.pageEventsManager = new PageEventsManager({id: componentId});
	}

	getPageEventsManager(): PageEventsManager
	{
		if (!this.pageEventsManager)
		{
			this.initPageEventsManager();
		}

		return this.pageEventsManager;
	}

	canEdit(): boolean
	{
		return this.getSettingValue('allowEdit', false) === true;
	}

	enableEdit()
	{
		// Cannot use editSelected because checkboxes have been removed
		const rows = this.getGrid().getRows().getRows();
		rows.forEach((current) => {
			if (!current.isHeadChild() && !current.isTemplate())
			{
				current.edit();
			}
		});
	}

	addFirstRowIfEmpty(): void
	{
		if (this.getGrid().getRows().getCountDisplayed() === 0)
		{
			requestAnimationFrame(() => this.addProductRow());
		}
	}

	clearEditor()
	{
		this.unsubscribeProductsEvents();

		this.products = [];
		this.productsWasInitiated = false;

		this.destroySettingsPopup();
		this.unsubscribeDomEvents();
		this.unsubscribeCustomEvents();

		Event.unbindAll(this.container);
	}

	wasProductsInitiated()
	{
		return this.productsWasInitiated;
	}

	unsubscribeProductsEvents()
	{
		this.products.forEach((current) => {
			const productSelector = current.getSelector();
			if (productSelector)
			{
				productSelector.unsubscribeEvents();
			}
			const barcodeSelector = current.getBarcodeSelector();
			if (barcodeSelector)
			{
				barcodeSelector.unsubscribeEvents();
			}
		});
	}

	destroy()
	{
		this.setForm(null);
		this.clearController();
		this.clearEditor();
	}

	setController(controller)
	{
		if (this.controller === controller)
		{
			return;
		}
		if (this.controller)
		{
			this.controller.clearProductList();
		}
		this.controller = controller;
	}

	clearController()
	{
		this.controller = null;
	}

	getId()
	{
		return this.id;
	}

	setId(id)
	{
		this.id = id;
	}

	/* settings tools */
	getSettings()
	{
		return this.settings;
	}

	setSettings(settings)
	{
		this.settings = settings ? settings : {};
	}

	getSettingValue(name: string, defaultValue)
	{
		return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultValue;
	}

	setSettingValue(name, value)
	{
		this.settings[name] = value;
	}

	getComponentName()
	{
		return this.getSettingValue('componentName', '');
	}

	getReloadUrl()
	{
		return this.getSettingValue('reloadUrl', '');
	}

	getSignedParameters()
	{
		return this.getSettingValue('signedParameters', '');
	}

	getContainerId()
	{
		return this.getSettingValue('containerId', '');
	}

	getGridId(): string
	{
		return this.getSettingValue('gridId', '');
	}

	getLanguageId()
	{
		return this.getSettingValue('languageId', '');
	}

	getSiteId()
	{
		return this.getSettingValue('siteId', '');
	}

	getCatalogId()
	{
		return this.getSettingValue('catalogId', 0);
	}

	isReadOnly()
	{
		return this.getSettingValue('readOnly', true);
	}

	setReadOnly(readOnly)
	{
		this.setSettingValue('readOnly', readOnly);
	}

	getCurrencyId(): string
	{
		return this.getSettingValue('currencyId', '');
	}

	setCurrencyId(currencyId): Promise
	{
		this.setSettingValue('currencyId', currencyId);
		return CurrencyCore.loadCurrencyFormat(currencyId);
	}

	changeCurrencyId(currencyId): void
	{
		const oldCurrencyId = this.getCurrencyId();
		if (oldCurrencyId === currencyId)
		{
			return;
		}

		this
			.setCurrencyId(currencyId)
			.then(() => {
				const products = [];
				this.products.forEach((product) => {
					product.getModel().setOption('currency', currencyId);
					products.push({
						fields: product.getFields(),
						id: product.getId()
					});
				});

				if (products.length > 0)
				{
					ajax.runComponentAction(
						this.getComponentName(),
						'calculateProductPrices',
						{
							mode: 'class',
							signedParameters: this.getSignedParameters(),
							data: {
								products,
								currencyId,
								oldCurrencyId,
							}
						}
					)
						.then(this.onCalculatePricesResponse.bind(this));
				}

				const editData = this.getGridEditData();
				const templateRow = editData[GRID_TEMPLATE_ROW];

				templateRow['CURRENCY'] = this.getCurrencyId();
				const templateFieldNames = ['BASE_PRICE', 'PURCHASING_PRICE'];

				templateFieldNames.forEach((field) => {
					if (templateRow[field] && templateRow[field]['CURRENCY'])
					{
						templateRow[field]['CURRENCY']['VALUE'] = this.getCurrencyId();
					}
				});
				this.setGridEditData(editData);
			});
	}

	onCalculatePricesResponse(response)
	{
		const products = response.data;
		this.products.forEach((product) => {
			if (Type.isObject(products[product.getId()]))
			{
				product.updateField('BASE_PRICE', products[product.getId()]['BASE_PRICE']);
				product.updateField('PURCHASING_PRICE', products[product.getId()]['PURCHASING_PRICE']);
				product.updateUiCurrencyFields();
			}
		});

		this.updateTotalDataDelayed();
		this.updateTotalUiCurrency();
	}

	updateTotalUiCurrency()
	{
		const totalBlock = BX(this.getSettingValue('totalBlockContainerId', null));
		if (Type.isElementNode(totalBlock))
		{
			totalBlock.querySelectorAll('[data-role="currency-wrapper"]').forEach((row) => {
				row.innerHTML = this.getCurrencyText();
			});
		}
	}

	getCurrencyText(): string
	{
		const currencyId = this.getCurrencyId();
		if (!Type.isStringFilled(currencyId))
		{
			return '';
		}

		const format = CurrencyCore.getCurrencyFormat(currencyId);
		return format && format.FORMAT_STRING.replace(/(^|[^&])#/, '$1').trim() || '';
	}

	getDataFieldName()
	{
		return this.getSettingValue('dataFieldName', '');
	}

	getDataSettingsFieldName()
	{
		const field = this.getDataFieldName();

		return Type.isStringFilled(field) ? field + '_SETTINGS' : '';
	}

	getPricePrecision(): number
	{
		return this.getSettingValue('pricePrecision', DEFAULT_PRECISION);
	}

	getQuantityPrecision(): number
	{
		return this.getSettingValue('quantityPrecision', DEFAULT_PRECISION);
	}

	getCommonPrecision(): number
	{
		return this.getSettingValue('commonPrecision', DEFAULT_PRECISION);
	}

	getMeasures(): []
	{
		return this.getSettingValue('measures', []);
	}

	getDefaultMeasure()
	{
		return this.getSettingValue('defaultMeasure', {});
	}

	getRowIdPrefix()
	{
		return this.getSettingValue('rowIdPrefix', 'catalog_entity_product_list_');
	}

	/* settings tools finish */

	/* calculate tools */
	parseInt(value: number | string, defaultValue: number = 0)
	{
		let result;

		const isNumberValue = Type.isNumber(value);
		const isStringValue = Type.isStringFilled(value);

		if (!isNumberValue && !isStringValue)
		{
			return defaultValue;
		}

		if (isStringValue)
		{
			value = value.replace(/^\s+|\s+$/g, '');
			const isNegative = value.indexOf('-') === 0;
			result = parseInt(value.replace(/[^\d]/g, ''), 10);
			if (isNaN(result))
			{
				result = defaultValue;
			}
			else
			{
				if (isNegative)
				{
					result = -result;
				}
			}
		}
		else
		{
			result = parseInt(value, 10);
			if (isNaN(result))
			{
				result = defaultValue;
			}
		}

		return result;
	}

	parseFloat(value: number | string, precision: number = DEFAULT_PRECISION, defaultValue: number = 0.0)
	{
		let result;

		const isNumberValue = Type.isNumber(value);
		const isStringValue = Type.isStringFilled(value);

		if (!isNumberValue && !isStringValue)
		{
			return defaultValue;
		}

		if (isStringValue)
		{
			value = value.replace(/^\s+|\s+$/g, '');

			const dot = value.indexOf('.');
			const comma = value.indexOf(',');
			const isNegative = value.indexOf('-') === 0;

			if (dot < 0 && comma >= 0)
			{
				let s1 = value.substr(0, comma);
				const decimalLength = value.length - comma - 1;

				if (decimalLength > 0)
				{
					s1 += '.' + value.substr(comma + 1, decimalLength);
				}

				value = s1;
			}

			value = value.replace(/[^\d.]+/g, '');
			result = parseFloat(value);

			if (isNaN(result))
			{
				result = defaultValue;
			}
			if (isNegative)
			{
				result = -result;
			}
		}
		else
		{
			result = parseFloat(value);
		}

		if (precision >= 0)
		{
			result = this.round(result, precision);
		}

		return result;
	}

	round(value: number, precision: number = DEFAULT_PRECISION)
	{
		const factor = Math.pow(10, precision);

		return Math.round(value * factor) / factor;
	}

	/* calculate tools finish */

	getContainer()
	{
		return this.cache.remember('container', () => {
			return document.getElementById(this.getContainerId());
		});
	}

	initForm()
	{
		const formId = this.getSettingValue('formId', '');
		const form = Type.isStringFilled(formId) ? BX('form_' + formId) : null;

		if (Type.isElementNode(form))
		{
			this.setForm(form);
		}
	}

	isExistForm()
	{
		return Type.isElementNode(this.getForm());
	}

	getForm()
	{
		return this.form;
	}

	setForm(form)
	{
		this.form = form;
	}

	initFormFields()
	{
		const container = this.getForm();
		if (Type.isElementNode(container))
		{
			const field = this.getDataField();
			if (!Type.isElementNode(field))
			{
				this.initDataField();
			}

			const settingsField = this.getDataSettingsField();
			if (!Type.isElementNode(settingsField))
			{
				this.initDataSettingsField();
			}
		}
	}

	initFormField(fieldName)
	{
		const container = this.getForm();

		if (Type.isElementNode(container) && Type.isStringFilled(fieldName))
		{
			container.appendChild(Dom.create(
				'input',
				{attrs: {type: "hidden", name: fieldName}}
			));
		}
	}

	removeFormFields()
	{
		const field = this.getDataField();
		if (Type.isElementNode(field))
		{
			Dom.remove(field);
		}

		const settingsField = this.getDataSettingsField();
		if (Type.isElementNode(settingsField))
		{
			Dom.remove(settingsField);
		}
	}

	initDataField()
	{
		this.initFormField(this.getDataFieldName());
	}

	initDataSettingsField()
	{
		this.initFormField(this.getDataSettingsFieldName());
	}

	getFormField(fieldName)
	{
		const container = this.getForm();

		if (Type.isElementNode(container) && Type.isStringFilled(fieldName))
		{
			return container.querySelector('input[name="' + fieldName + '"]');
		}

		return null;
	}

	getDataField()
	{
		return this.getFormField(this.getDataFieldName());
	}

	getDataSettingsField()
	{
		return this.getFormField(this.getDataSettingsFieldName());
	}

	getProductCount()
	{
		return this.products
			.filter(item => !item.isEmptyRow())
			.length
		;
	}

	initProducts()
	{
		const list = this.getSettingValue('items', []);

		for (let item of list)
		{
			const fields = {...item.fields};
			this.products.push(new Row(item.rowId, fields, this.getSettingValue('rowSettings', {}), this));
		}

		this.numerateRows();
		this.productsWasInitiated = true;

		this.updateTotalDataDelayed();
	}

	numerateRows()
	{
		this.products.forEach((product, index) => {
			product.setRowNumber(index + 1);
		})
	}

	getGrid(): ?BX.Main.Grid
	{
		return this.cache.remember('grid', () => {
			const gridId = this.getGridId();

			if (!Reflection.getClass('BX.Main.gridManager.getInstanceById'))
			{
				throw Error(`Cannot find grid with '${gridId}' id.`)
			}

			return BX.Main.gridManager.getInstanceById(gridId);
		});
	}

	initGridData()
	{
		const gridEditData = this.getSettingValue('templateGridEditData', null);
		if (gridEditData)
		{
			this.setGridEditData(gridEditData);
		}
	}

	paintColumns()
	{
		const paintedColumns = this.getSettingValue('paintedColumns', null);
		const grid = this.getGrid();
		if (grid && Type.isArray(paintedColumns))
		{
			paintedColumns.forEach((columnName) => {
				const rows = grid.getRows().getRows();
				rows.forEach((current) => {
					const cell = current.getCellById(columnName);
					if (cell)
					{
						Dom.addClass(cell, 'main-grid-cell-light-blue-background');
					}
				});
			});
		}
	}

	getGridEditData()
	{
		return this.getGrid().arParams.EDITABLE_DATA;
	}

	getColumnInfo(code: string)
	{
		return this.getGrid()?.arParams?.COLUMNS_ALL[code] || {};
	}

	setGridEditData(data: {})
	{
		this.getGrid().arParams.EDITABLE_DATA = data;
	}

	setOriginalTemplateEditData(data)
	{
		this.getGrid().arParams.EDITABLE_DATA[GRID_TEMPLATE_ROW] = data;
	}

	handleProductErrorsChange()
	{
		if (this.#childrenHasErrors())
		{
			this.controller.disableSaveButton();
		}
		else
		{
			this.controller.enableSaveButton();
		}
	}

	#childrenHasErrors()
	{
		return this.products
			.filter((product) => product.getModel().getErrorCollection().hasErrors())
			.length > 0
			;
	}

	handleFieldChange(event)
	{
		const row = event.target.closest('tr');
		if (row && row.hasAttribute('data-id'))
		{
			const product = this.getProductById(row.getAttribute('data-id'));
			if (product)
			{
				let fieldCode = event.target.getAttribute('data-name');
				if (!Type.isStringFilled(fieldCode))
				{
					const cell = event.target.closest('td');
					fieldCode = this.getFieldCodeByGridCell(row, cell);
				}

				if (fieldCode)
				{
					product.updateFieldByEvent(fieldCode, event);
				}
			}
		}
	}

	handleDropdownChange(event: BaseEvent)
	{
		const [dropdownId, , , , value] = event.getData();
		const regExp = new RegExp(this.getRowIdPrefix() + '([A-Za-z0-9]+)_(\\w+)_control', 'i');
		const matches = dropdownId.match(regExp);
		if (matches)
		{
			const [, rowId, fieldCode] = matches;
			const product = this.getProductById(rowId);
			if (product)
			{
				product.updateDropdownField(fieldCode, value);
			}
		}
	}

	getProductById(id: string): ?Row
	{
		const rowId = this.getRowIdPrefix() + id;

		return this.getProductByRowId(rowId);
	}

	getProductByRowId(rowId: string): ?Row
	{
		return this.products.find((row: Row) => {
			return row.getId() === rowId;
		});
	}

	getFieldCodeByGridCell(row: HTMLTableRowElement, cell: HTMLTableCellElement): ?string
	{
		if (!Type.isDomNode(row) || !Type.isDomNode(cell))
		{
			return null;
		}

		const grid = this.getGrid();
		if (grid)
		{
			const headRow = grid.getRows().getHeadFirstChild();
			const index = [...row.cells].indexOf(cell);

			return headRow.getCellNameByCellIndex(index);
		}

		return null;
	}

	addProductRow(anchorProduct: Row = null): string
	{
		const row = this.createGridProductRow();
		const newId = row.getId();

		if (anchorProduct)
		{
			const anchorRowNode = this.getGrid().getRows().getById(anchorProduct.getField('ID'))?.getNode();
			if (anchorRowNode)
			{
				anchorRowNode.parentNode.insertBefore(row.getNode(), anchorRowNode.nextSibling);
			}
		}

		this.initializeNewProductRow(newId, anchorProduct);
		this.getGrid().bindOnRowEvents();
		return newId;
	}

	handleProductRowAdd(): void
	{
		const id = this.addProductRow();
		this.focusProductSelector(id);
	}

	handleProductRowCreate(): void
	{
	}

	handleShowBarcodeSettingsPopup()
	{
		this.getSettingsPopup().show();
	}

	handleShowSettingsPopup()
	{
		this.getSettingsPopup().show();
	}

	destroySettingsPopup()
	{
		if (this.cache.has('settings-popup'))
		{
			this.cache.get('settings-popup').getPopup().destroy();
			this.cache.delete('settings-popup');
		}
	}

	getSettingsPopup()
	{
		return this.cache.remember('settings-popup', () => {
			return new SettingsPopup(
				this.getContainer().querySelector('.catalog-document-product-list-add-block-active [data-role="product-list-settings-button"]'),
				this.getSettingValue('popupSettings', []),
				this
			);
		});
	}

	getHintPopup(): HintPopup
	{
		return this.cache.remember('hint-popup', () => {
			return new HintPopup(this);
		});
	}

	createGridProductRow(): BX.Grid.Row
	{
		const newId = Text.getRandom();
		const originalTemplate = this.redefineTemplateEditData(newId);

		const grid = this.getGrid();
		let newRow;
		if (this.getSettingValue('newRowPosition') === 'bottom')
		{
			newRow = grid.appendRowEditor();
		}
		else
		{
			newRow = grid.prependRowEditor();
		}

		const newNode = newRow.getNode();

		if (Type.isDomNode(newNode))
		{
			newNode.setAttribute('data-id', newId);
			newRow.makeCountable();
		}

		if (originalTemplate)
		{
			this.setOriginalTemplateEditData(originalTemplate);
		}

		EventEmitter.emit('Grid::thereEditedRows', []);

		grid.adjustRows();
		grid.updateCounterDisplayed();
		grid.updateCounterSelected();

		return newRow;
	}

	handleDeleteRow(rowId, event: BaseEvent)
	{
		event.preventDefault();
		const row = this.getProductByRowId(rowId);
		if (row)
		{
			this.deleteRow(rowId);
		}
	}

	redefineTemplateEditData(newId: string)
	{
		const data = this.getGridEditData();
		const originalTemplateData = data[GRID_TEMPLATE_ROW];
		const customEditData = this.prepareCustomEditData(originalTemplateData, newId);

		this.setOriginalTemplateEditData({...originalTemplateData, ...customEditData})

		return originalTemplateData;
	}

	prepareCustomEditData(originalEditData, newId)
	{
		const customEditData = {};
		const templateIdMask = this.getSettingValue('templateIdMask', '');

		for (let i in originalEditData)
		{
			if (originalEditData.hasOwnProperty(i))
			{
				if (Type.isStringFilled(originalEditData[i]) && originalEditData[i].indexOf(templateIdMask) >= 0)
				{
					customEditData[i] = originalEditData[i].replace(
						new RegExp(templateIdMask, 'g'),
						newId
					);
				}
				else if (Type.isPlainObject(originalEditData[i]))
				{
					customEditData[i] = this.prepareCustomEditData(originalEditData[i], newId);
				}
				else
				{
					customEditData[i] = originalEditData[i];
				}
			}
		}

		return customEditData;
	}

	initializeNewProductRow(newId, anchorProduct: Row = null): Row
	{
		let fields = {};
		if (anchorProduct !== null)
		{
			fields = Object.assign(fields, anchorProduct?.getFields());
		}
		else
		{
			fields = {
				...this.getSettingValue('templateItemFields', {}),
				...{
					CURRENCY: this.getCurrencyId()
				}
			};
		}

		const rowId = this.getRowIdPrefix() + newId;
		fields.ID = newId;
		fields.ROW_ID = newId;
		if (Type.isObject(fields.IMAGE_INFO))
		{
			delete(fields.IMAGE_INFO.input);
		}
		const product = new Row(
			rowId,
			fields,
			this.getSettingValue('rowSettings', {}),
			this
		);

		if (anchorProduct instanceof Row)
		{
			this.products.splice(1 + this.products.indexOf(anchorProduct), 0, product);

			product.refreshFieldsLayout();
		}
		else if (this.getSettingValue('newRowPosition') === 'bottom')
		{
			this.products.push(product);
		}
		else
		{
			this.products.unshift(product);
		}

		this.refreshSortFields();
		this.numerateRows();

		product.updateUiCurrencyFields();
		this.updateTotalUiCurrency();

		return product;
	}

	getProductSelector(newId: string): ?ProductSelector
	{
		return this.getProductById(newId).getSelector();
	}

	focusProductSelector(newId: string): void
	{
		requestAnimationFrame(() => {
			this
				.getProductSelector(newId)
				?.searchInDialog()
				.focusName()
			;
		});
	}

	handleOnBeforeProductChange(event: BaseEvent)
	{
		const data = event.getData();
		const product = this.getProductByRowId(data.rowId);
		if (product)
		{
			this.getGrid().tableFade();
			product.resetExternalActions();
		}
	}

	handleOnProductChange(event: BaseEvent)
	{
		const data = event.getData();

		const productRow = this.getProductByRowId(data.rowId);
		if (productRow && data.fields)
		{
			delete data.fields.ID;
			productRow.setFields(data.fields);
			Object.keys(data.fields).forEach((key) => {
				productRow.updateFieldValue(key, data.fields[key]);
			});

			productRow.setField('IS_NEW', data.isNew ? 'Y' : 'N');
			productRow.getSelector()?.layout();
			productRow.getBarcodeSelector()?.layout();
			productRow.updateProductStoreValues();

			productRow.initHandlersForSelectors();
			productRow.executeExternalActions();
			this.getGrid().tableUnfade();
		}
		else
		{
			this.getGrid().tableUnfade();
		}
	}

	handleOnProductClear(event: BaseEvent)
	{
		const {selectorId, rowId} = event.getData();

		const product = this.getProductByRowId(rowId);
		if (product && product.getSelector().getId() === selectorId)
		{
			product.initHandlersForSelectors();
			product.setMeasure(this.getDefaultMeasure());
			product.changePurchasingPrice(0);
			product.changeBasePrice(0);
			product.changeAmount(0);
			product.updateUiStoreValues();
			product.updateProductStoreValues();
			product.changeBarcode('');
			product
				.getBarcodeSelector()
				?.setConfig('ENABLE_SEARCH', true)
				.layout()
			;
			product.executeExternalActions();
		}
	}

	compileProductData(): void
	{
		if (!this.isExistForm())
		{
			return;
		}
		this.initFormFields();

		const field = this.getDataField();
		const settingsField = this.getDataSettingsField();

		this.cleanProductRows();

		if (Type.isElementNode(field) && Type.isElementNode(settingsField))
		{
			field.value = this.prepareProductDataValue();
		}
	}

	prepareProductDataValue(): string
	{
		let productDataValue = '';

		if (this.getProductCount())
		{
			const productData = [];

			this.products.forEach((item) => {
				const itemFields = item.getFields();

				if (!/^[0-9]+$/.test(itemFields['ID']))
				{
					itemFields['ID'] = 0;
				}

				itemFields['CUSTOMIZED'] = 'Y';

				productData.push(itemFields);
			});

			productDataValue = JSON.stringify(productData);
		}

		return productDataValue;
	}

	/* actions */
	executeActions(actions)
	{
		if (!Type.isArrayFilled(actions))
		{
			return;
		}

		for (let item of actions)
		{
			if (
				!Type.isPlainObject(item)
				|| !Type.isStringFilled(item.type)
			)
			{
				continue;
			}

			switch (item.type)
			{
				case this.actions.productChange:
					this.actionSendProductChange(item);
					break;

				case this.actions.productListChanged:
					this.actionSendProductListChanged();
					break;

				case this.actions.updateTotal:
					this.actionUpdateTotalData();
					break;
			}
		}
	}

	actionSendProductChange(item)
	{
		if (!Type.isStringFilled(item.id))
		{
			return;
		}

		const product = this.getProductByRowId(item.id);
		if (!product)
		{
			return;
		}

		// EventEmitter.emit(this, 'ProductList::onChangeFields', {
		// 	rowId: item.id,
		// 	productId: product.getField('PRODUCT_ID'),
		// 	fields: this.getProductByRowId(item.id).getCatalogFields()
		// });

		if (this.controller)
		{
			this.controller.productChange();
		}
	}

	actionSendProductListChanged(disableSaveButton: boolean = false): void
	{
		if (this.controller)
		{
			this.controller.productChange(disableSaveButton);
		}
	}

	actionUpdateListField(item)
	{
		if (!Type.isStringFilled(item.field) || !('value' in item))
		{
			return;
		}

		this.updateFieldForList = item.field;

		for (let row of this.products)
		{
			row.updateFieldByName(item.field, item.value);
		}

		this.updateFieldForList = null;
	}

	actionUpdateTotalData(options = {})
	{
		this.updateTotalDataDelayedHandler(options);
	}

	/* actions finish */

	updateTotalDataDelayed(options = {})
	{
		let totalCost = 0;
		const field = this.getSettingValue('totalCalculationSumField', 'PURCHASING_PRICE');
		this.products.forEach(item => totalCost += Text.toNumber(item.getField(field)) * Text.toNumber(item.getField('AMOUNT')));
		this.setTotalData({totalCost});
	}

	getProductsFields(fields: Array = [])
	{
		const productFields = [];

		for (let item of this.products)
		{
			productFields.push(item.getFields(fields));
		}

		return productFields;
	}

	setTotalData(data)
	{
		const item = BX(this.getSettingValue('totalBlockContainerId', null));
		if (Type.isElementNode(item))
		{
			const currencyId = this.getCurrencyId();
			const list = ['totalCost'];

			for (const id of list)
			{
				const row = item.querySelector('[data-total="' + id + '"]');
				if (Type.isElementNode(row) && (id in data))
				{
					row.innerHTML = CurrencyCore.currencyFormat(data[id], currencyId, false);
				}
			}
		}

		this.controller?.setTotal(data);
	}

	/* action tools finish */

	/* ajax tools */
	// ajaxRequest(action, data)
	// {
	// 	if (!Type.isPlainObject(data.options))
	// 	{
	// 		data.options = {};
	// 	}
	// 	data.options.ACTION = action;
	// 	ajax.runComponentAction(
	// 		this.getComponentName(),
	// 		action,
	// 		{
	// 			mode: 'class',
	// 			signedParameters: this.getSignedParameters(),
	// 			data: data
	// 		}
	// 	).then(
	// 		(response) => this.ajaxResultSuccess(response, data.options),
	// 		(response) => this.ajaxResultFailure(response)
	// 	);
	// }
	//
	// ajaxResultSuccess(response, requestOptions)
	// {
	// 	if (!this.ajaxResultCommonCheck(response))
	// 	{
	// 		return;
	// 	}
	//
	// 	switch (response.data.action)
	// 	{
	// 		case 'calculateTotalData':
	// 			// if (Type.isPlainObject(response.data.result))
	// 			// {
	// 			// 	this.setTotalData(response.data.result, requestOptions);
	// 			// }
	//
	// 			break;
	// 		case 'calculateProductPrices':
	// 			if (Type.isPlainObject(response.data.result))
	// 			{
	// 				this.onCalculatePricesResponse(response.data.result);
	// 			}
	//
	// 			break;
	// 	}
	// }

	// ajaxResultFailure(response)
	// {
	//
	// }

	ajaxResultCommonCheck(responce)
	{
		if (!Type.isPlainObject(responce))
		{
			return false;
		}

		if (!Type.isStringFilled(responce.status))
		{
			return false;
		}

		if (responce.status !== 'success')
		{
			return false;
		}

		if (!Type.isPlainObject(responce.data))
		{
			return false;
		}

		if (!Type.isStringFilled(responce.data.action))
		{
			return false;
		}

		// noinspection RedundantIfStatementJS
		if (!('result' in responce.data))
		{
			return false;
		}

		return true;
	}

	deleteRow(row: Row): void
	{
		const gridRow = this.getGrid().getRows().getById(row.getField('ID'));
		if (gridRow)
		{
			Dom.remove(gridRow.getNode());
			this.getGrid().getRows().reset();
		}

		const index = this.products.indexOf(row);
		if (index > -1)
		{
			this.products.splice(index, 1);
			this.refreshSortFields();
			this.numerateRows();
		}

		EventEmitter.emit('Grid::thereEditedRows', []);

		this.addFirstRowIfEmpty();
		this.executeActions([
			{type: this.actions.productListChanged},
			{type: this.actions.updateTotal}
		]);
	}

	copyRow(row: Row): void
	{
		this.addProductRow(row)
		this.refreshSortFields();
		this.numerateRows();

		EventEmitter.emit('Grid::thereEditedRows', []);

		this.executeActions([
			{type: this.actions.productListChanged},
			{type: this.actions.updateTotal}
		]);
	}

	cleanProductRows(): void
	{
		this.products
			.filter(item => item.isEmptyRow())
			.forEach((row) => this.deleteRow(row))
		;
	}

	resortProductsByIds(ids: Array): boolean
	{
		let changed = false;

		if (Type.isArrayFilled(ids))
		{
			this.products.sort((a, b) => {
				if (ids.indexOf(a.getField('ID')) > ids.indexOf(b.getField('ID')))
				{
					return 1;
				}

				changed = true;

				return -1;
			});
		}

		return changed;
	}

	refreshSortFields(): void
	{
		this.products.forEach((item, index) => item.setField('SORT', (index + 1) * 10, false));
	}

	handleOnTabShow(): void
	{
		EventEmitter.emit('onDemandRecalculateWrapper');
	}

	closeBarcodeSpotlights(): void
	{
		this.products.forEach((product) => {
			product.getBarcodeSelector()?.removeSpotlight();
		})

		this.setSettingValue('showBarcodeSpotlightInfo',false);
	}

	closeBarcodeQrAuths(): void
	{
		this.products.forEach((product) => {
			product.getBarcodeSelector()?.removeQrAuth();
		})

		this.setSettingValue('showBarcodeQrAuth',false);
	}

	validate(): Array
	{
		if (this.getProductCount() === 0)
		{
			return [Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_IS_EMPTY')];
		}

		let errorsArray = [];

		this.products.forEach((product) => {
			errorsArray = errorsArray.concat(product.validate());
		});

		return errorsArray;
	}
}
