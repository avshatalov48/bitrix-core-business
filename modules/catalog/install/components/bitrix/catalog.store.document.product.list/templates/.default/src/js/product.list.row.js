import {Cache, Dom, Event, Loc, Reflection, Runtime, Tag, Text, Type} from 'main.core';
import {Editor} from './product.list.editor';
import {CurrencyCore} from 'currency.currency-core';
import 'ui.hint';
import HintPopup from './hint.popup';
import {ProductModel} from "catalog.product-model";
import {BaseEvent, EventEmitter} from "main.core.events";
import {ProductSelector} from "catalog.product-selector";
import {StoreSelector} from "catalog.store-selector";
import {PopupMenu} from "main.popup";
import {PriceCalculator} from "./price.calculator";
import {AccessDeniedInput} from './access.denied.input';

type Action = {
	type: string,
	field?: string,
	value?: string,
};

type Settings = {}

const MODE_EDIT = 'EDIT';
const MODE_SET = 'SET';

export class Row
{
	id: ?string;
	settings: Object;
	editor: ?Editor
	model: ?ProductModel
	fields: Object = {};
	mainSelector: ?ProductSelector;
	barcodeSelector: ?ProductSelector;
	storeSelectors: Array<StoreSelector> = [];
	externalActions: Array<Action> = [];
	cache = new Cache.MemoryCache();
	modeChanges = {
		EDIT: MODE_EDIT,
		SET: MODE_SET,
	};
	validatingFields: Map<string, boolean> = new Map();
	realValues: ?Array;

	constructor(id: string, fields: Object, settings: Settings, editor: Editor): void
	{
		this.setId(id);
		this.setSettings(settings);
		this.setEditor(editor);
		this.setModel(fields, settings);
		this.initFields(fields);
		this.#initSelector();
		this.#initBarcode();
		// this.#initPriceExtra();
		this.#initStoreSelector(this.getSettingValue('storeHeaderMap', {}));
		this.#initActions();
		this.#hideFields();
		requestAnimationFrame(this.initHandlers.bind(this));
	}

	getNode(): ?HTMLElement
	{
		return this.cache.remember('node', () => {
			const rowId = this.getField('ID', 0);

			return this.getEditorContainer().querySelector('[data-id="' + rowId + '"]');
		});
	}

	getSelector(): ?ProductSelector
	{
		return this.mainSelector;
	}

	getBarcodeSelector(): ?ProductSelector
	{
		return this.barcodeSelector;
	}

	getId(): string
	{
		return this.id;
	}

	setId(id: string): void
	{
		this.id = id;
	}

	getSettings()
	{
		return this.settings;
	}

	setSettings(settings: Settings): void
	{
		this.settings = Type.isPlainObject(settings) ? settings : {};
	}

	getSettingValue(name, defaultValue)
	{
		return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultValue;
	}

	setSettingValue(name, value): void
	{
		this.settings[name] = value;
	}

	setEditor(editor: Editor): void
	{
		this.editor = editor;
	}

	getEditor(): Editor
	{
		return this.editor;
	}

	getEditorContainer(): HTMLElement
	{
		return this.getEditor().getContainer();
	}

	getHintPopup(): HintPopup
	{
		return this.getEditor().getHintPopup();
	}

	initHandlers()
	{
		const editor = this.getEditor();

		this.getNode().querySelectorAll('input').forEach((node) => {
			Event.bind(node, 'input', editor.changeProductFieldHandler);
			Event.bind(node, 'change', editor.changeProductFieldHandler);
			// disable drag-n-drop events for text fields
			Event.bind(node, 'mousedown', (event) => event.stopPropagation());
			Event.bind(node, 'blur', editor.blurProductFieldHandler)
		});
		this.getNode().querySelectorAll('select').forEach((node) => {
			Event.bind(node, 'change', editor.changeProductFieldHandler);
			// disable drag-n-drop events for select fields
			Event.bind(node, 'mousedown', (event) => event.stopPropagation());
		});
	}

	initHandlersForSelectors()
	{
		const editor = this.getEditor();

		let selectorNames = ['MAIN_INFO', 'BARCODE_INFO'];
		const storeFields = this.getSettingValue('storeHeaderMap', {});
		selectorNames = [...selectorNames, ...Object.keys(storeFields)];

		selectorNames.forEach((name) => {
			this.getNode().querySelectorAll('[data-name="'+ name +'"] input[type="text"]').forEach(node => {
				Event.bind(node, 'input', editor.changeProductFieldHandler);
				Event.bind(node, 'change', editor.changeProductFieldHandler);
				// disable drag-n-drop events for select fields
				Event.bind(node, 'mousedown', (event) => event.stopPropagation());
			});
		});
	}

	#initActions()
	{
		if (this.getEditor().isReadOnly() || this.getField('EDITABLE') === false)
		{
			return;
		}

		const actionCellContentContainer = this.getNode().querySelector('.main-grid-cell-action .main-grid-cell-content');
		if (Type.isDomNode(actionCellContentContainer))
		{
			const actionsButton = Tag.render`
				<a
					href="#"
					class="main-grid-row-action-button"
				></a>
			`;

			Event.bind(actionsButton, 'click', (event) => {
				const menuItems = [
					{
						text: Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_COPY_ACTION'),
						onclick: this.handleCopyAction.bind(this),
					},
					{
						text: Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_DELETE_ACTION'),
						onclick: this.handleDeleteAction.bind(this),
					}
				];

				PopupMenu.show({
					id: this.getId() + '_actions_popup',
					bindElement: actionsButton,
					items: menuItems
				});

				event.preventDefault();
				event.stopPropagation();
			});

			Dom.append(actionsButton, actionCellContentContainer);
		}
	}

	#initSelector()
	{
		const selectorOptions = {
			iblockId: this.model.getIblockId(),
			basePriceId: this.model.getBasePriceId(),
			currency: this.model.getCurrency(),
			model: this.model,
			config: {
				ENABLE_SEARCH: true,
				IS_ALLOWED_CREATION_PRODUCT: this.getSettingValue('isAllowedCreationProduct', true),
				ENABLE_IMAGE_INPUT: true,
				ROLLBACK_INPUT_AFTER_CANCEL: true,
				ENABLE_INPUT_DETAIL_LINK: true,
				ROW_ID: this.getId(),
				ENABLE_SKU_SELECTION: true,
				ENABLE_EMPTY_PRODUCT_ERROR: true,
				RESTRICTED_PRODUCT_TYPES: this.getEditor().getRestrictedProductTypes(),
				URL_BUILDER_CONTEXT: this.editor.getSettingValue('productUrlBuilderContext'),
			},
			mode: ProductSelector.MODE_EDIT,
		};

		this.mainSelector = new ProductSelector('catalog_document_grid_' + this.getId(), selectorOptions);
		const mainInfoNode = this.getNode().querySelector('[data-name="MAIN_INFO"]');
		if (mainInfoNode)
		{
			const numberSelector = mainInfoNode.querySelector('.main-grid-row-number');
			if (!Type.isDomNode(numberSelector))
			{
				mainInfoNode.appendChild(Tag.render`<div class="main-grid-row-number"></div>`);
			}

			let selectorWrapper =  mainInfoNode.querySelector('.main-grid-row-product-selector');
			if (!Type.isDomNode(selectorWrapper))
			{
				selectorWrapper = Tag.render`<div class="main-grid-row-product-selector"></div>`
				mainInfoNode.appendChild(selectorWrapper)
			}
			this.mainSelector.renderTo(selectorWrapper);
		}


		EventEmitter.subscribe(
			this.mainSelector,
			'onBeforeCreate',
			this.#handleBeforeCreateProduct.bind(this)
		);
	}

	#initBarcode()
	{
		const selectorOptions = {
			iblockId: this.model.getIblockId(),
			basePriceId: this.model.getBasePriceId(),
			currency: this.model.getCurrency(),
			model: this.model,
			inputFieldName: 'BARCODE',
			type: ProductSelector.INPUT_FIELD_BARCODE,
			config: {
				ENABLE_SEARCH: true,
				IS_ALLOWED_CREATION_PRODUCT: this.getSettingValue('isAllowedCreationProduct', true),
				ENABLE_INFO_SPOTLIGHT: this.editor.getSettingValue('showBarcodeSpotlightInfo', true),
				ENABLE_BARCODE_QR_AUTH: this.editor.getSettingValue('showBarcodeQrAuth', true),
				IS_INSTALLED_MOBILE_APP: this.editor.getSettingValue('isInstalledMobileApp', null),
				ENABLE_IMAGE_INPUT: false,
				ROLLBACK_INPUT_AFTER_CANCEL: true,
				ENABLE_INPUT_DETAIL_LINK: false,
				ROW_ID: this.getId(),
				ENABLE_SKU_SELECTION: false,
				ENABLE_SKU_TREE: false,
				ENABLE_EMPTY_PRODUCT_ERROR: false,
				RESTRICTED_PRODUCT_TYPES: this.getEditor().getRestrictedProductTypes(),
			},
			mode: ProductSelector.MODE_EDIT,
			scannerToken: this.getEditor().scannerToken,
		};

		this.barcodeSelector = new ProductSelector('catalog_document_grid_' + this.getId() + '_barcode', selectorOptions);

		EventEmitter.subscribe(
			this.barcodeSelector,
			'onBeforeCreate',
			this.#handleBeforeCreateProduct.bind(this)
		);

		EventEmitter.subscribe(
			this.barcodeSelector,
			'onSpotlightClose',
			this.#handleSpotlightClose.bind(this)
		);

		EventEmitter.subscribe(
			this.barcodeSelector,
			'onBarcodeQrClose',
			this.#handleBarcodeQrClose.bind(this)
		);

		EventEmitter.subscribe(
			this.barcodeSelector,
			'onBarcodeScannerInstallChecked',
			this.#handleBarcodeScannerInstallCheck.bind(this)
		);

		EventEmitter.subscribe(
			this.barcodeSelector,
			'onBarcodeChange',
			this.#handleBarcodeChange.bind(this)
		);

		this.layoutBarcode();
	}

	layoutBarcode(): void
	{
		const barcodeWrapper = this.getNode().querySelector('[data-name="BARCODE_INFO"]');
		if (this.barcodeSelector && barcodeWrapper)
		{
			barcodeWrapper.innerHTML = '';

			if (this.#needBarcode())
			{
				this.barcodeSelector.renderTo(barcodeWrapper);
			}
		}
	}

	#initPriceExtra()
	{
		const node = this.getNode().querySelector('[data-name="BASE_PRICE"]');
		if (!Type.isDomNode(node))
		{
			return;
		}

		const oldExtraNode = node.querySelector('.catalog-store-extra-price');
		if (Type.isDomNode(oldExtraNode))
		{
			oldExtraNode.parentNode.removeChild(oldExtraNode);
		}

		const extraValue = this.getField('BASE_PRICE_EXTRA') ?? '';
		const inputValue = Tag.render`
			<div>
				<input
					placeholder="-"
					class="catalog-store-extra-price"
					data-name="BASE_PRICE_EXTRA"
					value="${extraValue}"
				/>
			</div>
		`;

		const extraMeasureValue =
			this.getField('BASE_PRICE_EXTRA_RATE') === PriceCalculator.EXTRA_TYPE_MONETARY
				? this.getEditor().getCurrencyText()
				: '%'
		;

		const measureValue = Tag.render`
			<div class="main-grid-editor main-grid-editor-money-currency">
				<span class="main-dropdown-inner" data-name="BASE_PRICE_EXTRA_RATE">${extraMeasureValue}</span>
			</div>
		`;

		Event.bind(measureValue, 'click', () => {
			const menuItems = [
				{
					text: '%',
					onclick: this.handleSelectExtraPriceType.bind(this),
					type: PriceCalculator.EXTRA_TYPE_PERCENTAGE,
				},
				{
					text: this.getEditor().getCurrencyText(),
					onclick: this.handleSelectExtraPriceType.bind(this),
					type: PriceCalculator.EXTRA_TYPE_MONETARY,
				}
			];

			PopupMenu.show({
				id: this.getId() + '_extra_type_popup',
				bindElement: measureValue,
				items: menuItems
			});
		});

		const extraNode = Tag.render`
			<div class="main-grid-editor catalog-store-extra-price">
				${inputValue}
				${measureValue}
			</div>
		`;

		node.appendChild(extraNode);
	}

	#initStoreSelector(fieldNames: {})
	{
		Object.keys(fieldNames).forEach( rowName => {
			const selectorOptions = {
				inputFieldId: fieldNames[rowName],
				inputFieldTitle: fieldNames[rowName] + '_TITLE',
				isDisabledEmpty: true,
				config: {
					ENABLE_SEARCH: true,
					ENABLE_INPUT_DETAIL_LINK: false,
					ROW_ID: this.getId(),
				},
				mode: StoreSelector.MODE_EDIT,
				model: this.model,
			};

			const storeSelector = new StoreSelector(this.getId() + '_' + rowName, selectorOptions);

			EventEmitter.subscribe(
				storeSelector,
				'onChange',
				Runtime.debounce(this.#onStoreFieldChange.bind(this), 500, this)
			);

			EventEmitter.subscribe(
				storeSelector,
				'onClear',
				Runtime.debounce(this.#onStoreFieldChange.bind(this), 500, this)
			);

			this.storeSelectors.push(storeSelector);
		});

		this.layoutStoreSelector(fieldNames);
	}

	layoutStoreSelector(fieldNames: {})
	{
		Object.keys(fieldNames).forEach(rowName => {
			const selectorId = this.getId() + '_' + rowName;

			this.storeSelectors.forEach((selector) => {
				if (selector.getId() === selectorId)
				{
					const storeWrapper = this.getNode().querySelector('[data-name="' + rowName + '"]');
					if (storeWrapper)
					{
						storeWrapper.innerHTML = '';

						if (this.#needInventory())
						{
							selector.renderTo(storeWrapper);
						}
					}
				}
			});
		});
	}

	#onStoreFieldChange(event)
	{
		const data = event.getData();
		data.fields.forEach((item) => {
			this.updateField(item.NAME, item.VALUE);
		});
	}

	setRowNumber(number)
	{
		this.getNode().querySelectorAll('.main-grid-row-number').forEach(node => {
			node.textContent = number + '.';
		});
	}

	getFields(fields: Array = [])
	{
		let result;

		if (!Type.isArrayFilled(fields))
		{
			result = Runtime.clone(this.fields);
		}
		else
		{
			result = {};

			for (let fieldName of fields)
			{
				result[fieldName] = this.getField(fieldName);
			}
		}

		// merge with real values
		const realValues = this.#getRealValues();
		for (const fieldName in realValues)
		{
			if (
				Object.hasOwnProperty.call(realValues, fieldName)
				&& Object.hasOwnProperty.call(result, fieldName)
			)
			{
				result[fieldName] = realValues[fieldName];
			}
		}

		return result;
	}

	/**
	 * Get real values field.
	 *
	 * Stores the real values of rows that are hidden due to lack of user access.
	 *
	 * @returns
	 */
	#getRealValues()
	{
		if (!!this.realValues)
		{
			return this.realValues
		}

		try
		{
			const value = this.getField('REAL_VALUES');
			if (value)
			{
				const parsedValue = JSON.parse(atob(value));
				if (Type.isPlainObject(parsedValue))
				{
					this.realValues = parsedValue;
				}
			}
		}
		catch (e)
		{
			console.error('Cannot parse REAL_VALUE: ' + e.getMessage());
		}

		return this.realValues;
	}

	initFields(fields: Object): void
	{
		this.getModel().initFields(fields, false);
		this.setFields(fields);
	}

	setFields(fields: Object): void
	{
		for (let name in fields)
		{
			if (fields.hasOwnProperty(name))
			{
				this.setField(name, fields[name]);
			}
		}
	}

	getField(name: string, defaultValue)
	{
		if (name !== 'REAL_VALUES')
		{
			const realValues = this.#getRealValues();
			if (realValues && Object.hasOwnProperty.call(realValues, name))
			{
				return realValues[name];
			}
		}

		return this.fields.hasOwnProperty(name) ? this.fields[name] : defaultValue;
	}

	setField(name: string, value, changeModel: boolean = true): void
	{
		this.fields[name] = value;

		if (changeModel)
		{
			this.getModel().setField(name, value);
		}
	}

	getUiFieldId(field): string
	{
		return this.getId() + '_' + field;
	}

	getBasePrice(): number
	{
		return this.getField('BASE_PRICE', 0);
	}

	getAmount(): number
	{
		return this.getField('AMOUNT', 1);
	}

	updateFieldByEvent(fieldCode: string, event: UIEvent): void
	{
		const target = event.target;
		const value = target.type === 'checkbox' ? target.checked : target.value;
		const mode = (event.type === 'input' || event.type === 'change') ? MODE_EDIT : MODE_SET;

		this.updateField(fieldCode, value, mode);
	}

	updateDropdownField(fieldCode: string, value: any): void
	{
		this.updateField(fieldCode, value, MODE_EDIT);
	}

	updateField(fieldCode: string, value: any, mode = MODE_SET): void
	{
		this.resetExternalActions();
		this.updateFieldValue(fieldCode, value, mode);
		this.executeExternalActions();
	}

	updateFieldValue(code: string, value, mode = MODE_SET): void
	{
		switch (code)
		{
			case 'SKU_ID':
				this.changeProductId(value);
				break;

			case 'BASE_PRICE':
				this.changeBasePrice(value, mode);
				break;

			// case 'BASE_PRICE_EXTRA':
			// 	this.changeExtra(value, mode);
			// 	break;

			case 'PURCHASING_PRICE':
				this.changePurchasingPrice(value, mode);
				break;

			case 'AMOUNT':
				this.changeAmount(value, mode);
				break;

			case 'MEASURE_CODE':
				this.changeMeasureCode(value, mode);
				break;

			case 'BARCODE':
				this.changeBarcode(value, mode);
				break;
			case 'STORE_FROM':
			case 'STORE_TO':
				this.changeStore(value, code);
				break;
			case 'STORE_FROM_TITLE':
			case 'STORE_TO_TITLE':
				this.changeStoreName(value, code);
				break;
			case 'NAME':
			case 'MAIN_INFO':
				this.changeProductName(value, mode);
				break;

			case 'SORT':
				this.changeSort(value, mode);
				break;
		}
	}

	updateFieldByName(field, value)
	{
		switch (field)
		{
			case 'TAX_INCLUDED':
				this.setTaxIncluded(value);
				break;
		}
	}

	changeProductId(value)
	{
		const preparedValue = this.parseInt(value);

		this.setProductId(preparedValue);
	}

	handleCopyAction(event, menuItem)
	{
		this.getEditor()?.copyRow(this);
		const menu = menuItem.getMenuWindow();
		if (menu)
		{
			menu.destroy();
		}
	}

	handleDeleteAction(event, menuItem)
	{
		this.getEditor()?.deleteRow(this);
		const menu = menuItem.getMenuWindow();
		if (menu)
		{
			menu.destroy();
		}
		this.unsubscribeEvents();

		this.#handleProductErrorsChange();
	}

	unsubscribeEvents()
	{
		this.getBarcodeSelector().unsubscribeEvents();
	}

	handleSelectExtraPriceType(event, menuItem)
	{
		this.changeExtraType(menuItem.type, MODE_EDIT);
		const menu = menuItem.getMenuWindow();
		if (menu)
		{
			menu.destroy();
		}
	}

	#getCalculator()
	{
		const extra =
			Type.isNumber(this.getModel().getField('BASE_PRICE_EXTRA'))
				? this.getModel().getField('BASE_PRICE_EXTRA')
				: null
		;

		return new PriceCalculator({
			basePrice: Text.toNumber(this.getModel().getField('PURCHASING_PRICE')),
			finalPrice: Text.toNumber(this.getModel().getField('BASE_PRICE')),
			extra,
			extraType: Text.toNumber(this.getModel().getField('BASE_PRICE_EXTRA_RATE')),
		});
	}

	changeExtraType(value, mode = MODE_SET)
	{
		let text = '%';
		if (value === PriceCalculator.EXTRA_TYPE_MONETARY)
		{
			text = this.getEditor().getCurrencyText();
		}
		else
		{
			value = PriceCalculator.EXTRA_TYPE_PERCENTAGE;
		}

		if (value === this.getField('BASE_PRICE_EXTRA_RATE'))
		{
			return;
		}

		if (mode === MODE_EDIT)
		{
			const calculator =
				this.#getCalculator()
					.calculateExtraType(value)
			;

			this.changeExtra(calculator.getExtra());
			this.changeBasePrice(calculator.getFinalPrice());
		}

		const node = this.getNode().querySelector('[data-name="BASE_PRICE_EXTRA_RATE"]');
		if (Type.isDomNode(node))
		{
			node.innerHTML = text;
		}

		this.setField('BASE_PRICE_EXTRA_RATE', value);
	}

	changeExtra(value, mode = MODE_SET)
	{
		const preparedValue = (Type.isNil(value) || value === '') ? null : this.parseFloat(value, this.getPricePrecision());
		this.setField('BASE_PRICE_EXTRA', preparedValue);

		if (preparedValue === null)
		{
			return;
		}

		if (mode === MODE_EDIT)
		{
			const calculator =
				this.#getCalculator()
					.calculateExtra(preparedValue)
			;

			this.changeBasePrice(calculator.getFinalPrice());
		}
		else
		{
			const node = this.getNode().querySelector('[data-name="BASE_PRICE_EXTRA"]');
			if (Type.isDomNode(node))
			{
				node.value = preparedValue;
			}
		}
	}

	changeBasePrice(value, mode = MODE_SET)
	{
		const preparedValue = this.parseFloat(value, this.getPricePrecision());
		this.setBasePrice(preparedValue, mode);

		// if (mode === MODE_EDIT)
		// {
		// 	const calculator =
		// 		this.#getCalculator()
		// 			.calculateFinalPrice(preparedValue)
		// 	;
		//
		// 	this.changeExtra(calculator.getExtra());
		// 	this.changeExtraType(calculator.getExtraType());
		// }
	}

	changePurchasingPrice(value, mode = MODE_SET)
	{
		if (this.#isPurchasingPriceAccessDenied())
		{
			return;
		}

		const preparedValue = this.parseFloat(value, this.getPricePrecision());
		this.setPurchasingPrice(preparedValue, mode);

		// const currentExtra = this.getField('BASE_PRICE_EXTRA');
		// if (mode === MODE_EDIT && !Type.isNil(currentExtra) && currentExtra !== '')
		// {
		// 	const calculator =
		// 		this.#getCalculator()
		// 			.calculateBasePrice(preparedValue)
		// 	;
		//
		// 	this.changeBasePrice(calculator.getFinalPrice());
		// }
	}

	changeAmount(value, mode = MODE_SET)
	{
		const preparedValue = this.parseFloat(value, this.getQuantityPrecision());
		this.setAmount(preparedValue, mode);
	}

	changeMeasureCode(value: string, mode = MODE_SET): void
	{
		this
			.getEditor()
			.getMeasures()
			.filter((item) => item.CODE === value)
			.forEach((item) => this.setMeasure(item, mode))
		;
	}

	changeBarcode(value, mode = MODE_SET)
	{
		const preparedValue = value.toString();
		const isChangedValue = this.getField('BARCODE') !== preparedValue;

		if (isChangedValue && mode === MODE_SET)
		{
			this.setField('BARCODE', preparedValue);
			this.setField('DOC_BARCODE', preparedValue);
			this.addActionProductChange();
		}
		else if (mode === MODE_EDIT)
		{
			this.setField('DOC_BARCODE', preparedValue);
			this.addActionProductChange();
		}
	}

	changeStore(value: number, code: string)
	{
		const preparedValue = Text.toNumber(value);
		const isChangedValue = this.getField(code) !== preparedValue;

		if (isChangedValue)
		{
			this.setField(code, preparedValue);
			this.setStoreAmount(value, code);
			this.layoutStoreSelector(this.getSettingValue('storeHeaderMap', {}));
			this.addActionProductChange();
		}
	}

	changeStoreName(value: number, code: string)
	{
		const preparedValue = value.toString();
		this.setField(code, preparedValue);
		this.addActionProductChange();
	}

	changeProductName(value, mode = MODE_SET)
	{
		const preparedValue = value.toString();
		const isChangedValue = this.getField('NAME') !== preparedValue;

		if (isChangedValue && mode === MODE_SET)
		{
			this.setField('NAME', preparedValue);
			this.addActionProductChange();
		}
	}

	changeSort(value, mode = MODE_SET)
	{
		const preparedValue = this.parseInt(value);

		if (mode === MODE_SET)
		{
			this.setField('SORT', preparedValue);
		}

		const isChangedValue = this.getField('SORT') !== preparedValue;

		if (isChangedValue)
		{
			this.addActionProductChange();
		}
	}

	refreshFieldsLayout(exceptFields: Array<string> = []): void
	{
		for (let field in this.fields)
		{
			if (this.fields.hasOwnProperty(field) && !exceptFields.includes(field))
			{
				this.updateUiField(field, this.fields[field]);
			}
		}
		this.updateUiMeasure(
			this.getField('MEASURE_CODE'),
			this.getField('MEASURE_NAME')
		)
		this.getSelector()?.reloadFileInput();
		this.getSelector()?.layout();
		this.getBarcodeSelector()?.layout();
		this.updateUiStoreValues();
	}

	setModel(fields: {} = {}, settings: Settings = {}): void
	{
		const selectorId = 'catalog_document_grid_' + this.getId();
		if (selectorId)
		{
			const model = ProductModel.getById(selectorId);
			if (model)
			{
				this.model = model;
			}
		}

		if (!(this.model instanceof ProductModel))
		{
			this.model = new ProductModel({
				id: selectorId,
				currency: this.getEditor().getCurrencyId(),
				iblockId: fields['IBLOCK_ID'],
				basePriceId: fields['BASE_PRICE_ID'],
				skuTree: Type.isStringFilled(fields['SKU_TREE']) ? JSON.parse(fields['SKU_TREE']) : null,
				storeMap: fields['STORE_AMOUNT_MAP'],
				fields,
			});

			if (Type.isObject(fields['IMAGE_INFO']))
			{
				this.model.getImageCollection().setPreview(fields['IMAGE_INFO']['preview']);
				this.model.getImageCollection().setEditInput(fields['IMAGE_INFO']['input']);
				this.model.getImageCollection().setMorePhotoValues(fields['IMAGE_INFO']['values']);
			}

			if (!Type.isNil(fields['DETAIL_URL']))
			{
				this.model.setDetailPath(fields['DETAIL_URL']);
			}
		}

		EventEmitter.subscribe(
			this.model,
			'onErrorsChange',
			Runtime.debounce(this.#handleProductErrorsChange, 500, this)
		);

		EventEmitter.subscribe(
			this.model,
			'onChangeStoreData',
			this.updateUiStoreValues.bind(this)
		);
	}

	getModel(): ?ProductModel
	{
		return this.model;
	}

	#handleProductErrorsChange()
	{
		const errors = this.getModel().getErrorCollection().getErrors();
		for (const code in errors)
		{
			if (code === ProductSelector.ErrorCodes.NOT_SELECTED_PRODUCT || code === StoreSelector.ErrorCodes.NOT_SELECTED_STORE)
			{
				this.getSelector().layoutErrors();
			}
		}

		this.getEditor().handleProductErrorsChange();
	}

	#handleBeforeCreateProduct(event: BaseEvent)
	{
		const {model} = event.getData();
		model.setField('BARCODE', this.barcodeSelector.getNameInputFilledValue());
		model.setField('NAME', this.mainSelector.getNameInputFilledValue());
	}

	#handleSpotlightClose(event: BaseEvent): void
	{
		this.editor.closeBarcodeSpotlights();
	}

	#handleBarcodeQrClose(event: BaseEvent): void
	{
		this.editor.closeBarcodeQrAuths();
	}

	#handleBarcodeScannerInstallCheck(event: BaseEvent): void
	{
		this.editor.enableSendBarcodeMobilePush();
	}

	#handleBarcodeChange(event: BaseEvent): void
	{
		const {value} = event.getData();
		this.changeBarcode(value, MODE_EDIT);
	}

	setProductId(value)
	{
		const isChangedValue = this.getField('PRODUCT_ID') !== value;
		if (isChangedValue)
		{
			this.setField('PRODUCT_ID', value);
			this.setField('SKU_ID', value);

			this.updateUiStoreValues();

			this.addActionProductChange();
			this.addActionUpdateTotal();

			this.#hidePurchasingPrice();
		}
	}

	setBasePrice(value, mode = MODE_SET)
	{
		// price can't be less than zero
		value = Math.max(value, 0);
		if (mode === MODE_SET)
		{
			this.updateUiField('BASE_PRICE', value.toFixed(this.getPricePrecision()));
		}
		this.setField('BASE_PRICE', value);
		this.addActionProductChange();
		this.addActionUpdateTotal();

		this.updateRowTotalPrice();
	}

	updateRowTotalPrice()
	{
		const field = this.getEditor().getSettingValue('totalCalculationSumField', 'PURCHASING_PRICE');
		let value = this.getAmount() * this.getField(field, 0);
		value = Math.max(value, 0);

		this.setField('TOTAL_PRICE', value);
		this.updateUiField('TOTAL_PRICE', value.toFixed(this.getPricePrecision()));
	}

	updateProductStoreValues()
	{
		this.storeSelectors.forEach((selector) => {
			selector.setProductId(this.getModel().getSkuId());
		});
	}

	updateUiStoreValues()
	{
		const storeHeaderMap = this.getSettingValue('storeHeaderMap', {});
		Object.keys(storeHeaderMap).forEach((key) => {
			const fieldName = storeHeaderMap[key];
			let value = this.getField(fieldName);
			if (fieldName === 'STORE_FROM')
			{
				const currentAmount = this.model.getStoreCollection().getStoreAmount(value);
				if (currentAmount <= 0)
				{
					const maxStore = this.model.getStoreCollection().getMaxFilledStore();
					const storeSelector = StoreSelector.getById(this.getId() + '_' + key);
					if (maxStore.AMOUNT > currentAmount && storeSelector)
					{
						storeSelector.onStoreSelect(maxStore.STORE_ID, maxStore.STORE_TITLE);
						value = maxStore.STORE_ID;
					}
				}
			}

			this.setStoreAmount(value, fieldName)
		})

		this.layoutStoreSelector(this.getSettingValue('storeHeaderMap', {}));
	}

	setStoreAmount(value, fieldName, mode = MODE_SET)
	{
		if (!this.model.getStoreCollection().isInited())
		{
			return;
		}

		// price can't be less than zero
		if (mode === MODE_SET)
		{
			let amount;

			const amounts = {
				'_AMOUNT': () => this.model.getStoreCollection().getStoreAmount(value),
				'_RESERVED': () => this.model.getStoreCollection().getStoreReserved(value),
				'_AVAILABLE_AMOUNT': () => this.model.getStoreCollection().getStoreAvailableAmount(value),
			};
			for (const postfix in amounts) {
				if (Object.hasOwnProperty.call(amounts, postfix)) {
					const wrapper = this.#getNodeChildByDataName(fieldName + postfix);
					if (wrapper)
					{
						wrapper.innerHTML = '';

						if (this.#needInventory())
						{
							amount = amounts[postfix]() || 0;

							const amountWithMeasure = amount + ' ' + Text.encode(this.getField('MEASURE_NAME'));
							let htmlAmount = amountWithMeasure;

							if (postfix === '_AVAILABLE_AMOUNT')
							{
								htmlAmount =
									amount > 0
										? amountWithMeasure
										: `<span class="text--danger">${amountWithMeasure}</span>`
								;
							}

							wrapper.innerHTML = htmlAmount;
						}
					}
				}
			}
		}
	}

	setPurchasingPrice(value, mode = MODE_SET)
	{
		if (this.#isPurchasingPriceAccessDenied())
		{
			return;
		}

		// price can't be less than zero
		value = Math.max(value, 0);

		if (mode === MODE_SET)
		{
			this.updateUiField('PURCHASING_PRICE', value.toFixed(this.getPricePrecision()));
		}
		this.setField('PURCHASING_PRICE', value);
		this.addActionProductChange();
		this.addActionUpdateTotal();

		this.updateRowTotalPrice();
	}

	setAmount(value, mode = MODE_SET)
	{
		if (mode === MODE_SET)
		{
			this.updateUiInputField('AMOUNT', value);
		}

		const isChangedValue = this.getField('AMOUNT') !== value;
		if (isChangedValue)
		{
			this.setField('AMOUNT', value);
			this.addActionProductChange();
			this.addActionUpdateTotal();

			this.updateRowTotalPrice();
		}
	}

	setMeasure(measure, mode = MODE_SET)
	{
		if (this.model.isEmpty())
		{
			this.setField('MEASURE_CODE', measure.CODE);
			this.setField('MEASURE_NAME', measure.SYMBOL);
			this.updateUiMeasure(measure.CODE,	measure.SYMBOL);

			return;
		}

		if (mode === MODE_EDIT)
		{
			this.getModel().showSaveNotifier(
				'measureChanger_' + this.getId(),
				{
					title: Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_MEASURE_CHANGED_QUERY'),
					declineCancelTitle: Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_DECLINE_SAVE'),
					events: {
						onSave: () => {
							this.setField('MEASURE_CODE', measure.CODE);
							this.setField('MEASURE_NAME', measure.SYMBOL);
							this.updateUiMeasure(
								this.getField('MEASURE_CODE'),
								this.getField('MEASURE_NAME')
							)
							this.getModel().save(['MEASURE_CODE', 'MEASURE_NAME']);
						},
						onCancel: () => {
							this.updateUiMeasure(
								this.getField('MEASURE_CODE'),
								this.getField('MEASURE_NAME')
							)
						}
					},
				}
			);
		}
		else
		{
			this.updateUiMeasure(measure.CODE,	measure.SYMBOL);
		}

		this.addActionProductChange();
	}

	// controls
	getInputByFieldName(fieldName: string): ?HTMLElement
	{
		const fieldId = this.getUiFieldId(fieldName);
		let item = document.getElementById(fieldId);

		if (!Type.isElementNode(item))
		{
			item = this.getNode().querySelector('[name="' + fieldId + '"]');
		}

		return item;
	}

	getInputWrapperByFieldName(fieldName: string): ?HTMLElement
	{
		const inputBlock = this.getInputByFieldName(fieldName);

		if (Type.isElementNode(inputBlock))
		{
			return Type.isElementNode(inputBlock.parentNode) ? inputBlock.parentNode : inputBlock;
		}

		return undefined;
	}

	updateUiInputField(name, value)
	{
		const item = this.getInputByFieldName(name);

		if (Type.isElementNode(item))
		{
			item.value = value;
		}
	}

	updateUiCheckboxField(name, value)
	{
		const item = this.getInputByFieldName(name);

		if (Type.isElementNode(item))
		{
			item.checked = value === 'Y';
		}
	}

	getMoneyFieldDropdownApi(name): ?BX.Main.dropdown
	{
		if (!Reflection.getClass('BX.Main.dropdownManager'))
		{
			return null;
		}

		return BX.Main.dropdownManager.getById(this.getId() + '_' + name + '_control');
	}

	updateMoneyFieldUiWithDropdownApi(dropdown: BX.Main.dropdown, value: number | string)
	{
		if (dropdown.getValue() === value)
		{
			return;
		}

		if (dropdown.menu)
		{
			dropdown.menu.destroy();
		}

		const item = dropdown.menu.itemsContainer.querySelector('[data-value="' + value + '"]');
		const menuItem = item && dropdown.getMenuItem(item);
		if (menuItem)
		{
			dropdown.refresh(menuItem);
			dropdown.selectItem(menuItem);
		}
	}

	updateUiMoneyField(name: string, value: number | string, text: string): void
	{
		const item = this.getInputByFieldName(name);
		if (!Type.isElementNode(item))
		{
			return;
		}

		item.dataset.value = value;

		const span = item.querySelector('span.main-dropdown-inner');
		if (!Type.isElementNode(span))
		{
			return;
		}

		span.innerHTML = text;
	}

	updateUiMeasure(code, name)
	{
		this.updateUiMoneyField(
			'MEASURE_CODE',
			code,
			Text.encode(name)
		);

		this.updateUiStoreValues();
	}

	updateUiHtmlField(name, html)
	{
		const item = this.getNode().querySelector('[data-name="' + name + '"]');;
		if (Type.isElementNode(item))
		{
			item.innerHTML = html;
		}
	}

	updateUiCurrencyFields()
	{
		const currencyText = this.getEditor().getCurrencyText();
		const currencyId = '' + this.getEditor().getCurrencyId();

		const currencyFieldNames = ['BASE_PRICE_CURRENCY', 'PURCHASING_PRICE_CURRENCY'];
		currencyFieldNames.forEach((name) => {
			const dropdownValues = [];
			dropdownValues.push({
				NAME: currencyText,
				VALUE: currencyId,
			});

			Dom.attr(this.getInputByFieldName(name), 'data-items', dropdownValues);
			this.updateUiMoneyField(name, currencyId, currencyText);
		});
	}

	updateUiField(field, value): void
	{
		const uiName = this.getUiFieldName(field);
		if (!uiName)
		{
			return;
		}

		const uiType = this.getUiFieldType(field);
		if (!uiType)
		{
			return;
		}

		switch (uiType)
		{
			case 'input':
				this.updateUiInputField(uiName, value);
				break;

			case 'money':
				value = BX.util.number_format(value, this.getPricePrecision(), ".", "");
				this.updateUiInputField(uiName, value);
				break;

			case 'money_html':
				value = CurrencyCore.currencyFormat(value, this.getEditor().getCurrencyId(), true);
				this.updateUiHtmlField(uiName, value);
				break;
		}
	}

	getUiFieldName(field)
	{
		let result = null;

		switch (field)
		{
			case 'AMOUNT':
			case 'MEASURE_CODE':
			case 'BASE_PRICE':
			case 'PURCHASING_PRICE':
			case 'TOTAL_PRICE':
				result = field;
				break;
		}

		return result;
	}

	getUiFieldType(field)
	{
		const moneyFields = ['BASE_PRICE', 'PURCHASING_PRICE', 'TOTAL_PRICE'];
		if (moneyFields.includes(field))
		{
			const column = this.getEditor()?.getColumnInfo(field);
			if (column?.editable?.TYPE === 'MONEY')
			{
				return  'money';
			}

			return  'money_html';
		}
		else if (field === 'AMOUNT')
		{
			return  'input';
		}

		return null;
	}

	// proxy
	parseInt(value: number | string, defaultValue: number = 0)
	{
		return this.getEditor().parseInt(value, defaultValue);
	}

	parseFloat(value: number | string, precision: number, defaultValue = 0)
	{
		return this.getEditor().parseFloat(value, precision, defaultValue);
	}

	getPricePrecision()
	{
		return this.getEditor().getPricePrecision();
	}

	getQuantityPrecision()
	{
		return this.getEditor().getQuantityPrecision();
	}

	getCommonPrecision()
	{
		return this.getEditor().getCommonPrecision();
	}

	resetExternalActions()
	{
		this.externalActions.length = 0;
	}

	addExternalAction(action: Action)
	{
		this.externalActions.push(action);
	}

	addActionProductChange()
	{
		this.addExternalAction({
			type: this.getEditor().actions.productChange,
			id: this.getId()
		});
	}

	addActionUpdateTotal()
	{
		this.addExternalAction({
			type: this.getEditor().actions.updateTotal
		});
	}

	executeExternalActions()
	{
		if (this.externalActions.length === 0)
		{
			return;
		}

		this.getEditor().executeActions(this.externalActions);
		this.resetExternalActions();
	}

	isEmptyRow()
	{
		return (
			!Type.isStringFilled(this.getField('NAME', '').trim())
			&& this.model.isEmpty()
			&& this.getBasePrice() <= 0
		)
	}

	validate(): Array
	{
		const errorsList = [];

		if (!this.#isProductCountCorrect(this.getAmount()))
		{
			this.#subscribeFieldToValidator('AMOUNT', this.#isProductCountCorrect);
			errorsList.push(Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_INVALID_AMOUNT_2'));
		}

		return errorsList;
	}

	#subscribeFieldToValidator(fieldName: string, validatorCallback: Function): void
	{
		const fieldInput = this.getInputByFieldName(fieldName);
		const fieldWrapper = this.getInputWrapperByFieldName(fieldName);

		if (validatorCallback(fieldInput.valueAsNumber) || this.validatingFields.get(fieldName))
		{
			return;
		}

		this.validatingFields.set(fieldName, true);

		fieldWrapper.classList.add('main-grid-editor-cell-danger');

		const validator = (eventObject) => {
			if (Boolean(validatorCallback(eventObject.target.valueAsNumber)))
			{
				this.validatingFields.set(fieldName, false);
				Event.unbind(fieldInput, 'blur', validator);
				fieldWrapper.classList.remove('main-grid-editor-cell-danger');
			}
		};

		Event.bind(fieldInput, 'blur', validator);
	}

	#isProductCountCorrect(amountValue): boolean
	{
		return amountValue > 0;
	}

	#getNodeChildByDataName(name: String): HTMLElement
	{
		return this.getNode().querySelector(`[data-name="${name}"]`);
	}

	#needInventory(): boolean
	{
		return !this.getModel().isService();
	}

	#needBarcode(): boolean
	{
		return !this.getModel().isService();
	}

	#isRowAccessDenied()
	{
		return this.getField('ACCESS_DENIED') === true;
	}

	#hideFields()
	{
		if (!this.#isRowAccessDenied())
		{
			this.#hidePurchasingPrice();
			return;
		}

		const hiddenFields = this.getEditor().getSettingValue('hiddenFields');
		const columnIndexes = this.getEditor().getGridColumnIndexes();

		hiddenFields.forEach((fieldName) => {
			const columnIndex = columnIndexes[fieldName];
			if (columnIndex === undefined)
			{
				return;
			}

			const item = this.getNode().querySelector(`.main-grid-cell:nth-child(${columnIndex+1}) .main-grid-cell-content`);
			if (Type.isElementNode(item))
			{
				item.innerHTML = '';
			}
		});

		const fieldWithHintIndex = columnIndexes['AMOUNT'];
		if (fieldWithHintIndex)
		{
			const fieldWithHintNode = this.getNode().querySelector(`.main-grid-cell:nth-child(${fieldWithHintIndex+1}) .main-grid-cell-content`);
			if (fieldWithHintNode)
			{
				const input = new AccessDeniedInput({
					hint: Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACCESS_DENIED_STORE_HINT'),
					isReadOnly: this.getEditor().isReadOnly(),
				});
				input.renderTo(fieldWithHintNode);
			}
		}
	}

	#isPurchasingPriceAccessDenied()
	{
		return this.getField('ACCESS_DENIED_TO_PURCHASING_PRICE') === true;
	}

	#hidePurchasingPrice()
	{
		if (!this.#isPurchasingPriceAccessDenied())
		{
			return;
		}

		const columnIndexes = this.getEditor().getGridColumnIndexes();
		const fieldWithHintIndex = columnIndexes['PURCHASING_PRICE'];
		if (fieldWithHintIndex)
		{
			const fieldWithHintNode = this.getNode().querySelector(`.main-grid-cell:nth-child(${fieldWithHintIndex+1})`);
			if (fieldWithHintNode)
			{
				const priceNode = fieldWithHintNode.querySelector('.main-grid-editor-container');
				if (priceNode)
				{
					priceNode.remove();
				}

				const contentNode = fieldWithHintNode.querySelector('.main-grid-cell-content');
				if (contentNode)
				{
					const input = new AccessDeniedInput({
						hint: Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACCESS_DENIED_PURCHASING_PRICE_HINT'),
						isReadOnly: this.getEditor().isReadOnly(),
					});
					input.renderTo(contentNode);
					contentNode.style.display = 'block';
				}
			}
		}
	}
}
