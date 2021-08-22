import {ajax, Cache, Dom, Event, Loc, Reflection, Runtime, Tag, Text, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {SkuTree} from 'catalog.sku-tree';
import {ProductSearchInput} from "./product-search-input";
import {ProductImageInput} from "./product-image-input";
import {Empty} from "./models/empty";
import {Sku} from "./models/sku";
import {Product} from "./models/product";
import {Base} from "./models/base";
import {Simple} from "./models/simple";
import 'ui.forms';
import './component.css';

const instances = new Map();

export class ProductSelector extends EventEmitter
{
	static MODE_VIEW = 'view';
	static MODE_EDIT = 'edit';

	static PRODUCT_TYPE = 'product';
	static SKU_TYPE = 'sku';

	mode: ProductSelector.MODE_EDIT | ProductSelector.MODE_VIEW = ProductSelector.MODE_EDIT;
	cache = new Cache.MemoryCache();
	fileInput: ?ProductImageInput;
	searchInput: ?ProductSearchInput;
	skuTreeInstance: ?SkuTree;

	variationChangeHandler = this.handleVariationChange.bind(this);
	onSaveImageHandler = this.onSaveImage.bind(this);
	onChangeFieldsHandler = Runtime.debounce(this.onChangeFields, 500, this);

	static getById(id: string): ?ProductSelector
	{
		return instances.get(id) || null;
	}

	constructor(id, options = {})
	{
		super();
		this.setEventNamespace('BX.Catalog.ProductSelector');

		this.id = id || Text.getRandom();
		options.inputFieldName = options.inputFieldName || 'NAME';
		this.options = options || {};
		this.iblockId = Text.toNumber(options.iblockId);
		this.basePriceId = Text.toNumber(options.basePriceId);

		this.setMode(options.mode);

		this.model = this.createModel(options);
		this.model.setFields(options.fields);
		this.model.setMorePhotoValues(options.morePhotoValues);
		this.model.setDetailPath(this.getConfig('DETAIL_PATH'));

		if (this.isSimpleModel() && this.isEnabledEmptyProductError())
		{
			this.model.setError(
				'NOT_SELECTED_PRODUCT',
				Loc.getMessage('CATALOG_SELECTOR_SELECTED_PRODUCT_TITLE')
			);
		}

		this.skuTree = options.skuTree || null;
		this.setFileType(options.fileType);

		this.layout();

		EventEmitter.subscribe('ProductList::onChangeFields', this.onChangeFieldsHandler);
		EventEmitter.subscribe('Catalog.ImageInput::save', this.onSaveImageHandler);

		instances.set(this.id, this);
	}

	createModel(options = {}): Base
	{
		if (options.isSimpleModel)
		{
			return new Simple();
		}

		const productId = Text.toInteger(options.productId) || 0;
		if (productId <= 0)
		{
			return new Empty();
		}

		const modelConfig = options?.config?.MODEL_CONFIG || {};
		const skuId = Text.toInteger(options.skuId) || 0;
		if (skuId > 0 && skuId !== productId)
		{
			return new Sku(skuId, {...modelConfig, ...{productId}});
		}

		return new Product(productId, modelConfig);
	}

	setModel(model: Base): void
	{
		this.model = model;
	}

	getModel(): Base
	{
		return this.model;
	}

	isEmptyModel(): boolean
	{
		return (this.getModel() instanceof Empty);
	}

	isSimpleModel(): boolean
	{
		return (this.getModel() instanceof Simple);
	}

	setMode(mode): void
	{
		if (!Type.isNil(mode))
		{
			this.mode = mode === ProductSelector.MODE_VIEW ? ProductSelector.MODE_VIEW : ProductSelector.MODE_EDIT;
		}
	}

	setFileType(fileType): void
	{
		this.fileType = fileType === ProductSelector.SKU_TYPE ? ProductSelector.SKU_TYPE : ProductSelector.PRODUCT_TYPE;
	}

	isViewMode(): boolean
	{
		return this.mode === ProductSelector.MODE_VIEW;
	}

	isSaveable(): boolean
	{
		return !this.isViewMode() && this.model.isSaveable();
	}

	getId(): string
	{
		return this.id;
	}

	getIblockId(): number
	{
		return this.iblockId;
	}

	getBasePriceId(): number
	{
		return this.basePriceId;
	}

	getConfig(name, defaultValue)
	{
		return BX.prop.get(this.options.config, name, defaultValue);
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
					view: this.options.fileView,
					inputHtml: this.options.fileInput,
					enableSaving: this.getConfig('ENABLE_IMAGE_CHANGE_SAVING', false)
				}
			);
		}

		return this.fileInput;
	}

	isProductFileType(): boolean
	{
		return this.fileType === ProductSelector.PRODUCT_TYPE;
	}

	isProductSearchEnabled(): boolean
	{
		return this.getConfig('ENABLE_SEARCH', false) && this.getIblockId() > 0;
	}

	isImageFieldEnabled(): boolean
	{
		return this.getConfig('ENABLE_IMAGE_INPUT', true) !== false;
	}

	isEnabledEmptyProductError(): boolean
	{
		return this.getConfig('ENABLE_EMPTY_PRODUCT_ERROR', false);
	}

	isEnabledChangesRendering(): boolean
	{
		return this.getConfig('ENABLE_CHANGES_RENDERING', true);
	}

	isInputDetailLinkEnabled(): boolean
	{
		return this.getConfig('ENABLE_INPUT_DETAIL_LINK', false) && Type.isStringFilled(this.model.getDetailPath());
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
		const block = Tag.render`<div class="catalog-product-field-inner"></div>`;
		wrapper.appendChild(block);
		block.appendChild(this.layoutNameBlock());

		if (this.isImageFieldEnabled())
		{
			if (!Reflection.getClass('BX.UI.ImageInput'))
			{
				ajax
					.runAction(	'catalog.productSelector.getFileInput', {
						json:{
							iblockId: this.iblockId
						}
					})
					.then(() => {
						this.layoutImage();
					});
			}
			else
			{
				this.layoutImage();
			}
			block.appendChild(this.getImageContainer());
		}
		else
		{
			Dom.addClass(wrapper, 'catalog-product-field-no-image');
		}

		wrapper.appendChild(this.getErrorContainer());
		this.layoutErrors();

		this.layoutSkuTree();
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

	searchInDialog(searchQuery: string = ''): this
	{
		if (this.searchInput)
		{
			this.searchInput.searchInDialog(searchQuery);
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
		if (!this.model.hasErrors())
		{
			return;
		}

		const errors = this.model.getErrors();
		for (const code in errors)
		{
			this.getErrorContainer().appendChild(
				Tag.render`<div class="catalog-product-error-item">${errors[code]}</div>`
			);
		}

		if (this.searchInput)
		{
			Dom.addClass(this.searchInput.getNameBlock(), 'ui-ctl-danger');
		}
	}

	layoutImage()
	{
		this.getImageContainer().innerHTML = '';
		this.getImageContainer().appendChild(this.getFileInput().layout());
		this.refreshImageSelectorId = null;
	}

	clearState(): void
	{
		this.model = this.createModel();
		this.fileInput.restoreDefaultInputHtml();
		this.skuTree = null;
		this.skuTreeInstance = null;
		this.refreshImageSelectorId = null;
	}

	clearLayout(): void
	{
		const wrapper = this.getWrapper();
		if (wrapper)
		{
			Event.unbindAll(wrapper);
			wrapper.innerHTML = '';
		}

		this.unsubscribeToVariationChange();
	}

	unsubscribeEvents()
	{
		this.unsubscribeToVariationChange();

		EventEmitter.unsubscribe('ProductList::onChangeFields', this.onChangeFieldsHandler);
	}

	defineWrapperClass(wrapper)
	{
		if (this.isViewMode())
		{
			Dom.addClass(wrapper, 'catalog-product-view');
			Dom.removeClass(wrapper, 'catalog-product-edit');
		}
		else
		{
			Dom.addClass(wrapper, 'catalog-product-edit');
			Dom.removeClass(wrapper, 'catalog-product-view');
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

	layoutNameBlock(): HTMLElement
	{
		const block = Tag.render`<div class="catalog-product-field-input"></div>`;

		if (this.isViewMode())
		{
			block.appendChild(this.getNameBlockView());
		}
		else
		{
			this.searchInput = new ProductSearchInput(this.id, {
				selector: this,
				model: this.getModel(),
				inputName: this.options.inputFieldName,
				isSearchEnabled: this.isProductSearchEnabled(),
				isEnabledEmptyProductError: this.isEnabledEmptyProductError(),
				iblockId: this.getIblockId(),
				basePriceId: this.getBasePriceId(),
				isEnabledDetailLink: this.isInputDetailLinkEnabled()
			});
			block.appendChild(this.searchInput.layout());
		}

		return block;
	}

	updateSkuTree(tree): void
	{
		this.skuTree = tree;
		this.skuTreeInstance = null;
	}

	getSkuTreeInstance(): SkuTree
	{
		if (this.skuTree && !this.skuTreeInstance)
		{
			this.skuTreeInstance = new SkuTree({
				skuTree: this.skuTree,
				selectable: this.getConfig('ENABLE_SKU_SELECTION', true),
				hideUnselected: this.getConfig('HIDE_UNSELECTED_ITEMS', false),
			});
		}

		return this.skuTreeInstance;
	}

	layoutSkuTree(): void
	{
		const skuTree = this.getSkuTreeInstance();
		const wrapper = this.getWrapper();

		if (skuTree && wrapper)
		{
			const skuTreeWrapper = skuTree.layout();

			wrapper.appendChild(skuTreeWrapper);
		}
	}

	subscribeToVariationChange()
	{
		const skuTree = this.getSkuTreeInstance();
		if (skuTree)
		{
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

	handleVariationChange(event)
	{
		const [skuFields] = event.getData();
		const productId = Text.toNumber(skuFields.PARENT_PRODUCT_ID);
		const variationId = Text.toNumber(skuFields.ID);

		if (productId <= 0 || variationId <= 0)
		{
			return;
		}

		this.model.setSaveable(false);
		this.emit('onBeforeChange', {
			selectorId: this.getId(),
			rowId: this.getRowId()
		});

		ajax.runAction(
			'catalog.productSelector.getSelectedSku',
			{
				json: {
					variationId,
					options: {
						priceId: this.basePriceId,
						urlBuilder: this.getConfig('URL_BUILDER_CONTEXT')
					}
				}
			}
		)
			.then(response => this.processResponse(response, {...this.options.config}));
	}

	onChangeFields(event)
	{
		const eventData = event.getData();

		if (!this.isSaveable() || eventData.rowId !== this.getRowId())
		{
			return;
		}

		if (!Type.isNil(eventData.productId) && eventData.productId !== this.getModel().getProductId())
		{
			return;
		}

		const fields = eventData.fields;
		const priceValue = Text.toNumber(fields.PRICE);
		if (priceValue > 0 && Type.isStringFilled(fields.CURRENCY))
		{
			fields.PRICES = {};
			fields.PRICES[this.getBasePriceId()] = {
				PRICE: priceValue,
				CURRENCY: fields.CURRENCY
			};
		}

		this.updateProduct(fields);
	}

	updateProduct(fields)
	{
		if (!Type.isPlainObject(fields))
		{
			return;
		}

		if (this.getModel().getId() <= 0 || this.getIblockId() <= 0)
		{
			return;
		}

		ajax.runAction(
			'catalog.productSelector.updateProduct',
			{
				json: {
					id: this.getModel().getId(),
					iblockId: this.getIblockId(),
					updateFields: fields
				}
			}
		);
	}

	onSaveImage(event)
	{
		const [, inputId, response] = event.getData();
		if (this.isEmptyModel() || this.isSimpleModel() || inputId !== this.getFileInput().getId())
		{
			return;
		}

		this.getFileInput().setId(response.data.id);
		this.getFileInput().setInputHtml(response.data.input);
		this.getFileInput().setView(response.data.preview);
		this.getModel().setMorePhotoValues(response.data.values);
		if (this.isImageFieldEnabled())
		{
			this.layoutImage();
		}
	}

	onProductSelect(productId, itemConfig)
	{
		this.emit('onBeforeChange', {
			selectorId: this.getId(),
			rowId: this.getRowId()
		});

		this.productSelectAjaxAction(productId, itemConfig);
	}

	productSelectAjaxAction(
		productId,
		itemConfig = {
			saveProductFields: false,
			isNew: false
		}
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
							urlBuilder: this.getConfig('URL_BUILDER_CONTEXT')
						}
					}
				}
			)
			.then(response => this.processResponse(response, {...this.options.config, ...itemConfig}, true));
	}

	processResponse(response, config = {}, isProductAction = false)
	{
		const data = response?.data || null;

		if (data)
		{
			this.changeSelectedElement(data, config);
		}
		else if (isProductAction)
		{
			this.clearState();
		}
		else
		{
			this.productSelectAjaxAction(this.getModel().getProductId());
		}

		this.unsubscribeToVariationChange();

		if (this.isEnabledChangesRendering())
		{
			this.clearLayout();
			this.layout();
		}

		const fields = data?.fields || null;

		this.emit('onChange', {
			selectorId: this.id,
			rowId: this.getRowId(),
			isNew: config.isNew || false,
			fields
		});
	}

	changeSelectedElement(data, config)
	{
		const productId = Text.toInteger(data.productId);
		const productChanged = this.getModel().getId() !== productId;

		if (productChanged)
		{
			const skuId = Text.toInteger(data.skuId);
			if (skuId > 0 && skuId !== productId)
			{
				config.productId = productId;
				this.model = new Sku(skuId, config);
			}
			else
			{
				this.model = new Product(productId, config);
			}
		}

		this.getModel().setFields(data.fields);

		const imageField = {
			id: '',
			input: '',
			preview: '',
			values: []
		};

		if (Type.isObject(data.image))
		{
			imageField.id = data.image.id;
			imageField.input = data.image.input;
			imageField.preview = data.image.preview;
			imageField.values = data.image.values;
			this.getModel().setFileType(data.fileType);
		}

		this.getFileInput().setId(imageField.id);
		this.getFileInput().setInputHtml(imageField.input);
		this.getFileInput().setView(imageField.preview);

		this.getModel().setMorePhotoValues(imageField.values);

		if (data.detailUrl)
		{
			this.getModel().setDetailPath(data.detailUrl);
		}

		if (Type.isObject(data.skuTree))
		{
			this.updateSkuTree(data.skuTree);
		}
	}
}
