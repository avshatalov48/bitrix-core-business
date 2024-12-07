import { Browser, Extension, Loc, Tag, Text, Type } from 'main.core';
import { Dialog, Item } from 'ui.entity-selector';
import { EventEmitter } from 'main.core.events';
import { ProductModel } from 'catalog.product-model';
import { ProductSelector } from 'catalog.product-selector';
import { ProductSearchInputDefaultFooter } from './footer-default';
import { ProductSearchInputLimitedFooter } from './footer-limited';
import { DialogMode } from './dialog-mode';
import { ProductSearchInputBase } from './input-base';
import 'ui.notification';

export class ProductSearchInputDefault extends ProductSearchInputBase
{
	constructor(id, options = {})
	{
		super(id, options);

		this.immutableFieldNames = [ProductSelector.INPUT_FIELD_BARCODE, ProductSelector.INPUT_FIELD_NAME];
		if (!this.immutableFieldNames.includes(this.inputName))
		{
			this.immutableFieldNames.push(this.inputName);
		}

		this.ajaxInProcess = false;
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

	getDialogParams(): Object
	{
		const params = {
			...super.getDialogParams(),
			searchTabOptions: {
				stub: true,
				stubOptions: {
					title: Tag.message`${'CATALOG_SELECTOR_IS_EMPTY_TITLE'}`,
					subtitle: this.isAllowedCreateProduct()
						? Tag.message`${'CATALOG_SELECTOR_IS_EMPTY_SUBTITLE'}`
						: '',
					arrow: true,
				},
			},
		};

		const settingsCollection = Extension.getSettings('catalog.product-selector');
		if (Type.isObject(settingsCollection.get('limitInfo')))
		{
			params.footer = ProductSearchInputLimitedFooter;
		}
		else if (this.model && this.model.isCatalogExisted())
		{
			params.footer = ProductSearchInputDefaultFooter;
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

		params.events['Search:onItemCreateAsync'] = this.createProduct.bind(this);
		params.events['ChangeItem:onClick'] = this.showChangeNotification.bind(this);

		return params;
	}

	isAllowedCreateProduct(): boolean
	{
		return (
			this.selector.getConfig('IS_ALLOWED_CREATION_PRODUCT', true)
			&& this.selector.checkProductAddRights()
		);
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

	onChangeValue(value: string): void
	{
		super.onChangeValue(value);

		const fields = {};
		fields[this.inputName] = value;
		EventEmitter.emit('ProductSelector::onNameChange', {
			rowId: this.selector.getRowId(),
			fields,
		});

		if (!this.selector.isEnabledAutosave())
		{
			return;
		}

		this.selector.getModel().setFields(fields);
		this.selector.getModel().save()
			.then(() => {
				BX.UI.Notification.Center.notify({
					id: 'saving_field_notify_name',
					closeButton: false,
					content: Tag.render`<div>${Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_NAME')}</div>`,
					autoHide: true,
				});
			}).catch((error) => console.error(error));
	}

	searchInDialog(): void
	{
		if (this.isSearchQueryEmpty())
		{
			if (this.isHasDialogItems === false)
			{
				this.getDialog().hide();

				return;
			}

			this.loadedSelectedItem = null;
			this.showPreselectedItems();

			return;
		}

		this.dialogMode = DialogMode.SEARCHING;
		this.#searchItem(this.getSearchQuery());
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

	getImmutableFieldNames(): []
	{
		return this.immutableFieldNames;
	}

	getOnProductSelectConfig(item: Item): Object
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

		return {
			isNew,
			immutableFields,
		};
	}

	createProductModelFromSearchQuery(searchQuery: string): ProductModel
	{
		const fields = { ...this.selector.getModel().getFields() };
		fields[this.inputName] = searchQuery;

		return new ProductModel({
			isSimpleModel: true,
			isNew: true,
			currency: this.selector.options.currency,
			iblockId: this.selector.getModel().getIblockId(),
			basePriceId: this.selector.getModel().getBasePriceId(),
			fields,
		});
	}

	createProduct(event): ?Promise
	{
		if (this.ajaxInProcess)
		{
			return null;
		}

		this.ajaxInProcess = true;
		const dialog: Dialog = event.getTarget();
		const { searchQuery } = event.getData();
		const newProduct = this.createProductModelFromSearchQuery(searchQuery.getQuery());

		EventEmitter.emit(this.selector, 'onBeforeCreate', { model: newProduct });

		return new Promise((resolve, reject) => {
			if (!this.checkCreationModel(newProduct))
			{
				this.ajaxInProcess = false;
				dialog.hide();
				reject();

				return;
			}

			dialog.showLoader();
			newProduct.save()
				.then((response) => {
					dialog.hideLoader();
					const id = Text.toInteger(response.data.id);
					const item = dialog.addItem({
						id,
						entityId: 'product',
						title: searchQuery.getQuery(),
						tabs: dialog.getRecentTab().getId(),
						customData: {
							isNew: true,
						},
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
		const { query } = event.getData();
		const options = {
			title: Loc.getMessage(`CATALOG_SELECTOR_SAVING_NOTIFICATION_${this.selector.getType()}`),
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
				},
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
			`nameChanger_${this.selector.getId()}`,
			options,
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

	#searchItem(searchQuery: string = ''): void
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		const dialog = this.getDialog();
		dialog.getPopup().show();
		dialog.search(searchQuery);
	}
}
