import {Extension, Loc, Tag, Type, Text, Dom, Event, userOptions, ajax} from 'main.core';
import {Dialog, Item} from 'ui.entity-selector';
import './component.css';
import {EventEmitter} from 'main.core.events';
import {ProductModel} from 'catalog.product-model';
import ProductCreationLimitedFooter from './product-creation-limited-footer';
import {ProductSearchInput} from './product-search-input';
import {ProductSelector} from 'catalog.product-selector';
import {BarcodeScanner} from 'catalog.barcode-scanner';
import 'ui.notification';
import {BarcodeSearchSelectorFooter} from './barcode-search-selector-footer';
import {QrAuthorization} from 'ui.qrauthorization';
import {SelectorErrorCode} from './selector-error-code';
import 'spotlight';
import {Guide} from 'ui.tour';

export class BarcodeSearchInput extends ProductSearchInput
{
	static SEARCH_TYPE_ID = 'barcode';

	onFocusHandler = this.handleFocusEvent.bind(this);
	onBlurHandler = this.handleBlurEvent.bind(this);

	constructor(id, options = {})
	{
		super(id, options);

		this.focused = false;
		this.settingsCollection = Extension.getSettings('catalog.product-selector');

		this.isInstalledMobileApp =
			this.selector.getConfig('IS_INSTALLED_MOBILE_APP')
			|| this.settingsCollection.get('isInstallMobileApp')
		;

		if (
			!this.settingsCollection.get('isEnabledQrAuth')
			&& this.selector.getConfig('ENABLE_BARCODE_QR_AUTH', true)
		)
		{
			this.qrAuth = new QrAuthorization();
			this.qrAuth.createQrCodeImage();
		}
	}

	destroy()
	{
		Event.unbind(this.getNameInput(), 'focus', this.onFocusHandler);
		Event.unbind(this.getNameInput(), 'blur', this.onBlurHandler);
	}

	handleFocusEvent()
	{
		this.focused = true;
	}

	handleBlurEvent()
	{
		this.focused = false;
	}

	isSearchEnabled(): boolean
	{
		return true;
	}

	showDetailLink(): boolean
	{
		return false
	}

	getNameBlock(): HTMLElement
	{
		return this.cache.remember('nameBlock', () => {
			return Tag.render`
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${this.getNameInput()}
					${this.getHiddenNameInput()}
				</div>
			`;
		});
	}

	getDialog(): ?Dialog
	{
		return this.cache.remember('dialog', () => {
			const entity = {
				id: BarcodeSearchInput.SEARCH_TYPE_ID,
				options: {
					iblockId: this.model.getIblockId(),
					basePriceId: this.model.getBasePriceId(),
					currency: this.model.getCurrency(),
				},
				dynamicLoad: true,
				dynamicSearch: true,
				searchFields: [
					{ name: 'title', type: 'string', system: true, searchable: false },
				],
			};

			const restrictedProductTypes = this.selector.getConfig('RESTRICTED_PRODUCT_TYPES', null);
			if (!Type.isNil(restrictedProductTypes))
			{
				entity.options.restrictedProductTypes = restrictedProductTypes;
			}

			const params = {
				id: this.id + '_' + BarcodeSearchInput.SEARCH_TYPE_ID,
				height: 300,
				width: Math.max(this.getNameInput()?.offsetWidth, 565),
				context: null,
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

			if (this.model.getSkuId() && !Type.isStringFilled(this.model.getField(this.inputName)))
			{
				params.preselectedItems = [[BarcodeSearchInput.SEARCH_TYPE_ID, this.model.getSkuId()]];
			}

			if (Type.isObject(this.settingsCollection.get('limitInfo')))
			{
				params.footer = ProductCreationLimitedFooter;
			}
			else
			{
				params.footer = BarcodeSearchSelectorFooter;
				params.footerOptions = {
					inputEntity: this,
					isEmptyBarcode: !this.model || !this.model.isCatalogExisted(),
					inputName: this.inputName,
					errorAdminHint: this.settingsCollection.get('errorAdminHint'),
					allowEditItem: this.isAllowedEditProduct(),
					allowCreateItem: this.isAllowedCreateProduct(),
					creationLabel: Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE_WITH_BARCODE'),
					currentValue: this.getValue(),
					searchOptions: {
						allowCreateItem: this.isAllowedCreateProduct(),
						footerOptions: {
							label: Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE_WITH_BARCODE')
						},
					}
				};
			}

			return new Dialog(params);
		});
	}

	layoutMobileQrPopup(): HTMLElement
	{
		return this.cache.remember('qrMobilePopup', () => {

			const closeIcon = Tag.render`<span class="popup-window-close-icon"></span>`;
			Event.bind(closeIcon, 'click', this.closeMobilePopup.bind(this));

			let sendButton = '';
			let helpButton = '';
			if (top.BX.Helper)
			{
				helpButton = Tag.render`
					<a class="product-selector-mobile-popup-link ui-btn ui-btn-light-border ui-btn-round">
						${Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_HELP_BUTTON')}
					</a>
				`;
				Event.bind(helpButton, 'click', () => {
					top.BX.Helper.show("redirect=detail&code=14956818");
				});

				sendButton = Tag.render`
					<a class="product-selector-mobile-popup-link ui-btn ui-btn-link">
						${Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_SEND_PUSH_BUTTON')}
					</a>
				`;

				Event.bind(sendButton, 'click', () => {
					top.BX.Helper.show("redirect=detail&code=15042444");
				});
			}

			return Tag.render`
				<div data-role="mobile-popup">
					<div class="product-selector-mobile-popup-overlay"></div>
					<div class="product-selector-mobile-popup-content">
						<div class="product-selector-mobile-popup-title">${Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_TITLE')}</div>
						<div class="product-selector-mobile-popup-text">${Loc.getMessage('CATALOG_SELECTOR_MOBILE_POPUP_INSTRUCTION')}</div>
						<div class="product-selector-mobile-popup-qr">
							${this.qrAuth.getQrNode()}
						</div>
						<div class="product-selector-mobile-popup-link-container">
							${helpButton}
							${sendButton}
						</div>
						${closeIcon}
					</div>
				</div>
			`;
		});
	}

	closeMobilePopup()
	{
		this.removeQrAuth();

		ajax
			.runAction(
				'catalog.ProductSelector.isInstalledMobileApp',
				{
					json: {}
				}
			)
			.then((result) => {
				this.selector.emit('onBarcodeQrClose', {});

				if (result.data === true)
				{
					this.selector.emit('onBarcodeScannerInstallChecked', {});
					this.isInstalledMobileApp = true;
				}
			})
		;

		userOptions.save('product-selector', 'barcodeQrAuth', 'showed', 'Y');
	}

	handleClickNameInput(event: UIEvent)
	{
		if (this.qrAuth && this.getDialog().getContainer())
		{
			if (!Dom.hasClass(this.getDialog().getContainer(), 'qr-barcode-info'))
			{
				Dom.addClass(this.getDialog().getContainer(),'qr-barcode-info');
			}

			if (this.getDialog().getContainer())
			{
				Dom.append(this.layoutMobileQrPopup(), this.getDialog().getContainer());
			}
		}

		super.handleClickNameInput(event);
	}

	showItems()
	{
		this.searchInDialog();
	}

	onChangeValue(value: string)
	{
		const fields = {};

		this.getNameInput().title = value;
		this.getNameInput().value = value;

		fields[this.inputName] = value;

		EventEmitter.emit('ProductSelector::onBarcodeChange', {
			rowId: this.selector.getRowId(),
			fields
		});

		this.selector.emit('onBarcodeChange', {value});

		if (this.selector.isEnabledAutosave())
		{
			this.selector.getModel().setField(this.inputName, value);
			this.selector.getModel().showSaveNotifier(
				'barcodeChanger_' + this.selector.getId(),
				{
					title: Loc.getMessage('CATALOG_SELECTOR_SAVING_NOTIFICATION_BARCODE'),
					disableCancel: true,
					events: {
						onSave: () => {
							if (this.selector)
							{
								this.selector.getModel().save([this.inputName]);
							}
						}
					},
				}
			);
		}
	}

	searchInDialog(): void
	{
		const searchQuery = this.getFilledValue().trim();
		this.searchByBarcode(searchQuery);
	}

	searchByBarcode(searchQuery: string = ''): void
	{
		if (!this.selector.isProductSearchEnabled())
		{
			return;
		}

		const dialog = this.getDialog();
		if (!dialog)
		{
			return;
		}

		dialog.removeItems();
		if (!Type.isStringFilled(searchQuery))
		{
			if (this.model && this.model.isCatalogExisted())
			{
				dialog.setPreselectedItems([[BarcodeSearchInput.SEARCH_TYPE_ID, this.model.getSkuId()]])
				dialog.loadState = 'UNSENT';
				dialog.load();
			}
		}

		dialog.show();
		dialog.search(searchQuery);
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
				this.toggleIcon(
					this.getSearchIcon(),
					Type.isStringFilled(this.getFilledValue()) ? 'none' : 'block'
				);
			}
		}, 200);
	}

	setInputValueOnProductSelect(item: Item)
	{
		item.getDialog().getTargetNode().value = item.getSubtitle()
	}

	getCreationProduct(name)
	{
		const fields = {...this.selector.getModel().getFields()};
		fields[ProductSelector.INPUT_FIELD_NAME] = name;

		return new ProductModel({
			isSimpleModel: true,
			isNew: true,
			currency: this.selector.options.currency,
			iblockId: this.selector.getModel().getIblockId(),
			basePriceId: this.selector.getModel().getBasePriceId(),
			fields
		})
	}

	createProductModelFromSearchQuery(searchQuery: string)
	{
		const model = super.createProductModelFromSearchQuery(searchQuery);
		model.setField(ProductSelector.INPUT_FIELD_NAME, Loc.getMessage('CATALOG_SELECTOR_NEW_BARCODE_PRODUCT_NAME'));
		model.setField(this.inputName, searchQuery);

		return model;
	}

	checkCreationModel(creationModel: ProductModel): boolean
	{
		if (!Type.isStringFilled(creationModel.getField(ProductSelector.INPUT_FIELD_NAME)))
		{
			this.model.getErrorCollection().setError(
				SelectorErrorCode.NOT_SELECTED_PRODUCT,
				Loc.getMessage('CATALOG_SELECTOR_EMPTY_TITLE')
			);

			return false;
		}

		return true;
	}

	getPlaceholder(): string
	{
		return (
			this.isSearchEnabled() && this.model.isEmpty()
				? Loc.getMessage('CATALOG_SELECTOR_BEFORE_SEARCH_BARCODE_TITLE')
				: Loc.getMessage('CATALOG_SELECTOR_VIEW_BARCODE_TITLE')
		);
	}

	handleClearIconClick(event: UIEvent)
	{
		this.toggleIcon(this.getClearIcon(), 'none');
		this.onChangeValue('');

		this.selector.focusName();

		event.stopPropagation();
		event.preventDefault();
	}

	startMobileScanner(event)
	{
		if (this.isInstalledMobileApp)
		{
			this.sendMobilePush(event);

			return;
		}

		if (!this.qrAuth)
		{
			this.qrAuth = new QrAuthorization();
			this.qrAuth.createQrCodeImage();
		}

		if (this.getDialog().isOpen())
		{
			this.getDialog().hide();
			this.getDialog().subscribeOnce('onHide', this.handleClickNameInput.bind(this));
		}
		else
		{
			this.handleClickNameInput(event);
		}
	}

	sendMobilePush(event)
	{
		event?.preventDefault();
		this.getDialog().hide();
		this.getNameInput().focus();

		if (!this.selector.isEnabledMobileScanning())
		{
			return;
		}

		const token = this.selector.getMobileScannerToken();
		BarcodeScanner.open(token);

		const repeatLink = Tag.render`<span class='ui-notification-balloon-action'>${Loc.getMessage('CATALOG_SELECTOR_SEND_PUSH_ON_SCANNER_NOTIFICATION_REPEAT')}</span>`;
		Event.bind(repeatLink, 'click', this.sendMobilePush.bind(this));

		const content = Tag.render`
			<div>
				<span>${Loc.getMessage('CATALOG_SELECTOR_SEND_PUSH_ON_SCANNER_NOTIFICATION')}</span>
				${repeatLink}
			</div>
		`;

		BX.UI.Notification.Center.notify({
			content,
			category: 'sending_push_barcode_scanner_notification',
			autoHideDelay: 5000,
		});
	}

	getProductIdByBarcode(barcode)
	{
		return ajax.runAction(
			'catalog.ProductSelector.getProductIdByBarcode',
			{
				json: {
					barcode: barcode,
				}
			}
		)
	}

	applyScannerData(barcode)
	{
		this.getProductIdByBarcode(barcode).then((response) => {
			const productId = response?.data;
			if (productId)
			{
				this.selectScannedBarcodeProduct(productId);
			}
			else
			{
				this.searchByBarcode(barcode);
			}
			this.getNameInput().value = Text.encode(barcode);
		});
	}

	selectScannedBarcodeProduct(productId)
	{
		this.toggleIcon(this.getSearchIcon(), 'none');
		this.clearErrors();
		if (this.selector)
		{
			this.selector.onProductSelect(
				productId,
				{
					isNew: false,
					immutableFields: [],
				}
			);

			this.selector.clearLayout();
			this.selector.layout();
		}

		this.cache.delete('dialog');
	}

	getBarcodeIcon(): HTMLElement
	{
		return this.cache.remember('barcodeIcon', () => {
			const barcodeIcon = Tag.render`
				<button	class="ui-ctl-before warehouse-barcode-icon" title="${Loc.getMessage('CATALOG_SELECTOR_BARCODE_ICON_TITLE')}"></button>
			`;

			if (
				!this.settingsCollection.get('isShowedBarcodeSpotlightInfo')
				&& this.settingsCollection.get('isAllowedShowBarcodeSpotlightInfo')
				&& this.selector.getConfig('ENABLE_INFO_SPOTLIGHT', true)
			)
			{
				this.spotlight = new BX.SpotLight(
					{
						id: 'selector_barcode_scanner_info',
						targetElement: barcodeIcon,
						autoSave: true,
						targetVertex: "middle-center",
						zIndex: 200
					}
				);

				this.spotlight.show();

				EventEmitter.subscribe(this.spotlight, 'BX.SpotLight:onTargetEnter', () => {
					const guide = new Guide({
						steps: [
							{
								target: barcodeIcon,
								title: Loc.getMessage('CATALOG_SELECTOR_BARCODE_SCANNER_FIRST_TIME_HINT_TITLE'),
								text: Loc.getMessage('CATALOG_SELECTOR_BARCODE_SCANNER_FIRST_TIME_HINT_TEXT'),
							},
						],
						onEvents: true,
					});

					guide.getPopup().setAutoHide(true);
					guide.showNextStep();
					this.selector.setConfig('ENABLE_INFO_SPOTLIGHT', false);
					this.selector.emit('onSpotlightClose', {});
				});
			}

			Event.bind(barcodeIcon, 'click', (event) => {
				event.preventDefault();
				if (this.qrAuth)
				{
					this.handleClickNameInput(event);
				}
				else
				{
					this.startMobileScanner(event);
				}
			});

			return barcodeIcon;
		});
	}

	layout(): HTMLElement
	{
		const block = super.layout();
		Dom.append(this.getBarcodeIcon(), block);
		this.getNameInput().className += ' catalog-product-field-input-barcode';
		Event.bind(this.getNameInput(), 'focus', this.onFocusHandler);
		Event.bind(this.getNameInput(), 'blur', this.onBlurHandler);

		return block;
	}

	removeSpotlight()
	{
		if (this.spotlight)
		{
			this.spotlight.close();
		}
	}

	removeQrAuth()
	{
		const mobilePopup = this.getDialog().getContainer()?.querySelector('[data-role="mobile-popup"]');
		if (mobilePopup)
		{
			Dom.remove(mobilePopup);
			if (Dom.hasClass(this.getDialog().getContainer(), 'qr-barcode-info'))
			{
				Dom.removeClass(this.getDialog().getContainer(),'qr-barcode-info');
			}
		}

		this.qrAuth = null;
	}
}
