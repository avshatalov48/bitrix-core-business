import { Extension, Loc, Text } from 'main.core';
import { Item, Tab } from 'ui.entity-selector';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { DialogMode } from './dialog-mode';
import { ProductSearchInputPlacementFooterLock } from './footer-placement-lock';
import { ProductSearchInputPlacementFooterFailure } from './footer-placement-failure';
import { ProductSearchInputPlacementFooterLoading } from './footer-placement-loading';
import { ProductSearchInputPlacementFooterSuccess } from './footer-placement-success';
import { ProductSearchInputBase } from './input-base';
import { OneCPlanRestrictionSlider } from 'catalog.tool-availability-manager';
import { ExternalCatalogPlacement } from 'catalog.external-catalog-placement';
import 'ui.notification';

export class ProductSearchInputPlacement extends ProductSearchInputBase
{
	#searchTimer = null;
	#productCreateTimer = null;
	#settingsCollection: Object = {};

	constructor(id, options = {})
	{
		super(id, options);

		this.#settingsCollection = Extension.getSettings('catalog.product-selector');

		EventEmitter.subscribe(
			'Catalog:ProductSelectorPlacement:onProductCreated',
			this.#onProductCreated.bind(this),
		);
		EventEmitter.subscribe(
			'Catalog:ProductSelectorPlacement:onProductsFound',
			this.#onProductsFound.bind(this),
		);

		this.#initializePlacement().catch(() => {});
	}

	isSearchEnabled(): boolean
	{
		return true;
	}

	onDialogShow(event: BaseEvent): void
	{
		this.#initializePlacement().catch(() => {});
	}

	getDialogParams(): Object
	{
		return {
			...super.getDialogParams(),
			...this.#getDialogParamsFooter(),
			searchOptions: {
				allowCreateItem: false,
			},
			searchTabOptions: {
				stub: true,
				stubOptions: {
					title: Loc.getMessage('CATALOG_SELECTOR_IS_EMPTY_TITLE'),
					subtitle: '',
					arrow: false,
				},
			},
			recentTabOptions: {
				stub: true,
				stubOptions: {
					title: Loc.getMessage('CATALOG_SELECTOR_1C_RECENT_TAB_SEARCH_TITLE'),
					subtitle: Loc.getMessage('CATALOG_SELECTOR_1C_RECENT_TAB_SEARCH_SUBTITLE'),
				},
			},
		};
	}

	searchInDialog(): void
	{
		this.getDialog().getPopup().show();
		this.#initializePlacement()
			.then(() => this.searchInDialogActual())
			.catch(() => {});
	}

	searchInDialogActual()
	{
		const dialog = this.getDialog();

		dialog.getPopup().show();

		if (this.isSearchQueryEmpty())
		{
			this.clear();
			dialog.selectTab(this.getDialog().getRecentTab().getId());
			this.showItems();
		}
		else
		{
			this.dialogMode = DialogMode.SEARCHING;
			dialog.selectTab(dialog.getSearchTab().getId());
			dialog.getSearchTab().getStub().hide();
			this.#initializePlacement()
				.then(() => this.#searchInExternalCatalog())
				.catch(() => {});
		}
	}

	handleClickNameInput(): void
	{
		if (this.#settingsCollection.is1cPlanRestricted)
		{
			OneCPlanRestrictionSlider.show();

			return;
		}

		this.getDialog().getPopup().show();
		this.#initializePlacement()
			.then(() => this.showItems())
			.catch(() => {});
	}

	getPlaceholder(): string
	{
		return Loc.getMessage('CATALOG_SELECTOR_1C_INPUT_PLACEHOLDER');
	}

	getOnProductSelectConfig(item: Item): Object
	{
		return {
			needExternalUpdate: item.getCustomData().get('needExternalUpdate'),
		};
	}

	onProductSelect(event: BaseEvent): void
	{
		const item: Item = event.getData().item;

		if (
			event.getTarget() === this.getDialog()
			&& item.getCustomData().has('appSid')
		)
		{
			this.clearErrors();
			this.selector.emitOnProductSelectEvents();
			this.#onExternalCatalogProductSelect(item);

			return;
		}

		super.onProductSelect(event);
	}

	isFooterHidable(): boolean
	{
		return false;
	}

	#onExternalCatalogProductSelect(item: Item): void
	{
		if (this.#productCreateTimer)
		{
			return;
		}

		const returnEventData = {
			rowId: this.selector.getRowId(),
		};
		EventEmitter.emit('Catalog:ProductSelectorPlacement:onNeedProductCreate', {
			appSid: item.getCustomData().get('appSid'),
			productId: item.id,
			returnEventData,
		});

		this.#productCreateTimer = setTimeout(() => {
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_RESPONDING_ERROR'),
				autoHide: true,
				autoHideDelay: 4000,
			});

			this.#onProductCreated(
				new BaseEvent({
					data: {
						...returnEventData,
						createdProduct: null,
					},
				}),
			);
		}, ExternalCatalogPlacement.RESPONSE_TIMEOUT);
	}

	#onProductsFound(event: BaseEvent): void
	{
		const { rowId, searchResults, searchQuery } = event.getData();
		if (rowId !== this.selector.getRowId())
		{
			return;
		}

		this.#clearSearchTimer();

		if (searchQuery !== this.getSearchQuery())
		{
			return;
		}

		const dialog = this.getDialog();
		dialog.selectTab(dialog.getSearchTab().getId());

		if (searchResults.length === 0)
		{
			this.#renderStub(
				this.getDialog().getSearchTab(),
				{
					title: Loc.getMessage('CATALOG_SELECTOR_IS_EMPTY_TITLE'),
					subtitle: '',
					arrow: false,
				},
			);
		}

		for (const searchResultItem of searchResults)
		{
			dialog.addItem({
				id: searchResultItem.id,
				title: searchResultItem.name,
				avatar: '/bitrix/js/catalog/product-selector/images/icon1C.png',
				entityId: 'product',
				tabs: dialog.getSearchTab().getId(),
				customData: {
					appSid: this.selector.placement.getAppSidId(),
				},
			});
		}

		this.#hideSearchLoader();
		this.#toggleEmptyResult();

		this.getDialog().setFooter(ProductSearchInputPlacementFooterSuccess);
	}

	#clearSearchTimer(): void
	{
		clearTimeout(this.#searchTimer);
		this.#searchTimer = null;
	}

	#onProductCreated(event: BaseEvent): void
	{
		if (this.#productCreateTimer === null)
		{
			return;
		}

		const { rowId, createdProduct } = event.getData();
		if (rowId !== this.selector.getRowId())
		{
			return;
		}

		const dialog = this.getDialog();

		const createdProductId = Text.toNumber(createdProduct?.id);
		const item = new Item({
			id: createdProductId || 0,
			entityId: 'product',
			title: createdProduct?.title || '',
			customData: {
				needExternalUpdate: false,
			},
		});
		item.setDialog(dialog);

		if (createdProductId > 0)
		{
			dialog.saveRecentItem(item);
		}

		this.onProductSelect(
			new BaseEvent({
				data: {
					item,
				},
			}),
		);

		dialog.removeItems();
		dialog.hide();

		clearTimeout(this.#productCreateTimer);
		this.#productCreateTimer = null;
	}

	#showSearchLoader(): void
	{
		const searchLoader = this.getDialog().getSearchTab().getSearchLoader();

		searchLoader.show();
		searchLoader.getTextContainer().textContent = Loc.getMessage('CATALOG_SELECTOR_1C_SEARCH');
	}

	#hideSearchLoader(): void
	{
		this.getDialog().getSearchTab().getSearchLoader().hide();
	}

	#toggleEmptyResult(): void
	{
		this.getDialog().getSearchTab().toggleEmptyResult();
	}

	#searchInExternalCatalog(): void
	{
		this.#clearSearchTimer();
		this.#showSearchLoader();
		this.getDialog().removeItems();

		EventEmitter.emit('Catalog:ProductSelectorPlacement:onNeedSearchProducts', {
			appSid: this.selector.placement.getAppSidId(),
			searchQuery: this.getSearchQuery(),
			returnEventData: {
				rowId: this.selector.getRowId(),
				searchQuery: this.getSearchQuery(),
			},
		});

		this.#searchTimer = setTimeout(() => {
			this.#clearSearchTimer();
			this.#hideSearchLoader();
			this.#toggleEmptyResult();

			this.getDialog().setFooter(
				ProductSearchInputPlacementFooterFailure,
				{
					text: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_RESPONDING'),
				},
			);
			this.#renderStub(
				this.getDialog().getSearchTab(),
				{
					title: Loc.getMessage('CATALOG_SELECTOR_1C_RECENT_TAB_NO_RESPONSE_TITLE'),
					subtitle: Loc.getMessage('CATALOG_SELECTOR_1C_RECENT_TAB_NO_RESPONSE_SUBTITLE').replace('[break]', '<br>'),
					arrow: true,
				},
			);

			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_RESPONDING_ERROR'),
				autoHide: true,
				autoHideDelay: 4000,
			});
		}, ExternalCatalogPlacement.RESPONSE_TIMEOUT);
	}

	#getDialogParamsFooter(): Object
	{
		let footer = ProductSearchInputPlacementFooterLoading;
		let footerOptions = {};

		if (this.selector.placement.isInitialized())
		{
			footer = this.selector.placement.isInitializedSuccessfully()
				? ProductSearchInputPlacementFooterSuccess
				: ProductSearchInputPlacementFooterFailure
			;
			if (this.selector.placement.isInitializedSuccessfully())
			{
				footer = ProductSearchInputPlacementFooterSuccess;
			}
			else
			{
				footer = ProductSearchInputPlacementFooterFailure;
				footerOptions = {
					text: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_CONNECTED'),
				};
			}
		}

		return {
			footer,
			footerOptions,
		};
	}

	#initializePlacement(): Promise
	{
		return new Promise((resolve, reject) => {
			this.selector.placement.initialize()
				.then(() => {
					this.getDialog().setFooter(ProductSearchInputPlacementFooterSuccess);

					resolve();
				})
				.catch((error) => {
					this.#renderStub(
						this.getDialog().getRecentTab(),
						{
							title: Loc.getMessage('CATALOG_SELECTOR_1C_RECENT_TAB_INIT_FAILURE_TITLE'),
							subtitle: Loc.getMessage('CATALOG_SELECTOR_1C_RECENT_TAB_INIT_FAILURE_SUBTITLE')
								.replace('[break]', '<br>'),
							arrow: true,
						},
					);

					if (error?.reason === 'tariff')
					{
						this.getDialog().setFooter(
							ProductSearchInputPlacementFooterLock,
							{ text: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_CONNECTED') },
						);
					}
					else
					{
						this.getDialog().setFooter(
							ProductSearchInputPlacementFooterFailure,
							{ text: Loc.getMessage('CATALOG_SELECTOR_1C_NOT_CONNECTED') },
						);
					}

					reject();
				});
		});
	}

	loadPreselectedItems(): void
	{
		this.selector.placement.initialize().then(() => super.loadPreselectedItems()).catch(() => {});
	}

	#renderStub(tab: Tab, stubOptions: Object)
	{
		this.getDialog().removeItems();

		tab.getStub().hide();
		tab.setStub(true, stubOptions);
		tab.getStub().show();
	}
}
