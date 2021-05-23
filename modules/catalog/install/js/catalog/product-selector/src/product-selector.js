import {ajax, Cache, Dom, Event, Loc, Runtime, Tag, Text, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {SkuTree} from 'catalog.sku-tree';
import {ProductSearchInput} from "./product-search-input";
import {ProductImageInput} from "./product-image-input";
import {Empty} from "./models/empty";
import {Sku} from "./models/sku";
import {Product} from "./models/product";
import {Base} from "./models/base";
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

	static getById(id: string): ?ProductSelector
	{
		return instances.get(id) || null;
	}

	constructor(id, options = {})
	{
		super();
		this.setEventNamespace('BX.Catalog.ProductSelector');

		this.id = id || Text.getRandom();
		this.options = options || {};

		this.iblockId = Text.toNumber(options.iblockId);
		this.basePriceId = Text.toNumber(options.basePriceId);

		this.setMode(options.mode);

		this.model = this.createModel(options);
		this.model.setFields(options.fields);
		this.model.setMorePhotoValues(options.morePhotoValues);
		this.model.setDetailPath(this.getConfig('DETAIL_PATH'));

		this.skuTree = options.skuTree || null;
		this.setFileType(options.fileType);

		this.layout();

		EventEmitter.subscribe('ProductList::onChangeFields', Runtime.debounce(this.onChangeFields, 500, this));

		instances.set(this.id, this);
	}

	createModel(options = {}): Base
	{
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

	getModel(): Base
	{
		return this.model;
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

	isInputDetailLinkEnabled(): boolean
	{
		return this.getConfig('ENABLE_INPUT_DETAIL_LINK', false) && Type.isStringFilled(this.model.getDetailPath());
	}

	getWrapper(): HTMLElement
	{
		this.wrapper = document.getElementById(this.id);

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
		this.layoutImage();

		const block = Tag.render`<div class="catalog-product-field-inner"></div>`;
		wrapper.appendChild(block);
		block.appendChild(this.layoutNameBlock());
		block.appendChild(this.getImageContainer());

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

	layoutImage()
	{
		this.getImageContainer().innerHTML = '';
		this.getImageContainer().appendChild(this.getFileInput().layout());
		this.refreshImageSelectorId = null;
	}

	clearState(): void
	{
		this.model = this.createModel();
		this.fileInput.setInputHtml(this.options.fileInput || '');
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
				inputName: 'NAME',
				isSearchEnabled: this.isProductSearchEnabled(),
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
				selectable: this.getConfig('ENABLE_SKU_SELECTION', true)
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
			wrapper.appendChild(skuTree.layout());
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

	saveFiles(rebuild)
	{
		const imageValues = this.getModel().getMorePhotoValues();
		if (this.submitFileTimeOut)
		{
			clearTimeout(this.submitFileTimeOut);
		}

		const requestId = Text.getRandom(20);
		this.refreshImageSelectorId = requestId;
		this.submitFileTimeOut = setTimeout(() => {
			ajax.runAction(
				'catalog.productSelector.saveMorePhoto',
				{
					json: {
						productId: this.model.getProductId(),
						variationId: this.model.getId(),
						iblockId: this.getIblockId(),
						imageValues
					}
				}
			).then((response) => {
				if (!rebuild && this.refreshImageSelectorId === requestId)
				{
					return;
				}
				this.getFileInput().setId(response.data.id);
				this.getFileInput().setInputHtml(response.data.input);
				this.getFileInput().setView(response.data.preview);
				this.getModel().setMorePhotoValues(response.data.values);
				this.layoutImage();
			});
		}, 500);
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

		this.clearLayout();
		this.layout();

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
