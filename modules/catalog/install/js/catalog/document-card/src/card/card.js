import {Loc, Reflection, Tag} from "main.core";
import {BaseCard} from "catalog.entity-card";
import {EventEmitter} from "main.core.events";
import {Dialog} from 'ui.entity-selector';
import ControllersFactory from "../controllers-factory";
import ModelFactory from "../model/model-factory";
import FieldsFactory from "../editor-fields/fields-factory";
import {MenuManager} from "main.popup";
import {Text} from "main.core";
import ProductListController from "../product-list/controller";
import {Slider} from 'catalog.store-use'

class DocumentCard extends BaseCard
{
	static #instance;

	static #fieldFactory;
	static #modelFactory;
	static #controllersFactory;

	static initializeEntityEditorFactories()
	{
		DocumentCard.registerFieldFactory();
		DocumentCard.registerModelFactory();
		DocumentCard.registerDocumentControllersFactory();
	}

	constructor(id, settings)
	{
		super(id, settings);
		this.documentType = settings.documentType;
		this.isDocumentConducted = settings.documentStatus === 'Y';
		this.componentName = settings.componentName;
		this.signedParameters = settings.signedParameters;
		this.isConductLocked = settings.isConductLocked;
		this.masterSliderUrl = settings.masterSliderUrl;

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
		BX.UI.SidePanel.Wrapper.setParam("closeAfterSave", true);
		this.showNotificationOnClose = false;
	}

	static getInstance()
	{
		return DocumentCard.#instance;
	}

	initDocumentTypeSelector()
	{
		let documentTypeSelector = this.settings.documentTypeSelector;
		let documentTypeSelectorTypes = this.settings.documentTypeSelectorTypes;
		if (!documentTypeSelector || !documentTypeSelectorTypes)
		{
			return;
		}

		let menuItems = [];
		documentTypeSelectorTypes.forEach((type) => {
			menuItems.push({
				text: Loc.getMessage('DOC_TYPE_SHORT_' + type),
				onclick: (e) => {
					let slider = BX.SidePanel.Instance.getTopSlider();
					if (slider)
					{
						slider.url = BX.Uri.addParam(slider.getUrl(), {DOCUMENT_TYPE: type});
						slider.url = BX.Uri.removeParam(slider.url, ['firstTime']);
						slider.setFrameSrc();
					}
				},
			});
		});
		let popupMenu = MenuManager.create({
			id: 'document-type-selector',
			bindElement: documentTypeSelector,
			items: menuItems,
		});

		documentTypeSelector.addEventListener('click', e => {
			e.preventDefault();
			popupMenu.show();
		});
	}

	openMasterSlider()
	{
		let card = this;

		new Slider().open(
			this.masterSliderUrl,
			{
				data: {
					openGridOnDone: false,
				},
				events: {
					onCloseComplete: function(event) {
						let slider = event.getSlider();
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
					}
				}
			}
		);
	}

	adjustToolPanel()
	{
		return;
	}

	static registerDocumentControllersFactory()
	{
		DocumentCard.#controllersFactory = new ControllersFactory();
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
		if (Reflection.getClass('BX.UI.EntityEditor'))
		{
			return BX.UI.EntityEditor.getDefault();
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
	}

	subscribeToUserSelectorEvent()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorUser:openSelector', (event) => {
			let eventData = event.data[1];
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
						let fieldId = eventData.id;
						let selectedItem = onSelectEvent.data.item;
						let userData = {
							entityId: selectedItem.id,
							avatar: selectedItem.avatar,
							name: Text.encode(selectedItem.title.text),
						};

						if (this.entityId > 0)
						{
							let fields = {};
							fields[fieldId] = selectedItem.id;
							BX.ajax.runComponentAction(
								this.componentName,
								'save',
								{
									mode: 'class',
									signedParameters: this.signedParameters,
									data: {
										fields: fields,
									}
								}
							).then((result) => {
								eventData.callback(dialog, userData);
							});
						}
						else
						{
							eventData.callback(dialog, userData);
						}
					}
				},
			});
			dialog.show();
		});
	}

	subscribeToValidationFailedEvent()
	{
		EventEmitter.subscribe('BX.UI.EntityEditor:onFailedValidation', (event) => {
			EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {tabId: 'main'});
		});
		EventEmitter.subscribe('onProductsCheckFailed', (event) => {
			EventEmitter.emit('BX.Catalog.EntityCard.TabManager:onOpenTab', {tabId: 'tab_products'});
		});
	}

	subscribeToOnSaveEvent()
	{
		EventEmitter.subscribe('BX.UI.EntityEditor:onSave', (event) => {
			const eventEditor = event.data[0];
			const action = event.data[1]?.actionId;
			if (eventEditor && eventEditor._ajaxForm)
			{
				eventEditor._toolPanel?.clearErrors();

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

				let form = eventEditor._ajaxForms[action];
				if (form)
				{
					form.addUrlParams({
						documentType: this.documentType,
						isNewDocument: this.entityId <= 0 ? 'Y' : 'N',
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
				});
				this.isTabAnalyticsSent = true;
			}
		});
	}

	subscribeToDirectActionEvent()
	{
		EventEmitter.subscribe('BX.UI.EntityEditor:onDirectAction', (event) => {

			const eventEditor = event.data[0];

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

				event.data[0]._ajaxForms['CONDUCT'].addUrlParams({
					documentType: this.documentType,
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

			let editor = event?.data[0]?.sender;
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
			let editor = event?.data[0]?.sender;
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
									}
								}
							}
						],
					});
				}
			}
		});
	}

	validateControllers(controllers)
	{
		let validateResult = true;
		if (controllers instanceof Array)
		{
			controllers.forEach((controller) => {
				if (controller instanceof ProductListController)
				{
					if (!controller.validateProductList())
					{
						validateResult = false;
					}
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
			}
		);
	}

	addCopyLinkPopup()
	{
		let copyLinkButton = document.getElementById(this.settings.copyLinkButtonId);
		if (!copyLinkButton)
		{
			return;
		}

		copyLinkButton.onclick = () => {
			this.copyDocumentLinkToClipboard();
		}
	}

	copyDocumentLinkToClipboard()
	{
		let url = BX.util.remove_url_param(window.location.href, ["IFRAME", "IFRAME_TYPE"]);
		if(!BX.clipboard.copy(url))
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
				bindOptions: { position: "top" }
			}
		);
		popup.show();

		setTimeout(function(){ popup.close(); }, 1500);
	}

	static registerFieldFactory()
	{
		DocumentCard.#fieldFactory = new FieldsFactory();
	}

	static registerModelFactory()
	{
		DocumentCard.#modelFactory = new ModelFactory();
	}

	setSliderText()
	{
		let slider = BX.SidePanel.Instance.getTopSlider();
		if (slider)
		{
			slider.getLabel().setText(Loc.getMessage('SLIDER_LABEL_' + this.documentType));
		}
	}

	disableSaveAndConductButton()
	{
		if(!this.conductAndSaveButton)
		{
			return;
		}

		this.conductAndSaveButton.disabled = true;
		BX.addClass(this.conductAndSaveButton, 'ui-btn-disabled');
	}

	enableSaveAndConductButton()
	{
		if(!this.conductAndSaveButton)
		{
			return;
		}

		this.conductAndSaveButton.disabled = false;
		BX.removeClass(this.conductAndSaveButton, 'ui-btn-disabled');
	}
}

export default DocumentCard;
