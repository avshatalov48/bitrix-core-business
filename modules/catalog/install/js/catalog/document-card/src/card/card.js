import { BaseCard } from 'catalog.entity-card';
import { AnalyticsContextList, EnableWizardOpener } from 'catalog.store-enable-wizard';
import { Dom, Event, Loc, Reflection, Tag, Text, Type } from 'main.core';
import { type BaseEvent, EventEmitter } from 'main.core.events';
import { MenuManager, Popup } from 'main.popup';
import { Button } from 'ui.buttons';
import { Dialog } from 'ui.entity-selector';
import StoreDocumentFieldConfigurationManager from '../configurator/store-document-field-configurator-manager';
import ControllersFactory from '../controllers-factory';
import FieldsFactory from '../editor-fields/fields-factory';
import ModelFactory from '../model/model-factory';
import ProductListController from '../product-list/controller';

class DocumentCard extends BaseCard
{
	static #instance;

	static #fieldFactory;
	static #modelFactory;
	static #controllersFactory;

	constructor(id, settings)
	{
		super(id, settings);
		this.documentType = settings.documentType;
		this.isDocumentConducted = settings.documentStatus === 'Y';
		this.componentName = settings.componentName;
		this.signedParameters = settings.signedParameters;
		this.isConductLocked = settings.isConductLocked;
		this.masterSliderUrl = settings.masterSliderUrl;
		this.isInventoryManagementDisabled = settings.isInventoryManagementDisabled;
		this.inventoryManagementFeatureCode = settings.inventoryManagementFeatureCode;
		this.editorName = settings.includeCrmEntityEditor ? 'BX.Crm.EntityEditor' : 'BX.UI.EntityEditor';
		this.inventoryManagementSource = settings.inventoryManagementSource;
		this.lockedCancellation = settings.lockedCancellation || false;
		this.activeTabId = 'main';

		this.isTabAnalyticsSent = false;

		this.setSliderText();
		this.addCopyLinkPopup();
		this.subscribeToEvents();

		if (settings.documentTypeSelector)
		{
			this.initDocumentTypeSelector();
		}

		DocumentCard.#instance = this;

		// setting this to true so that we can decide
		// whether to close the slider or not on the fly on backend (closeOnSave=Y)
		BX.UI.SidePanel.Wrapper.setParam('closeAfterSave', true);
		this.showNotificationOnClose = false;
	}

	static getInstance()
	{
		return DocumentCard.#instance;
	}

	initDocumentTypeSelector()
	{
		const documentTypeSelector = this.settings.documentTypeSelector;
		const documentTypeSelectorTypes = this.settings.documentTypeSelectorTypes;
		if (!documentTypeSelector || !documentTypeSelectorTypes)
		{
			return;
		}

		const menuItems = [];
		documentTypeSelectorTypes.forEach((type) => {
			menuItems.push({
				text: Loc.getMessage(`DOC_TYPE_SHORT_${type}`),
				onclick: (e) => {
					const slider = BX.SidePanel.Instance.getTopSlider();
					if (slider)
					{
						slider.url = BX.Uri.addParam(slider.getUrl(), { DOCUMENT_TYPE: type });
						slider.url = BX.Uri.removeParam(slider.url, ['firstTime', 'focusedTab']);

						if (this.activeTabId !== 'main')
						{
							slider.url = BX.Uri.addParam(slider.getUrl(), { focusedTab: this.activeTabId });
						}

						if (type === 'A' || type === 'S')
						{
							slider.requestMethod = 'post';
							slider.requestParams = {
								preloadedFields: {
									DOCUMENT_FIELDS: this.getDocumentFieldsForTypeSwitching(),
									PRODUCTS: this.getProductsForTypeSwitching(),
								},
							};
						}

						slider.setFrameSrc();
					}
				},
			});
		});
		const popupMenu = MenuManager.create({
			id: 'document-type-selector',
			bindElement: documentTypeSelector,
			items: menuItems,
		});

		documentTypeSelector.addEventListener('click', (e) => {
			e.preventDefault();
			popupMenu.show();
		});
	}

	getDocumentFieldsForTypeSwitching()
	{
		const documentFields = {};
		const editor = this.getEditorInstance();
		if (!editor)
		{
			return documentFields;
		}

		const form = editor.getFormElement();
		const formData = new FormData(form);
		const formProps = Object.fromEntries(formData);

		const fieldsToTransfer = ['TITLE', 'CURRENCY', 'TOTAL'];
		fieldsToTransfer.forEach((field) => {
			documentFields[field] = formProps[field] ?? '';
		});

		return documentFields;
	}

	getProductsForTypeSwitching()
	{
		const products = [];
		if (!Reflection.getClass('BX.Catalog.Store.ProductList.Instance'))
		{
			return products;
		}

		const productFields = ['ID', 'STORE_TO', { ELEMENT_ID: 'SKU_ID' }, 'AMOUNT', 'PURCHASING_PRICE', 'BASE_PRICE', 'BASE_PRICE_EXTRA', 'BASE_PRICE_EXTRA_RATE'];
		BX.Catalog.Store.ProductList.Instance.getProductsFields().forEach((productRow) => {
			const product = {};
			productFields.forEach((field) => {
				if (Type.isObject(field))
				{
					const destinationField = Object.keys(field)[0];
					const sourceField = field[destinationField];
					product[destinationField] = productRow[sourceField] ?? '';
				}
				else
				{
					product[field] = productRow[field] ?? '';
				}
			});
			products.push(product);
		});

		return products;
	}

	openMasterSlider()
	{
		const card = this;

		new EnableWizardOpener().open(
			this.masterSliderUrl,
			{
				urlParams: {
					analyticsContextSection: AnalyticsContextList.DOCUMENT_CARD,
				},
				data: {
					openGridOnDone: false,
				},
				events: {
					onCloseComplete: function(event) {
						const slider = event.getSlider();
						if (!slider)
						{
							return;
						}

						if (slider.getData().get('isInventoryManagementEnabled'))
						{
							card.isConductLocked = false;

							BX.SidePanel.Instance.getOpenSliders().forEach((slider) => {
								if (slider.getWindow()?.BX.Catalog?.DocumentGridManager)
								{
									slider.allowChangeHistory = false;
									slider.getWindow().location.reload();
								}
							});
						}
					},
				},
			},
		);
	}

	adjustToolPanel()
	{}

	focusOnTab(tabId)
	{
		EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', { tabId: tabId });
	}

	// deprecated
	setViewModeButtons(editor)
	{
		editor._toolPanel.showViewModeButtons();
	}

	// deprecated
	setEditModeButtons(editor)
	{
		editor._toolPanel.showEditModeButtons();
	}

	getEditorInstance()
	{
		const editorInstance = Reflection.getClass(this.editorName);
		if (editorInstance)
		{
			return editorInstance.getDefault();
		}

		return null;
	}

	subscribeToEvents()
	{
		this.subscribeToUserSelectorEvent();
		this.subscribeToValidationFailedEvent();
		this.subscribeToOnSaveEvent();
		this.subscribeToTabOpenEvent();
		this.subscribeToDirectActionEvent();
		this.subscribeToEntityCreateEvent();
		this.subscribeToBeforeEntityRedirectEvent();
		this.subscribeToCreateUserFieldEvent();
		this.subscribeToFieldConfiguratorEvent();
	}

	subscribeToFieldConfiguratorEvent()
	{
		EventEmitter.subscribe('BX.UI.EntityConfigurationManager:onInitialize', this.onConfigurationManagerInit.bind(this));
	}

	onConfigurationManagerInit(event: BaseEvent)
	{
		const [, eventArgs] = event.getCompatData();

		if (!eventArgs.type || eventArgs.type === 'editor')
		{
			eventArgs.configurationFieldManager = StoreDocumentFieldConfigurationManager.create(this.id, eventArgs);
		}

		event.stopImmediatePropagation();
	}

	subscribeToCreateUserFieldEvent()
	{
		EventEmitter.subscribe('BX.UI.EntityConfigurationManager:onCreateClick', ((e) => {
			e.data.isCanceled = true;

			const editor = this.getEditorInstance();
			const createUrl = editor.getConfigurationFieldManager().getCreationPageUrl('custom');

			if (createUrl)
			{
				top.BX.SidePanel.Instance.open(createUrl);
			}
		}));
	}

	subscribeToUserSelectorEvent()
	{
		if (this.editorName !== 'BX.UI.EntityEditor')
		{
			return;
		}

		EventEmitter.subscribe('BX.UI.EntityEditorUser:openSelector', (event) => {
			const eventData = event.data[1];
			const dialog = new Dialog({
				targetNode: eventData.anchor,
				enableSearch: true,
				multiple: false,
				context: 'CATALOG_DOCUMENT',
				entities: [
					{
						id: 'user',
					},
					{
						id: 'department',
					},
				],
				events: {
					'Item:onSelect': (onSelectEvent) => {
						const fieldId = eventData.id;
						const selectedItem = onSelectEvent.data.item;
						const userData = {
							entityId: selectedItem.id,
							avatar: selectedItem.avatar,
							name: Text.encode(selectedItem.title.text),
						};

						if (this.entityId > 0)
						{
							const fields = {};
							fields[fieldId] = selectedItem.id;
							BX.ajax.runComponentAction(
								this.componentName,
								'save',
								{
									mode: 'class',
									signedParameters: this.signedParameters,
									data: {
										fields: fields,
									},
								},
							).then((result) => {
								eventData.callback(dialog, userData);
							});
						}
						else
						{
							eventData.callback(dialog, userData);
						}
					},
				},
			});
			dialog.show();
		});
	}

	subscribeToValidationFailedEvent()
	{
		EventEmitter.subscribe(`${this.editorName}:onFailedValidation`, (event) => {
			EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', { tabId: 'main' });
		});
		EventEmitter.subscribe('onProductsCheckFailed', (event) => {
			EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', { tabId: 'tab_products' });
		});
	}

	subscribeToOnSaveEvent()
	{
		EventEmitter.subscribe(`${this.editorName}:onSave`, (event) => {
			const eventEditor = event.data[0];
			const action = event.data[1]?.actionId;
			if (eventEditor && eventEditor._ajaxForm)
			{
				eventEditor._toolPanel?.clearErrors();

				if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
				{
					event.data[1].cancel = true;
					event.data[0]._toolPanel?.setLocked(false);
					top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

					return;
				}

				if (action === 'SAVE_AND_CONDUCT')
				{
					if (this.isConductLocked)
					{
						event.data[1].cancel = true;
						event.data[0]._toolPanel?.setLocked(false);
						this.openMasterSlider();

						return;
					}

					if (!this.validateControllers(eventEditor.getControllers()))
					{
						event.data[1].cancel = true;
						eventEditor._toolPanel?.setLocked(false);

						return;
					}

					if (event.data[1].cancel)
					{
						return;
					}
				}

				const form = eventEditor._ajaxForms[action];
				if (form)
				{
					form.addUrlParams({
						documentType: this.documentType,
						isNewDocument: this.entityId <= 0 ? 'Y' : 'N',
						inventoryManagementSource: this.inventoryManagementSource,
					});
				}
			}
		});
	}

	subscribeToTabOpenEvent()
	{
		EventEmitter.subscribe('BX.Catalog.EntityCard.TabManager:onSelectItem', (event) => {
			const tabId = event.data.tabId;
			if (tabId === 'tab_products' && !this.isTabAnalyticsSent)
			{
				this.sendAnalyticsData({
					tab: 'products',
					isNewDocument: this.entityId <= 0 ? 'Y' : 'N',
					documentType: this.documentType,
					inventoryManagementSource: this.inventoryManagementSource,
				});
				this.isTabAnalyticsSent = true;
			}

			if (tabId)
			{
				this.activeTabId = tabId;
			}
		});
	}

	subscribeToDirectActionEvent()
	{
		EventEmitter.subscribe(`${this.editorName}:onDirectAction`, (event) => {
			const eventEditor = event.data[0];

			if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode)
			{
				event.data[1].cancel = true;
				event.data[0]._toolPanel?.setLocked(false);
				top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);

				return;
			}

			if (event.data[1]?.actionId === 'CONDUCT')
			{
				eventEditor._toolPanel?.clearErrors();

				if (this.isConductLocked)
				{
					event.data[1].cancel = true;
					event.data[0]._toolPanel?.setLocked(false);
					this.openMasterSlider();

					return;
				}

				if (!this.validateControllers(eventEditor.getControllers()))
				{
					event.data[1].cancel = true;
					eventEditor._toolPanel?.setLocked(false);

					return;
				}

				event.data[0]._ajaxForms.CONDUCT.addUrlParams({
					documentType: this.documentType,
					inventoryManagementSource: this.inventoryManagementSource,
				});
			}

			if (event.data[1]?.actionId === 'CANCEL_CONDUCT')
			{
				if (this.isLockedCancellation())
				{
					this.showCancellationInfo();
					event.data[1].cancel = true;
					event.data[0]._toolPanel?.setLocked(false);

					return;
				}

				event.data[0]._ajaxForms.CANCEL_CONDUCT.addUrlParams({
					documentType: this.documentType,
					inventoryManagementSource: this.inventoryManagementSource,
				});
			}
		});
	}

	subscribeToEntityCreateEvent()
	{
		EventEmitter.subscribe('onEntityCreate', (event) => {
			window.top.BX.onCustomEvent('DocumentCard:onEntityCreate');
			BX.SidePanel.Instance.getOpenSliders().forEach((slider) => {
				if (slider.getWindow()?.BX.Catalog?.DocumentGridManager)
				{
					slider.getWindow().BX.onCustomEvent('DocumentCard:onEntityCreate');
				}
			});

			const editor = event?.data[0]?.sender;
			if (editor)
			{
				editor._toolPanel.disableSaveButton();
				editor.hideToolPanel();
			}
		});
	}

	subscribeToBeforeEntityRedirectEvent()
	{
		EventEmitter.subscribe('beforeEntityRedirect', (event) => {
			window.top.BX.onCustomEvent('DocumentCard:onBeforeEntityRedirect');
			BX.SidePanel.Instance.getOpenSliders().forEach((slider) => {
				slider.getWindow().BX.onCustomEvent('DocumentCard:onBeforeEntityRedirect');
			});
			const editor = event?.data[0]?.sender;
			if (editor)
			{
				editor._toolPanel.disableSaveButton();
				editor.hideToolPanel();

				this.showNotificationOnClose = event?.data[0]?.showNotificationOnClose === 'Y';

				if (this.showNotificationOnClose)
				{
					let url = event.data[0].redirectUrl;
					if (!url)
					{
						return;
					}
					url = BX.Uri.removeParam(url, 'closeOnSave');

					window.top.BX.UI.Notification.Center.notify({
						content: Loc.getMessage('DOCUMENT_CONDUCT_SUCCESSFUL'),
						actions: [
							{
								title: Loc.getMessage('DOCUMENT_CONDUCT_SUCCESSFUL_VIEW'),
								href: url,
								events: {
									click: function(event, balloon, action) {
										balloon.close();
									},
								},
							},
						],
					});
				}
			}
		});
	}

	validateControllers(controllers)
	{
		let validateResult = true;
		if (Array.isArray(controllers))
		{
			controllers.forEach((controller) => {
				if (controller instanceof ProductListController && !controller.validateProductList())
				{
					validateResult = false;
				}
			});
		}
		else
		{
			validateResult = false;
		}

		return validateResult;
	}

	sendAnalyticsData(data)
	{
		BX.ajax.runAction(
			'catalog.analytics.sendAnalyticsLabel',
			{
				analyticsLabel: data,
			},
		);
	}

	addCopyLinkPopup()
	{
		const copyLinkButton = document.getElementById(this.settings.copyLinkButtonId);
		if (!copyLinkButton)
		{
			return;
		}

		copyLinkButton.onclick = () => {
			this.copyDocumentLinkToClipboard();
		};
	}

	copyDocumentLinkToClipboard()
	{
		const url = BX.util.remove_url_param(window.location.href, ['IFRAME', 'IFRAME_TYPE']);
		if (!BX.clipboard.copy(url))
		{
			return;
		}

		var popup = new BX.PopupWindow(
			'catalog_copy_document_url_to_clipboard',
			document.getElementById(this.settings.copyLinkButtonId),
			{
				content: Loc.getMessage('DOCUMENT_LINK_COPIED'),
				darkMode: true,
				autoHide: true,
				zIndex: 1000,
				angle: true,
				bindOptions: { position: 'top' },
			},
		);
		popup.show();

		setTimeout(() => { popup.close(); }, 1500);
	}

	static registerFieldFactory()
	{
		DocumentCard.#fieldFactory = new FieldsFactory();
	}

	static registerModelFactory()
	{
		DocumentCard.#modelFactory = new ModelFactory();
	}

	static registerDocumentControllersFactory(eventName)
	{
		DocumentCard.#controllersFactory = new ControllersFactory(eventName);
	}

	setSliderText()
	{
		const slider = BX.SidePanel.Instance.getTopSlider();
		if (slider)
		{
			slider.getLabel().setText(Loc.getMessage(`SLIDER_LABEL_${this.documentType}`));
		}
	}

	disableSaveAndConductButton()
	{
		if (!this.conductAndSaveButton)
		{
			return;
		}

		this.conductAndSaveButton.disabled = true;
		BX.addClass(this.conductAndSaveButton, 'ui-btn-disabled');
	}

	enableSaveAndConductButton()
	{
		if (!this.conductAndSaveButton)
		{
			return;
		}

		this.conductAndSaveButton.disabled = false;
		BX.removeClass(this.conductAndSaveButton, 'ui-btn-disabled');
	}

	isLockedCancellation(): boolean
	{
		return this.lockedCancellation;
	}

	showCancellationInfo(): void
	{
		const popup = new Popup(null, null, {
			events: {
				onPopupClose: () => {
					popup.destroy();
				},
			},
			content: this.getCancellationPopupContent(),
			overlay: true,
			buttons: [
				new Button({
					text: Loc.getMessage('CANCEL_CONDUCT_CANCELLATION_POPUP_YES'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						this.lockedCancellation = false;

						this.getEditorInstance()?.performAction('CANCEL_CONDUCT');

						popup.close();
					},
				}),
				new BX.UI.Button({
					text: Loc.getMessage('CANCEL_CONDUCT_CANCELLATION_POPUP_NO'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						popup.close();
					},
				}),
			],
		});

		popup.show();
	}

	getCancellationPopupContent(): HTMLElement
	{
		const moreLink = Tag.render`<a href="#" class="ui-form-link">${Loc.getMessage('CANCEL_CONDUCT_CANCELLATION_POPUP_LINK')}</a>`;

		Event.bind(moreLink, 'click', () => {
			const articleId = 17858278;
			top.BX.Helper.show(`redirect=detail&code=${articleId}`);
		});

		const descriptionHtml = Tag.render`
			<div>${Loc.getMessage('CANCEL_CONDUCT_CANCELLATION_POPUP_HINT', {'#HELP_LINK#': '<help-link></help-link>'})}</div>
		`;

		Dom.replace(descriptionHtml.querySelector('help-link'), moreLink);

		return Tag.render`
			<div>
				<h3>${Loc.getMessage('CANCEL_CONDUCT_CANCELLATION_POPUP_TITLE')}</h3>
				<div>${Text.encode(Loc.getMessage('CANCEL_CONDUCT_CANCELLATION_POPUP_QUESTION'))}
				<br>${descriptionHtml}<div>
			</div>
		`;
	}
}

export default DocumentCard;
