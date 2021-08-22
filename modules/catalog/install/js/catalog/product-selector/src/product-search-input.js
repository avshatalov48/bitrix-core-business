import {ajax, Browser, Cache, Dom, Event, Loc, Tag, Text, Type} from 'main.core';
import {Dialog} from 'ui.entity-selector';
import './component.css';
import {EventEmitter} from 'main.core.events';
import {Base} from './models/base';
import {ProductSelector} from 'catalog.product-selector';

export class ProductSearchInput
{
	model: Base;
	selector: ProductSelector;
	cache = new Cache.MemoryCache();

	constructor(id, options = {})
	{
		this.id = id || Text.getRandom();
		this.selector = options.selector;
		if (!(this.selector instanceof ProductSelector))
		{
			throw new Error('Product selector instance not found.');
		}

		this.model = options.model || {};
		this.isEnabledSearch = options.isSearchEnabled;
		this.isEnabledDetailLink = options.isEnabledDetailLink;
		this.isEnabledEmptyProductError = options.isEnabledEmptyProductError;
		this.inputName = options.inputName || '';
	}

	getId()
	{
		return this.id;
	}

	getField(fieldName): string
	{
		return this.model.getField(fieldName);
	}

	getValue()
	{
		return this.getField(this.inputName);
	}

	isSearchEnabled(): boolean
	{
		return this.isEnabledSearch;
	}

	toggleIcon(icon, value)
	{
		if (Type.isDomNode(icon))
		{
			Dom.style(icon, 'display', value);
		}
	}

	getNameBlock(): HTMLElement
	{
		return this.cache.remember('nameBlock', () => {
			return Tag.render`
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${this.getNameTag()}
					${this.getNameInput()}
					${this.getHiddenNameInput()}
				</div>
			`;
		});
	}

	getNameTag(): ?HTMLElement
	{
		if (!this.model.isNew())
		{
			return '';
		}

		return Tag.render`
			<div class="ui-ctl-tag">${Loc.getMessage('CATALOG_SELECTOR_NEW_TAG_TITLE')}</div>
		`;
	}

	getNameInput(): HTMLInputElement
	{
		return this.cache.remember('nameInput', () => {
			return Tag.render`
				<input type="text" 
					class="ui-ctl-element ui-ctl-textbox" 
					autocomplete="off"
					value="${Text.encode(this.getValue())}"
					placeholder="${Text.encode(this.getPlaceholder())}"
					onchange="${this.handleNameInputHiddenChange.bind(this)}"
				>
			`;
		});
	}

	getHiddenNameInput(): HTMLInputElement
	{
		return this.cache.remember('hiddenNameInput', () => {
			return Tag.render`
				<input
				 	type="hidden" 
					name="${Text.encode(this.inputName)}" 
					value="${Text.encode(this.getValue())}"
				>
			`;
		});
	}

	handleNameInputHiddenChange(event: UIEvent)
	{
		this.getHiddenNameInput().value = event.target.value;
	}

	getClearIcon(): HTMLElement
	{
		return this.cache.remember('closeIcon', () => {
			return Tag.render`
				<button
					class="ui-ctl-after ui-ctl-icon-clear" 
					onclick="${this.handleClearIconClick.bind(this)}"
				></button>
			`;
		});
	}

	getArrowIcon(): HTMLElement
	{
		return this.cache.remember('arrowIcon', () => {
			return Tag.render`
				<a
					href="${this.model.getDetailPath()}"
					target="_blank"
					class="ui-ctl-after ui-ctl-icon-forward"
				></button>
			`;
		});
	}

	getSearchIcon(): HTMLElement
	{
		return this.cache.remember('searchIcon', () => {
			return Tag.render`
				<button
					class="ui-ctl-after ui-ctl-icon-search"
					onclick="${this.handleSearchIconClick.bind(this)}"
				></button>
			`;
		});
	}

	layout(): HTMLElement
	{
		const block = Tag.render`<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon"></div>`;

		if (!Type.isStringFilled(this.getValue()))
		{
			this.toggleIcon(this.getClearIcon(), 'none');
		}

		block.appendChild(this.getClearIcon());

		if (this.showDetailLink() && Type.isStringFilled(this.getValue()))
		{
			this.toggleIcon(this.getClearIcon(), 'none');
			this.toggleIcon(this.getArrowIcon(), 'block');
			block.appendChild(this.getArrowIcon());
		}

		if (this.isSearchEnabled())
		{
			const iconValue = Type.isStringFilled(this.getValue()) ? 'none' : 'block';
			this.toggleIcon(this.getSearchIcon(), iconValue);
			block.appendChild(this.getSearchIcon());

			Event.bind(this.getNameInput(), 'click', this.handleShowSearchDialog.bind(this));
			Event.bind(this.getNameInput(), 'input', this.handleShowSearchDialog.bind(this));
			Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
			Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));
		}

		Event.bind(this.getNameInput(), 'click', this.handleIconsSwitchingOnNameInput.bind(this));
		Event.bind(this.getNameInput(), 'input', this.handleIconsSwitchingOnNameInput.bind(this));

		if (this.selector && this.selector.isSaveable())
		{
			Event.bind(this.getNameInput(), 'change', this.handleNameInputChange.bind(this));
		}

		block.appendChild(this.getNameBlock());
		return block;
	}

	showDetailLink(): string
	{
		return this.isEnabledDetailLink;
	}

	getDialog(): ?Dialog
	{
		return this.cache.remember('dialog', () => {
			return new Dialog({
				id: this.id,
				height: 300,
				context: 'catalog-products',
				targetNode: this.getNameInput(),
				enableSearch: false,
				multiple: false,
				dropdownMode: true,
				searchTabOptions: {
					stub: true,
					stubOptions: {
						title: Tag.message`${'CATALOG_SELECTOR_IS_EMPTY_TITLE'}`,
						subtitle: Tag.message`${'CATALOG_SELECTOR_IS_EMPTY_SUBTITLE'}`,
						arrow: true
					}
				},
				searchOptions: {
					allowCreateItem: true
				},
				events: {
					'Item:onSelect': this.onProductSelect.bind(this),
					'Search:onItemCreateAsync': this.createProduct.bind(this)
				},
				entities: [
					{
						id: 'product',
						options: {
							iblockId: this.selector.getIblockId(),
							basePriceId: this.selector.getBasePriceId()
						}
					}
				]
			});
		});
	}

	handleNameInputKeyDown(event: KeyboardEvent): void
	{
		const dialog = this.getDialog();
		if (event.key === 'Enter' && dialog.getActiveTab() === dialog.getSearchTab())
		{
			// prevent a form submit
			event.preventDefault();

			if ((Browser.isMac() && event.metaKey) || event.ctrlKey)
			{
				dialog.getSearchTab().getFooter().createItem();
			}
		}
	}

	handleIconsSwitchingOnNameInput(event: UIEvent): void
	{
		this.toggleIcon(this.getArrowIcon(), 'none');

		if (Type.isStringFilled(event.target.value))
		{
			this.toggleIcon(this.getClearIcon(), 'block');
			this.toggleIcon(this.getSearchIcon(), 'none');
		}
		else
		{
			this.toggleIcon(this.getClearIcon(), 'none');
			this.toggleIcon(this.getSearchIcon(), 'block');
		}
	}

	handleClearIconClick(event: UIEvent)
	{
		if (this.selector.isProductSearchEnabled() && !this.selector.isEmptyModel())
		{
			this.selector.clearState();
			this.selector.clearLayout();
			this.selector.layout();
			this.selector.searchInDialog();
		}
		else
		{
			this.getNameInput().value = '';
			this.toggleIcon(this.getClearIcon(), 'none');
		}

		this.selector.focusName();

		this.selector.emit('onClear', {
			selectorId: this.selector.getId(),
			rowId: this.selector.getRowId()
		});

		event.stopPropagation();
		event.preventDefault();
	}

	handleNameInputChange(event: UIEvent)
	{
		const value = event.target.value;

		EventEmitter.emit('ProductList::onChangeFields', {
			rowId: this.selector.getRowId(),
			fields: {
				'NAME': value
			}
		});
	}

	focusName()
	{
		requestAnimationFrame(() => this.getNameInput().focus());
	}

	searchInDialog(searchQuery: string = '')
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		const dialog = this.getDialog();
		if (dialog)
		{
			dialog.show();
			dialog.search(searchQuery);
		}
	}

	handleShowSearchDialog(event: UIEvent)
	{
		if (this.selector.isEmptyModel() || this.selector.isSimpleModel())
		{
			this.selector.searchInDialog(event.target.value);
		}
	}

	handleNameInputBlur(event: UIEvent)
	{
		// timeout to toggle clear icon handler while cursor is inside of name input
		setTimeout(() => {
			this.toggleIcon(this.getClearIcon(), 'none');

			if (this.showDetailLink() && Type.isStringFilled(this.getValue()))
			{
				this.toggleIcon(this.getSearchIcon(), 'none');
				this.toggleIcon(this.getArrowIcon(), 'block');
			}
			else
			{
				this.toggleIcon(this.getArrowIcon(), 'none');
				this.toggleIcon(this.getSearchIcon(), 'block');
			}
		}, 200);

		if (this.isSearchEnabled() && this.isEnabledEmptyProductError)
		{
			setTimeout(() => {
				if (this.selector.isEmptyModel())
				{
					this.model.setError(
						'NOT_SELECTED_PRODUCT',
						Loc.getMessage('CATALOG_SELECTOR_SELECTED_PRODUCT_TITLE')
					);

					this.selector.layoutErrors();
				}
			}, 200);
		}
	}

	handleSearchIconClick(event: UIEvent)
	{
		this.selector.searchInDialog();
		this.selector.focusName();

		event.stopPropagation();
		event.preventDefault();
	}

	resetModel(title)
	{
		const fields = this.selector.getModel().getFields();
		const newModel = this.selector.createModel({isSimpleModel: true});
		this.selector.setModel(newModel);
		fields['NAME'] = title;
		this.selector.getModel().setFields(fields);
	}

	onProductSelect(event)
	{
		const item = event.getData().item;
		item.getDialog().getTargetNode().value = item.getTitle();
		this.toggleIcon(this.getSearchIcon(), 'none');

		this.resetModel(item.getTitle());

		this.selector.clearLayout();
		this.selector.layout();

		if (this.selector)
		{
			this.selector.onProductSelect(item.getId(), {
				saveProductFields: item.getCustomData().get('saveProductFields'),
				isNew: item.getCustomData().get('isNew')
			});
		}

		item.getDialog().hide();
	}

	createProduct(event): Promise
	{
		const {searchQuery} = event.getData();
		this.resetModel(searchQuery.getQuery());
		return new Promise(
			(resolve, reject) => {
				const dialog: Dialog = event.getTarget();
				const fields = {
					NAME: searchQuery.getQuery(),
					IBLOCK_ID: this.selector.getIblockId()
				};

				const price = this.selector.getModel().getField('PRICE', null);
				if (!Type.isNil(price))
				{
					fields['PRICE'] = price;
				}

				const currency = this.selector.getModel().getField('CURRENCY', null);
				if (Type.isStringFilled(currency))
				{
					fields['CURRENCY'] = currency;
				}

				dialog.showLoader();
				ajax.runAction(
					'catalog.productSelector.createProduct',
					{
						json: {
							fields
						}
					}
				)
					.then(response => {
						dialog.hideLoader();
						const item = dialog.addItem({
							id: response.data.id,
							entityId: 'product',
							title: searchQuery.getQuery(),
							tabs: dialog.getRecentTab().getId(),
							customData: {
								saveProductFields: true,
								isNew: true
							}
						});

						if (item)
						{
							item.select();
						}

						dialog.hide();
						resolve();
					})
					.catch(() => reject());
			});
	}

	getPlaceholder(): string
	{
		return (
			this.isSearchEnabled() && this.selector.isEmptyModel()
				? Loc.getMessage('CATALOG_SELECTOR_BEFORE_SEARCH_TITLE')
				: Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE')
		);
	}
}
