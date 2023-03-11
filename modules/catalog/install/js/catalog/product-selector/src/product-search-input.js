import {ajax, Browser, Cache, Dom, Event, Extension, Loc, Runtime, Tag, Text, Type} from 'main.core';
import {Dialog, Item} from 'ui.entity-selector';
import './component.css';
import {EventEmitter} from 'main.core.events';
import {ProductModel} from 'catalog.product-model';
import {ProductSelector} from 'catalog.product-selector';
import ProductSearchSelectorFooter from './product-search-selector-footer';
import ProductCreationLimitedFooter from './product-creation-limited-footer';
import {SelectorErrorCode} from './selector-error-code';
import 'ui.notification';

class DialogMode
{
	static SEARCHING: string = 'SEARCHING';
	static SHOW_PRODUCT_ITEM: string = 'SHOW_PRODUCT_ITEM';
	static SHOW_RECENT: string = 'SHOW_RECENT';
}

export class ProductSearchInput
{
	static SEARCH_TYPE_ID = 'product';

	model: ProductModel;
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
		this.inputName = options.inputName || ProductSelector.INPUT_FIELD_NAME;
		this.immutableFieldNames = [ProductSelector.INPUT_FIELD_BARCODE, ProductSelector.INPUT_FIELD_NAME];
		if (!this.immutableFieldNames.includes(this.inputName))
		{
			this.immutableFieldNames.push(this.inputName);
		}
		this.ajaxInProcess = false;
		this.loadedSelectedItem = null;

		this.handleSearchInput = Runtime.debounce(this.searchInDialog, 500, this);
	}

	destroy()
	{

	}

	getId(): string
	{
		return this.id;
	}

	getSelectorType(): string
	{
		return ProductSelector.INPUT_FIELD_NAME;
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
			const input = Tag.render`
				<input type="text"
					class="ui-ctl-element ui-ctl-textbox"
					autocomplete="off"
					data-name="${Text.encode(this.inputName)}"
					value="${Text.encode(this.getValue())}"
					placeholder="${Text.encode(this.getPlaceholder())}"
					title="${Text.encode(this.getValue())}"
					onchange="${this.handleNameInputHiddenChange.bind(this)}"
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
					href="${Text.encode(this.model.getDetailPath())}"
					target="_blank"
					class="ui-ctl-after ui-ctl-icon-forward"
				>
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
		this.clearInputCache();
		const block = Tag.render`<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon"></div>`;

		this.toggleIcon(this.getClearIcon(), 'none');
		Dom.append(this.getClearIcon(), block);

		if (this.isSearchEnabled())
		{
			if (this.selector.isProductSearchEnabled())
			{
				this.initHasDialogItems();
			}

			this.toggleIcon(
				this.getSearchIcon(),
				Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block'
			);
			Dom.append(this.getSearchIcon(), block);

			Event.bind(this.getNameInput(), 'click', this.handleClickNameInput.bind(this));
			Event.bind(this.getNameInput(), 'input', this.handleSearchInput);
			Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
			Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));

			this.dialogMode =
				this.model.isCatalogExisted()
					? DialogMode.SHOW_PRODUCT_ITEM
					: DialogMode.SHOW_RECENT
			;
		}

		if (this.showDetailLink() && Type.isStringFilled(this.getValue()))
		{
			this.toggleIcon(this.getClearIcon(), 'none');
			this.toggleIcon(this.getSearchIcon(), 'none');
			this.toggleIcon(this.getArrowIcon(), 'block');
			Dom.append(this.getArrowIcon(), block);
		}

		Event.bind(this.getNameInput(), 'click', this.handleIconsSwitchingOnNameInput.bind(this));
		Event.bind(this.getNameInput(), 'input', this.handleIconsSwitchingOnNameInput.bind(this));
		Event.bind(this.getNameInput(), 'change', this.handleNameInputChange.bind(this));

		Dom.append(this.getNameBlock(), block);
		return block;
	}

	showDetailLink(): boolean
	{
		return this.isEnabledDetailLink;
	}

	getDialog(): ?Dialog
	{
		return this.cache.remember('dialog', () => {
			const searchTypeId = ProductSearchInput.SEARCH_TYPE_ID ;
			const entity = {
				id: searchTypeId,
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

			const params = {
				id: this.id + '_' + searchTypeId,
				height: 300,
				width: Math.max(this.getNameInput()?.offsetWidth, 565),
				context: 'catalog-products',
				targetNode: this.getNameInput(),
				enableSearch: false,
				multiple: false,
				dropdownMode: true,
				searchTabOptions: {
					stub: true,
					stubOptions: {
						title: Tag.message`${'CATALOG_SELECTOR_IS_EMPTY_TITLE'}`,
						subtitle:
							this.isAllowedCreateProduct()
								? Tag.message`${'CATALOG_SELECTOR_IS_EMPTY_SUBTITLE'}`
								: ''
						,
						arrow: true
					}
				},
				events: {
					'Item:onSelect': this.onProductSelect.bind(this),
					'Search:onItemCreateAsync': this.createProduct.bind(this),
					'ChangeItem:onClick': this.showChangeNotification.bind(this),
				},
				entities: [entity]
			};

			const settingsCollection = Extension.getSettings('catalog.product-selector');
			if (Type.isObject(settingsCollection.get('limitInfo')))
			{
				params.footer = ProductCreationLimitedFooter;
			}
			else if (this.model && this.model.isCatalogExisted())
			{
				params.footer = ProductSearchSelectorFooter;
				params.footerOptions = {
					inputName: this.inputName,
					allowEditItem: this.isAllowedEditProduct(),
					allowCreateItem: this.isAllowedCreateProduct(),
					errorAdminHint: settingsCollection.get('errorAdminHint'),
					creationLabel: Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE'),
					currentValue: this.getValue(),
				};
			}
			else
			{
				params.searchOptions = { allowCreateItem: this.isAllowedCreateProduct() };
			}

			return new Dialog(params);
		});
	}

	initHasDialogItems()
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
			this.#loadPreselectedItems();
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

	isAllowedCreateProduct(): boolean
	{
		return this.selector.getConfig('IS_ALLOWED_CREATION_PRODUCT', true) && this.selector.checkProductAddRights();
	}

	isAllowedEditProduct(): boolean
	{
		return this.selector.checkProductEditRights();
	}

	handleNameInputKeyDown(event: KeyboardEvent): void
	{
		const dialog = this.getDialog();
		if (event.key === 'Enter' && dialog.getActiveTab() === dialog.getSearchTab())
		{
			// prevent a form submit
			event.stopPropagation();
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
			if (this.isSearchEnabled())
			{
				this.toggleIcon(this.getSearchIcon(), 'block');
			}
		}
	}

	clearInputCache()
	{
		this.cache.delete('dialog');
		this.cache.delete('nameBlock');
		this.cache.delete('nameInput');
		this.cache.delete('hiddenNameInput');
	}

	handleClearIconClick(event: UIEvent)
	{
		this.selector.emit('onBeforeClear', {
			selectorId: this.selector.getId(),
			rowId: this.selector.getRowId()
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
			rowId: this.selector.getRowId()
		});

		event.stopPropagation();
		event.preventDefault();
	}

	handleNameInputChange(event: UIEvent)
	{
		const value = event.target.value;
		this.onChangeValue(value);
	}

	onChangeValue(value: string)
	{
		const fields = {};
		this.getNameInput().title = value;
		this.getNameInput().value = value;
		fields[this.inputName] = value;
		EventEmitter.emit('ProductSelector::onNameChange', {
			rowId: this.selector.getRowId(),
			fields
		});

		if (!this.selector.isEnabledAutosave())
		{
			return;
		}

		this.selector.getModel().setFields(fields);
		this.selector.getModel().save().then(() => {
			BX.UI.Notification.Center.notify({
				id: 'saving_field_notify_name',
				closeButton: false,
				content: Tag.render`<div>${Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_NAME')}</div>`,
				autoHide: true,
			});
		});
	}

	focusName()
	{
		requestAnimationFrame(() => this.getNameInput().focus());
	}

	searchInDialog(): void
	{
		const searchQuery = this.getFilledValue().trim();
		if (searchQuery === '')
		{
			if (this.isHasDialogItems === false)
			{
				this.getDialog().hide();
				return;
			}

			this.loadedSelectedItem = null;
			this.#showPreselectedItems()
			return;
		}

		this.dialogMode = DialogMode.SEARCHING;
		this.#searchItem(searchQuery);
		this.isSearchingInProcess = true;
	}

	#showSelectedItem()
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		const dialog = this.getDialog();
		dialog.removeItems();

		new Promise((resolve) => {
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
					}
				}
			)
				.then((response) => {
					dialog.hideLoader();
					this.loadedSelectedItem = null;
					if (Type.isObject(response.data) && !dialog.isLoading())
					{
						this.loadedSelectedItem = dialog.addItem(response.data);
					}
					resolve();
				});
		})
			.then(() => {
				if (!Type.isNil(this.loadedSelectedItem))
				{
					dialog.setPreselectedItems([this.selector.getModel().getSkuId()]);
					dialog.getRecentTab().getRootNode().addItem(this.loadedSelectedItem);
					dialog.selectFirstTab();
					dialog.getFooter()?.hide();
				}
				else
				{
					this.searchInDialog();
				}
			});

		dialog.getPopup().show();
		dialog.getFooter()?.hide();
	}

	#loadPreselectedItems()
	{
		const dialog = this.getDialog();
		if (dialog.isLoading())
		{
			return;
		}

		if (this.loadedSelectedItem)
		{
			dialog.removeItems();
			dialog.loadState = 'UNSENT';
			this.loadedSelectedItem = null;
		}

		dialog.load();
	}

	#showPreselectedItems()
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		this.dialogMode = DialogMode.SHOW_RECENT;
		const dialog = this.getDialog();
		this.#loadPreselectedItems();

		dialog.selectFirstTab();
		dialog.getFooter()?.hide();
		dialog.show();
	}

	#searchItem(searchQuery: string = '')
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		const dialog = this.getDialog();
		dialog.getPopup().show();
		dialog.search(searchQuery);
	}

	handleClickNameInput(): void
	{
		const dialog = this.getDialog();

		if (
			dialog.isOpen()
			|| (this.getFilledValue() === '' && this.isHasDialogItems === false)
		)
		{
			dialog.hide();

			return;
		}

		this.showItems();
	}

	showItems()
	{
		if (this.getFilledValue() === '')
		{
			this.#showPreselectedItems();
			return;
		}

		if (!this.model.isCatalogExisted() || this.dialogMode !== DialogMode.SHOW_PRODUCT_ITEM)
		{
			this.searchInDialog();
			return;
		}

		this.#showSelectedItem();
	}

	handleNameInputBlur(event: UIEvent)
	{
		// timeout to toggle clear icon handler while cursor is inside of name input
		setTimeout(() => {
			this.toggleIcon(this.getClearIcon(), 'none');

			if (this.showDetailLink() && Type.isStringFilled(this.getValue()))
			{
				if (this.isSearchEnabled())
				{
					this.toggleIcon(this.getSearchIcon(), 'none');
				}
				this.toggleIcon(this.getArrowIcon(), 'block');
			}
			else
			{
				this.toggleIcon(this.getArrowIcon(), 'none');
				if (this.isSearchEnabled())
				{
					this.toggleIcon(
						this.getSearchIcon(),
						Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block'
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
						this.selector.getEmptySelectErrorMessage()
					);

					this.selector.layoutErrors();
				}
			}, 200);
		}
	}

	handleSearchIconClick(event: UIEvent)
	{
		this.searchInDialog();
		this.focusName();

		event.stopPropagation();
		event.preventDefault();
	}

	getImmutableFieldNames(): []
	{
		return this.immutableFieldNames;
	}

	setInputValueOnProductSelect(item: Item)
	{
		item.getDialog().getTargetNode().value = item.getTitle()
	}

	onProductSelect(event)
	{
		const item = event.getData().item;
		this.setInputValueOnProductSelect(item);

		this.toggleIcon(this.getSearchIcon(), 'none');
		this.clearErrors();
		if (this.selector)
		{
			const isNew = item.getCustomData().get('isNew');
			const immutableFields = [];
			this.getImmutableFieldNames().forEach((key) => {
				if (!Type.isNil(item.getCustomData().get(key)))
				{
					this.model.setField(key, item.getCustomData().get(key));
					immutableFields.push(key);
				}
			});

			this.selector.onProductSelect(
				item.getId(),
					{
						isNew,
						immutableFields,
				}
			);

			this.selector.clearLayout();
			this.selector.layout();
		}

		this.dialogMode = DialogMode.SHOW_PRODUCT_ITEM;
		this.loadedSelectedItem = item;
		this.cache.delete('dialog');
	}

	clearErrors()
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

	createProductModelFromSearchQuery(searchQuery: string): ProductModel
	{
		const fields = {...this.selector.getModel().getFields()};
		fields[this.inputName] = searchQuery;

		return new ProductModel({
			isSimpleModel: true,
			isNew: true,
			currency: this.selector.options.currency,
			iblockId: this.selector.getModel().getIblockId(),
			basePriceId: this.selector.getModel().getBasePriceId(),
			fields
		})
	}

	createProduct(event): ?Promise
	{
		if (this.ajaxInProcess)
		{
			return;
		}

		this.ajaxInProcess = true;
		const dialog: Dialog = event.getTarget();
		const {searchQuery} = event.getData();
		const newProduct = this.createProductModelFromSearchQuery(searchQuery.getQuery());

		EventEmitter.emit(this.selector, 'onBeforeCreate', {model: newProduct});

		return new Promise(
			(resolve, reject) => {
				if (!this.checkCreationModel(newProduct))
				{
					this.ajaxInProcess = false;
					dialog.hide();
					reject();
					return;
				}

				dialog.showLoader();
				newProduct.save()
					.then(response => {
						dialog.hideLoader();
						const id = Text.toInteger(response.data.id);
						const item = dialog.addItem({
							id,
							entityId: ProductSearchInput.SEARCH_TYPE_ID,
							title: searchQuery.getQuery(),
							tabs: dialog.getRecentTab().getId(),
							customData: {
								isNew: true,
							}
						});

						this.selector.getModel().setOption('isSimpleModel', false);
						this.selector.getModel().setOption('isNew', true);

						this.getImmutableFieldNames().forEach((name) => {
							this.selector.getModel().setField(name, newProduct.getField(name));
							this.selector.getModel().setOption(name, newProduct.getField(name));
						});

						if (item)
						{
							item.select();
						}

						dialog.hide();
						this.cache.delete('dialog');
						this.ajaxInProcess = false;
						this.isHasDialogItems = true;
						resolve();
					})
					.catch((errorResponse) => {
						dialog.hideLoader();
						errorResponse.errors.forEach((error) => {
							BX.UI.Notification.Center.notify({
								closeButton: true,
								content: Tag.render`<div>${error.message}</div>`,
								autoHide: true,
							});
						});

						this.ajaxInProcess = false;
						reject();
					});
			});
	}

	checkCreationModel(creationModel: ProductModel): boolean
	{
		return true;
	}

	showChangeNotification(event): void
	{
		const {query} = event.getData();
		const options = {
			title: Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_' + this.selector.getType()),
			events: {
				onSave: () => {
					if (this.selector)
					{
						this.selector.getModel().setField(this.inputName, query);
						this.selector.getModel().save([this.inputName])
							.catch((errorResponse) => {
								errorResponse.errors.forEach((error) => {
									BX.UI.Notification.Center.notify({
										closeButton: true,
										content: Tag.render`<div>${error.message}</div>`,
										autoHide: true,
									});
								});
							});
					}
				}
			},
		};

		if (this.selector.getConfig('ROLLBACK_INPUT_AFTER_CANCEL', false))
		{
			options.declineCancelTitle = Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_CANCEL_TITLE');
			options.events.onCancel = () => {
				this.selector.clearLayout();
				this.selector.layout();
			};
		}

		this.selector.getModel().showSaveNotifier(
			'nameChanger_' + this.selector.getId(),
			options
		);
	}

	getPlaceholder(): string
	{
		return (
			this.isSearchEnabled() && this.model.isEmpty()
				? Loc.getMessage('CATALOG_SELECTOR_BEFORE_SEARCH_TITLE')
				: Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE')
		);
	}

	removeSpotlight()
	{
	}

	removeQrAuth()
	{
	}
}
