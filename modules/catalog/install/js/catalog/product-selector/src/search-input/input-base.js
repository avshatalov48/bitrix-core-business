import { ProductModel } from 'catalog.product-model';
import { ProductSelector } from 'catalog.product-selector';
import { ajax, Cache, Dom, Event, Runtime, Tag, Text, Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Dialog, Item } from 'ui.entity-selector';
import { DialogMode } from './dialog-mode';
import { SelectorErrorCode } from '../selector-error-code';

export class ProductSearchInputBase
{
	model: ProductModel;
	selector: ProductSelector;
	cache = new Cache.MemoryCache();

	constructor(id, options = {})
	{
		this.options = options;

		this.id = id || Text.getRandom();
		this.selector = options.selector;
		if (!(this.selector instanceof ProductSelector))
		{
			throw new TypeError('Product selector instance not found.');
		}

		this.model = options.model || {};
		this.isEnabledDetailLink = options.isEnabledDetailLink;
		this.inputName = options.inputName || ProductSelector.INPUT_FIELD_NAME;
		this.loadedSelectedItem = null;

		this.handleSearchInput = Runtime.debounce(this.searchInDialog, 500, this);
	}

	layout(): HTMLElement
	{
		this.#clearInputCache();
		const block = Tag.render`<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon"></div>`;

		this.toggleIcon(this.getClearIcon(), 'none');
		Dom.append(this.getClearIcon(), block);

		if (this.isSearchEnabled())
		{
			if (this.selector.isProductSearchEnabled())
			{
				this.#initHasDialogItems();
			}

			this.toggleIcon(
				this.#getSearchIcon(),
				Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block',
			);
			Dom.append(this.#getSearchIcon(), block);

			Event.bind(this.getNameInput(), 'click', this.handleClickNameInput.bind(this));
			Event.bind(this.getNameInput(), 'input', this.handleSearchInput);
			Event.bind(this.getNameInput(), 'blur', this.#handleNameInputBlur.bind(this));
			Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));

			this.dialogMode = this.model.isCatalogExisted()
				? DialogMode.SHOW_PRODUCT_ITEM
				: DialogMode.SHOW_RECENT
			;
		}

		if (this.showDetailLink() && Type.isStringFilled(this.getValue()))
		{
			this.toggleIcon(this.getClearIcon(), 'none');
			this.toggleIcon(this.#getSearchIcon(), 'none');
			this.toggleIcon(this.#getArrowIcon(), 'block');
			Dom.append(this.#getArrowIcon(), block);
		}

		Event.bind(this.getNameInput(), 'click', this.#handleIconsSwitchingOnNameInput.bind(this));
		Event.bind(this.getNameInput(), 'input', this.#handleIconsSwitchingOnNameInput.bind(this));
		Event.bind(this.getNameInput(), 'change', this.#handleNameInputChange.bind(this));

		Dom.append(this.getNameBlock(), block);

		return block;
	}

	getId(): string
	{
		return this.id;
	}

	getField(fieldName): string
	{
		return this.model.getField(fieldName);
	}

	getValue(): string
	{
		return this.getField(this.inputName);
	}

	getFilledValue(): string
	{
		return this.getNameInput().value || '';
	}

	getSearchQuery(): string
	{
		return this.getFilledValue().trim();
	}

	isSearchQueryEmpty(): boolean
	{
		return this.getSearchQuery() === '';
	}

	isSearchEnabled(): boolean
	{
		return Boolean(this.options.isSearchEnabled);
	}

	toggleIcon(icon, value): void
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
					${this.#getHiddenNameInput()}
				</div>
			`;
		});
	}

	getNameTag(): ?HTMLElement
	{
		return null;
	}

	getNameInput(): HTMLInputElement
	{
		return this.cache.remember('nameInput', () => {
			const input = Tag.render`
				<input type="text"
					class="ui-ctl-element ui-ctl-textbox"
					autocomplete="off"
					data-name="${Text.encode(this.inputName)}"
					value="${Text.encode(this.getValue())}"
					placeholder="${Text.encode(this.getPlaceholder())}"
					title="${Text.encode(this.getValue())}"
					onchange="${this.#handleNameInputHiddenChange.bind(this)}"
				>
			`;

			if (this.selector.getConfig('SELECTOR_INPUT_DISABLED', false))
			{
				Dom.addClass(input, 'ui-ctl-disabled');
				input.setAttribute('disabled', true);
			}

			return input;
		});
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

	showDetailLink(): boolean
	{
		return this.isEnabledDetailLink;
	}

	handleNameInputKeyDown(event: KeyboardEvent): void
	{}

	clearErrors(): void
	{
		const errors = this.model.getErrorCollection().getErrors();
		for (const code in errors)
		{
			if (ProductSelector.ErrorCodes.getCodes().includes(code))
			{
				this.model.getErrorCollection().removeError(code);
			}
		}
	}

	focusName(): void
	{
		requestAnimationFrame(() => this.getNameInput().focus());
	}

	removeSpotlight(): void
	{}

	removeQrAuth(): void
	{}

	destroy(): void
	{}

	showItems(): void
	{
		if (this.getFilledValue() === '')
		{
			this.showPreselectedItems();

			return;
		}

		if (!this.model.isCatalogExisted() || this.dialogMode !== DialogMode.SHOW_PRODUCT_ITEM)
		{
			this.searchInDialog();

			return;
		}

		this.#showSelectedItem();
	}

	showPreselectedItems(): void
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		this.dialogMode = DialogMode.SHOW_RECENT;
		const dialog = this.getDialog();
		this.loadPreselectedItems();

		dialog.selectFirstTab();
		dialog.show();
		this.#hideFooter();
	}

	isFooterHidable(): boolean
	{
		return true;
	}

	/**
	 * @abstract
	 */
	searchInDialog(): void
	{
		throw new Error('Method "searchInDialog" should be overridden');
	}

	/**
	 * @abstract
	 */
	handleClickNameInput(): void
	{
		throw new Error('Method "handleClickNameInput" should be overridden');
	}

	/**
	 * @abstract
	 */
	getPlaceholder(): string
	{
		throw new Error('Method "getPlaceholder" should be overridden');
	}

	getDialog(): Dialog
	{
		return this.cache.remember('dialog', () => {
			return new Dialog(this.getDialogParams());
		});
	}

	getDialogParams(): Object
	{
		const entity = {
			id: 'product',
			options: {
				iblockId: this.model.getIblockId(),
				basePriceId: this.model.getBasePriceId(),
				currency: this.model.getCurrency(),
			},
			dynamicLoad: true,
			dynamicSearch: true,
		};
		const restrictedProductTypes = this.selector.getConfig('RESTRICTED_PRODUCT_TYPES', null);
		if (!Type.isNil(restrictedProductTypes))
		{
			entity.options.restrictedProductTypes = restrictedProductTypes;
		}

		return {
			id: `${this.id}_product`,
			height: 300,
			width: Math.max(this.getNameInput()?.offsetWidth, 565),
			context: 'catalog-products',
			targetNode: this.getNameInput(),
			enableSearch: false,
			multiple: false,
			dropdownMode: true,
			recentTabOptions: {
				stub: true,
				stubOptions: {
					title: Tag.message`${'CATALOG_SELECTOR_RECENT_TAB_STUB_TITLE'}`,
				},
			},
			entities: [entity],
			events: {
				'Item:onSelect': this.onProductSelect.bind(this),
				onShow: this.onDialogShow.bind(this),
			},
		};
	}

	onDialogShow(event: BaseEvent): void
	{}

	/**
	 * @abstract
	 */
	getOnProductSelectConfig(item: Item): Object
	{
		throw new Error('Method "getOnProductSelectConfig" should be overridden');
	}

	onProductSelect(event: BaseEvent): void
	{
		const item = event.getData().item;

		item.getDialog().getTargetNode().value = item.getTitle();
		this.toggleIcon(this.#getSearchIcon(), 'none');
		this.clearErrors();
		if (this.selector)
		{
			this.selector.onProductSelect(
				item.getId(),
				this.getOnProductSelectConfig(item),
			);

			this.selector.clearLayout();
			this.selector.layout();
		}

		this.dialogMode = DialogMode.SHOW_PRODUCT_ITEM;
		this.loadedSelectedItem = item;
		this.cache.delete('dialog');
	}

	onChangeValue(value: string): void
	{
		this.getNameInput().title = value;
		this.getNameInput().value = value;
	}

	handleClearIconClick(event: UIEvent): void
	{
		this.clear();

		event.stopPropagation();
		event.preventDefault();
	}

	clear(): void
	{
		this.selector.emit('onBeforeClear', {
			selectorId: this.selector.getId(),
			rowId: this.selector.getRowId(),
		});

		this.loadedSelectedItem = null;
		if (this.selector.isProductSearchEnabled() && !this.model.isEmpty())
		{
			this.selector.clearState();
			this.selector.clearLayout();
			this.selector.layout();
		}
		else
		{
			const newValue = '';
			this.toggleIcon(this.getClearIcon(), 'none');
			this.onChangeValue(newValue);
		}

		this.selector.focusName();

		this.selector.emit('onClear', {
			selectorId: this.selector.getId(),
			rowId: this.selector.getRowId(),
		});
	}

	#handleIconsSwitchingOnNameInput(event: UIEvent): void
	{
		this.toggleIcon(this.#getArrowIcon(), 'none');

		if (Type.isStringFilled(event.target.value))
		{
			this.toggleIcon(this.getClearIcon(), 'block');
			this.toggleIcon(this.#getSearchIcon(), 'none');
		}
		else
		{
			this.toggleIcon(this.getClearIcon(), 'none');
			if (this.isSearchEnabled())
			{
				this.toggleIcon(this.#getSearchIcon(), 'block');
			}
		}
	}

	#initHasDialogItems(): void
	{
		if (!Type.isNil(this.selector.getConfig('EXIST_DIALOG_ITEMS')))
		{
			return;
		}

		if (!this.selector.getModel().isEmpty())
		{
			this.selector.setConfig('EXIST_DIALOG_ITEMS', true);

			return;
		}

		// is null, that not send ajax
		this.selector.setConfig('EXIST_DIALOG_ITEMS', false);

		const dialog = this.getDialog();
		if (dialog.hasDynamicLoad())
		{
			this.loadPreselectedItems();
			dialog.subscribeOnce('onLoad', () => {
				if (dialog.getPreselectedItems().length > 1)
				{
					this.selector.setConfig('EXIST_DIALOG_ITEMS', true);
				}
			});
		}
		else
		{
			this.selector.setConfig('EXIST_DIALOG_ITEMS', true);
		}
	}

	#hideFooter(): void
	{
		if (this.isFooterHidable())
		{
			this.getDialog().getFooter()?.hide();
		}
	}

	#handleNameInputChange(event: UIEvent): void
	{
		const value = event.target.value;
		this.onChangeValue(value);
	}

	#clearInputCache(): void
	{
		this.cache.delete('dialog');
		this.cache.delete('nameBlock');
		this.cache.delete('nameInput');
		this.cache.delete('hiddenNameInput');
	}

	loadPreselectedItems(): void
	{
		const dialog = this.getDialog();

		if (dialog.isLoading())
		{
			return;
		}

		dialog.removeItems();
		dialog.loadState = 'UNSENT';
		this.loadedSelectedItem = null;

		dialog.load();
	}

	#showSelectedItem(): void
	{
		const dialog = this.getDialog();

		dialog.removeItems();

		new Promise((resolve, reject) => {
			if (!Type.isNil(this.loadedSelectedItem))
			{
				resolve();

				return;
			}

			dialog.showLoader();
			ajax.runAction(
				'catalog.productSelector.getSkuSelectorItem',
				{
					json: {
						id: this.selector.getModel().getSkuId(),
						options: {
							iblockId: this.model.getIblockId(),
							basePriceId: this.model.getBasePriceId(),
							currency: this.model.getCurrency(),
						},
					},
				},
			)
				.then((response) => {
					dialog.hideLoader();
					this.loadedSelectedItem = null;
					if (Type.isObject(response.data) && !dialog.isLoading())
					{
						this.loadedSelectedItem = dialog.addItem(response.data);
					}
					resolve();
				})
				.catch((error) => reject(error));
		})
			.then(() => {
				if (Type.isNil(this.loadedSelectedItem))
				{
					this.searchInDialog();
				}
				else
				{
					dialog.setPreselectedItems([this.selector.getModel().getSkuId()]);
					dialog.getRecentTab().getRootNode().addItem(this.loadedSelectedItem);
					dialog.selectFirstTab();
					this.#hideFooter();
				}
			})
			.catch((error) => console.error(error));

		dialog.getPopup().show();
		this.#hideFooter();
	}

	#handleNameInputHiddenChange(event: UIEvent)
	{
		this.#getHiddenNameInput().value = event.target.value;
	}

	#handleSearchIconClick(event: UIEvent): void
	{
		this.searchInDialog();
		this.focusName();

		event.stopPropagation();
		event.preventDefault();
	}

	#handleNameInputBlur(event: UIEvent): void
	{
		// timeout to toggle clear icon handler while cursor is inside of name input
		setTimeout(() => {
			this.toggleIcon(this.getClearIcon(), 'none');

			if (this.showDetailLink() && Type.isStringFilled(this.getValue()))
			{
				if (this.isSearchEnabled())
				{
					this.toggleIcon(this.#getSearchIcon(), 'none');
				}
				this.toggleIcon(this.#getArrowIcon(), 'block');
			}
			else
			{
				this.toggleIcon(this.#getArrowIcon(), 'none');
				if (this.isSearchEnabled())
				{
					this.toggleIcon(
						this.#getSearchIcon(),
						Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block',
					);
				}
			}
		}, 200);

		if (this.isSearchEnabled() && this.selector.isEnabledEmptyProductError())
		{
			setTimeout(() => {
				if (
					!this.selector.inProcess()
					&& (
						this.model.isEmpty()
						|| !Type.isStringFilled(this.getFilledValue())
					)
				)
				{
					this.model.getErrorCollection().setError(
						SelectorErrorCode.NOT_SELECTED_PRODUCT,
						this.selector.getEmptySelectErrorMessage(),
					);

					this.selector.layoutErrors();
				}
			}, 200);
		}
	}

	#getHiddenNameInput(): HTMLInputElement
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

	#getArrowIcon(): HTMLElement
	{
		return this.cache.remember('arrowIcon', () => {
			return Tag.render`
				<a
					href="${Text.encode(this.model.getDetailPath())}"
					target="_blank"
					class="ui-ctl-after ui-ctl-icon-forward"
				>
			`;
		});
	}

	#getSearchIcon(): HTMLElement
	{
		return this.cache.remember('searchIcon', () => {
			return Tag.render`
				<button
					class="ui-ctl-after ui-ctl-icon-search"
					onclick="${this.#handleSearchIconClick.bind(this)}"
				></button>
			`;
		});
	}
}
