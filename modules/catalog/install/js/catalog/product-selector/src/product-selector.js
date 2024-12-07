import { ajax, Cache, Dom, Extension, Loc, Reflection, Runtime, Tag, Text, Type } from 'main.core';
import 'ui.design-tokens';
import 'ui.forms';
import 'fileinput';
import 'ui.notification';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { SkuTree } from 'catalog.sku-tree';
import { ProductSearchInputDefault } from './search-input/input-default';
import { ProductSearchInputBarcode } from './search-input/input-barcode';
import { ProductSearchInputBase } from './search-input/input-base';
import { ProductSearchInputPlacement } from './search-input/input-placement';
import { ProductImageInput } from './product-image-input';
import { ProductModel, RightActionDictionary } from 'catalog.product-model';
import { SelectorErrorCode } from './selector-error-code';
import { ExternalCatalogPlacement } from 'catalog.external-catalog-placement';

import './component.css';

const instances = new Map();
const iblockSkuTreeProperties = new Map();

export class ProductSelector extends EventEmitter
{
	static MODE_VIEW = 'view';
	static MODE_EDIT = 'edit';
	static SHORT_VIEW_FORMAT = 'short';
	static FULL_VIEW_FORMAT = 'full';
	static INPUT_FIELD_NAME = 'NAME';
	static INPUT_FIELD_BARCODE = 'BARCODE';
	static ErrorCodes = SelectorErrorCode;
	static UIInputRequest = null;

	#inAjaxProcess = false;
	mode: ProductSelector.MODE_EDIT | ProductSelector.MODE_VIEW = ProductSelector.MODE_EDIT;
	cache = new Cache.MemoryCache();
	type = ProductSelector.INPUT_FIELD_NAME;
	fileInput: ?ProductImageInput;
	searchInput: ?ProductSearchInputBase;
	skuTreeInstance: ?SkuTree;
	mobileScannerToken = null;

	variationChangeHandler = this.handleVariationChange.bind(this);
	onSaveImageHandler = this.onSaveImage.bind(this);
	onChangeFieldsHandler = Runtime.debounce(this.onChangeFields, 500, this);
	onUploaderIsInitedHandler = this.onUploaderIsInited.bind(this);
	onNameChangeFieldHandler = Runtime.debounce(this.onNameChange, 500, this);

	placement: ?ExternalCatalogPlacement;
	placementOnProductUpdatedHandler = this.placementOnProductUpdated.bind(this);

	static getById(id: string): ?ProductSelector
	{
		return instances.get(id) || null;
	}

	constructor(id, options = {})
	{
		super();

		this.setEventNamespace('BX.Catalog.ProductSelector');
		this.id = id || Text.getRandom();
		options.inputFieldName = options.inputFieldName || ProductSelector.INPUT_FIELD_NAME;
		this.options = options || {};
		this.settings = Extension.getSettings('catalog.product-selector');

		this.type = this.options.type || ProductSelector.INPUT_FIELD_NAME;

		this.setMode(options.mode);

		this.isExternalCatalog = this.settings.get('isExternalCatalog', false);
		if (this.isExternalCatalog)
		{
			this.placement = ExternalCatalogPlacement.create();
			this.placement.initialize();
		}

		if (options.model && (options.model instanceof ProductModel))
		{
			this.model = options.model;
		}
		else
		{
			this.model = ProductModel.getById(this.id);
		}

		if (!(this.model instanceof ProductModel))
		{
			this.model = new ProductModel(
				{
					currency: options.currency,
					iblockId: Text.toNumber(options.iblockId),
					basePriceId: Text.toNumber(options.basePriceId),
					fields: options.fields,
					skuTree: options.skuTree,
					storeMap: options.storeMap,
				},
			);
		}

		this.model.getImageCollection().setMorePhotoValues(options.morePhotoValues);
		if (!Type.isNil(this.getConfig('DETAIL_PATH')))
		{
			this.model.setDetailPath(this.getConfig('DETAIL_PATH'));
		}

		if (options.failedProduct)
		{
			this.model.getErrorCollection().setError(
				SelectorErrorCode.FAILED_PRODUCT,
				'',
			);
		}

		if (this.isShowableEmptyProductError())
		{
			this.model.getErrorCollection().setError(
				SelectorErrorCode.NOT_SELECTED_PRODUCT,
				this.getEmptySelectErrorMessage(),
			);
		}

		if (options.fileView)
		{
			this.model.getImageCollection().setPreview(options.fileView);
		}

		if (options.fileInput)
		{
			this.model.getImageCollection().setEditInput(options.fileInput);
		}

		this.layout();

		if (options.skuTree)
		{
			this.updateSkuTree(options.skuTree);
		}

		if (options.scannerToken)
		{
			this.setMobileScannerToken(options.scannerToken);
		}

		this.subscribeEvents();

		instances.set(this.id, this);
	}

	setModel(model: ProductModel): void
	{
		this.model = model;
	}

	getModel(): ProductModel
	{
		return this.model;
	}

	setMode(mode: ProductSelector.MODE_VIEW | ProductSelector.MODE_EDIT): void
	{
		if (!Type.isNil(mode))
		{
			this.mode = mode === ProductSelector.MODE_VIEW ? ProductSelector.MODE_VIEW : ProductSelector.MODE_EDIT;
		}
	}

	isViewMode(): boolean
	{
		return this.mode === ProductSelector.MODE_VIEW;
	}

	isShortViewFormat(): boolean
	{
		return this.getConfig('VIEW_FORMAT', ProductSelector.FULL_VIEW_FORMAT) === ProductSelector.SHORT_VIEW_FORMAT;
	}

	isSaveable(): boolean
	{
		return !this.isViewMode() && this.model.isSaveable();
	}

	isEnabledAutosave(): boolean
	{
		return this.isSaveable() && this.getConfig('ENABLE_AUTO_SAVE', false);
	}

	isEnabledMobileScanning(): boolean
	{
		return !this.isViewMode() && this.getConfig('ENABLE_MOBILE_SCANNING', true);
	}

	getEmptySelectErrorMessage()
	{
		return !this.isExternalCatalog && this.checkProductAddRights()
			? Loc.getMessage('CATALOG_SELECTOR_SELECTED_PRODUCT_TITLE')
			: Loc.getMessage('CATALOG_SELECTOR_SELECT_PRODUCT_TITLE')
		;
	}

	getMobileScannerToken(): string
	{
		return this.mobileScannerToken || Text.getRandom(16);
	}

	checkProductViewRights(): boolean
	{
		return this.model.checkAccess(RightActionDictionary.ACTION_PRODUCT_VIEW) ?? true;
	}

	checkProductEditRights(): boolean
	{
		return this.model.checkAccess(RightActionDictionary.ACTION_PRODUCT_EDIT) ?? false;
	}

	checkProductAddRights(): boolean
	{
		return this.model.checkAccess(RightActionDictionary.ACTION_PRODUCT_ADD) ?? false;
	}

	setMobileScannerToken(token: string): void
	{
		this.mobileScannerToken = token;
	}

	removeMobileScannerToken(): void
	{
		this.mobileScannerToken = null;
	}

	getId(): string
	{
		return this.id;
	}

	getType(): string
	{
		return this.type;
	}

	getConfig(name, defaultValue)
	{
		return BX.prop.get(this.options.config, name, defaultValue);
	}

	setConfig(name, value): this
	{
		this.options.config[name] = value;

		return this;
	}

	getRowId(): string
	{
		return this.getConfig('ROW_ID');
	}

	getFileInput(): ProductImageInput
	{
		if (!this.fileInput)
		{
			this.fileInput = new ProductImageInput(
				this.options.fileInputId,
				{
					selector: this,
					enableSaving: this.getConfig('ENABLE_IMAGE_CHANGE_SAVING', false),
				},
			);
		}

		return this.fileInput;
	}

	isProductSearchEnabled(): boolean
	{
		return this.getConfig('ENABLE_SEARCH', false)
			&& this.model.getIblockId() > 0
			&& this.checkProductViewRights()
		;
	}

	isSkuTreeEnabled(): boolean
	{
		return this.getConfig('ENABLE_SKU_TREE', true) !== false;
	}

	isImageFieldEnabled(): boolean
	{
		return this.getConfig('ENABLE_IMAGE_INPUT', true) !== false;
	}

	isShowableEmptyProductError(): boolean
	{
		const emptyChanged = this.model.isEmpty() && this.model.isChanged();

		return this.isEnabledEmptyProductError() && (emptyChanged || this.model.isSimple());
	}

	isShowableErrors(): boolean
	{
		return this.isEnabledEmptyProductError() || this.isEnabledEmptyImagesError();
	}

	isEnabledEmptyProductError(): boolean
	{
		return this.getConfig('ENABLE_EMPTY_PRODUCT_ERROR', false);
	}

	isEnabledEmptyImagesError(): boolean
	{
		return this.getConfig('ENABLE_EMPTY_IMAGES_ERROR', false);
	}

	isEnabledChangesRendering(): boolean
	{
		return this.getConfig('ENABLE_CHANGES_RENDERING', true);
	}

	isInputDetailLinkEnabled(): boolean
	{
		return this.getConfig('ENABLE_INPUT_DETAIL_LINK', false)
			&& Type.isStringFilled(this.model.getDetailPath())
			&& this.checkProductViewRights()
		;
	}

	getWrapper(): HTMLElement
	{
		if (!this.wrapper)
		{
			this.wrapper = document.getElementById(this.id);
		}

		return this.wrapper;
	}

	renderTo(node)
	{
		this.clearLayout();
		this.wrapper = node;
		this.layout();
	}

	layout()
	{
		const wrapper = this.getWrapper();
		if (!wrapper)
		{
			return;
		}

		this.defineWrapperClass(wrapper);
		wrapper.innerHTML = '';

		const block = Tag.render`<div class="catalog-product-field-inner"></div>`;
		Dom.append(this.layoutNameBlock(), block);

		if (this.getSkuTreeInstance())
		{
			Dom.append(this.getSkuTreeInstance().layout(), block);
		}

		Dom.append(this.getErrorContainer(), block);

		if (!this.isViewMode())
		{
			Dom.append(block, wrapper);
		}

		if (this.isImageFieldEnabled())
		{
			if (Reflection.getClass('BX.UI.ImageInput'))
			{
				this.layoutImage();
			}

			if (ProductSelector.UIInputRequest instanceof Promise)
			{
				ProductSelector.UIInputRequest.then(() => {
					this.layoutImage();
				});
			}
			else
			{
				ProductSelector.UIInputRequest = new Promise(resolve => {
					ajax
						.runAction(
							'catalog.productSelector.getFileInput',
							{
								json: {
									iblockId: this.getModel().getIblockId(),
								},
							},
						)
						.then(() => {
							this.layoutImage();
							ProductSelector.UIInputRequest = null;
							resolve();
						});
				});
			}

			Dom.append(this.getImageContainer(), wrapper);
		}

		if (this.isViewMode())
		{
			Dom.append(block, wrapper);
		}

		if (this.isViewMode())
		{
			Dom.append(block, wrapper);
		}

		if (this.isShowableErrors)
		{
			this.layoutErrors();
		}

		this.subscribeToVariationChange();
	}

	focusName(): this
	{
		if (this.searchInput)
		{
			this.searchInput.focusName();
		}

		return this;
	}

	getImageContainer(): HTMLElement
	{
		return this.cache.remember('imageContainer', () => (
			Tag.render`<div class="catalog-product-img"></div>`
		));
	}

	getErrorContainer(): HTMLElement
	{
		return this.cache.remember('errorContainer', () => (
			Tag.render`<div class="catalog-product-error"></div>`
		));
	}

	layoutErrors()
	{
		this.getErrorContainer().innerHTML = '';
		this.clearImageErrorBorder();
		if (!this.model.getErrorCollection().hasErrors())
		{
			return;
		}

		const errors = this.model.getErrorCollection().getErrors();
		for (const code in errors)
		{
			if (!ProductSelector.ErrorCodes.getCodes().includes(code))
			{
				continue;
			}

			if (code === 'EMPTY_IMAGE')
			{
				this.setImageErrorBorder();
			}
			else
			{
				Dom.append(
					Tag.render`<div class="catalog-product-error-item">${errors[code].text}</div>`,
					this.getErrorContainer(),
				);

				if (this.searchInput)
				{
					Dom.addClass(this.searchInput.getNameBlock(), 'ui-ctl-danger');
				}
			}
		}
	}

	setImageErrorBorder()
	{
		Dom.addClass(
			this.getImageContainer().querySelector('.adm-fileinput-area'),
			'adm-fileinput-drag-area-error',
		);
	}

	clearImageErrorBorder()
	{
		Dom.removeClass(
			this.getImageContainer().querySelector('.adm-fileinput-area'),
			'adm-fileinput-drag-area-error',
		);
	}

	onUploaderIsInited()
	{
		if (this.isEnabledEmptyImagesError())
		{
			requestAnimationFrame(this.layoutErrors.bind(this));
		}
	}

	layoutImage()
	{
		this.getImageContainer().innerHTML = '';
		Dom.append(this.getFileInput().layout(), this.getImageContainer());
		this.refreshImageSelectorId = null;
	}

	clearState(): void
	{
		this
			.getModel()
			.initFields({
				ID: '',
				NAME: '',
				BARCODE: '',
				PRODUCT_ID: null,
				SKU_ID: null,
			})
			.setOption('isNew', false)
		;

		this
			.getFileInput()
			.restoreDefaultInputHtml()
		;

		this
			.getModel()
			.clearSkuTree()
		;
		this.skuTreeInstance = null;

		this
			.getModel()
			.getStoreCollection()
			.clear()
		;
	}

	clearLayout(): void
	{
		this.unsubscribeToVariationChange();
		const wrapper = this.getWrapper();
		if (wrapper)
		{
			wrapper.innerHTML = '';
		}
	}

	subscribeEvents()
	{
		EventEmitter.incrementMaxListeners('ProductList::onChangeFields', 1);
		EventEmitter.incrementMaxListeners('ProductSelector::onNameChange', 1);
		EventEmitter.incrementMaxListeners('Catalog.ImageInput::save', 1);
		EventEmitter.incrementMaxListeners('onUploaderIsInited', 1);
		EventEmitter.incrementMaxListeners('Catalog:ProductSelectorPlacement:onProductUpdated', 1);

		EventEmitter.subscribe('ProductList::onChangeFields', this.onChangeFieldsHandler);
		EventEmitter.subscribe('ProductSelector::onNameChange', this.onNameChangeFieldHandler);
		EventEmitter.subscribe('Catalog.ImageInput::save', this.onSaveImageHandler);
		EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		EventEmitter.subscribe(
			'Catalog:ProductSelectorPlacement:onProductUpdated',
			this.placementOnProductUpdatedHandler,
		);
	}

	unsubscribeEvents()
	{
		this.unsubscribeToVariationChange();

		EventEmitter.unsubscribe('Catalog.ImageInput::save', this.onSaveImageHandler);
		EventEmitter.unsubscribe('ProductList::onChangeFields', this.onChangeFieldsHandler);
		EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		EventEmitter.unsubscribe('ProductSelector::onNameChange', this.onNameChangeFieldHandler);
		EventEmitter.unsubscribe(
			'Catalog:ProductSelectorPlacement:onProductUpdated',
			this.placementOnProductUpdatedHandler,
		);
	}

	defineWrapperClass(wrapper)
	{
		if (this.isViewMode())
		{
			Dom.addClass(wrapper, 'catalog-product-view');
			Dom.removeClass(wrapper, 'catalog-product-edit');

			if (this.isShortViewFormat())
			{
				Dom.addClass(wrapper, '--short-format');
			}
		}
		else
		{
			Dom.addClass(wrapper, 'catalog-product-edit');
			Dom.removeClass(wrapper, 'catalog-product-view');
		}

		if (this.isImageFieldEnabled())
		{
			Dom.addClass(wrapper, '--with-images');
		}
	}

	getNameBlockView(): HTMLElement
	{
		const productName = Text.encode(this.model.getField('NAME'));
		const namePlaceholder = Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE');

		if (this.getModel().getDetailPath())
		{
			return Tag.render`
				<a href="${this.getModel().getDetailPath()}" title="${namePlaceholder}">${productName}</a>
			`;
		}

		return Tag.render`<span title="${namePlaceholder}">${productName}</span>`;
	}

	getNameInputFilledValue(): string
	{
		if (this.searchInput)
		{
			return this.searchInput.getFilledValue();
		}

		return '';
	}

	layoutNameBlock(): HTMLElement
	{
		const block = Tag.render`<div class="catalog-product-field-input"></div>`;

		if (this.isViewMode())
		{
			Dom.append(this.getNameBlockView(), block);
		}
		else
		{
			this.searchInput = this.#createSearchInput();

			Dom.append(this.searchInput.layout(), block);
		}

		return block;
	}

	#createSearchInput(): ProductSearchInputBase
	{
		if (this.placement)
		{
			return new ProductSearchInputPlacement(
				this.id,
				{
					selector: this,
					model: this.getModel(),
					inputName: this.options.inputFieldName,
					isSearchEnabled: this.isProductSearchEnabled(),
					isEnabledEmptyProductError: this.isEnabledEmptyProductError(),
					isEnabledDetailLink: this.isInputDetailLinkEnabled(),
				},
			);
		}

		if (this.getType() === ProductSelector.INPUT_FIELD_BARCODE)
		{
			if (!this.searchInput)
			{
				return new ProductSearchInputBarcode(
					this.id,
					{
						selector: this,
						model: this.getModel(),
						inputName: this.options.inputFieldName,
					},
				);
			}

			return this.searchInput;
		}

		return new ProductSearchInputDefault(
			this.id,
			{
				selector: this,
				model: this.getModel(),
				inputName: this.options.inputFieldName,
				isSearchEnabled: this.isProductSearchEnabled(),
				isEnabledEmptyProductError: this.isEnabledEmptyProductError(),
				isEnabledDetailLink: this.isInputDetailLinkEnabled(),
			},
		);
	}

	searchInDialog(): ProductSelector
	{
		this.searchInput.searchInDialog();

		return this;
	}

	updateSkuTree(tree): ProductSelector
	{
		this.getModel().setSkuTree(tree);
		this.skuTreeInstance = null;

		return this;
	}

	getIblockSkuTreeProperties(): Promise
	{
		return new Promise((resolve) => {
			if (iblockSkuTreeProperties.has(this.getModel().getIblockId()))
			{
				resolve(iblockSkuTreeProperties.get(this.getModel().getIblockId()));
			}
			else
			{
				ajax
					.runAction(
						'catalog.productSelector.getSkuTreeProperties',
						{
							json: {
								iblockId: this.getModel().getIblockId(),
							},
						},
					)
					.then(response => {
						iblockSkuTreeProperties.set(this.getModel().getIblockId(), response);
						resolve(response);
					});
			}
		});
	}

	getSkuTreeInstance(): SkuTree
	{
		if (this.isSkuTreeEnabled() && this.getModel()?.getSkuTree() && !this.skuTreeInstance)
		{
			this.skuTreeInstance = new SkuTree({
				skuTree: this.getModel().getSkuTree(),
				selectable: this.getConfig('ENABLE_SKU_SELECTION', true),
				hideUnselected: this.getConfig('HIDE_UNSELECTED_ITEMS', false),
				isShortView: this.isViewMode() && this.isShortViewFormat(),
			});
		}

		return this.skuTreeInstance;
	}

	subscribeToVariationChange()
	{
		const skuTree = this.getSkuTreeInstance();
		if (skuTree)
		{
			this.unsubscribeToVariationChange();
			skuTree.subscribe('SkuProperty::onChange', this.variationChangeHandler);
		}
	}

	unsubscribeToVariationChange()
	{
		const skuTree = this.getSkuTreeInstance();
		if (skuTree)
		{
			skuTree.unsubscribe('SkuProperty::onChange', this.variationChangeHandler);
		}
	}

	handleVariationChange(event: BaseEvent): void
	{
		const [skuFields] = event.getData();
		const productId = Text.toNumber(skuFields.PARENT_PRODUCT_ID);
		const variationId = Text.toNumber(skuFields.ID);

		if (productId <= 0 || variationId <= 0)
		{
			return;
		}

		this.emit('onBeforeChange', {
			selectorId: this.getId(),
			rowId: this.getRowId(),
		});

		this.#inAjaxProcess = true;

		if (this.placement)
		{
			this.placement.initialize()
				.then(() => {
					this.placementEmitOnCatalogProductNeedToUpdate({
						productId: variationId,
						returnEventData: {
							rowId: this.getRowId(),
							scenario: 'variationChange',
							payload: [
								variationId,
							],
						},
					});
				})
				.catch(() => {
					this.handleVariationChangeAjaxAction(variationId);

					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_RESPONDING_ERROR'),
						autoHide: true,
						autoHideDelay: 4000,
					});
				});

			return;
		}

		this.handleVariationChangeAjaxAction(variationId);
	}

	handleVariationChangeAjaxAction(variationId: number): void
	{
		ajax
			.runAction(
				'catalog.productSelector.getSelectedSku',
				{
					json: {
						variationId,
						options: {
							priceId: this.basePriceId,
							currency: this.model.getCurrency(),
							urlBuilder: this.getConfig('URL_BUILDER_CONTEXT'),
						},
					},
				},
			)
			.then(response => this.processResponse(response, { ...this.options.config }));
	}

	onChangeFields(event)
	{
		const eventData = event.getData();

		if (eventData.rowId !== this.getRowId())
		{
			return;
		}

		const fields = eventData.fields;
		this.getModel().setFields(fields);
	}

	reloadFileInput()
	{
		ajax
			.runAction(
				'catalog.productSelector.getFileInput',
				{
					json: {
						iblockId: this.getModel().getIblockId(),
						skuId: this.getModel()?.getSkuId(),
					},
				},
			)
			.then((event) => {
				this.getModel().getImageCollection().setEditInput(event.data.html);
				if (this.isImageFieldEnabled())
				{
					this.layoutImage();
				}
			});
	}

	onNameChange(event)
	{
		const eventData = event.getData();

		if (eventData.rowId !== this.getRowId() || !this.isEnabledAutosave())
		{
			return;
		}

		const fields = eventData.fields;
		this.getModel().setFields(fields);
		this.getModel().save().then(() => {
			BX.UI.Notification.Center.notify({
				id: 'saving_field_notify_name',
				closeButton: false,
				content: Tag.render`<div>${Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_NAME_CHANGED')}</div>`,
				autoHide: true,
			});
		});
	}

	onSaveImage(event)
	{
		const [, inputId, response] = event.getData();
		if (inputId !== this.getFileInput().getId())
		{
			return;
		}

		this.getFileInput().setId(response.data.id);
		this.getFileInput().setInputHtml(response.data.input);
		this.getFileInput().setView(response.data.preview);
		this.getModel().getImageCollection().setMorePhotoValues(response.data.values);
		if (this.isImageFieldEnabled())
		{
			this.layoutImage();
		}

		this.emit('onChange', {
			selectorId: this.id,
			rowId: this.getRowId(),
			fields: this.getModel().getFields(),
			morePhoto: this.getModel().getImageCollection().getMorePhotoValues(),
		});
	}

	inProcess()
	{
		return this.#inAjaxProcess;
	}

	onProductSelect(productId, itemConfig)
	{
		this.emitOnProductSelectEvents();
		this.productSelectRequest(productId, itemConfig);
	}

	emitOnProductSelectEvents()
	{
		this.emit('onProductSelect', {
			selectorId: this.getId(),
			rowId: this.getRowId(),
		});

		this.emit('onBeforeChange', {
			selectorId: this.getId(),
			rowId: this.getRowId(),
		});
	}

	productSelectRequest(
		productId,
		itemConfig = {
			isNew: false,
			needExternalUpdate: true,
			immutableFields: [],
		},
	)
	{
		this.#inAjaxProcess = true;

		if (
			this.placement
			&& itemConfig.needExternalUpdate !== false
		)
		{
			this.placement.initialize()
				.then(() => {
					this.placementEmitOnCatalogProductNeedToUpdate({
						productId,
						returnEventData: {
							rowId: this.getRowId(),
							scenario: 'productSelect',
							payload: [
								productId,
								itemConfig,
							],
						},
					});
				})
				.catch(() => {
					this.productSelectAjaxAction(productId, itemConfig);

					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_RESPONDING_ERROR'),
						autoHide: true,
						autoHideDelay: 4000,
					});
				});

			return;
		}

		this.productSelectAjaxAction(productId, itemConfig);
	}

	productSelectAjaxAction(
		productId,
		itemConfig = {
			isNew: false,
			immutableFields: [],
		},
	)
	{
		ajax
			.runAction(
				'catalog.productSelector.getProduct',
				{
					json: {
						productId,
						options: {
							priceId: this.basePriceId,
							currency: this.model.getCurrency(),
							urlBuilder: this.getConfig('URL_BUILDER_CONTEXT'),
						},
					},
				},
			)
			.then(response => this.processResponse(response, { ...this.options.config, ...itemConfig }, true));
	}

	placementEmitOnCatalogProductNeedToUpdate(eventData: Object): void
	{
		EventEmitter.emit('Catalog:ProductSelectorPlacement:onNeedProductUpdate', {
			appSid: this.placement.getAppSidId(),
			...eventData,
		});

		this.placementProductUpdateTimer = setTimeout(() => {
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_RESPONDING_ERROR'),
				autoHide: true,
				autoHideDelay: 4000,
			});
			this.placementOnProductUpdated(new BaseEvent({ data: { ...eventData.returnEventData } }));
		}, ExternalCatalogPlacement.RESPONSE_TIMEOUT);
	}

	placementOnProductUpdated(event: BaseEvent): void
	{
		if (this.placementProductUpdateTimer === null)
		{
			return;
		}

		const { rowId, scenario, payload } = event.getData();

		if (rowId !== this.getRowId())
		{
			return;
		}

		if (scenario === 'productSelect')
		{
			this.productSelectAjaxAction(...payload);
		}
		else if (scenario === 'variationChange')
		{
			this.handleVariationChangeAjaxAction(...payload);
		}

		clearTimeout(this.placementProductUpdateTimer);
		this.placementProductUpdateTimer = null;
	}

	processResponse(response, config = {}, isProductAction = false)
	{
		const data = response?.data || null;
		this.#inAjaxProcess = false;

		const fields = data?.fields || [];
		if (Type.isArray(config.immutableFields))
		{
			config.immutableFields.forEach((field) => {
				fields[field] = this.getModel().getField(field);
			});

			if (data)
			{
				data.fields = fields;
			}
		}

		if (isProductAction)
		{
			this.clearState();
		}

		if (data)
		{
			this.changeSelectedElement(data, config);
		}
		else if (!isProductAction)
		{
			this.productSelectRequest(this.getModel().getProductId());
		}

		this.unsubscribeToVariationChange();

		if (this.isEnabledChangesRendering())
		{
			this.clearLayout();
			this.layout();
		}

		this.emit('onChange', {
			selectorId: this.id,
			rowId: this.getRowId(),
			isNew: config.isNew || false,
			fields,
			morePhoto: this.getModel().getImageCollection().getMorePhotoValues(),
		});
	}

	changeSelectedElement(data, config)
	{
		const productId = Text.toInteger(data.productId);
		const productChanged = this.getModel().getProductId() !== productId;

		if (productChanged)
		{
			this.getModel().setOption('productId', productId);
			this.getModel().setOption('skuId', Text.toInteger(data.skuId));
			this.getModel().setOption('isSimpleModel', false);
			this.getModel().setOption('isNew', config.isNew);
		}

		this.getModel().initFields(data.fields);

		const imageField = {
			id: '',
			input: '',
			preview: '',
			values: [],
		};

		if (Type.isObject(data.image))
		{
			imageField.id = data.image.id;
			imageField.input = data.image.input;
			imageField.preview = data.image.preview;
			imageField.values = data.image.values;
		}

		this.getFileInput().setId(imageField.id);
		this.getFileInput().setInputHtml(imageField.input);
		this.getFileInput().setView(imageField.preview);

		this.getModel().getImageCollection().setMorePhotoValues(imageField.values);
		this.checkEmptyImageError();

		if (data.detailUrl)
		{
			this.getModel().setDetailPath(data.detailUrl);
		}

		if (Type.isObject(data.skuTree))
		{
			this.updateSkuTree(data.skuTree);
		}
	}

	checkEmptyImageError()
	{
		if (
			!Type.isArrayFilled(this.getModel().getImageCollection().getMorePhotoValues())
			&& this.isEnabledEmptyImagesError()
		)
		{
			this.getModel().getErrorCollection().setError(
				'EMPTY_IMAGE',
				Loc.getMessage('CATALOG_SELECTOR_EMPTY_IMAGE_ERROR'),
			);
		}
		else
		{
			this.getModel().getErrorCollection().removeError('EMPTY_IMAGE');
		}
	}

	removeSpotlight()
	{
		this.searchInput?.removeSpotlight();
		this.setConfig('ENABLE_INFO_SPOTLIGHT', false);
	}

	removeQrAuth()
	{
		this.searchInput?.removeQrAuth();
		this.setConfig('ENABLE_BARCODE_QR_AUTH', false);
	}
}
