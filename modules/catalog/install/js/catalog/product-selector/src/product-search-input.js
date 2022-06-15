import {Browser, Cache, Dom, Event, Extension, Loc, Tag, Text, Type} from 'main.core';
import {Dialog, Item} from 'ui.entity-selector';
import './component.css';
import {EventEmitter} from 'main.core.events';
import {ProductModel} from 'catalog.product-model';
import {ProductSelector} from 'catalog.product-selector';
import ProductSearchSelectorFooter from './product-search-selector-footer';
import ProductCreationLimitedFooter from './product-creation-limited-footer';
import {SelectorErrorCode} from './selector-error-code';
import 'ui.notification';

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
	}

	destroy()
	{

	}

	getId()
	{
		return this.id;
	}

	getSelectorType()
	{
		return ProductSelector.INPUT_FIELD_NAME;
	}

	getField(fieldName): string
	{
		return this.model.getField(fieldName);
	}

	getValue()
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
			return Tag.render`
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

			Event.bind(this.getNameInput(), 'click', this.handleShowSearchDialog.bind(this));
			Event.bind(this.getNameInput(), 'input', this.handleShowSearchDialog.bind(this));
			Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
			Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));
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
			else if (this.model && this.model.isSaveable() && this.model.isCatalogExisted())
			{
				params.footer = ProductSearchSelectorFooter;
				params.footerOptions = {
					inputName: this.inputName,
					allowCreateItem: this.isAllowedCreateProduct(),
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
		if (!Type.isNil(this.isHasDialogItems))
		{
			return;
		}

		if (!this.selector.getModel().isEmpty())
		{
			this.isHasDialogItems = true;
			return;
		}
		
		// is null, that not send ajax
		this.isHasDialogItems = false;
		
		const dialog = this.getDialog();
		if (dialog.hasDynamicLoad())
		{
			dialog.hasRecentItems().then((isHasItems) => {
				if (isHasItems === true)
				{
					this.isHasDialogItems = true;
				}
			});	
		}
		else
		{
			this.isHasDialogItems = true;
		}
	}

	isAllowedCreateProduct()
	{
		return this.selector.getConfig('IS_ALLOWED_CREATION_PRODUCT', true);
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
		if (this.selector.isProductSearchEnabled() && !this.model.isEmpty())
		{
			this.selector.clearState();
			this.selector.clearLayout();
			this.selector.layout();
			this.selector.searchInDialog();
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

	searchInDialog(searchQuery: string = '')
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		const dialog = this.getDialog();
		if (dialog)
		{
			dialog.removeItems();

			searchQuery = searchQuery.trim();
			if (searchQuery === '')
			{
				if (this.isHasDialogItems === false)
				{
					dialog.hide();
					return;
				}
				
				dialog.loadState = 'UNSENT';
				dialog.load();
			}
			
			dialog.show();
			dialog.search(searchQuery);
		}
	}

	handleShowSearchDialog(event: UIEvent)
	{
		this.searchInDialog(this.getNameInput().value);
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
						|| !Type.isStringFilled(this.getNameInput().value)
					)
				)
				{
					this.model.getErrorCollection().setError(
						SelectorErrorCode.NOT_SELECTED_PRODUCT,
						Loc.getMessage('CATALOG_SELECTOR_SELECTED_PRODUCT_TITLE')
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

	getImmutableFieldNames()
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
		this.model.getErrorCollection().clearErrors();
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

		this.cache.delete('dialog');
	}

	createProductModelFromSearchQuery(searchQuery: string)
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
