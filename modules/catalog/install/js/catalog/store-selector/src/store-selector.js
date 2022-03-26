import {Cache, Dom, Event, Extension, Loc, Tag, Text, Type} from 'main.core';
import 'ui.forms';
import 'ui.notification';
import {EventEmitter} from 'main.core.events';
import {StoreSearchInput} from "./store-search-input";
import './component.css';
import {ProductModel} from "catalog.product-model";

const instances = new Map();

export class StoreSelector extends EventEmitter
{
	static MODE_VIEW = 'view';
	static MODE_EDIT = 'edit';
	static INPUT_FIELD_TITLE = 'STORE_TITLE';
	static INPUT_FIELD_ID = 'STORE_ID';

	mode: StoreSelector.MODE_EDIT | StoreSelector.MODE_VIEW = StoreSelector.MODE_EDIT;
	productId: number = null;
	cache = new Cache.MemoryCache();
	searchInput: ?StoreSearchInput;

	#storeInfo: Map = new Map();

	static getById(id: string): ?StoreSelector
	{
		return instances.get(id) || null;
	}

	constructor(id, options = {})
	{
		super();
		this.setEventNamespace('BX.Catalog.StoreSelector');

		this.id = id || Text.getRandom();
		options.inputFieldTitle = options.inputFieldTitle || StoreSelector.INPUT_FIELD_TITLE;
		options.inputFieldId = options.inputFieldId || StoreSelector.INPUT_FIELD_ID;
		this.options = options || {};

		this.setMode(options.mode);

		if (options.model instanceof ProductModel && options.model.getField(options.inputFieldId) > 0)
		{
			this.#storeInfo.set('id', options.model.getField(options.inputFieldId));
			const name =
				Type.isStringFilled(options.model.getField(options.inputFieldTitle))
					? options.model.getField(options.inputFieldTitle)
					: ''
			;

			this.setProductId(options.model.getSkuId());
			this.#storeInfo.set('title', name);
		}
		else
		{
			const settingsCollection = Extension.getSettings('catalog.store-selector');

			this.#storeInfo.set('id', settingsCollection.get('defaultStoreId'));
			this.#storeInfo.set('title', settingsCollection.get('defaultStoreName'));
		}

		this.searchInput = new StoreSearchInput(this.id, {
			selector: this,
			inputName: this.options.inputFieldTitle,
		});

		// this.setDetailPath(this.getConfig('DETAIL_PATH'));
		this.layout();

		instances.set(this.id, this);
	}

	setMode(mode: StoreSelector.MODE_VIEW | StoreSelector.MODE_EDIT): void
	{
		if (!Type.isNil(mode))
		{
			this.mode = mode === StoreSelector.MODE_VIEW ? StoreSelector.MODE_VIEW : StoreSelector.MODE_EDIT;
		}
	}

	setProductId(productId: number): void
	{
		productId = Text.toNumber(productId);
		if (productId > 0)
		{
			this.productId = productId;
		}
		else
		{
			this.productId = null;
		}

		this.searchInput?.clearDialogCache();
	}

	isViewMode(): boolean
	{
		return this.mode === StoreSelector.MODE_VIEW;
	}

	isSaveable(): boolean
	{
		return !this.isViewMode() && this.isSaveable();
	}

	getId(): string
	{
		return this.id;
	}

	getProductId(): ?number
	{
		return this.productId;
	}

	getConfig(name, defaultValue)
	{
		return BX.prop.get(this.options.config, name, defaultValue);
	}

	getDetailPath(): string
	{
		return this.getConfig('detailPath', '');
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
		const block = Tag.render`<div class="catalog-store-field-inner"></div>`;
		wrapper.appendChild(block);
		block.appendChild(this.layoutNameBlock());
	}

	focusName(): this
	{
		if (this.searchInput)
		{
			this.searchInput.focusName();
		}

		return this;
	}

	onClear()
	{
		this.clearState();
		this.clearLayout();
		this.layout();
		this.searchInput.searchInDialog();
		this.focusName();

		this.emit('onClear', {
			selectorId: this.getId(),
			rowId: this.getRowId()
		});
	}

	clearState(): void
	{
		this.#storeInfo.clear();
	}

	clearLayout(): void
	{
		const wrapper = this.getWrapper();
		if (wrapper)
		{
			wrapper.innerHTML = '';
		}
	}

	unsubscribeEvents()
	{
	}

	defineWrapperClass(wrapper)
	{
		if (this.isViewMode())
		{
			Dom.addClass(wrapper, 'catalog-store-view');
			Dom.removeClass(wrapper, 'catalog-store-edit');
		}
		else
		{
			Dom.addClass(wrapper, 'catalog-store-edit');
			Dom.removeClass(wrapper, 'catalog-store-view');
		}
	}

	getViewHtml(): HTMLElement
	{
		const storeTitle = Text.encode(this.getStoreTitle());
		const titlePlaceholder = Loc.getMessage('CATALOG_STORE_SELECTOR_VIEW_NAME_TITLE');

		if (this.getDetailPath())
		{
			return Tag.render`
				<a href="${this.getDetailPath()}" title="${titlePlaceholder}">${storeTitle}</a>
			`;
		}

		return Tag.render`<span title="${titlePlaceholder}">${storeTitle}</span>`;

	}

	layoutNameBlock(): HTMLElement
	{
		const block = Tag.render`<div class="catalog-store-field-input"></div>`;

		if (this.isViewMode())
		{
			block.appendChild(this.getViewHtml());
		}
		else
		{
			block.appendChild(this.searchInput.layout());
		}

		return block;
	}

	getStoreTitle()
	{
		return this.#storeInfo.get('title') || '';
	}

	getStoreId()
	{
		return this.#storeInfo.get('id') || '';
	}

	onStoreSelect(storeId, storeTitle)
	{
		if (storeTitle === '')
		{
			storeTitle = Loc.getMessage('CATALOG_STORE_SELECTOR_EMPTY_STORE_TITLE');
		}
		this.#storeInfo.set('id', storeId);
		this.#storeInfo.set('title', storeTitle);
		this.clearLayout();
		this.layout();

		this.emit('onChange', {
			selectorId: this.id,
			rowId: this.getRowId(),
			fields: [
				{
					NAME: this.options.inputFieldId,
					VALUE: storeId,
				},
				{
					NAME: this.options.inputFieldTitle,
					VALUE: storeTitle,
				},
			],
		});
	}

	getRowId(): string
	{
		return this.getConfig('ROW_ID');
	}
}
